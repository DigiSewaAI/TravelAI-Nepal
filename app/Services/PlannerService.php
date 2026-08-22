<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Service;
use App\Models\PlannerRequest;
use App\Models\PlannerResult;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PlannerService
{
    protected LlmService $llm;
    protected ItineraryValidator $validator;

    public function __construct(LlmService $llm, ItineraryValidator $validator)
    {
        $this->llm = $llm;
        $this->validator = $validator;
    }

    public function generate(array $input): array
    {
        // 1. Resolve route
        $route = $this->resolveRoute($input['destination'] ?? null);
        if (!$route) {
            throw ValidationException::withMessages(['destination' => 'Route not found.']);
        }

        $route->load(['segments.fromWaypoint', 'segments.toWaypoint', 'costs']);

        // 2. Ensure segments (for tours)
        if ($route->segments->isEmpty()) {
            $this->ensureSegmentsForTour($route);
            $route->load(['segments.fromWaypoint', 'segments.toWaypoint']);
        }

        // 3. Get relevant services (filtered by location, category, style, budget)
        $availableServices = $this->getServicesForRoute($route, $input);

        // 4. Calculate cost with style + budget consideration
        $costBreakdown = $this->calculateCost($route, $input, $availableServices);

        // 5. Build context for LLM (includes service pool)
        $context = $this->buildContext($route, $input, $costBreakdown, $availableServices);

        // 6. Try AI, fallback if needed
        $aiResponse = null;
        $usedFallback = false;
        try {
            $prompt = $this->buildPrompt($context, $input);
            $aiResponse = $this->llm->generateItinerary($prompt);
            Log::info('AI itinerary generated successfully.');
        } catch (\Exception $e) {
            Log::error('AI generation failed, using fallback.', ['error' => $e->getMessage()]);
            $aiResponse = $this->buildFallbackResponse($route, $input);
            $usedFallback = true;
        }

        // 7. Validate and normalize
        $validated = $this->validator->validate($aiResponse, $route, $input, $context);

        // 8. Save to DB
        $result = DB::transaction(function () use ($input, $route, $validated, $aiResponse, $usedFallback, $costBreakdown) {
            $plannerRequest = PlannerRequest::create([
                'user_id' => auth()->id() ?? null,
                'session_id' => session()->getId(),
                'route_id' => $route->id,
                'destination' => $route->name,
                'days' => $input['days'],
                'budget' => $input['budget'],
                'travel_style' => $input['travel_style'] ?? 'mid_range',
                'interests' => $input['interests'] ?? [],
            ]);

            $plannerResult = PlannerResult::create([
                'request_id' => $plannerRequest->id,
                'raw_ai_response' => $aiResponse,
                'model' => config('services.groq.model', 'qwen/qwen3.6-27b'),
                'model_version' => 'latest',
                'prompt_version' => 'v3', // version bump
                'route_snapshot' => [
                    'route_id' => $route->id,
                    'name' => $route->name,
                    'segments' => $route->segments->toArray(),
                ],
                'validation_status' => $usedFallback ? 'fallback' : 'valid',
                'fallback_used' => $usedFallback,
                'validation_errors' => null,
            ]);

            foreach ($validated['days'] as $dayData) {
                $day = ItineraryDay::create([
                    'result_id' => $plannerResult->id,
                    'day_number' => $dayData['day_number'],
                    'title' => $dayData['title'],
                    'description' => $dayData['description'] ?? null,
                    'overnight_waypoint_id' => $dayData['overnight_waypoint_id'] ?? null,
                    'distance_km' => $dayData['distance_km'] ?? null,
                    'estimated_time_hours' => $dayData['estimated_time_hours'] ?? null,
                    'altitude_m' => $dayData['altitude_m'] ?? null,
                ]);

                foreach ($dayData['items'] as $itemData) {
                    ItineraryItem::create([
                        'day_id' => $day->id,
                        'title' => $itemData['title'],
                        'description' => $itemData['description'] ?? null,
                        'time_of_day' => $itemData['time_of_day'] ?? null,
                        'cost' => $itemData['cost'] ?? null,
                        'currency' => 'NPR',
                        'pricing_source' => $itemData['pricing_source'] ?? 'system_estimate',
                        'pricing_snapshot' => $itemData['pricing_snapshot'] ?? null,
                        'service_id' => $itemData['service_id'] ?? null,
                        'is_optional' => $itemData['is_optional'] ?? false,
                        'metadata' => $itemData['metadata'] ?? null,
                    ]);
                }
            }

            return [
                'request' => $plannerRequest,
                'result' => $plannerResult,
                'days' => $plannerResult->days()->with('items')->get(),
                'total_cost' => $costBreakdown['total'] ?? 0,
                'breakdown' => $costBreakdown['breakdown'] ?? [],
            ];
        });

        return $result;
    }

    // ==========================================
    //  SERVICE FILTERING (LOCATION + STYLE + BUDGET)
    // ==========================================

    protected function getServicesForRoute(Route $route, array $input): array
{
    $style = $input['travel_style'] ?? 'mid_range';
    $budget = $input['budget'] ?? 0;
    $days = $input['days'] ?? $route->duration_days;

    // Base query: active services from allowed categories
    $query = Service::where('status', 'active')
        ->whereHas('category', function ($q) {
            $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
        });

    // 🔁 Try with location filter first (if route has location_id)
    $services = collect();
    // ✅ Location filter हटाइयो, सबै active services लिने
$services = $query->get();

    // Style-based price thresholds (USD)
    $priceRanges = [
        'budget'     => ['max' => 30,  'prefer' => 'lowest'],
        'backpacker' => ['max' => 50,  'prefer' => 'low'],
        'mid_range'  => ['max' => 120, 'prefer' => 'medium'],
        'luxury'     => ['max' => 500, 'prefer' => 'high'],
    ];

    $range = $priceRanges[$style] ?? $priceRanges['mid_range'];

    // Filter by budget (if budget is given) and style max
    $filtered = [];
    foreach ($services as $service) {
        $price = (float) $service->price;
        // Convert to NPR if needed (for comparison we keep in USD for budget check)
        $priceUsd = $price;
        if (strtoupper($service->currency) === 'NPR') {
            $priceUsd = $price / 133; // convert to USD for budget cap
        }
        // Check if service price fits within the style's max (in USD) and overall budget (daily cap)
        if ($priceUsd <= $range['max'] && ($budget == 0 || $priceUsd <= $budget / $days)) {
            $filtered[] = $service;
        }
    }

    // Sort by price according to style preference
    usort($filtered, function ($a, $b) use ($range) {
        $priceA = (float) $a->price;
        $priceB = (float) $b->price;
        if ($range['prefer'] === 'lowest' || $range['prefer'] === 'low') {
            return $priceA <=> $priceB;
        } elseif ($range['prefer'] === 'high') {
            return $priceB <=> $priceA;
        }
        // mid_range: prefer medium (closest to average)
        $avg = ($priceA + $priceB) / 2;
        return abs($priceA - $avg) <=> abs($priceB - $avg);
    });

    // Return top 3 per category
    $grouped = [];
    foreach ($filtered as $svc) {
        $cat = $svc->category->slug ?? 'other';
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        if (count($grouped[$cat]) < 3) {
            $grouped[$cat][] = $svc;
        }
    }

    // Flatten and return
    $result = [];
    foreach ($grouped as $cat => $items) {
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $cat,
                'price' => (float) $item->price,
                'currency' => $item->currency ?? 'NPR',
                'provider' => $item->provider->name ?? null,
                'description' => $item->description,
                'rating' => $item->reviews->avg('rating') ?? null,
            ];
        }
    }

    return $result;
}

    // ==========================================
    //  COST CALCULATION (WITH STYLE + BUDGET)
    // ==========================================

    protected function calculateCost(Route $route, array $input, array $services): array
{
    $days = $input['days'] ?? $route->duration_days;
    $style = $input['travel_style'] ?? 'mid_range';
    $budget = $input['budget'] ?? 0;

    $total = 0;
    $breakdown = [];

    // 1. Route fixed costs
    foreach ($route->costs as $cost) {
        $amount = $cost->amount;
        if (strtoupper($cost->currency) === 'USD') {
            $amount *= 133;
        }
        if ($cost->unit === 'per_day') {
            $amount *= $days;
        }
        $breakdown[$cost->type] = [
            'name' => $cost->name,
            'amount' => $amount,
            'currency' => 'NPR',
            'unit' => $cost->unit,
            'is_mandatory' => (bool) $cost->is_mandatory,
            'provider_name' => 'System',
        ];
        $total += $amount;
    }

    // 2. Selected services (with provider name)
    $selectedServices = $this->selectServicesForStyle($services, $style, $budget, $days);
    foreach ($selectedServices as $svc) {
        $price = $svc['price'];
        if (strtoupper($svc['currency']) === 'USD') {
            $price *= 133;
        }
        if ($svc['category'] === 'hotel' || $svc['category'] === 'guide') {
            $price *= $days;
        }
        $breakdown[$svc['category']] = [
            'name' => $svc['name'],
            'provider_name' => $svc['provider'] ?? 'Local Partner',
            'amount' => $price,
            'currency' => 'NPR',
            'unit' => 'per_day',
            'is_mandatory' => false,
            'service_id' => $svc['id'],
        ];
        $total += $price;
    }

    // 3. Budget sufficiency check
    $budgetNpr = $budget * 133;
    if ($budget > 0 && $total > $budgetNpr) {
        $breakdown['budget_insufficient'] = [
            'name' => 'Budget Note',
            'amount' => 0,
            'currency' => 'NPR',
            'unit' => 'Your budget of $' . $budget . ' USD may be insufficient. Consider adjusting style or days.',
            'is_mandatory' => false,
            'provider_name' => 'System',
        ];
    }

    return ['total' => $total, 'breakdown' => $breakdown];
}

    protected function selectServicesForStyle(array $services, string $style, float $budget, int $days): array
{
    $grouped = [];
    foreach ($services as $svc) {
        $cat = $svc['category'] ?? 'other';
        $grouped[$cat][] = $svc;
    }

    $selected = [];

    foreach ($grouped as $cat => $items) {
        if (empty($items)) continue;

        if ($style === 'budget' || $style === 'backpacker') {
            usort($items, fn($a, $b) => $a['price'] <=> $b['price']);
            $selected[] = $items[0];
        } elseif ($style === 'luxury') {
            usort($items, fn($a, $b) => $b['price'] <=> $a['price']);
            $selected[] = $items[0];
        } else { // mid_range
            usort($items, fn($a, $b) => $a['price'] <=> $b['price']);
            $mid = floor(count($items) / 2);
            $selected[] = $items[$mid];
        }
    }

    // ✅ Ensure provider name is preserved
    $result = [];
    foreach ($selected as $svc) {
        $result[] = [
            'id' => $svc['id'],
            'name' => $svc['name'],
            'category' => $svc['category'],
            'price' => $svc['price'],
            'currency' => $svc['currency'],
            'provider' => $svc['provider'] ?? 'Local Partner', // ✅ यो लाइन महत्वपूर्ण छ
        ];
    }

    return $result;
}

    protected function getMandatoryCost(array $services): float
    {
        // Placeholder – in full implementation, calculate from route_costs
        return 0;
    }

    // ==========================================
    //  CONTEXT + PROMPT (with services)
    // ==========================================

    protected function buildContext(Route $route, array $input, array $cost, array $services): array
    {
        $segments = [];
        foreach ($route->segments as $seg) {
            $segments[] = [
                'sequence' => $seg->sequence,
                'from' => $seg->fromWaypoint->name,
                'to' => $seg->toWaypoint->name,
                'distance_km' => $seg->distance_km,
                'time_hours' => $seg->estimated_time_hours,
                'elevation_gain' => $seg->elevation_gain_m,
                'elevation_loss' => $seg->elevation_loss_m,
                'from_altitude' => $seg->fromWaypoint->altitude,
                'to_altitude' => $seg->toWaypoint->altitude,
            ];
        }

        return [
            'route_name' => $route->name,
            'duration_days' => $route->duration_days,
            'difficulty' => $route->difficulty,
            'max_altitude' => $route->max_altitude,
            'season' => $route->season,
            'user_days' => $input['days'],
            'user_budget' => $input['budget'],
            'travel_style' => $input['travel_style'] ?? 'mid_range',
            'interests' => $input['interests'] ?? [],
            'fitness_level' => $input['fitness_level'] ?? 'moderate',
            'cost_breakdown' => $cost,
            'segments' => $segments,
            'available_services' => $services, // Pass filtered services to LLM
        ];
    }

    protected function buildPrompt(array $context, array $input): string
    {
        // Reduce segments to first 3 + last 3 to save tokens
        $segments = $context['segments'];
        $total = count($segments);
        if ($total > 6) {
            $segments = array_merge(
                array_slice($segments, 0, 3),
                array_slice($segments, -3)
            );
        }
        $context['segments'] = $segments;

        $payload = [
            'instruction' => 'Generate a personalized day-by-day itinerary for a Nepal trek. Use ONLY the provided data.',
            'user_request' => [
                'days' => $input['days'],
                'budget' => $input['budget'],
                'style' => $input['travel_style'] ?? 'mid_range',
                'interests' => $input['interests'] ?? [],
                'fitness' => $input['fitness_level'] ?? 'moderate',
            ],
            'route_data' => [
                'name' => $context['route_name'],
                'difficulty' => $context['difficulty'],
                'max_altitude' => $context['max_altitude'],
                'segments' => $segments,
            ],
            'available_services' => array_map(function ($svc) {
                return [
                    'id' => $svc['id'],
                    'name' => $svc['name'],
                    'category' => $svc['category'],
                    'price' => $svc['price'],
                    'currency' => $svc['currency'],
                    'provider' => $svc['provider'],
                ];
            }, $context['available_services'] ?? []),
            'cost_breakdown' => $context['cost_breakdown']['breakdown'] ?? [],
            'rules' => [
                'Use ONLY services from available_services for accommodation, guides, transport.',
                'Do NOT invent any waypoints, distances, or costs.',
                'Return valid JSON with a "days" array.',
                'If a service is used, include its id as service_id in the item.',
            ],
        ];

        return json_encode($payload, JSON_PRETTY_PRINT);
    }

    // ==========================================
    //  FALLBACK (requested days respected)
    // ==========================================

    protected function buildFallbackResponse(Route $route, array $input): array
    {
        $segments = $route->segments()->orderBy('sequence')->get();
        $requestedDays = $input['days'];
        $days = [];
        $dayNumber = 1;

        $routeCosts = $route->costs;
        $dailyFoodCost = 0;
        $totalFixedCost = 0;

        foreach ($routeCosts as $cost) {
            $amount = $cost->amount;
            if (strtoupper($cost->currency) === 'USD') {
                $amount *= 133;
            }
            if ($cost->unit === 'per_day') {
                $dailyFoodCost = $amount;
            } else {
                $totalFixedCost += $amount;
            }
        }

        $perDayFixedCost = $requestedDays > 0 ? $totalFixedCost / $requestedDays : 0;

        $segmentsToUse = $segments->take($requestedDays);

        foreach ($segmentsToUse as $seg) {
            $from = $seg->fromWaypoint;
            $to = $seg->toWaypoint;
            $dayCost = $perDayFixedCost + $dailyFoodCost;

            $days[] = [
                'day_number' => $dayNumber,
                'title' => "{$from->name} → {$to->name}",
                'description' => "Trek from {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$seg->distance_km} km, estimated time: {$seg->estimated_time_hours} hrs.",
                'overnight_waypoint_id' => $seg->to_waypoint_id,
                'distance_km' => (float) $seg->distance_km,
                'estimated_time_hours' => (float) $seg->estimated_time_hours,
                'altitude_m' => $to->altitude,
                'items' => [
                    [
                        'title' => 'Trekking Day',
                        'description' => "Hike from {$from->name} to {$to->name}",
                        'time_of_day' => 'morning',
                        'cost' => round($dayCost, 2),
                        'pricing_source' => 'system_estimate',
                        'pricing_snapshot' => null,
                        'service_id' => null,
                        'is_optional' => false,
                        'metadata' => null,
                    ]
                ]
            ];
            $dayNumber++;
        }

        while (count($days) < $requestedDays) {
            $last = end($days);
            $days[] = [
                'day_number' => count($days) + 1,
                'title' => "Rest & Acclimatization",
                'description' => 'Rest day to acclimatize and enjoy the mountain views.',
                'overnight_waypoint_id' => $last['overnight_waypoint_id'] ?? null,
                'distance_km' => 0,
                'estimated_time_hours' => 0,
                'altitude_m' => $last['altitude_m'] ?? null,
                'items' => [
                    [
                        'title' => 'Rest & Acclimatization',
                        'description' => 'Take it easy, hydrate, and enjoy the scenery.',
                        'time_of_day' => 'morning',
                        'cost' => round($dailyFoodCost, 2),
                        'pricing_source' => 'system_estimate',
                        'pricing_snapshot' => null,
                        'service_id' => null,
                        'is_optional' => false,
                        'metadata' => null,
                    ]
                ]
            ];
        }

        return ['days' => $days];
    }

    // ==========================================
    //  HELPER: ensure tour segments
    // ==========================================

    protected function ensureSegmentsForTour(Route $route): void
    {
        $waypoints = \App\Models\Waypoint::whereHas('fromSegments', function ($q) use ($route) {
            $q->where('route_id', $route->id);
        })->orWhereHas('toSegments', function ($q) use ($route) {
            $q->where('route_id', $route->id);
        })->get();

        if ($waypoints->count() >= 2) {
            RouteSegment::create([
                'route_id' => $route->id,
                'from_waypoint_id' => $waypoints[0]->id,
                'to_waypoint_id' => $waypoints[1]->id,
                'sequence' => 1,
                'distance_km' => 5.0,
                'estimated_time_hours' => 2.0,
                'elevation_gain_m' => 0,
                'elevation_loss_m' => 0,
            ]);
            return;
        }

        $wp1 = \App\Models\Waypoint::create([
            'name' => $route->name . ' Start',
            'slug' => $route->slug . '-start',
            'type' => 'village',
            'latitude' => 28.0,
            'longitude' => 84.0,
            'altitude' => 1000,
        ]);
        $wp2 = \App\Models\Waypoint::create([
            'name' => $route->name . ' End',
            'slug' => $route->slug . '-end',
            'type' => 'village',
            'latitude' => 28.1,
            'longitude' => 84.1,
            'altitude' => 1100,
        ]);

        RouteSegment::create([
            'route_id' => $route->id,
            'from_waypoint_id' => $wp1->id,
            'to_waypoint_id' => $wp2->id,
            'sequence' => 1,
            'distance_km' => 10.0,
            'estimated_time_hours' => 3.0,
            'elevation_gain_m' => 100,
            'elevation_loss_m' => 0,
        ]);
    }

    protected function resolveRoute(?string $destination): ?Route
    {
        if (!$destination) {
            return Route::where('is_active', true)->first();
        }
        return Route::where('name', 'LIKE', "%{$destination}%")
            ->orWhere('slug', 'LIKE', "%{$destination}%")
            ->where('is_active', true)
            ->first();
    }
}
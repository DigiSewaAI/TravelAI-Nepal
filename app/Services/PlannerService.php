<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Service;
use App\Models\Waypoint;
use App\Models\PlannerRequest;
use App\Models\PlannerResult;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Support\Str;
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

    public function generate(array $input, string $locale = 'en'): array
    {
        Log::info('🔍 [PlannerService] generate called', [
            'locale' => $locale,
            'input' => $input,
        ]);

        $route = $this->resolveRoute($input['destination'] ?? null);
        if (!$route) {
            throw ValidationException::withMessages(['destination' => 'Route not found.']);
        }

        $route->load(['segments.fromWaypoint', 'segments.toWaypoint', 'costs']);

        if ($route->segments->isEmpty()) {
            $this->ensureSegmentsForTour($route);
            $route->load(['segments.fromWaypoint', 'segments.toWaypoint']);
        }

        // Build day-level service map
        $dayServicesMap = [];
        $dayDiagnostics = [];

        foreach ($route->segments as $segment) {
            $dayNumber = $segment->sequence;
            $waypoint = $segment->toWaypoint;
            $result = $this->getServicesForDay($waypoint, $input);
            $dayServicesMap[$dayNumber] = $result['services'] ?? collect();
            if ($result['diagnostic']) {
                $dayDiagnostics[$dayNumber] = $result['diagnostic'];
            }
        }

        $totalDays = $input['days'];
        for ($i = count($route->segments) + 1; $i <= $totalDays; $i++) {
            $dayServicesMap[$i] = collect();
        }

        // Calculate cost (only system costs)
        $costBreakdown = $this->calculateCost($route, $input, [], $locale);

        // Build context
        $context = $this->buildContext($route, $input, $costBreakdown, $dayServicesMap, $dayDiagnostics);

        // Try AI, fallback if needed
        $aiResponse = null;
        $usedFallback = false;
        try {
            $prompt = $this->buildPrompt($context, $input, $locale);
            Log::info('🔍 [PlannerService] Prompt to LLM', [
                'prompt_length' => strlen($prompt),
                'locale' => $locale,
            ]);
            $aiResponse = $this->llm->generateItinerary($prompt, $locale);
            Log::info('✅ AI itinerary generated successfully.');
        } catch (\Exception $e) {
            Log::error('❌ AI generation failed, using fallback.', ['error' => $e->getMessage()]);
            $aiResponse = $this->buildFallbackResponse($route, $input, $locale);
            $usedFallback = true;
        }

        if ($aiResponse && !$usedFallback) {
            if (!$this->isLanguageCorrect($aiResponse, $locale)) {
                Log::warning('⚠️ AI response language mismatch, using fallback.', ['locale' => $locale]);
                $aiResponse = $this->buildFallbackResponse($route, $input, $locale);
                $usedFallback = true;
            }
        }

        // Validate and normalize
        $validated = $this->validator->validate($aiResponse, $route, $input, $context, $locale);

        // ============================================================
        // PROGRAMMATICALLY ATTACH SERVICES (AI बिना पनि)
        // ============================================================
        foreach ($validated['days'] as &$dayData) {
            $dayNumber = $dayData['day_number'];
            $services = $dayServicesMap[$dayNumber] ?? collect();

            if ($services->isNotEmpty()) {
                $bestService = $services->first();

                $priceNpr = $bestService['price'];
                if (strtoupper($bestService['currency'] ?? 'NPR') === 'USD') {
                    $priceNpr *= 133;
                }

                $hasService = false;
                foreach ($dayData['items'] as $item) {
                    if (!empty($item['service_id'])) {
                        $hasService = true;
                        break;
                    }
                }

                if (!$hasService) {
                    $dayData['items'][] = [
                        'title' => $bestService['name'],
                        'description' => 'Recommended by TravelAI',
                        'time_of_day' => 'afternoon',
                        'cost' => $priceNpr,
                        'currency' => 'NPR',
                        'pricing_source' => 'provider_service',
                        'pricing_snapshot' => null,
                        'service_id' => $bestService['id'],
                        'is_optional' => false,
                        'metadata' => null,
                        'provider' => $bestService['provider'] ?? 'TravelAI Partner', // ✅ added for breakdown
                    ];
                }
            }
        }
        unset($dayData);

        // Save to DB
        $result = DB::transaction(function () use ($input, $route, $validated, $aiResponse, $usedFallback, $costBreakdown, $dayServicesMap) {
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
                'model' => config('services.groq.model', 'openai/gpt-oss-20b'),
                'model_version' => 'latest',
                'prompt_version' => 'v4',
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

            // ============================================================
            // STEP 4: Per-Day Cost Breakdown (instead of lump sum)
            // ============================================================
            $breakdown = $costBreakdown['breakdown'] ?? [];
            $totalCost = 0;

            // Add system costs
            foreach ($breakdown as $key => $item) {
                if ($key !== 'services') { // remove any existing lump sum
                    $totalCost += $item['amount'] ?? 0;
                }
            }

            // Build per-day service costs
            $perDayServiceCosts = [];
            foreach ($validated['days'] as $dayData) {
                foreach ($dayData['items'] as $item) {
                    if (($item['pricing_source'] ?? '') === 'provider_service' && !empty($item['service_id'])) {
                        $dayNumber = $dayData['day_number'];
                        $key = "day_{$dayNumber}_service";
                        $perDayServiceCosts[$key] = [
                            'name' => "Day {$dayNumber}: {$item['title']}",
                            'amount' => $item['cost'] ?? 0,
                            'currency' => 'NPR',
                            'unit' => 'total',
                            'is_mandatory' => false,
                            'provider_name' => $item['provider'] ?? 'TravelAI Partner',
                        ];
                        $totalCost += $item['cost'] ?? 0;
                    }
                }
            }

            // Merge system costs + per-day service costs
            $finalBreakdown = array_merge($breakdown, $perDayServiceCosts);

            return [
                'request' => $plannerRequest,
                'result' => $plannerResult,
                'days' => $plannerResult->days()->with('items')->get(),
                'total_cost' => $totalCost,
                'breakdown' => $finalBreakdown,
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

        $query = Service::where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
            });

        $services = collect();
        $locationId = $route->segments->first()->fromWaypoint->location_id ?? null;
        if ($locationId) {
            $services = $query->where('location_id', $locationId)->get();
            if ($services->isEmpty()) {
                $services = $query->whereNull('location_id')->get();
            }
        } else {
            $services = $query->get();
        }

        $priceRanges = [
            'budget'     => ['max' => 30,  'prefer' => 'lowest'],
            'backpacker' => ['max' => 50,  'prefer' => 'low'],
            'mid_range'  => ['max' => 120, 'prefer' => 'medium'],
            'luxury'     => ['max' => 500, 'prefer' => 'high'],
        ];

        $range = $priceRanges[$style] ?? $priceRanges['mid_range'];

        $filtered = [];
        foreach ($services as $service) {
            $price = (float) $service->price;
            $priceUsd = $price;
            if (strtoupper($service->currency) === 'NPR') {
                $priceUsd = $price / 133;
            }
            if ($priceUsd <= $range['max'] && ($budget == 0 || $priceUsd <= $budget / $days)) {
                $filtered[] = $service;
            }
        }

        usort($filtered, function ($a, $b) use ($range) {
            $priceA = (float) $a->price;
            $priceB = (float) $b->price;
            if ($range['prefer'] === 'lowest' || $range['prefer'] === 'low') {
                return $priceA <=> $priceB;
            } elseif ($range['prefer'] === 'high') {
                return $priceB <=> $priceA;
            }
            $avg = ($priceA + $priceB) / 2;
            return abs($priceA - $avg) <=> abs($priceB - $avg);
        });

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
    //  COST CALCULATION
    // ==========================================

    protected function calculateCost(Route $route, array $input, array $services, string $locale = 'en'): array
    {
        $days = $input['days'] ?? $route->duration_days;
        $style = $input['travel_style'] ?? 'mid_range';
        $budget = $input['budget'] ?? 0;

        $total = 0;
        $breakdown = [];

        foreach ($route->costs as $cost) {
            $amount = $cost->amount;
            if (strtoupper($cost->currency) === 'USD') {
                $amount *= 133;
            }
            if ($cost->unit === 'per_day') {
                $amount *= $days;
            }
            $breakdown[$cost->type] = [
                'name' => $this->translateName($cost->name, 'cost', $locale),
                'amount' => $amount,
                'currency' => 'NPR',
                'unit' => $cost->unit,
                'is_mandatory' => (bool) $cost->is_mandatory,
                'provider_name' => 'System',
            ];
            $total += $amount;
        }

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
                'name' => $this->translateName($svc['name'], 'service', $locale),
                'provider_name' => $svc['provider'] ?? 'Local Partner',
                'amount' => $price,
                'currency' => 'NPR',
                'unit' => 'per_day',
                'is_mandatory' => false,
                'service_id' => $svc['id'],
            ];
            $total += $price;
        }

        $budgetNpr = $budget * 133;
        if ($budget > 0 && $total > $budgetNpr) {
            $breakdown['budget_insufficient'] = [
                'name' => 'Budget Note',
                'amount' => 0,
                'currency' => 'NPR',
                'unit' => __('messages.note', ['amount' => $input['budget']]),
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
            } else {
                usort($items, fn($a, $b) => $a['price'] <=> $b['price']);
                $mid = floor(count($items) / 2);
                $selected[] = $items[$mid];
            }
        }

        $result = [];
        foreach ($selected as $svc) {
            $result[] = [
                'id' => $svc['id'],
                'name' => $svc['name'],
                'category' => $svc['category'],
                'price' => $svc['price'],
                'currency' => $svc['currency'],
                'provider' => $svc['provider'] ?? 'Local Partner',
            ];
        }

        return $result;
    }

    protected function getMandatoryCost(array $services): float
    {
        return 0;
    }

    // ==========================================
    //  CONTEXT + PROMPT
    // ==========================================

    protected function buildContext(Route $route, array $input, array $cost, array $dayServicesMap, array $dayDiagnostics = []): array
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
            'day_services' => $dayServicesMap,
            'day_diagnostics' => $dayDiagnostics,
        ];
    }

    protected function buildPrompt(array $context, array $input, string $locale = 'en'): string
    {
        $segments = $context['segments'];
        $total = count($segments);
        if ($total > 6) {
            $segments = array_merge(
                array_slice($segments, 0, 3),
                array_slice($segments, -3)
            );
        }
        $context['segments'] = $segments;

        // छोटो day_services (id, name, category, price)
        $dayServicesForPrompt = [];
        foreach ($context['day_services'] as $dayNumber => $services) {
            $dayServicesForPrompt[$dayNumber] = $services->map(function ($svc) {
                return [
                    'id' => $svc['id'],
                    'name' => $svc['name'],
                    'category' => $svc['category'],
                    'price' => (float) $svc['price'],
                ];
            })->take(6)->values()->toArray();
        }

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
            'day_services' => $dayServicesForPrompt,
            'cost_breakdown' => $context['cost_breakdown']['breakdown'] ?? [],
            'locale' => $locale,
            'rules' => [
                'For each day, use ONLY services from that day\'s "day_services" list.',
                'If a service is used, include its "id" as "service_id" in that day\'s item.',
                'You may also include items without service_id (e.g., free activities).',
                'Do NOT invent any waypoints, distances, or costs.',
                'Return valid JSON with a "days" array.',
                "IMPORTANT: ALL text content (titles, descriptions, item names, cost labels) MUST be in the language: " . match($locale) {
                    'hi' => 'Hindi (Devanagari script). ONLY waypoint names like "Nayapul" can remain in English. Everything else must be in Hindi.',
                    'zh' => 'Chinese (Simplified Chinese characters). ONLY waypoint names like "Nayapul" can remain in English. Everything else must be in Chinese.',
                    default => 'English.',
                },
            ],
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    //  FALLBACK (with Rest Day Limit)
    // ==========================================

    protected function buildFallbackResponse(Route $route, array $input, string $locale = 'en'): array
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

        // ✅ Detect rest/acclimatization day
        $isRestDay = ($from->id === $to->id || (float)$seg->distance_km == 0);

        // Day title & description
        if ($isRestDay) {
            $title = match($locale) {
                'hi' => "{$to->name} में अनुकूलन दिवस",
                'zh' => "{$to->name} 适应日",
                'np' => "{$to->name} मा अनुकूलन दिन",
                default => "Acclimatization Day at {$to->name}",
            };
            $desc = match($locale) {
                'hi' => "आज कोई ट्रेकिंग नहीं। {$to->name} में आराम और अनुकूलन।",
                'zh' => "今天不徒步。在 {$to->name} 休息和适应。",
                'np' => "आज कुनै ट्रेकिङ छैन। {$to->name} मा आराम र अनुकूलन।",
                default => "No trekking today. Rest and acclimatize at {$to->name}.",
            };
        } else {
            $title = match($locale) {
                'hi' => "{$from->name} → {$to->name}",
                'zh' => "{$from->name} → {$to->name}",
                default => "{$from->name} → {$to->name}",
            };
            $desc = match($locale) {
                'hi' => "{$from->name} ({$from->altitude}मी) से {$to->name} ({$to->altitude}मी) तक ट्रेक। दूरी: {$seg->distance_km} किमी, अनुमानित समय: {$seg->estimated_time_hours} घंटे।",
                'zh' => "从 {$from->name}（{$from->altitude}米）徒步到 {$to->name}（{$to->altitude}米）。距离：{$seg->distance_km}公里，预计时间：{$seg->estimated_time_hours}小时。",
                'np' => "{$from->name} ({$from->altitude}मी) देखि {$to->name} ({$to->altitude}मी) सम्म ट्रेक। दूरी: {$seg->distance_km} किमी, अनुमानित समय: {$seg->estimated_time_hours} घण्टा।",
                default => "Trek from {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$seg->distance_km} km, estimated time: {$seg->estimated_time_hours} hrs.",
            };
        }

        // ✅ Item title & description
        if ($isRestDay) {
            $itemTitle = match($locale) {
                'hi' => "आराम / अनुकूलन",
                'zh' => "休息 / 适应",
                'np' => "आराम / अनुकूलन",
                default => "Rest / Acclimatization",
            };
            $itemDesc = match($locale) {
                'hi' => "{$to->name} में आराम और अनुकूलन।",
                'zh' => "在 {$to->name} 休息和适应。",
                'np' => "{$to->name} मा आराम र अनुकूलन।",
                default => "Rest and acclimatization at {$to->name}.",
            };
        } else {
            $itemTitle = match($locale) {
                'hi' => "ट्रेकिंग दिन",
                'zh' => "徒步日",
                default => "Trekking Day",
            };
            $itemDesc = match($locale) {
                'hi' => "{$from->name} से {$to->name} तक ट्रेक करें",
                'zh' => "从 {$from->name} 徒步到 {$to->name}",
                default => "Hike from {$from->name} to {$to->name}",
            };
        }

        $days[] = [
            'day_number' => $dayNumber,
            'title' => $title,
            'description' => $desc,
            'overnight_waypoint_id' => $seg->to_waypoint_id,
            'distance_km' => (float) $seg->distance_km,
            'estimated_time_hours' => (float) $seg->estimated_time_hours,
            'altitude_m' => $to->altitude,
            'items' => [
                [
                    'title' => $itemTitle,
                    'description' => $itemDesc,
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

    // Rest day limit for extra days beyond segments (unchanged)
    $maxRestDays = min(3, $requestedDays - count($segments));
    $restDaysAdded = 0;

    while (count($days) < $requestedDays && $restDaysAdded < $maxRestDays) {
        $last = end($days);

        $waypointId = $last['overnight_waypoint_id'] ?? null;
        $waypoint = $waypointId ? Waypoint::find($waypointId) : null;
        $waypointName = $waypoint ? $waypoint->name : 'Unknown';

        $restTitle = match($locale) {
            'hi' => "{$waypointName} में अनुकूलन दिवस",
            'zh' => "{$waypointName} 适应日",
            'np' => "{$waypointName} मा अनुकूलन दिन",
            default => "Acclimatization Day at {$waypointName}",
        };
        $restDesc = match($locale) {
            'hi' => "आज कोई ट्रेकिंग नहीं। {$waypointName} में आराम और अनुकूलन।",
            'zh' => "今天不徒步。在 {$waypointName} 休息和适应。",
            'np' => "आज कुनै ट्रेकिङ छैन। {$waypointName} मा आराम र अनुकूलन।",
            default => "No trekking today. Rest and acclimatize at {$waypointName}.",
        };

        $days[] = [
            'day_number' => count($days) + 1,
            'title' => $restTitle,
            'description' => $restDesc,
            'overnight_waypoint_id' => $waypointId,
            'distance_km' => 0,
            'estimated_time_hours' => 0,
            'altitude_m' => $last['altitude_m'] ?? null,
            'items' => [
                [
                    'title' => 'Rest / Acclimatization',
                    'description' => 'Rest and acclimatization.',
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
        $restDaysAdded++;
    }

    // Remaining days as "No Itinerary Data" (unchanged)
    while (count($days) < $requestedDays) {
        $dayNumber = count($days) + 1;
        $titleNoData = match($locale) {
            'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
            'zh' => "第 {$dayNumber} 天: 无行程数据",
            default => "Day {$dayNumber}: No Itinerary Data",
        };
        $descNoData = match($locale) {
            'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया। कृपया अपना अनुरोध समायोजित करें या पुनः प्रयास करें।",
            'zh' => "AI 没有为此天生成数据。请调整您的请求或重试。",
            default => "The AI did not generate data for this day. Please adjust your request or try again.",
        };
        $days[] = [
            'day_number' => $dayNumber,
            'title' => $titleNoData,
            'description' => $descNoData,
            'overnight_waypoint_id' => null,
            'distance_km' => null,
            'estimated_time_hours' => null,
            'altitude_m' => null,
            'items' => [],
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
            \App\Models\RouteSegment::create([
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

        \App\Models\RouteSegment::create([
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

    /**
     * Translate a dynamic name using the current locale.
     * Falls back to original if translation not found.
     */
    private function translateName(string $name, string $prefix, string $locale): string
    {
        if ($locale === 'hi') {
            $map = [
                'cost.daily_food_budget' => 'दैनिक भोजन बजट',
                'cost.manang_special_permit' => 'मनांग विशेष अनुमति',
                'service.homestay_experience' => 'होमस्टे अनुभव',
                'service.group_guide_service' => 'समूह गाइड सेवा',
                'service.standard_room' => 'स्टैंडर्ड रूम',
                'service.private_jeep' => 'प्राइवेट जीप',
            ];
            $key = $prefix . '.' . Str::slug($name, '_');
            return $map[$key] ?? $name;
        }

        if ($locale === 'zh') {
            $map = [
                'cost.daily_food_budget' => '每日食品预算',
                'cost.manang_special_permit' => '马南特别许可证',
                'service.homestay_experience' => '寄宿家庭体验',
                'service.group_guide_service' => '团体导游服务',
                'service.standard_room' => '标准间',
                'service.private_jeep' => '私人吉普车',
            ];
            $key = $prefix . '.' . Str::slug($name, '_');
            return $map[$key] ?? $name;
        }

        $key = $prefix . '.' . Str::slug($name, '_');
        $translated = __($key, [], $locale);
        return ($translated !== $key) ? $translated : $name;
    }

    /**
     * Recursively extract all string values and check for Devanagari.
     * For hi/np, require at least one Devanagari character.
     */
    private function isLanguageCorrect(array $data, string $locale): bool
    {
        if (!in_array($locale, ['hi', 'np'])) {
            return true;
        }

        $strings = [];
        array_walk_recursive($data, function ($value) use (&$strings) {
            if (is_string($value) && !is_numeric($value)) {
                $strings[] = $value;
            }
        });

        $text = implode(' ', $strings);
        return preg_match('/[\x{0900}-\x{097F}]/u', $text) === 1;
    }

    // ==========================================
    //  DAY-LEVEL SERVICE FETCHER
    // ==========================================

    protected function getServicesForDay(Waypoint $waypoint, array $input): array
    {
        $style = $input['travel_style'] ?? 'mid_range';
        $locationId = $waypoint->location_id;

        if (!$locationId) {
            return ['services' => collect(), 'diagnostic' => 'no_location_match'];
        }

        $query = Service::where('status', 'active')
            ->where('location_id', $locationId)
            ->whereHas('category', function ($q) {
                $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
            });

        $services = $query->with(['provider.styles', 'reviews'])->get();

        if ($services->isEmpty()) {
            return ['services' => collect(), 'diagnostic' => 'no_active_service'];
        }

        $filtered = $services->filter(function ($service) use ($style) {
            return $service->provider->styles->contains('style_slug', $style);
        });

        if ($filtered->isEmpty()) {
            return ['services' => collect(), 'diagnostic' => 'no_style_match'];
        }

        $grouped = [];
        foreach ($filtered as $svc) {
            $cat = $svc->category->slug ?? 'other';
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            if (count($grouped[$cat]) < 2) {
                $grouped[$cat][] = $svc;
            }
        }

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
                    'provider_id' => $item->provider_id,
                    'description' => $item->description,
                    'rating' => $item->reviews->avg('rating') ?? null,
                ];
            }
        }

        return ['services' => collect($result), 'diagnostic' => null];
    }
}
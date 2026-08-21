<?php

namespace App\Services;

use App\Models\Route;
use App\Models\RouteSegment;
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

        // ✅ FIX: If no segments, create a default one for tours
        if ($route->segments->isEmpty()) {
            $this->ensureSegmentsForTour($route);
            $route->load(['segments.fromWaypoint', 'segments.toWaypoint']);
        }

        if ($route->segments->isEmpty()) {
            throw new \Exception('Route has no segments defined.');
        }

        // 2. Cost calculation
        $costBreakdown = $this->calculateCost($route, $input);

        // 3. Build context
        $context = $this->buildContext($route, $input, $costBreakdown);

        // 4. Try to get AI itinerary, fallback if fails
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

        // 5. Validate
        $validated = $this->validator->validate($aiResponse, $route, $input, $context);

        // 6. Save
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
                'prompt_version' => 'v2',
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
            ];
        });

        return $result;
    }

    /**
     * ✅ Ensure a route has at least one segment (for tours without segments)
     */
    protected function ensureSegmentsForTour(Route $route): void
    {
        // Get waypoints from the route's costs (metadata can store waypoint IDs)
        // Or try to get waypoints from route_snapshot
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
            Log::info('Created default segment for tour route', ['route_id' => $route->id]);
            return;
        }

        // If no waypoints found, create two dummy waypoints
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
        Log::info('Created dummy waypoints and segment for tour route', ['route_id' => $route->id]);
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

    protected function calculateCost(Route $route, array $input): array
    {
        $days = $input['days'] ?? $route->duration_days;
        $total = 0;
        $breakdown = [];

        foreach ($route->costs as $cost) {
            $amount = $cost->amount;
            
            // ✅ Convert USD to NPR (1 USD ≈ 133 NPR) for consistent backend calculation
            if (strtoupper($cost->currency) === 'USD') {
                $amount *= 133;
            }
            
            if ($cost->unit === 'per_day') {
                $amount *= $days;
            }
            $breakdown[$cost->type] = [
                'name' => $cost->name,
                'amount' => $amount,
                'currency' => 'NPR', // Always store as NPR for internal calculation
                'unit' => $cost->unit,
                'is_mandatory' => (bool) $cost->is_mandatory,
            ];
            $total += $amount;
        }

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    protected function buildContext(Route $route, array $input, array $cost): array
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
            'instruction' => 'Generate a day-by-day itinerary for a Nepal trek. Use ONLY the provided verified data.',
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
            'cost_breakdown' => $context['cost_breakdown']['breakdown'] ?? [],
            'rules' => [
                'Use ONLY data from route_data for distances, waypoints, and altitudes.',
                'Do NOT invent any waypoints, distances, or costs.',
                'Do NOT calculate the total cost.',
                'Return valid JSON with a "days" array.',
            ],
        ];

        return json_encode($payload, JSON_PRETTY_PRINT);
    }

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
            // ✅ Convert any USD amount to NPR (base currency for display)
            $amount = $cost->amount;
            if (strtoupper($cost->currency) === 'USD') {
                $amount *= 133; // Approx exchange rate
            }

            if ($cost->unit === 'per_day') {
                $dailyFoodCost = $amount; // ✅ Assign, don't accumulate
            } else {
                // Permits, transport, conservation fees – all fixed per person
                $totalFixedCost += $amount;
            }
        }

        // Spread fixed costs across all requested days
        $perDayFixedCost = $requestedDays > 0 ? $totalFixedCost / $requestedDays : 0;

        // ✅ Only take the first `requestedDays` segments
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

        // ✅ Add rest days if user requested more days than segments
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
                        'cost' => round($dailyFoodCost, 2), // ✅ Rest day only food cost
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
}
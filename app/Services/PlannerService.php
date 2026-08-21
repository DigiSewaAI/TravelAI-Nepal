<?php

namespace App\Services;

use App\Models\Route;
use App\Models\PlannerRequest;
use App\Models\PlannerResult;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Support\Facades\DB;
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

        // 2. Load verified data
        $route->load(['segments.fromWaypoint', 'segments.toWaypoint', 'costs']);
        if ($route->segments->isEmpty()) {
            throw new \Exception('Route has no segments defined.');
        }

        // 3. Calculate base cost (Laravel engine)
        $costBreakdown = $this->calculateCost($route, $input);

        // 4. Build grounded context
        $context = $this->buildContext($route, $input, $costBreakdown);

        // 5. Send to LLM
        $prompt = $this->buildPrompt($context, $input);
        $aiResponse = $this->llm->generateItinerary($prompt);

        // 6. Validate AI output against database facts (pass context)
        $validated = $this->validator->validate($aiResponse, $route, $input, $context);

        // 7. Save everything
        $result = DB::transaction(function () use ($input, $route, $validated, $aiResponse) {
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
                'validation_status' => 'valid',
                'fallback_used' => false,
                'validation_errors' => null,
            ]);

            // Save days and items
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
            ];
        });

        // ✅ ADD total_cost from the already calculated breakdown
        $result['total_cost'] = $costBreakdown['total'] ?? 0;

        return $result;
    }

    protected function resolveRoute(?string $destination): ?Route
    {
        if (!$destination) return Route::where('is_active', true)->first();
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
            if ($cost->unit === 'per_day') {
                $amount *= $days;
            }
            $breakdown[$cost->type] = [
                'name' => $cost->name,
                'amount' => $amount,
                'currency' => $cost->currency,
                'unit' => $cost->unit,
                'is_mandatory' => (bool) $cost->is_mandatory,
            ];
            $total += $amount;
        }

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    /**
     * Get verified partner services safe to use for ABC.
     * Only hotel, transport, and guide categories – no activities/experiences.
     */
    protected function getSafeAbcServices(): array
    {
        $allowedCategories = ['hotel', 'transport', 'guide'];

        return \App\Models\Service::whereHas('category', function ($q) use ($allowedCategories) {
                $q->whereIn('slug', $allowedCategories);
            })
            ->where('status', 'active')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category->slug ?? 'unknown',
                    'description' => $service->description,
                    'price' => $service->price,
                    'currency' => $service->currency ?? 'NPR',
                    'provider' => $service->provider->name ?? null,
                ];
            })
            ->toArray();
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

        // ✅ Add safe partner services
        $availableServices = $this->getSafeAbcServices();

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
            'available_services' => $availableServices,
        ];
    }

    /**
     * Build a prompt with reduced token size and include available services.
     */
    protected function buildPrompt(array $context, array $input): string
{
    // Only 2+2 segments (first 2, last 2)
    $segments = $context['segments'];
    $total = count($segments);
    if ($total > 4) {
        $segments = array_merge(
            array_slice($segments, 0, 2),
            array_slice($segments, -2)
        );
    }

    // Only 4 services (id, name, category)
    $services = array_map(function($s) {
        return ['id' => $s['id'], 'name' => $s['name'], 'category' => $s['category']];
    }, array_slice($context['available_services'] ?? [], 0, 4));

    $payload = [
        'instruction' => 'Generate a 9-day ABC trek itinerary JSON. Use given segments and services. Return ONLY valid JSON.',
        'segments' => $segments,
        'services' => $services,
        'cost_total' => $context['cost_breakdown']['total'] ?? 0,
        'user' => [
            'days' => $input['days'],
            'style' => $input['travel_style'] ?? 'mid_range',
        ],
        'rules' => [
            'Use services with service_id only.',
            'Do NOT invent names.',
            'Do NOT calculate total.',
            'Return ONLY JSON. No explanation, no markdown.',
        ],
    ];

    return json_encode($payload, JSON_PRETTY_PRINT);
}
}
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

        // 6. Validate AI output against database facts
        $validated = $this->validator->validate($aiResponse, $route, $input);

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
                'model' => config('services.groq.model', 'qwen/qwen3.6-27b'), // ✅ dynamic model
                'model_version' => 'latest',
                'prompt_version' => 'v2', // ✅ version bumped
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

    /**
     * Build a prompt with reduced token size to avoid TPM limits.
     */
    protected function buildPrompt(array $context, array $input): string
{
    // ✅ Limit segments to only 6 (3 first, 3 last)
    $segments = $context['segments'];
    $total = count($segments);
    if ($total > 6) {
        $segments = array_merge(
            array_slice($segments, 0, 3),
            array_slice($segments, -3)
        );
    }
    $context['segments'] = $segments;

    // ✅ Simpler payload
    $payload = [
        'instruction' => 'Generate day-by-day itinerary for Nepal trek.',
        'user' => [
            'days' => $input['days'],
            'budget' => $input['budget'],
            'style' => $input['travel_style'] ?? 'mid_range',
        ],
        'route' => $context,
        'output' => 'Return JSON with "days" array. Each day: day_number, title, description, distance_km, altitude_m, items (title, description, cost).'
    ];

    return json_encode($payload, JSON_PRETTY_PRINT);
}
}
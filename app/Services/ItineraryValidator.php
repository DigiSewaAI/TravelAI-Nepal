<?php

namespace App\Services;

use App\Models\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ItineraryValidator
{
    /**
     * Validate AI-generated itinerary against ground truth.
     */
    public function validate(array $aiOutput, Route $route, array $input, array $context): array
    {
        $errors = [];

        // ✅ Normalize: if 'itinerary' exists, map to 'days'
        if (isset($aiOutput['itinerary']) && !isset($aiOutput['days'])) {
            $aiOutput['days'] = $aiOutput['itinerary'];
        }

        $actualDays = count($aiOutput['days'] ?? []);

        // Flexible: यदि AI ले कम दिन दियो भने warning मात्र
        if ($actualDays < $input['days']) {
            Log::warning("AI generated only {$actualDays} days, but user requested {$input['days']}. Days will be padded.", [
                'route' => $route->name,
                'user_id' => auth()->id() ?? 'guest'
            ]);
        }

        // Get valid waypoint IDs from the route
        $validWaypointIds = $route->segments->pluck('from_waypoint_id')
            ->merge($route->segments->pluck('to_waypoint_id'))
            ->unique()->toArray();

        // Get valid service IDs from context
        $availableServiceIds = array_column($context['available_services'] ?? [], 'id');

        foreach ($aiOutput['days'] ?? [] as $day) {
            // Validate overnight waypoint
            if (!empty($day['overnight_waypoint_id'])) {
                if (!in_array($day['overnight_waypoint_id'], $validWaypointIds)) {
                    $errors[] = "Day {$day['day_number']}: unknown waypoint ID {$day['overnight_waypoint_id']}.";
                }
            }

            // Validate items
            foreach ($day['items'] ?? [] as $item) {
                if (!empty($item['service_id'])) {
                    if (!in_array($item['service_id'], $availableServiceIds)) {
                        $errors[] = "Invalid service_id: {$item['service_id']} – not in available services.";
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['ai_response' => implode('; ', $errors)]);
        }

        // Normalize and pad days if needed
        return $this->normalize($aiOutput, $route, $input);
    }

    protected function normalize(array $aiOutput, Route $route, array $input): array
    {
        $normalized = ['days' => []];
        $requestedDays = $input['days'];

        // ✅ Sequential day numbers – start from 1
        $dayCounter = 1;

        // Loop through AI-generated days (if any)
        foreach ($aiOutput['days'] ?? [] as $day) {
            // Force day_number to be sequential
            $dayNumber = $dayCounter++;

            $normalized['days'][] = [
                'day_number' => $dayNumber,
                'title' => $day['title'] ?? "Day {$dayNumber}",
                'description' => $day['description'] ?? '',
                'overnight_waypoint_id' => $day['overnight_waypoint_id'] ?? null,
                'distance_km' => $day['distance_km'] ?? null,
                'estimated_time_hours' => $day['estimated_time_hours'] ?? null,
                'altitude_m' => $day['altitude_m'] ?? null,
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                        'time_of_day' => $item['time_of_day'] ?? null,
                        'cost' => $item['cost'] ?? 0,
                        'pricing_source' => $item['pricing_source'] ?? 'system_estimate',
                        'pricing_snapshot' => $item['pricing_snapshot'] ?? null,
                        'service_id' => $item['service_id'] ?? null,
                        'is_optional' => $item['is_optional'] ?? false,
                        'metadata' => $item['metadata'] ?? null,
                    ];
                }, $day['items'] ?? []),
            ];
        }

        $actualDays = count($normalized['days']);

        // ✅ यदि AI ले कम दिन दियो भने, बाँकी दिन "Rest/Acclimatization" थप
        if ($actualDays < $requestedDays) {
            // Use the last day's data (if any) for overnight waypoint and altitude
            $lastDay = end($normalized['days']);
            $lastWaypoint = $lastDay['overnight_waypoint_id'] ?? null;
            $lastAltitude = $lastDay['altitude_m'] ?? null;

            for ($i = $actualDays + 1; $i <= $requestedDays; $i++) {
                $normalized['days'][] = [
                    'day_number' => $i,
                    'title' => "Day {$i}: Rest & Acclimatization",
                    'description' => "Rest day to acclimatize and enjoy the mountain views. Explore nearby trails or relax at the teahouse.",
                    'overnight_waypoint_id' => $lastWaypoint,
                    'distance_km' => 0,
                    'estimated_time_hours' => 0,
                    'altitude_m' => $lastAltitude,
                    'items' => [
                        [
                            'title' => 'Rest & Acclimatization',
                            'description' => 'Take it easy, hydrate, and enjoy the scenery.',
                            'time_of_day' => 'morning',
                            'cost' => 0,
                            'pricing_source' => 'system_estimate',
                            'pricing_snapshot' => null,
                            'service_id' => null,
                            'is_optional' => false,
                            'metadata' => null,
                        ],
                        [
                            'title' => 'Local Exploration',
                            'description' => 'Explore nearby areas, capture photos, or interact with locals.',
                            'time_of_day' => 'afternoon',
                            'cost' => 0,
                            'pricing_source' => 'system_estimate',
                            'pricing_snapshot' => null,
                            'service_id' => null,
                            'is_optional' => true,
                            'metadata' => null,
                        ],
                    ],
                ];
            }
        }

        return $normalized;
    }
}
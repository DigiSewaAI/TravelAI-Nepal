<?php

namespace App\Services;

use App\Models\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ItineraryValidator
{
    public function validate(array $aiOutput, Route $route, array $input): array
    {
        $errors = [];
        $actualDays = count($aiOutput['days'] ?? []);

        // ✅ Flexible: यदि AI ले कम दिन दियो भने error नफालौं, warning मात्र
        if ($actualDays < $input['days']) {
            Log::warning("AI generated only {$actualDays} days, but user requested {$input['days']}. Days will be padded.", [
                'route' => $route->name,
                'user_id' => auth()->id() ?? 'guest'
            ]);
            // हामी पछि days padding गर्न सक्छौं, तर अहिले error नफालौं
        }

        // Verify waypoints exist in route
        $validWaypointIds = $route->segments->pluck('from_waypoint_id')
            ->merge($route->segments->pluck('to_waypoint_id'))
            ->unique()->toArray();

        foreach ($aiOutput['days'] ?? [] as $day) {
            if (!empty($day['overnight_waypoint_id'])) {
                if (!in_array($day['overnight_waypoint_id'], $validWaypointIds)) {
                    $errors[] = "Day {$day['day_number']}: unknown waypoint ID {$day['overnight_waypoint_id']}.";
                }
            }

            // Validate each item's cost (optional)
            foreach ($day['items'] ?? [] as $item) {
                if (isset($item['cost']) && $item['cost'] > 0) {
                    // Optional: check if cost is within reasonable range
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
        $actualDays = count($aiOutput['days'] ?? []);
        $requestedDays = $input['days'];

        // Loop through AI-generated days
        foreach ($aiOutput['days'] as $day) {
            $normalized['days'][] = [
                'day_number' => (int) $day['day_number'],
                'title' => $day['title'] ?? "Day {$day['day_number']}",
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

        // ✅ यदि AI ले कम दिन दियो भने, बाँकी दिन "Rest/Acclimatization" day थप
        if ($actualDays < $requestedDays) {
            $lastDay = end($normalized['days']);
            $lastDayNumber = $lastDay['day_number'] ?? $actualDays;

            for ($i = $actualDays + 1; $i <= $requestedDays; $i++) {
                $normalized['days'][] = [
                    'day_number' => $i,
                    'title' => "Day {$i}: Rest & Acclimatization",
                    'description' => "Rest day to acclimatize and enjoy the mountain views. Explore nearby trails or relax at the teahouse.",
                    'overnight_waypoint_id' => $lastDay['overnight_waypoint_id'] ?? null,
                    'distance_km' => 0,
                    'estimated_time_hours' => 0,
                    'altitude_m' => $lastDay['altitude_m'] ?? null,
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
<?php

namespace App\Services;

use App\Models\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ItineraryValidator
{
    public function validate(array $aiOutput, Route $route, array $input, array $context, string $locale = 'en'): array
    {
        $errors = [];

        // Normalize: if 'itinerary' exists, map to 'days'
        if (isset($aiOutput['itinerary']) && !isset($aiOutput['days'])) {
            $aiOutput['days'] = $aiOutput['itinerary'];
        }

        // If empty days, generate fallback
        if (empty($aiOutput['days']) || !is_array($aiOutput['days']) || count($aiOutput['days']) === 0) {
            Log::warning('AI returned empty days, generating fallback itinerary.', [
                'route' => $route->name,
                'user_id' => auth()->id() ?? 'guest'
            ]);
            $aiOutput['days'] = $this->generateFallbackItinerary($route, $input, $locale);
        }

        $actualDays = count($aiOutput['days'] ?? []);

        if ($actualDays < $input['days']) {
            Log::warning("AI generated only {$actualDays} days, but user requested {$input['days']}. Days will be padded.", [
                'route' => $route->name,
                'user_id' => auth()->id() ?? 'guest'
            ]);
        }

        // Get valid waypoint IDs
        $validWaypointIds = $route->segments->pluck('from_waypoint_id')
            ->merge($route->segments->pluck('to_waypoint_id'))
            ->unique()->toArray();

        // Get valid service IDs from context
        $availableServiceIds = array_column($context['available_services'] ?? [], 'id');

        $hasValidDays = false;

        foreach ($aiOutput['days'] ?? [] as $day) {
            // Validate overnight waypoint
            if (!empty($day['overnight_waypoint_id'])) {
                if (!in_array($day['overnight_waypoint_id'], $validWaypointIds)) {
                    $errors[] = "Day {$day['day_number']}: unknown waypoint ID {$day['overnight_waypoint_id']}.";
                }
            }

            // Validate service IDs
            foreach ($day['items'] ?? [] as $item) {
                if (!empty($item['service_id'])) {
                    if (!in_array($item['service_id'], $availableServiceIds)) {
                        $errors[] = "Invalid service_id: {$item['service_id']} – not in available services.";
                    }
                }
            }

            // Check if this day has meaningful content
            if (!empty($day['items']) || (!empty($day['description']) && strlen($day['description']) > 10)) {
                $hasValidDays = true;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['ai_response' => implode('; ', $errors)]);
        }

        return $this->normalize($aiOutput, $route, $input, $locale);
    }

    protected function generateFallbackItinerary(Route $route, array $input, string $locale = 'en'): array
    {
        $segments = $route->segments()->orderBy('sequence')->get();
        $requestedDays = $input['days'];
        $days = [];

        for ($i = 0; $i < $requestedDays; $i++) {
            $segment = $segments[$i] ?? null;

            if ($segment) {
                $from = $segment->fromWaypoint;
                $to = $segment->toWaypoint;

                $title = match($locale) {
                    'hi' => "दिन " . ($i + 1) . ": {$from->name} → {$to->name}",
                    'zh' => "第 " . ($i + 1) . " 天: {$from->name} → {$to->name}",
                    default => "Day " . ($i + 1) . ": {$from->name} → {$to->name}",
                };

                $desc = match($locale) {
                    'hi' => "{$from->name} ({$from->altitude}मी) से {$to->name} ({$to->altitude}मी) तक ट्रेक। दूरी: {$segment->distance_km} किमी, अनुमानित समय: {$segment->estimated_time_hours} घंटे।",
                    'zh' => "从 {$from->name}（{$from->altitude}米）徒步到 {$to->name}（{$to->altitude}米）。距离：{$segment->distance_km}公里，预计时间：{$segment->estimated_time_hours}小时。",
                    default => "Trek from {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$segment->distance_km} km, estimated time: {$segment->estimated_time_hours} hrs.",
                };

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

                $days[] = [
                    'day_number' => $i + 1,
                    'title' => $title,
                    'description' => $desc,
                    'overnight_waypoint_id' => $segment->to_waypoint_id,
                    'distance_km' => (float) $segment->distance_km,
                    'estimated_time_hours' => (float) $segment->estimated_time_hours,
                    'altitude_m' => $to->altitude,
                    'items' => [
                        [
                            'title' => $itemTitle,
                            'description' => $itemDesc,
                            'time_of_day' => 'morning',
                            'cost' => 0,
                            'pricing_source' => 'system_estimate',
                            'pricing_snapshot' => null,
                            'service_id' => null,
                            'is_optional' => false,
                            'metadata' => null,
                        ]
                    ]
                ];
            } else {
                $prevDay = $days[$i - 1] ?? null;

                $titleRest = match($locale) {
                    'hi' => "दिन " . ($i + 1) . ": आराम और अनुकूलन",
                    'zh' => "第 " . ($i + 1) . " 天: 休息和适应",
                    default => "Day " . ($i + 1) . ": Rest & Acclimatization",
                };

                $descRest = match($locale) {
                    'hi' => "आराम र अनुकूलनको दिन।",
                    'zh' => "休息和适应的一天。",
                    default => "Rest day to acclimatize and enjoy the mountain views.",
                };

                $days[] = [
                    'day_number' => $i + 1,
                    'title' => $titleRest,
                    'description' => $descRest,
                    'overnight_waypoint_id' => $prevDay ? $prevDay['overnight_waypoint_id'] : null,
                    'distance_km' => 0,
                    'estimated_time_hours' => 0,
                    'altitude_m' => $prevDay ? $prevDay['altitude_m'] : null,
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
                        ]
                    ]
                ];
            }
        }

        return $days;
    }

    protected function normalize(array $aiOutput, Route $route, array $input, string $locale = 'en'): array
    {
        $normalized = ['days' => []];
        $requestedDays = $input['days'];
        $dayCounter = 1;

        // ✅ Calculate daily food cost for rest days (consistency)
        $dailyFoodCost = 0;
        foreach ($route->costs as $cost) {
            if ($cost->unit === 'per_day') {
                $dailyFoodCost = $cost->amount;
                break;
            }
        }

        // Filter out generic/empty days
        $filteredDays = array_filter($aiOutput['days'] ?? [], function ($day) {
            if (!empty($day['items'])) {
                return true;
            }
            if (!empty($day['description']) && strlen($day['description']) > 10) {
                return true;
            }
            $title = $day['title'] ?? '';
            if (preg_match('/^Day\s*\d+$/i', trim($title))) {
                return false;
            }
            if (strlen($title) > 10) {
                return true;
            }
            return false;
        });

        // If no valid days, use fallback with locale
        if (empty($filteredDays)) {
            $filteredDays = $this->generateFallbackItinerary($route, $input, $locale);
        }

        foreach ($filteredDays as $day) {
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

        // ✅ Pad with rest days if needed (max 3)
        if ($actualDays < $requestedDays) {
            $gap = $requestedDays - $actualDays;
            $restDaysToAdd = min($gap, 3);

            $lastDay = end($normalized['days']);
            $lastWaypoint = $lastDay['overnight_waypoint_id'] ?? null;
            $lastAltitude = $lastDay['altitude_m'] ?? null;

            for ($i = 1; $i <= $restDaysToAdd; $i++) {
                $dayNumber = $actualDays + $i;

                $titleRest = match($locale) {
                    'hi' => "दिन {$dayNumber}: आराम और अनुकूलन",
                    'zh' => "第 {$dayNumber} 天: 休息和适应",
                    default => "Day {$dayNumber}: Rest & Acclimatization",
                };

                $descRest = match($locale) {
                    'hi' => "आराम र अनुकूलनको दिन।",
                    'zh' => "休息和适应的一天。",
                    default => "Rest day to acclimatize and enjoy the mountain views.",
                };

                $normalized['days'][] = [
                    'day_number' => $dayNumber,
                    'title' => $titleRest,
                    'description' => $descRest,
                    'overnight_waypoint_id' => $lastWaypoint,
                    'distance_km' => 0,
                    'estimated_time_hours' => 0,
                    'altitude_m' => $lastAltitude,
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

            // ✅ Remaining days as "No Itinerary Data" with translation
            $remainingDays = $requestedDays - ($actualDays + $restDaysToAdd);
            for ($i = 1; $i <= $remainingDays; $i++) {
                $dayNumber = $actualDays + $restDaysToAdd + $i;

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

                $normalized['days'][] = [
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
        }

        // ✅ Ultimate fallback if no days at all
        if (empty($normalized['days'])) {
            $titleFallback = match($locale) {
                'hi' => "कोई यात्रा उत्पन्न नहीं हुई",
                'zh' => "未生成行程",
                default => "No Itinerary Generated",
            };

            $descFallback = match($locale) {
                'hi' => "यात्रा उत्पन्न करने में असमर्थ। कृपया भिन्न पैरामीटर के साथ पुनः प्रयास करें।",
                'zh' => "无法生成行程。请尝试使用不同的参数。",
                default => "Unable to generate an itinerary. Please try again with different parameters.",
            };

            $normalized['days'][] = [
                'day_number' => 1,
                'title' => $titleFallback,
                'description' => $descFallback,
                'overnight_waypoint_id' => null,
                'distance_km' => null,
                'estimated_time_hours' => null,
                'altitude_m' => null,
                'items' => [],
            ];
        }

        return $normalized;
    }
}
<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Waypoint;
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

        // ============================================================
        //  Day-Level Service Validation
        // ============================================================
        $dayServicesMap = $context['day_services'] ?? [];

        $hasValidDays = false;

        foreach ($aiOutput['days'] ?? [] as $day) {
            $dayNumber = $day['day_number'] ?? null;

            // Validate overnight waypoint
            if (!empty($day['overnight_waypoint_id'])) {
                if (!in_array($day['overnight_waypoint_id'], $validWaypointIds)) {
                    $errors[] = "Day {$dayNumber}: unknown waypoint ID {$day['overnight_waypoint_id']}.";
                }
            }

            // Get valid service IDs for this day
            $validServiceIds = [];
            if ($dayNumber !== null && isset($dayServicesMap[$dayNumber])) {
                $validServiceIds = $dayServicesMap[$dayNumber]->pluck('id')->toArray();
            }

            // Validate service IDs
            foreach ($day['items'] ?? [] as $item) {
                if (!empty($item['service_id'])) {
                    if (!in_array($item['service_id'], $validServiceIds)) {
                        $errors[] = "Day {$dayNumber}: Invalid service_id: {$item['service_id']} – not available for this day.";
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

    /**
     * Generate a fallback itinerary when AI fails.
     * Now with Rest Day Limit (max 3 rest days).
     */
    protected function generateFallbackItinerary(Route $route, array $input, string $locale = 'en'): array
    {
        $segments = $route->segments()->orderBy('sequence')->get();
        $requestedDays = $input['days'];
        $days = [];

        // First, add all trekking days (segments)
        for ($i = 0; $i < count($segments) && $i < $requestedDays; $i++) {
            $segment = $segments[$i];
            $from = $segment->fromWaypoint;
            $to = $segment->toWaypoint;

            // Check if this is a rest day (from == to or distance = 0)
            $isRestDay = ($from->id === $to->id || (float) $segment->distance_km == 0);

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
                    'hi' => "दिन " . ($i + 1) . ": {$from->name} → {$to->name}",
                    'zh' => "第 " . ($i + 1) . " 天: {$from->name} → {$to->name}",
                    default => "Day " . ($i + 1) . ": {$from->name} → {$to->name}",
                };
                $desc = match($locale) {
                    'hi' => "{$from->name} ({$from->altitude}मी) से {$to->name} ({$to->altitude}मी) तक ट्रेक। दूरी: {$segment->distance_km} किमी, अनुमानित समय: {$segment->estimated_time_hours} घंटे।",
                    'zh' => "从 {$from->name}（{$from->altitude}米）徒步到 {$to->name}（{$to->altitude}米）。距离：{$segment->distance_km}公里，预计时间：{$segment->estimated_time_hours}小时。",
                    default => "Trek from {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$segment->distance_km} km, estimated time: {$segment->estimated_time_hours} hrs.",
                };
            }

            $itemTitle = match($locale) {
                'hi' => $isRestDay ? "आराम दिन" : "ट्रेकिंग दिन",
                'zh' => $isRestDay ? "休息日" : "徒步日",
                default => $isRestDay ? "Rest Day" : "Trekking Day",
            };

            $itemDesc = match($locale) {
                'hi' => $isRestDay ? "{$to->name} में आराम करें" : "{$from->name} से {$to->name} तक ट्रेक करें",
                'zh' => $isRestDay ? "在 {$to->name} 休息" : "从 {$from->name} 徒步到 {$to->name}",
                default => $isRestDay ? "Rest at {$to->name}" : "Hike from {$from->name} to {$to->name}",
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
        }

        // ✅ Rest Day Limit: अधिकतम 3 rest days मात्र
        $currentDays = count($days);
        $maxRestDays = min(3, $requestedDays - $currentDays);
        $restDaysAdded = 0;

        while ($restDaysAdded < $maxRestDays && $currentDays + $restDaysAdded < $requestedDays) {
            $prevDay = $days[$currentDays + $restDaysAdded - 1] ?? null;
            $dayNumber = $currentDays + $restDaysAdded + 1;
            $waypointId = $prevDay ? $prevDay['overnight_waypoint_id'] : null;
            $waypoint = $waypointId ? Waypoint::find($waypointId) : null;
            $waypointName = $waypoint ? $waypoint->name : 'Unknown';

            $titleRest = match($locale) {
                'hi' => "{$waypointName} में अनुकूलन दिवस",
                'zh' => "{$waypointName} 适应日",
                'np' => "{$waypointName} मा अनुकूलन दिन",
                default => "Acclimatization Day at {$waypointName}",
            };

            $descRest = match($locale) {
                'hi' => "आज कोई ट्रेकिंग नहीं। {$waypointName} में आराम और अनुकूलन।",
                'zh' => "今天不徒步。在 {$waypointName} 休息和适应。",
                'np' => "आज कुनै ट्रेकिङ छैन। {$waypointName} मा आराम र अनुकूलन।",
                default => "No trekking today. Rest and acclimatize at {$waypointName}.",
            };

            $days[] = [
                'day_number' => $dayNumber,
                'title' => $titleRest,
                'description' => $descRest,
                'overnight_waypoint_id' => $waypointId,
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
            $restDaysAdded++;
        }

        // ✅ बाँकी days को लागि "No Itinerary Data" (translated)
        while (count($days) < $requestedDays) {
            $dayNumber = count($days) + 1;

            $titleNoData = match($locale) {
                'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                'zh' => "第 {$dayNumber} 天: 无行程数据",
                'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                default => "Day {$dayNumber}: No Itinerary Data",
            };

            $descNoData = match($locale) {
                'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया। कृपया अपना अनुरोध समायोजित करें या पुनः प्रयास करें।",
                'zh' => "AI 没有为此天生成数据。请调整您的请求或重试。",
                'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन। कृपया आफ्नो अनुरोध समायोजन गर्नुहोस् वा पुनः प्रयास गर्नुहोस्।",
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

        return $days;
    }

    /**
     * Normalize and validate the AI output.
     * - Filters out generic/empty days.
     * - Ensures requested days count with rest days (max 3) and "No Itinerary Data".
     * - ✅ Overrides rest day titles (distance = 0) to "Acclimatization Day at [Waypoint]".
     */
    protected function normalize(array $aiOutput, Route $route, array $input, string $locale = 'en'): array
    {
        $normalized = ['days' => []];
        $requestedDays = $input['days'];
        $dayCounter = 1;

        // Calculate daily food cost for rest days (consistency)
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

        // ============================================================
        //  ✅ Rest Day Title Override (for days with distance = 0)
        //  यदि distance_km 0 छ र waypoint ID छ भने, title लाई
        //  "Acclimatization Day at [Waypoint]" मा बदल्ने
        // ============================================================
        foreach ($normalized['days'] as &$normalizedDay) {
            if (
                isset($normalizedDay['distance_km']) &&
                (float) $normalizedDay['distance_km'] == 0 &&
                !empty($normalizedDay['overnight_waypoint_id'])
            ) {
                $waypoint = Waypoint::find($normalizedDay['overnight_waypoint_id']);
                if ($waypoint) {
                    $normalizedDay['title'] = match($locale) {
                        'hi' => "{$waypoint->name} में अनुकूलन दिवस",
                        'zh' => "{$waypoint->name} 适应日",
                        'np' => "{$waypoint->name} मा अनुकूलन दिन",
                        default => "Acclimatization Day at {$waypoint->name}",
                    };
                    $normalizedDay['description'] = match($locale) {
                        'hi' => "आज कोई ट्रेकिंग नहीं। {$waypoint->name} में आराम और अनुकूलन।",
                        'zh' => "今天不徒步。在 {$waypoint->name} 休息和适应。",
                        'np' => "आज कुनै ट्रेकिङ छैन। {$waypoint->name} मा आराम र अनुकूलन।",
                        default => "No trekking today. Rest and acclimatize at {$waypoint->name}.",
                    };
                }
            }
        }
        unset($normalizedDay);

        $actualDays = count($normalized['days']);

        // Pad with rest days if needed (max 3)
        if ($actualDays < $requestedDays) {
            $gap = $requestedDays - $actualDays;
            $restDaysToAdd = min($gap, 3);

            $lastDay = end($normalized['days']);
            $lastWaypoint = $lastDay['overnight_waypoint_id'] ?? null;
            $lastAltitude = $lastDay['altitude_m'] ?? null;

            for ($i = 1; $i <= $restDaysToAdd; $i++) {
                $dayNumber = $actualDays + $i;
                $waypoint = $lastWaypoint ? Waypoint::find($lastWaypoint) : null;
                $waypointName = $waypoint ? $waypoint->name : 'Unknown';

                $titleRest = match($locale) {
                    'hi' => "{$waypointName} में अनुकूलन दिवस",
                    'zh' => "{$waypointName} 适应日",
                    'np' => "{$waypointName} मा अनुकूलन दिन",
                    default => "Acclimatization Day at {$waypointName}",
                };

                $descRest = match($locale) {
                    'hi' => "आज कोई ट्रेकिंग नहीं। {$waypointName} में आराम और अनुकूलन।",
                    'zh' => "今天不徒步。在 {$waypointName} 休息和适应。",
                    'np' => "आज कुनै ट्रेकिङ छैन। {$waypointName} मा आराम र अनुकूलन।",
                    default => "No trekking today. Rest and acclimatize at {$waypointName}.",
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

            // Remaining days as "No Itinerary Data" with translation
            $remainingDays = $requestedDays - ($actualDays + $restDaysToAdd);
            for ($i = 1; $i <= $remainingDays; $i++) {
                $dayNumber = $actualDays + $restDaysToAdd + $i;

                $titleNoData = match($locale) {
                    'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                    'zh' => "第 {$dayNumber} 天: 无行程数据",
                    'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                    default => "Day {$dayNumber}: No Itinerary Data",
                };

                $descNoData = match($locale) {
                    'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया। कृपया अपना अनुरोध समायोजित करें या पुनः प्रयास करें।",
                    'zh' => "AI 没有为此天生成数据。请调整您的请求或重试。",
                    'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन। कृपया आफ्नो अनुरोध समायोजन गर्नुहोस् वा पुनः प्रयास गर्नुहोस्।",
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

        // Ultimate fallback if no days at all
        if (empty($normalized['days'])) {
            $titleFallback = match($locale) {
                'hi' => "कोई यात्रा उत्पन्न नहीं हुई",
                'zh' => "未生成行程",
                'np' => "कुनै यात्रा उत्पन्न भएन",
                default => "No Itinerary Generated",
            };

            $descFallback = match($locale) {
                'hi' => "यात्रा उत्पन्न करने में असमर्थ। कृपया भिन्न पैरामीटर के साथ पुनः प्रयास करें।",
                'zh' => "无法生成行程。请尝试使用不同的参数。",
                'np' => "यात्रा उत्पन्न गर्न असमर्थ। कृपया फरक प्यारामिटरको साथ पुनः प्रयास गर्नुहोस्।",
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
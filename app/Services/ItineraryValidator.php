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

        if (isset($aiOutput['itinerary']) && !isset($aiOutput['days'])) {
            $aiOutput['days'] = $aiOutput['itinerary'];
        }

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

        $validWaypointIds = $route->segments->pluck('from_waypoint_id')
            ->merge($route->segments->pluck('to_waypoint_id'))
            ->unique()->toArray();

        $dayServicesMap = $context['day_services'] ?? [];
        $hasValidDays = false;

        foreach ($aiOutput['days'] ?? [] as $day) {
            $dayNumber = $day['day_number'] ?? null;

            if (!empty($day['overnight_waypoint_id'])) {
                if (!in_array($day['overnight_waypoint_id'], $validWaypointIds)) {
                    $errors[] = "Day {$dayNumber}: unknown waypoint ID {$day['overnight_waypoint_id']}.";
                }
            }

            $validServiceIds = [];
            if ($dayNumber !== null && isset($dayServicesMap[$dayNumber])) {
                $validServiceIds = $dayServicesMap[$dayNumber]->pluck('id')->toArray();
            }

            foreach ($day['items'] ?? [] as $item) {
                if (!empty($item['service_id'])) {
                    if (!in_array($item['service_id'], $validServiceIds)) {
                        $errors[] = "Day {$dayNumber}: Invalid service_id: {$item['service_id']} – not available for this day.";
                    }
                }
            }

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
        $isTour = $this->isTourRoute($route);

        for ($i = 0; $i < count($segments) && $i < $requestedDays; $i++) {
            $segment = $segments[$i];
            $from = $segment->fromWaypoint;
            $to = $segment->toWaypoint;

            $isRestDay = ($from->id === $to->id || (float) $segment->distance_km == 0);

            if ($isRestDay) {
                $altitude = $to->altitude ?? 0;
                $title = match($locale) {
                    'hi' => ($altitude >= 3000 && !$isTour) ? "{$to->name} में अनुकूलन दिवस" : "आराम दिन",
                    'zh' => ($altitude >= 3000 && !$isTour) ? "{$to->name} 适应日" : "休息日",
                    'np' => ($altitude >= 3000 && !$isTour) ? "{$to->name} मा अनुकूलन दिन" : "आराम दिन",
                    default => ($altitude >= 3000 && !$isTour) ? "Acclimatization Day at {$to->name}" : "Rest Day",
                };
                $desc = match($locale) {
                    'hi' => ($altitude >= 3000 && !$isTour) ? "आज कोई ट्रेकिंग नहीं। {$to->name} में आराम और अनुकूलन।" : "आजको दिन आराम गर्नुहोस्।",
                    'zh' => ($altitude >= 3000 && !$isTour) ? "今天不徒步。在 {$to->name} 休息和适应。" : "今天休息。",
                    'np' => ($altitude >= 3000 && !$isTour) ? "आज कुनै ट्रेकिङ छैन। {$to->name} मा आराम र अनुकूलन।" : "आजको दिन आराम गर्नुहोस्।",
                    default => ($altitude >= 3000 && !$isTour) ? "No trekking today. Rest and acclimatize at {$to->name}." : "Rest today.",
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
                        'title' => $isRestDay ? 'Rest Day' : 'Trekking Day',
                        'description' => $isRestDay ? 'Rest and relax.' : "Hike from {$from->name} to {$to->name}",
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

        $currentDays = count($days);
        $maxRestDays = min(3, $requestedDays - $currentDays);
        $restDaysAdded = 0;

        while ($restDaysAdded < $maxRestDays && $currentDays + $restDaysAdded < $requestedDays) {
            $prevDay = $days[$currentDays + $restDaysAdded - 1] ?? null;
            $dayNumber = $currentDays + $restDaysAdded + 1;
            $waypointId = $prevDay ? $prevDay['overnight_waypoint_id'] : null;
            $waypoint = $waypointId ? Waypoint::find($waypointId) : null;
            $altitude = $waypoint ? $waypoint->altitude : 0;

            if ($altitude < 3000) {
                break;
            }

            $waypointName = $waypoint ? $waypoint->name : 'Unknown';
            $titleRest = match($locale) {
                'hi' => $isTour ? "आराम दिन" : "{$waypointName} में अनुकूलन दिवस",
                'zh' => $isTour ? "休息日" : "{$waypointName} 适应日",
                'np' => $isTour ? "आराम दिन" : "{$waypointName} मा अनुकूलन दिन",
                default => $isTour ? "Rest Day" : "Acclimatization Day at {$waypointName}",
            };

            $days[] = [
                'day_number' => $dayNumber,
                'title' => $titleRest,
                'description' => "No trekking today. Rest and acclimatize at {$waypointName}.",
                'overnight_waypoint_id' => $waypointId,
                'distance_km' => 0,
                'estimated_time_hours' => 0,
                'altitude_m' => $altitude,
                'items' => [
                    [
                        'title' => 'Rest Day',
                        'description' => 'Rest and relax.',
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

        while (count($days) < $requestedDays) {
            $dayNumber = count($days) + 1;
            $days[] = [
                'day_number' => $dayNumber,
                'title' => match($locale) {
                    'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                    'zh' => "第 {$dayNumber} 天: 无行程数据",
                    'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                    default => "Day {$dayNumber}: No Itinerary Data",
                },
                'description' => match($locale) {
                    'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया।",
                    'zh' => "AI 没有为此天生成数据。",
                    'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन।",
                    default => "The AI did not generate data for this day.",
                },
                'overnight_waypoint_id' => null,
                'distance_km' => null,
                'estimated_time_hours' => null,
                'altitude_m' => null,
                'items' => [],
            ];
        }

        return $days;
    }

    protected function normalize(array $aiOutput, Route $route, array $input, string $locale = 'en'): array
    {
        $normalized = ['days' => []];
        $requestedDays = $input['days'];
        $dayCounter = 1;

        $dailyFoodCost = 0;
        foreach ($route->costs as $cost) {
            if ($cost->unit === 'per_day') {
                $dailyFoodCost = $cost->amount;
                break;
            }
        }

        $filteredDays = array_filter($aiOutput['days'] ?? [], function ($day) {
            $title = $day['title'] ?? '';
            
            if (stripos($title, 'no itinerary') !== false ||
                stripos($title, 'no data') !== false ||
                stripos($title, 'कोई यात्रा') !== false ||
                stripos($title, '无行程') !== false ||
                preg_match('/no\s*data/i', $title) ||
                preg_match('/no\s*itinerary/i', $title)) {
                return true;
            }
            
            if (!empty($day['items'])) return true;
            if (!empty($day['description']) && strlen($day['description']) > 10) return true;
            if (preg_match('/^Day\s*\d+$/i', trim($title))) return false;
            return strlen($title) > 10;
        });

        if (empty($filteredDays)) {
            $filteredDays = $this->generateFallbackItinerary($route, $input, $locale);
        }
// ✅ Trim days if more than requested (applies to ALL routes)
if (count($filteredDays) > $requestedDays) {
    $filteredDays = array_slice($filteredDays, 0, $requestedDays);
}
        // ============================================================
        // ✅ Track "No Itinerary Data" count for proper conversion
        // ============================================================
        $noDataCount = 0;
        $hasNoDataDay = false;

        foreach ($filteredDays as $day) {
            $isRestDay = isset($day['distance_km']) && (float) $day['distance_km'] == 0;
            $altitude = null;

            if ($isRestDay && !empty($day['overnight_waypoint_id'])) {
                $waypoint = Waypoint::find($day['overnight_waypoint_id']);
                $altitude = $waypoint ? $waypoint->altitude : null;
            }

            // Skip low-altitude rest days (Jomsom 2700m)
            if ($isRestDay && ($altitude === null || $altitude < 3000)) {
                Log::info("⏭️ Skipping low-altitude rest day at waypoint ID: " . ($day['overnight_waypoint_id'] ?? 'null'));
                continue;
            }

            $originalTitle = $day['title'] ?? '';
            
            $isNoData = (stripos($originalTitle, 'no itinerary') !== false ||
                         stripos($originalTitle, 'no data') !== false ||
                         stripos($originalTitle, 'कोई यात्रा') !== false ||
                         stripos($originalTitle, '无行程') !== false ||
                         stripos($originalTitle, 'buffer') !== false ||
                         preg_match('/no\s*data/i', $originalTitle) ||
                         preg_match('/no\s*itinerary/i', $originalTitle) ||
                         (trim($originalTitle) === '') ||
                         (preg_match('/^Day\s*\d+\s*[:：]?\s*$/i', trim($originalTitle))));

            if ($isNoData) {
                $noDataCount++;
                $dayNumber = $dayCounter++;

                if ($noDataCount > 1) {
                    // ✅ SECOND "No Itinerary Data" → Buffer Day
                    $newTitle = match($locale) {
                        'hi' => "दिन {$dayNumber}: बफर दिन",
                        'zh' => "第 {$dayNumber} 天: 缓冲日",
                        'np' => "दिन {$dayNumber}: बफर दिन",
                        default => "Day {$dayNumber}: Buffer Day",
                    };
                    $newDescription = match($locale) {
                        'hi' => "यो दिन यात्राको लागि अतिरिक्त बफरको रूपमा राखिएको छ।",
                        'zh' => "此日为行程预留的额外缓冲日。",
                        'np' => "यो दिन यात्राको लागि अतिरिक्त बफरको रूपमा राखिएको छ।",
                        default => "This day is kept as an extra buffer for the journey.",
                    };
                } else {
                    // ✅ FIRST "No Itinerary Data" — keep as is
                    $newTitle = match($locale) {
                        'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                        'zh' => "第 {$dayNumber} 天: 无行程数据",
                        'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                        default => "Day {$dayNumber}: No Itinerary Data",
                    };
                    $newDescription = match($locale) {
                        'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया। कृपया अपना अनुरोध समायोजित करें।",
                        'zh' => "AI 没有为此天生成数据。请调整您的请求。",
                        'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन। कृपया आफ्नो अनुरोध समायोजन गर्नुहोस्।",
                        default => "The AI did not generate data for this day. Please adjust your request.",
                    };
                    $hasNoDataDay = true;
                }

                $normalized['days'][] = [
                    'day_number' => $dayNumber,
                    'title' => $newTitle,
                    'description' => $newDescription,
                    'overnight_waypoint_id' => null,
                    'distance_km' => null,
                    'estimated_time_hours' => null,
                    'altitude_m' => null,
                    'items' => [],
                ];
                continue;
            }

            // Normal day with trekking data
            $dayNumber = $dayCounter++;
            $patterns = ['/^Day\s*\d+\s*[:：]/i', '/^दिन\s*\d+\s*[:：]/', '/^第\s*\d+\s*天\s*[:：]/'];
            $newTitle = preg_replace($patterns, "Day {$dayNumber}: ", $originalTitle);
            if ($newTitle === $originalTitle) {
                $newTitle = "Day {$dayNumber}: " . $originalTitle;
            }

            $normalized['days'][] = [
                'day_number' => $dayNumber,
                'title' => $newTitle,
                'description' => $day['description'] ?? '',
                'overnight_waypoint_id' => $day['overnight_waypoint_id'] ?? null,
                'distance_km' => $day['distance_km'] ?? null,
                'estimated_time_hours' => $day['estimated_time_hours'] ?? null,
                'altitude_m' => $day['altitude_m'] ?? $altitude ?? null,
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
        //  Rest Day Title Override (for distance=0, altitude>=3000m)
        // ============================================================
        $isTour = $this->isTourRoute($route);
        foreach ($normalized['days'] as &$normalizedDay) {
            if (isset($normalizedDay['distance_km']) && (float) $normalizedDay['distance_km'] == 0 && !empty($normalizedDay['overnight_waypoint_id'])) {
                $waypoint = Waypoint::find($normalizedDay['overnight_waypoint_id']);
                if ($waypoint && ($waypoint->altitude ?? 0) >= 3000 && !$isTour) {
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
                } else {
                    $normalizedDay['title'] = match($locale) {
                        'hi' => "आराम दिन",
                        'zh' => "休息日",
                        'np' => "आराम दिन",
                        default => "Rest Day",
                    };
                    $normalizedDay['description'] = match($locale) {
                        'hi' => "आजको दिन आराम गर्नुहोस्।",
                        'zh' => "今天休息。",
                        'np' => "आजको दिन आराम गर्नुहोस्।",
                        default => "Rest today.",
                    };
                }
            }
        }
        unset($normalizedDay);

        $actualDays = count($normalized['days']);

        // ============================================================
        //  ✅ FINAL PADDING: First missing → "No Itinerary Data",
        //     subsequent → "Buffer Day"
        // ============================================================
        if ($actualDays < $requestedDays) {
            $gap = $requestedDays - $actualDays;
            
            $lastDay = end($normalized['days']);
            $lastWaypointId = $lastDay['overnight_waypoint_id'] ?? null;
            $lastDistance = $lastDay['distance_km'] ?? null;
            $waypoint = $lastWaypointId ? Waypoint::find($lastWaypointId) : null;
            $lastAltitude = $waypoint ? $waypoint->altitude : 0;

            // First try to add rest days (if altitude >= 3000)
            if ($lastDistance !== null && $lastAltitude >= 3000) {
                $restDaysToAdd = min(3, $gap);
                for ($i = 1; $i <= $restDaysToAdd; $i++) {
                    $dayNumber = $actualDays + $i;
                    $waypointName = $waypoint ? $waypoint->name : 'Unknown';
                    $titleRest = match($locale) {
                        'hi' => $isTour ? "आराम दिन" : "{$waypointName} में अनुकूलन दिवस",
                        'zh' => $isTour ? "休息日" : "{$waypointName} 适应日",
                        'np' => $isTour ? "आराम दिन" : "{$waypointName} मा अनुकूलन दिन",
                        default => $isTour ? "Rest Day" : "Acclimatization Day at {$waypointName}",
                    };
                    $normalized['days'][] = [
                        'day_number' => $dayNumber,
                        'title' => $titleRest,
                        'description' => $isTour ? "Rest today." : "No trekking today. Rest and acclimatize at {$waypointName}.",
                        'overnight_waypoint_id' => $lastWaypointId,
                        'distance_km' => 0,
                        'estimated_time_hours' => 0,
                        'altitude_m' => $lastAltitude,
                        'items' => [
                            [
                                'title' => 'Rest Day',
                                'description' => 'Rest and relax.',
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
                $gap = $requestedDays - count($normalized['days']);
            }

            // ✅ Remaining days: first → "No Itinerary Data", rest → "Buffer Day"
            if ($gap > 0) {
                if (!$hasNoDataDay) {
                    // First missing day → "No Itinerary Data"
                    $dayNumber = count($normalized['days']) + 1;
                    $normalized['days'][] = [
                        'day_number' => $dayNumber,
                        'title' => match($locale) {
                            'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                            'zh' => "第 {$dayNumber} 天: 无行程数据",
                            'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                            default => "Day {$dayNumber}: No Itinerary Data",
                        },
                        'description' => match($locale) {
                            'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया। कृपया अपना अनुरोध समायोजित करें।",
                            'zh' => "AI 没有为此天生成数据。请调整您的请求。",
                            'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन। कृपया आफ्नो अनुरोध समायोजन गर्नुहोस्।",
                            default => "The AI did not generate data for this day. Please adjust your request.",
                        },
                        'overnight_waypoint_id' => null,
                        'distance_km' => null,
                        'estimated_time_hours' => null,
                        'altitude_m' => null,
                        'items' => [],
                    ];
                    $gap--;
                    $hasNoDataDay = true;
                }

                // ✅ All remaining → "Buffer Day"
                for ($i = 1; $i <= $gap; $i++) {
                    $dayNumber = count($normalized['days']) + 1;
                    $normalized['days'][] = [
                        'day_number' => $dayNumber,
                        'title' => match($locale) {
                            'hi' => "दिन {$dayNumber}: बफर दिन",
                            'zh' => "第 {$dayNumber} 天: 缓冲日",
                            'np' => "दिन {$dayNumber}: बफर दिन",
                            default => "Day {$dayNumber}: Buffer Day",
                        },
                        'description' => match($locale) {
                            'hi' => "यो दिन यात्राको लागि अतिरिक्त बफरको रूपमा राखिएको छ।",
                            'zh' => "此日为行程预留的额外缓冲日。",
                            'np' => "यो दिन यात्राको लागि अतिरिक्त बफरको रूपमा राखिएको छ।",
                            default => "This day is kept as an extra buffer for the journey.",
                        },
                        'overnight_waypoint_id' => null,
                        'distance_km' => null,
                        'estimated_time_hours' => null,
                        'altitude_m' => null,
                        'items' => [],
                    ];
                }
            }
        }

        if (empty($normalized['days'])) {
            $normalized['days'][] = [
                'day_number' => 1,
                'title' => match($locale) {
                    'hi' => "कोई यात्रा उत्पन्न नहीं हुई",
                    'zh' => "未生成行程",
                    'np' => "कुनै यात्रा उत्पन्न भएन",
                    default => "No Itinerary Generated",
                },
                'description' => match($locale) {
                    'hi' => "यात्रा उत्पन्न करने में असमर्थ। कृपया भिन्न पैरामीटर के साथ पुनः प्रयास करें।",
                    'zh' => "无法生成行程。请尝试使用不同的参数。",
                    'np' => "यात्रा उत्पन्न गर्न असमर्थ। कृपया फरक प्यारामिटरको साथ पुनः प्रयास गर्नुहोस्।",
                    default => "Unable to generate an itinerary. Please try again with different parameters.",
                },
                'overnight_waypoint_id' => null,
                'distance_km' => null,
                'estimated_time_hours' => null,
                'altitude_m' => null,
                'items' => [],
            ];
        }

        return $normalized;
    }

    private function isTourRoute(Route $route): bool
    {
        $tourKeywords = ['Tour', 'Safari', 'Heritage', 'Pilgrimage', 'Circuit', 'Sightseeing'];
        foreach ($tourKeywords as $keyword) {
            if (stripos($route->name, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}
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

        $route->refresh();
        $route->load(['segments.fromWaypoint', 'segments.toWaypoint', 'costs']);

        if ($route->segments->isEmpty()) {
            $this->ensureSegmentsForTour($route);
            $route->load(['segments.fromWaypoint', 'segments.toWaypoint']);
        }

        // ============================================================
        // BUILD SEGMENTS WITH OVERNIGHT STOP FILTER (UPDATED: track merged waypoints)
        // ============================================================
        $overnightSegments = [];
        $dayNumber = 1;
        $mergedSegment = null;
        $mergedWaypoints = []; // Track non-overnight waypoints for round-trip detection
        $segments = $route->segments()->orderBy('sequence')->get();

        foreach ($segments as $segment) {
            $toWaypoint = $segment->toWaypoint;
            $isOvernight = $toWaypoint->is_overnight_stop ?? true;

            if ($isOvernight) {
                if ($mergedSegment) {
                    $merged = $this->mergeSegments($mergedSegment, $segment);
                    $overnightSegments[] = [
                        'sequence' => $dayNumber++,
                        'segment' => $merged,
                        'merged_waypoints' => array_values(array_unique($mergedWaypoints)),
                    ];
                    $mergedSegment = null;
                    $mergedWaypoints = [];
                } else {
                    $overnightSegments[] = [
                        'sequence' => $dayNumber++,
                        'segment' => $segment,
                        'merged_waypoints' => [],
                    ];
                }
            } else {
                // Non-overnight waypoint: add to merged list
                $mergedWaypoints[] = $toWaypoint->name;
                if ($mergedSegment) {
                    $mergedSegment = $this->mergeSegments($mergedSegment, $segment);
                } else {
                    $mergedSegment = clone $segment;
                }
            }
        }

        if ($mergedSegment) {
            $overnightSegments[] = [
                'sequence' => $dayNumber++,
                'segment' => $mergedSegment,
                'merged_waypoints' => array_values(array_unique($mergedWaypoints)),
            ];
        }

        Log::info("📊 Total overnight segments: " . count($overnightSegments));

        // ============================================================
        // BUILD DAY SERVICES MAP
        // ============================================================
        $dayServicesMap = [];
        $dayDiagnostics = [];

        foreach ($overnightSegments as $item) {
            $dayNum = $item['sequence'];
            $seg = $item['segment'];
            $waypoint = $seg->toWaypoint;

            $result = $this->getServicesForDay($waypoint, $input);
            $dayServicesMap[$dayNum] = $result['services'] ?? collect();
            if ($result['diagnostic']) {
                $dayDiagnostics[$dayNum] = $result['diagnostic'];
            }
        }

        $totalDays = $input['days'];
        for ($i = count($overnightSegments) + 1; $i <= $totalDays; $i++) {
            $dayServicesMap[$i] = collect();
        }

        // ============================================================
        // COST CALCULATION (system costs only)
        // ============================================================
        $costBreakdown = $this->calculateCost($route, $input, [], $locale);

        // ============================================================
        // BUILD CONTEXT
        // ============================================================
        $context = $this->buildContext($route, $input, $costBreakdown, $dayServicesMap, $dayDiagnostics, $overnightSegments);

        // ============================================================
        // ⚠️ FORCE FALLBACK FOR TESTING (remove after fix)
        // ============================================================
        $aiResponse = $this->buildFallbackResponse($route, $input, $locale, $overnightSegments);
        $usedFallback = true;

        // ============================================================
        // (Original AI call – now skipped for forced fallback)
        // ============================================================
        /*
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
            $aiResponse = $this->buildFallbackResponse($route, $input, $locale, $overnightSegments);
            $usedFallback = true;
        }

        if ($aiResponse && !$usedFallback) {
            if (!$this->isLanguageCorrect($aiResponse, $locale)) {
                Log::warning('⚠️ AI response language mismatch, using fallback.', ['locale' => $locale]);
                $aiResponse = $this->buildFallbackResponse($route, $input, $locale, $overnightSegments);
                $usedFallback = true;
            }
        }
        */

        // ============================================================
        // VALIDATE & NORMALIZE
        // ============================================================
        $validated = $this->validator->validate($aiResponse, $route, $input, $context, $locale);

        // ============================================================
        // BUFFER DAY FIX: Convert second "No Itinerary Data" to Buffer Day
        // ============================================================
        $noDataCount = 0;
        foreach ($validated['days'] as $index => $day) {
            // Check if title contains "No Itinerary Data" (any language)
            if (strpos($day['title'], 'No Itinerary Data') !== false ||
                strpos($day['title'], 'कोई यात्रा डेटा नहीं') !== false ||
                strpos($day['title'], '无行程数据') !== false ||
                strpos($day['title'], 'यात्रा डेटा छैन') !== false) {
                $noDataCount++;
                if ($noDataCount > 1) {
                    $dayNumber = $day['day_number'];
                    $validated['days'][$index]['title'] = "Day {$dayNumber}: Buffer Day";
                    $validated['days'][$index]['description'] = "This day is kept as an extra buffer for the journey.";
                }
            }
        }

        // ============================================================
        // ATTACH SERVICES TO DAYS
        // ============================================================
        foreach ($validated['days'] as &$dayData) {
            $dayNumber = $dayData['day_number'];
            $services = $dayServicesMap[$dayNumber] ?? collect();

            if ($dayData['distance_km'] === null) {
                continue;
            }

            // ✅ REST DAY FIX (STRONGER): Override items and skip service attachment
            if ((float) $dayData['distance_km'] == 0) {
                $dayData['items'] = [
                    [
                        'title' => 'Rest Day',
                        'description' => 'Rest and relax at the lodge.',
                        'time_of_day' => 'morning',
                        'cost' => 0,
                        'pricing_source' => 'system_estimate',
                        'pricing_snapshot' => null,
                        'service_id' => null,
                        'is_optional' => false,
                        'metadata' => null,
                    ]
                ];
                continue;
            }

            $waypointId = $dayData['overnight_waypoint_id'] ?? null;
            if (!$waypointId) {
                continue;
            }

            $waypoint = Waypoint::find($waypointId);
            if (!$waypoint) {
                continue;
            }

            $locationId = $waypoint->location_id;
            $bestService = null;

            foreach ($services as $svc) {
                if (($svc['location_id'] ?? null) == $locationId) {
                    $bestService = $svc;
                    break;
                }
            }

            if (!$bestService) {
                $waypointName = $waypoint->name;
                $fallbackService = Service::where('status', 'active')
                    ->where('name', 'LIKE', "%{$waypointName}%")
                    ->whereHas('category', function ($q) {
                        $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
                    })
                    ->first();

                if ($fallbackService) {
                    $bestService = [
                        'id' => $fallbackService->id,
                        'name' => $fallbackService->name,
                        'price' => (float) $fallbackService->price,
                        'currency' => $fallbackService->currency ?? 'NPR',
                        'provider' => $fallbackService->provider->name ?? 'TravelAI Partner',
                        'location_id' => $fallbackService->location_id,
                    ];
                    Log::info("✅ Fallback: Day {$dayNumber} using service {$fallbackService->name} for {$waypointName}");
                }
            }

            if (!$bestService) {
                Log::info("ℹ️ No service found for Day {$dayNumber} ({$waypoint->name})");
                continue;
            }

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
                    'description' => 'Service Included',
                    'time_of_day' => 'afternoon',
                    'cost' => $priceNpr,
                    'currency' => 'NPR',
                    'pricing_source' => 'provider_service',
                    'pricing_snapshot' => null,
                    'service_id' => $bestService['id'],
                    'is_optional' => false,
                    'metadata' => null,
                    'provider' => $bestService['provider'] ?? 'TravelAI Partner',
                ];
                Log::info("✅ Attached service to Day {$dayNumber}: {$bestService['name']} (NPR {$priceNpr})");
            }
        }
        unset($dayData);

        // ============================================================
        // SAVE TO DB
        // ============================================================
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

            $breakdown = $costBreakdown['breakdown'] ?? [];
            $totalCost = 0;

            foreach ($breakdown as $key => $item) {
                if ($key !== 'services') {
                    $totalCost += $item['amount'] ?? 0;
                }
            }

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

            $finalBreakdown = array_merge($breakdown, $perDayServiceCosts);

            // ✅ BUDGET WARNING
            $budgetNpr = $input['budget'] * 133;
            if ($input['budget'] > 0 && $totalCost > $budgetNpr) {
                $overPercent = (($totalCost - $budgetNpr) / $budgetNpr) * 100;
                if ($overPercent > 10) {
                    $finalBreakdown['budget_insufficient'] = [
                        'name' => '⚠️ Budget Warning',
                        'amount' => 0,
                        'currency' => 'NPR',
                        'unit' => 'note',
                        'is_mandatory' => false,
                        'provider_name' => 'System',
                        'message' => "Your budget of {$input['budget']} USD is " . round($overPercent, 0) . "% over the estimated cost. Consider increasing your budget or choosing a more affordable style.",
                    ];
                    Log::info("⚠️ Budget warning added: {$overPercent}% over budget");
                }
            }

            Log::info("💰 Total cost: NPR {$totalCost}, Budget: NPR {$budgetNpr}");

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
    // HELPER: MERGE TWO SEGMENTS
    // ==========================================
    protected function mergeSegments($seg1, $seg2)
    {
        $merged = clone $seg1;
        $merged->distance_km = (float)$seg1->distance_km + (float)$seg2->distance_km;
        $merged->estimated_time_hours = (float)$seg1->estimated_time_hours + (float)$seg2->estimated_time_hours;
        $merged->elevation_gain_m = (float)$seg1->elevation_gain_m + (float)$seg2->elevation_gain_m;
        $merged->elevation_loss_m = (float)$seg1->elevation_loss_m + (float)$seg2->elevation_loss_m;
        $merged->to_waypoint_id = $seg2->to_waypoint_id;
        $merged->toWaypoint = $seg2->toWaypoint;
        return $merged;
    }

    // ==========================================
    // COST CALCULATION (system costs only)
    // ==========================================
    protected function calculateCost(Route $route, array $input, array $services, string $locale = 'en'): array
    {
        $days = $input['days'] ?? $route->duration_days;
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

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    // ==========================================
    // CONTEXT + PROMPT
    // ==========================================
    protected function buildContext(Route $route, array $input, array $cost, array $dayServicesMap, array $dayDiagnostics = [], array $overnightSegments = []): array
    {
        $segments = [];
        foreach ($overnightSegments as $item) {
            $seg = $item['segment'];
            $segments[] = [
                'sequence' => $item['sequence'],
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
                    'np' => 'Nepali (Devanagari script). ONLY waypoint names like "Nayapul" can remain in English. Everything else must be in Nepali.',
                    default => 'English.',
                },
            ],
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // FALLBACK (with round-trip detection)
    // ==========================================
    protected function buildFallbackResponse(Route $route, array $input, string $locale = 'en', array $overnightSegments = []): array
    {
        $days = [];
        $dayNumber = 1;
        $maxDailyDistance = 15;

        foreach ($overnightSegments as $item) {
            $seg = $item['segment'];
            $from = $seg->fromWaypoint;
            $to = $seg->toWaypoint;
            $mergedWaypoints = $item['merged_waypoints'] ?? [];

            $distance = (float) $seg->distance_km;
            $isLongDay = $distance > $maxDailyDistance;

            // 🆕 Round-trip detection: same start/end, distance > 0, and merged waypoints exist
            if ($from->id === $to->id && $distance > 0 && !empty($mergedWaypoints)) {
                $landmarkName = implode(' → ', $mergedWaypoints);
                $title = match($locale) {
                    'hi' => "दिन {$dayNumber}: {$from->name} → {$landmarkName} → {$to->name}",
                    'zh' => "第 {$dayNumber} 天: {$from->name} → {$landmarkName} → {$to->name}",
                    'np' => "दिन {$dayNumber}: {$from->name} → {$landmarkName} → {$to->name}",
                    default => "Day {$dayNumber}: {$from->name} → {$landmarkName} → {$to->name}",
                };
                $desc = match($locale) {
                    'hi' => "{$from->name} बाट {$landmarkName} को यात्रा र फिर्ता। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घंटे。" . ($isLongDay ? " ⚠️ लामो दिन – 15 किमी भन्दा बढी।" : ""),
                    'zh' => "从 {$from->name} 到 {$landmarkName} 的往返旅行。距离：{$distance}公里，预计时间：{$seg->estimated_time_hours}小时。" . ($isLongDay ? " ⚠️ 长日 – 超过15公里。" : ""),
                    'np' => "{$from->name} बाट {$landmarkName} को यात्रा र फिर्ता। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घण्टा。" . ($isLongDay ? " ⚠️ लामो दिन – १५ किमी भन्दा बढी。" : ""),
                    default => "Round trip from {$from->name} to {$landmarkName} and back. Distance: {$distance} km, estimated time: {$seg->estimated_time_hours} hrs." . ($isLongDay ? " ⚠️ Long day – over 15km." : ""),
                };
            } else {
                // Normal title/description
                $title = match($locale) {
                    'hi' => "दिन {$dayNumber}: {$from->name} → {$to->name}",
                    'zh' => "第 {$dayNumber} 天: {$from->name} → {$to->name}",
                    'np' => "दिन {$dayNumber}: {$from->name} → {$to->name}",
                    default => "Day {$dayNumber}: {$from->name} → {$to->name}",
                };
                $desc = match($locale) {
                    'hi' => "{$from->name} ({$from->altitude}मी) से {$to->name} ({$to->altitude}मी) तक। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घंटे。" . ($isLongDay ? " ⚠️ लामो दिन – 15 किमी भन्दा बढी。" : ""),
                    'zh' => "从 {$from->name}（{$from->altitude}米）到 {$to->name}（{$to->altitude}米）。距离：{$distance}公里，预计时间：{$seg->estimated_time_hours}小时。" . ($isLongDay ? " ⚠️ 长日 – 超过15公里。" : ""),
                    'np' => "{$from->name} ({$from->altitude}मी) देखि {$to->name} ({$to->altitude}मी) सम्म। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घण्टा。" . ($isLongDay ? " ⚠️ लामो दिन – १५ किमी भन्दा बढी。" : ""),
                    default => "From {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$distance} km, estimated time: {$seg->estimated_time_hours} hrs." . ($isLongDay ? " ⚠️ Long day – over 15km." : ""),
                };
            }

            // ✅ Get service for this waypoint
            $service = $this->getServiceForWaypoint($to, $input);
            $serviceCost = $service ? $service['price'] * 133 : 0;
            $serviceName = $service ? $service['name'] : 'Trekking Day';
            $serviceId = $service ? $service['id'] : null;
            $pricingSource = $service ? 'provider_service' : 'system_estimate';

            $days[] = [
                'day_number' => $dayNumber,
                'title' => $title,
                'description' => $desc,
                'overnight_waypoint_id' => $to->id,
                'distance_km' => $distance,
                'estimated_time_hours' => (float) $seg->estimated_time_hours,
                'altitude_m' => $to->altitude,
                'items' => [
                    [
                        'title' => $serviceName,
                        'description' => "Trek from {$from->name} to {$to->name}",
                        'time_of_day' => 'morning',
                        'cost' => $serviceCost,
                        'pricing_source' => $pricingSource,
                        'pricing_snapshot' => null,
                        'service_id' => $serviceId,
                        'is_optional' => false,
                        'metadata' => null,
                        'provider' => $service ? $service['provider'] : null,
                    ]
                ]
            ];
            $dayNumber++;
        }

        $requestedDays = $input['days'];
        $maxRestDays = min(3, $requestedDays - count($days));
        $restDaysAdded = 0;

        while (count($days) < $requestedDays && $restDaysAdded < $maxRestDays) {
            $last = end($days);
            $waypointId = $last['overnight_waypoint_id'] ?? null;
            $waypoint = $waypointId ? Waypoint::find($waypointId) : null;
            $waypointName = $waypoint ? $waypoint->name : 'Unknown';

            $altitude = $last['altitude_m'] ?? 0;
            if ($altitude < 3000) {
                break;
            }

            $restTitle = match($locale) {
                'hi' => "{$waypointName} में अनुकूलन दिवस",
                'zh' => "{$waypointName} 适应日",
                'np' => "{$waypointName} मा अनुकूलन दिन",
                default => "Acclimatization Day at {$waypointName}",
            };

            $days[] = [
                'day_number' => count($days) + 1,
                'title' => $restTitle,
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
            $titleNoData = match($locale) {
                'hi' => "दिन {$dayNumber}: कोई यात्रा डेटा नहीं",
                'zh' => "第 {$dayNumber} 天: 无行程数据",
                'np' => "दिन {$dayNumber}: यात्रा डेटा छैन",
                default => "Day {$dayNumber}: No Itinerary Data",
            };
            $descNoData = match($locale) {
                'hi' => "AI ने इस दिन के लिए डेटा उत्पन्न नहीं किया।",
                'zh' => "AI 没有为此天生成数据。",
                'np' => "AI ले यस दिनको लागि डेटा उत्पन्न गरेन।",
                default => "The AI did not generate data for this day.",
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
    // HELPER: GET SINGLE SERVICE FOR WAYPOINT
    // ==========================================
    protected function getServiceForWaypoint(Waypoint $waypoint, array $input): ?array
    {
        $style = $input['travel_style'] ?? 'mid_range';
        $locationId = $waypoint->location_id;

        if (!$locationId) {
            return null;
        }

        $service = Service::where('status', 'active')
            ->where('location_id', $locationId)
            ->whereHas('category', function ($q) {
                $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
            })
            ->whereHas('provider.styles', function ($q) use ($style) {
                $q->where('style_slug', $style);
            })
            ->first();

        if (!$service) {
            $service = Service::where('status', 'active')
                ->where('name', 'LIKE', "%{$waypoint->name}%")
                ->whereHas('category', function ($q) {
                    $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
                })
                ->first();
        }

        if (!$service) {
            return null;
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'currency' => $service->currency ?? 'USD',
            'provider' => $service->provider->name ?? 'TravelAI Partner',
            'location_id' => $service->location_id,
        ];
    }

    // ==========================================
    // HELPER: ensure tour segments
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

        if ($locale === 'np') {
            $map = [
                'cost.daily_food_budget' => 'दैनिक खाना बजेट',
                'cost.manang_special_permit' => 'मनाङ विशेष अनुमति',
                'service.homestay_experience' => 'होमस्टे अनुभव',
                'service.group_guide_service' => 'समूह गाइड सेवा',
                'service.standard_room' => 'स्ट्यान्डर्ड कोठा',
                'service.private_jeep' => 'निजी जीप',
            ];
            $key = $prefix . '.' . Str::slug($name, '_');
            return $map[$key] ?? $name;
        }

        $key = $prefix . '.' . Str::slug($name, '_');
        $translated = __($key, [], $locale);
        return ($translated !== $key) ? $translated : $name;
    }

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
    // DAY-LEVEL SERVICE FETCHER
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
                    'location_id' => $item->location_id,
                ];
            }
        }

        return ['services' => collect($result), 'diagnostic' => null];
    }
}
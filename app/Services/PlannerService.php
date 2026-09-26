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
    protected ItineraryValidator $validator;

    public function __construct(ItineraryValidator $validator)
    {
        $this->validator = $validator;
    }

    public function generate(array $input, string $locale = 'en'): array
    {
                Log::info('PlannerService generate called', [
            'locale' => $locale,
            'input_keys' => array_keys($input),
        ]);

        $route = $this->resolveRoute($input['destination'] ?? null);
        if (!$route) {
            throw ValidationException::withMessages(['destination' => 'Route not found.']);
        }

        $route->refresh();
$route->load(['costs']);

// ✅ DIRECTLY QUERY SEGMENTS FROM DATABASE – no ambiguity
$segments = $route->segments()
    ->with(['fromWaypoint', 'toWaypoint'])
    ->orderBy('sequence')
    ->get();

// if ($segments->isEmpty()) {
//     $this->ensureSegmentsForTour($route);
//     $segments = $route->segments()
//         ->with(['fromWaypoint', 'toWaypoint'])
//         ->orderBy('sequence')
//         ->get();
// }

Log::info("🔍 Loaded " . $segments->count() . " segments from DB for " . $route->slug);

// ✅ Important: Sort by sequence
$segments = $segments->sortBy('sequence');

        // ============================================================
        // BUILD SEGMENTS WITH OVERNIGHT STOP FILTER
        // ============================================================
        $overnightSegments = [];
        $dayNumber = 1;
        $mergedSegment = null;
        $mergedWaypoints = [];

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


        // ─── Route data sufficiency check ───
        $routeDataDays = count($overnightSegments);
        $requestedDays = $input['days'];
        $routeDataMismatch = $requestedDays > $routeDataDays;

        if ($routeDataMismatch) {
            Log::warning("⚠️ Requested {$requestedDays} days but route has {$routeDataDays} verified segments", [
                'route' => $route->name,
            ]);
        }

        // ============================================================
        // COST CALCULATION
        // ============================================================
        $costBreakdown = $this->calculateCost($route, $input, [], $locale);

        // ============================================================
        // BUILD CONTEXT
        // ============================================================
        $context = $this->buildContext($route, $input, $costBreakdown, $dayServicesMap, $dayDiagnostics, $overnightSegments);

        // ============================================================
        // FALLBACK
        // ============================================================
        $aiResponse = $this->buildFallbackResponse($route, $input, $locale, $overnightSegments);
        $usedFallback = true;

        // ============================================================
        // VALIDATE & NORMALIZE
        // ============================================================
        $validated = $this->validator->validate($aiResponse, $route, $input, $context, $locale);

                // ============================================================
        // Phase 4H — No buffer day conversion.
        // Validator now skips "no data" days entirely.
        // Verified days are returned as-is.
        // ============================================================

        // ============================================================
// ATTACH SERVICES TO DAYS
// ============================================================
foreach ($validated['days'] as &$dayData) {
    $dayNumber = $dayData['day_number'];
    $services = $dayServicesMap[$dayNumber] ?? collect();

    if ($dayData['distance_km'] === null) {
        continue;
    }

            // REST DAY
if ((float) $dayData['distance_km'] == 0) {
    $restWp = \App\Models\Waypoint::find($dayData['overnight_waypoint_id'] ?? 0);
    $restWpName = $restWp?->name ?? 'the lodge';

    // Phase 4R-fix-6: Attach lodge service so rest day includes accommodation cost.
    $restService = $restWp ? $this->getServiceForWaypoint($restWp, $input) : null;

    if ($restService) {
        $restPriceNpr = (float) $restService['price'];
        if (strtoupper($restService['currency'] ?? 'NPR') === 'USD') {
            $restPriceNpr *= 133;
        }

        $dayData['items'] = [
            [
                'title' => match($locale) {
    'np' => "{$restWpName} मा आराम दिन",
    'hi' => "{$restWpName} में आराम दिवस",
    'zh' => "在 {$restWpName} 休息日",
    default => "Rest Day at {$restWpName}",
},
                'description' => match($locale) {
    'np' => $restService['name'] . ' – आराम र अनुकूलन।',
    'hi' => $restService['name'] . ' – आराम और अनुकूलन।',
    'zh' => $restService['name'] . ' – 休息和适应。',
    default => $restService['name'] . ' – Rest and acclimatize.',
},
                'time_of_day' => 'afternoon',
                'cost' => $restPriceNpr,
                'currency' => 'NPR',
                'pricing_source' => 'provider_service',
                'pricing_snapshot' => null,
                'service_id' => $restService['id'],
                'is_optional' => false,
                'metadata' => null,
                'provider' => $restService['provider'] ?? null,
            ]
        ];
        Log::info("✅ Rest day service attached: {$restService['name']} (NPR {$restPriceNpr})");
    } else {
        $dayData['items'] = [
            [
                'title' => match($locale) {
    'np' => "{$restWpName} मा आराम दिन",
    'hi' => "{$restWpName} में आराम दिवस",
    'zh' => "在 {$restWpName} 休息日",
    default => "Rest Day at {$restWpName}",
},
                'description' => match($locale) {
    'np' => "{$restWpName} मा आराम गर्नुहोस्।",
    'hi' => "{$restWpName} में आराम करें।",
    'zh' => "在{$restWpName} 休息。",
    default => "Rest and relax at {$restWpName}.",
},
                'time_of_day' => 'morning',
                'cost' => 0,
                'pricing_source' => 'system_estimate',
                'pricing_snapshot' => null,
                'service_id' => null,
                'is_optional' => false,
                'metadata' => null,
            ]
        ];
        Log::info("ℹ️ No lodge for rest day at {$restWpName}");
    }
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

    // Try to find service by location
    foreach ($services as $svc) {
        if (($svc['location_id'] ?? null) == $locationId) {
            $bestService = $svc;
            break;
        }
    }

                // Phase 4R-fix-3: For activity routes, keep fallback-provided items.
    // Hotel/guide override is WRONG for activities (e.g. "Pokhara Hotel" on zipline).
    if ($route->route_type === 'activity') {
        Log::info("⏭️ Skipping service override for activity route: {$route->slug}");
        continue;
    }

        // 🔥 Delegate to centralized service resolver (Phase 4F — LIKE removed)
    $bestService = $this->getServiceForWaypoint($waypoint, $input);
    if (!$bestService) {
        Log::info("ℹ️ No service resolved for Day {$dayNumber} ({$waypoint->name})");
        continue;
    }

    Log::info("✅ Service resolved for Day {$dayNumber}: {$bestService['name']}");

    $priceNpr = $bestService['price'];
    if (strtoupper($bestService['currency'] ?? 'NPR') === 'USD') {
        $priceNpr *= 133;
    }

    // Override items completely
    $dayData['items'] = [
        [
            'title' => $bestService['name'],
            'description' => match($locale) {
    'np' => 'सेवा समावेश',
    'hi' => 'सेवा शामिल',
    'zh' => '服务包含',
    default => 'Service Included',
},
            'time_of_day' => 'afternoon',
            'cost' => $priceNpr,
            'currency' => 'NPR',
            'pricing_source' => 'provider_service',
            'pricing_snapshot' => null,
            'service_id' => $bestService['id'],
            'is_optional' => false,
            'metadata' => null,
            'provider' => $bestService['provider'] ?? 'TravelAI Partner',
        ]
    ];
    Log::info("✅ Attached service to Day {$dayNumber}: {$bestService['name']} (NPR {$priceNpr})");
}
unset($dayData);

        // ============================================================
        // SAVE TO DB
        // ============================================================
$result = DB::transaction(function () use ($input, $route, $validated, $aiResponse, $usedFallback, $costBreakdown, $routeDataDays, $requestedDays, $routeDataMismatch, $locale) {
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
    'segments' => $route->segments()->with(['fromWaypoint', 'toWaypoint'])->get()->toArray(),
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
$breakdownDayPrefix = match($locale) {
    'np' => "दिन {$dayNumber}: ",
    'hi' => "दिन {$dayNumber}: ",
    'zh' => "第 {$dayNumber} 天: ",
    default => "Day {$dayNumber}: ",
};
$perDayServiceCosts[$key] = [
    'name' => $breakdownDayPrefix . $item['title'],
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

            // BUDGET WARNING
            $budgetNpr = $input['budget'] * 133;
            if ($input['budget'] > 0 && $totalCost > $budgetNpr) {
                $overPercent = (($totalCost - $budgetNpr) / $budgetNpr) * 100;
                if ($overPercent > 10) {
                                        $overPctRounded = round($overPercent, 0);
                    $finalBreakdown['budget_insufficient'] = [
                        'name' => match($locale) {
                            'np' => '⚠️ बजेट चेतावनी',
                            'hi' => '⚠️ बजट चेतावनी',
                            'zh' => '⚠️ 预算警告',
                            default => '⚠️ Budget Warning',
                        },
                        'amount' => 0,
                        'currency' => 'NPR',
                        'unit' => 'note',
                        'is_mandatory' => false,
                        'provider_name' => 'System',
                        'message' => match($locale) {
                            'np' => "अनुमानित लागत तपाईंको \${$input['budget']} USD बजेट भन्दा {$overPctRounded}% बढी छ। कृपया बजेट बढाउनुहोस् वा किफायती शैली छान्नुहोस्।",
                            'hi' => "अनुमानित लागत आपके \${$input['budget']} USD बजट से {$overPctRounded}% अधिक है। कृपया बजट बढ़ाएँ या अधिक किफायती शैली चुनें।",
                            'zh' => "预计费用超出您 \${$input['budget']} USD 预算 {$overPctRounded}%。请考虑增加预算或选择更经济的旅行方式。",
                            default => "Estimated cost is {$overPctRounded}% over your budget of \${$input['budget']} USD. Consider increasing your budget or choosing a more affordable style.",
                        },
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
                'metadata' => [
                    'route_data_days' => $routeDataDays,
                    'requested_days' => $requestedDays,
                    'data_sufficiency' => $routeDataMismatch ? 'insufficient' : 'sufficient',
                    'recommended_days' => $route->recommended_days ?? $routeDataDays,
                ],
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
    // COST CALCULATION
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

            // ✅ Phase 4P Fix: unique key per cost (was $cost->type → collision)
            // Include cost id + slug of name to guarantee uniqueness
            $key = $cost->type . '_' . $cost->id . '_' . \Str::slug($cost->name ?? 'unnamed');

            $breakdown[$key] = [
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

    // ==========================================
    // FALLBACK
    // ==========================================
    protected function buildFallbackResponse(Route $route, array $input, string $locale = 'en', array $overnightSegments = []): array
{
    // ✅ Phase 4H-fix: Use passed $overnightSegments (merged by overnight rules)
    // instead of raw segments. This prevents pass/peak waypoints from
    // becoming overnight stops.
            if (!empty($overnightSegments)) {
            // Extract the merged segments from the passed structure
            $freshSegments = collect();
            foreach ($overnightSegments as $item) {
                $seg = $item['segment'];
                // Load waypoint relations if not already loaded
                if (!$seg->relationLoaded('fromWaypoint')) {
                    $seg->load('fromWaypoint', 'toWaypoint');
                }
                // Phase 4R-fix-17: carry merged_waypoints for trek day-hike RT detection
                $seg->merged_waypoints = $item['merged_waypoints'] ?? [];
                $freshSegments->push($seg);
            }

        // Fallback: if somehow empty, reload from DB
        if ($freshSegments->isEmpty()) {
            $freshSegments = $route->segments()
                ->with(['fromWaypoint', 'toWaypoint'])
                ->orderBy('sequence')
                ->get();
        }
    } else {
        // Legacy path — load raw segments
        $freshSegments = $route->segments()
            ->with(['fromWaypoint', 'toWaypoint'])
            ->orderBy('sequence')
            ->get();

        if ($freshSegments->isEmpty()) {
            $this->ensureSegmentsForTour($route);
            $freshSegments = $route->segments()
                ->with(['fromWaypoint', 'toWaypoint'])
                ->orderBy('sequence')
                ->get();
        }
    }

    $days = [];
    $dayNumber = 1;
    $maxDailyDistance = 15;

    foreach ($freshSegments as $seg) {
        $from = $seg->fromWaypoint;
        $to = $seg->toWaypoint;

        if (!$from || !$to) {
            Log::warning("⚠️ Segment missing waypoint relations: route_id={$route->id}, seg_id={$seg->id}");
            continue;
        }

        $distance = (float) $seg->distance_km;
        $isLongDay = $distance > $maxDailyDistance;
$mergedWaypoints = [];

// Phase 4R-fix: Populate intermediate waypoints for round-trip activities.
// Round-trip merged segments have from.id === to.id (e.g. Pokhara → Pokhara).
// Extract the intermediate waypoint (e.g. Kusma Bridge) from the route's
// original segments, otherwise the round-trip branch never fires and the
// title falls back to "Start → Start".
// Phase 4R-fix-4 (corrected): Round-trip detection ONLY for tours/activities.
// Treks must NEVER be RT — consecutive waypoints often share a location_id
// (e.g. Bahundanda → "Besisahar" location), which would wrongly trigger RT.
$isRoundTrip = false;
if (in_array($route->route_type, ['tour', 'activity'])) {
    $rtSameId   = ($from->id === $to->id);
    $rtSameName = (strcasecmp(trim($from->name ?? ''), trim($to->name ?? '')) === 0);
    $singleLocTour = ($route->route_type === 'tour'
        && $route->segments()->count() === 1
        && $from->location_id !== null
        && $from->location_id === $to->location_id);
    $isRoundTrip = ($rtSameId || $rtSameName || $singleLocTour);
} elseif ($route->route_type === 'trek' && $from->id === $to->id && $distance > 1) {
    // Phase 4R-fix-17: Trek day-hike (e.g. Gorak Shep → EBC → Gorak Shep)
    $isRoundTrip = true;
}

$targetWaypoint = $to; // default: end waypoint
if ($isRoundTrip && $distance > 0) {
    if ($route->route_type === 'trek') {
        // Phase 4R-fix-17: use attached merged_waypoints (from overnightSegments)
                    $attached = $seg->merged_waypoints ?? [];
            if (!empty($attached)) {
                $mergedWaypoints = $attached;
                // Phase 4R-fix-18: scope lookup to actual route segments
                // (avoid duplicate-named waypoints like two "Everest Base Camp")
                $validWpIds = $route->segments()->pluck('from_waypoint_id')
                    ->merge($route->segments()->pluck('to_waypoint_id'))
                    ->unique()->toArray();
                $first = \App\Models\Waypoint::where('name', $attached[0])
                    ->whereIn('id', $validWpIds)
                    ->first();
                if ($first) {
                    $targetWaypoint = $first;
                }
                Log::info("🔁 Trek day-hike RT: {$from->name} → " . implode(', ', $mergedWaypoints) . " → {$to->name}");
            }
        } else {
        // Phase 4S: For tours/activities, use attached merged_waypoints
        // (works for city tours where all waypoints share one location)
        $attached = $seg->merged_waypoints ?? [];
        if (!empty($attached)) {
            $mergedWaypoints = $attached;
            $validWpIds = $route->segments()->pluck('from_waypoint_id')
                ->merge($route->segments()->pluck('to_waypoint_id'))
                ->unique()->toArray();
            $first = \App\Models\Waypoint::where('name', $attached[0])
                ->whereIn('id', $validWpIds)
                ->first();
            if ($first) {
                $targetWaypoint = $first;
            }
            Log::info("🔁 Tour RT: {$from->name} → " . implode(', ', $mergedWaypoints) . " → {$to->name}");
        } else {
            Log::info("🎫 Single-location tour: {$route->slug}");
        }
    }
}

        // Phase 4S: "Hotel (City)" display for 2+ day tours
        $fromDisplay = $from->name;
        $toDisplay = $to->name;
        if ($route->route_type === 'tour' && $route->duration_days >= 2) {
            $fromGeneric = preg_match('/(\s|^)(start|end)(\s|$)/i', $from->name);
            $toGeneric = preg_match('/(\s|^)(start|end)(\s|$)/i', $to->name);
            if (!$fromGeneric && $from->is_overnight_stop && in_array($from->type, ['village', 'city'])) {
                $fromDisplay = "Hotel ({$from->name})";
            }
            if (!$toGeneric && $to->is_overnight_stop && in_array($to->type, ['village', 'city'])) {
                $toDisplay = "Hotel ({$to->name})";
            }
        }

                // Title & description based on locale
        if ($isRoundTrip && $distance > 0 && !empty($mergedWaypoints)) {
            $landmarkName = implode(' → ', $mergedWaypoints);
                        $title = match($locale) {
                'hi' => "दिन {$dayNumber}: {$fromDisplay} → {$landmarkName} → {$toDisplay}",
                'zh' => "第 {$dayNumber} 天: {$fromDisplay} → {$landmarkName} → {$toDisplay}",
                'np' => "दिन {$dayNumber}: {$fromDisplay} → {$landmarkName} → {$toDisplay}",
                default => "Day {$dayNumber}: {$fromDisplay} → {$landmarkName} → {$toDisplay}",
            };
                        $desc = match($locale) {
                                'hi' => "{$fromDisplay} से {$landmarkName} तक की यात्रा और वापसी। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घंटे।" . ($isLongDay ? " ⚠️ लंबा दिन – 15 किमी से अधिक।" : ""),
                'zh' => "从 {$fromDisplay} 到 {$landmarkName} 的往返旅行。距离：{$distance}公里，预计时间：{$seg->estimated_time_hours}小时。" . ($isLongDay ? " ⚠️ 长日 – 超过15公里。" : ""),
                'np' => "{$fromDisplay} बाट {$landmarkName} को यात्रा र फिर्ता। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घण्टा。" . ($isLongDay ? " ⚠️ लामो दिन – १५ किमी भन्दा बढी。" : ""),
                default => "Round trip from {$fromDisplay} to {$landmarkName} and back. Distance: {$distance} km, estimated time: {$seg->estimated_time_hours} hrs." . ($isLongDay ? " ⚠️ Long day – over 15km." : ""),
            };
        } elseif ($isRoundTrip && $distance > 0) {
            // Phase 4R-fix-4: Same-location RT — use route name.
            $title = match($locale) {
                'hi' => "दिन {$dayNumber}: {$route->name}",
                'zh' => "第 {$dayNumber} 天: {$route->name}",
                'np' => "दिन {$dayNumber}: {$route->name}",
                default => "Day {$dayNumber}: {$route->name}",
            };
            $desc = "Explore {$from->name}. Distance: {$distance} km, estimated time: {$seg->estimated_time_hours} hrs." . ($isLongDay ? " ⚠️ Long day – over 15km." : "");
        } else {
                        $title = match($locale) {
                'hi' => "दिन {$dayNumber}: {$fromDisplay} → {$toDisplay}",
                'zh' => "第 {$dayNumber} 天: {$fromDisplay} → {$toDisplay}",
                'np' => "दिन {$dayNumber}: {$fromDisplay} → {$toDisplay}",
                default => "Day {$dayNumber}: {$fromDisplay} → {$toDisplay}",
            };
            $desc = match($locale) {
                                'hi' => "{$from->name} ({$from->altitude}मी) से {$to->name} ({$to->altitude}मी) तक। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घंटे।" . ($isLongDay ? " ⚠️ लंबा दिन – 15 किमी से अधिक।" : ""),
                'zh' => "从 {$from->name}（{$from->altitude}米）到 {$to->name}（{$to->altitude}米）。距离：{$distance}公里，预计时间：{$seg->estimated_time_hours}小时。" . ($isLongDay ? " ⚠️ 长日 – 超过15公里。" : ""),
                'np' => "{$from->name} ({$from->altitude}मी) देखि {$to->name} ({$to->altitude}मी) सम्म। दूरी: {$distance} किमी, अनुमानित समय: {$seg->estimated_time_hours} घण्टा。" . ($isLongDay ? " ⚠️ लामो दिन – १५ किमी भन्दा बढी。" : ""),
                default => "From {$from->name} ({$from->altitude}m) to {$to->name} ({$to->altitude}m). Distance: {$distance} km, estimated time: {$seg->estimated_time_hours} hrs." . ($isLongDay ? " ⚠️ Long day – over 15km." : ""),
            };
        }

        // FORCE HOTEL FOR TOURS
$service = null;

// ✅ Special case for Paragliding (high priority)
if ($route->slug === 'pokhara-paragliding') {
    $paraglidingService = Service::where('slug', 'paragliding-pokhara')->first();
    if ($paraglidingService) {
        $service = [
            'id' => $paraglidingService->id,
            'name' => $paraglidingService->name,
            'price' => (float) $paraglidingService->price,
            'currency' => $paraglidingService->currency ?? 'USD',
            'provider' => $paraglidingService->provider->name ?? 'TravelAI Partner',
            'location_id' => $paraglidingService->location_id,
        ];
        Log::info("✅ Paragliding service manually attached");
    }
}

// ✅ For tours, try to get hotel by location
$isTour = $this->isTourRoute($route);
if (!$service && $isTour && $targetWaypoint->location_id !== null) {
    // Phase 4N.1b: Guard against null location — Laravel's where('col', null)
    // translates to WHERE col IS NULL, which incorrectly matches orphan services.
        $service = Service::where('status', 'active')
        ->where('location_id', $targetWaypoint->location_id)
        ->whereHas('category', function($q) {
            $q->where('slug', 'hotel');
        })
        ->first();

    if ($service) {
        $service = [
            'id' => $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'currency' => $service->currency ?? 'USD',
            'provider' => $service->provider->name ?? 'TravelAI Partner',
            'location_id' => $service->location_id,
        ];
    }
}

// ✅ Only call getServiceForWaypoint if no service found yet AND we have a waypoint
if (!$service && $targetWaypoint) {
    // Phase 4R-fix-2: Activity routes need activity service, NOT hotel/guide.
    // For activities, prefer an 'activity' category service at the target
    // waypoint's location. If not found, leave null — the label will use
    // the route name instead of "Trekking Day".
        if ($route->route_type === 'activity') {
        if ($targetWaypoint->location_id !== null) {
            // Phase 4R-fix-3b: Match activity service by route name keywords
            // (e.g. "Kayaking in Fewa Lake" → service with "kayaking"/"fewa"/"lake").
            // Without this, ->first() returns wrong activity (e.g. Paragliding for Kayaking).
                        // Phase 4R-fix-3c: Filter out common location words that cause
            // false matches (e.g. "Pokhara" matches "Paragliding in Pokhara"
            // for the zipline route, wrongly attaching paragliding service).
            $stopWords = [
                'pokhara', 'kathmandu', 'nepal', 'city', 'tour',
                'adventure', 'activity', 'lake', 'river', 'valley',
            ];
            $routeWords = array_filter(
                preg_split('/[\s\-]+/', strtolower($route->name)),
                fn($w) => strlen($w) >= 4 && !in_array($w, $stopWords)
            );

            $actQuery = Service::where('status', 'active')
                ->where('location_id', $targetWaypoint->location_id)
                ->whereHas('category', fn($q) => $q->where('slug', 'activity'));

            $actService = null;
            if (!empty($routeWords)) {
                $actService = (clone $actQuery)
                    ->where(function($q) use ($routeWords) {
                        foreach ($routeWords as $word) {
                            $q->orWhere('name', 'LIKE', "%{$word}%");
                        }
                    })
                    ->first();
            }
            // No fallback to "any activity" — better to show route name than wrong activity.

            if ($actService) {
                $service = $this->formatService($actService);
                Log::info("✅ Activity service attached: {$actService->name}");
            } else {
                Log::info("ℹ️ No matching activity service at loc={$targetWaypoint->location_id} for {$route->slug}");
            }
        }
    } else {
        $service = $this->getServiceForWaypoint($targetWaypoint, $input);
    }
}

        $serviceCost = $service ? $service['price'] * 133 : 0;
        $trekkingDayLabel = match($locale) {
    'np' => 'ट्रेकिङ दिन',
    'hi' => 'ट्रेकिंग दिवस',
    'zh' => '徒步日',
    default => 'Trekking Day',
};
$serviceName = $service
    ? $service['name']
    : (in_array($route->route_type, ['activity', 'tour']) ? $route->name : $trekkingDayLabel);
        $serviceId = $service ? $service['id'] : null;
        $pricingSource = $service ? 'provider_service' : 'system_estimate';

        $days[] = [
            'day_number' => $dayNumber,
            'title' => $title,
            'description' => $desc,
            'overnight_waypoint_id' => $targetWaypoint->id,
'distance_km' => $distance,
'estimated_time_hours' => (float) $seg->estimated_time_hours,
'altitude_m' => $targetWaypoint->altitude,
            'items' => [
                [
                    'title' => $serviceName,
                                                                                'description' => (function() use ($locale, $route, $fromDisplay, $targetWaypoint, $isRoundTrip, $mergedWaypoints) {
                        $isSingle = ($isRoundTrip && empty($mergedWaypoints));
                        $type = $route->route_type;
                        if ($isSingle) {
                            return match($locale) {
                                'np' => "{$fromDisplay} मा " . ($type === 'activity' ? 'गतिविधि' : ($type === 'tour' ? 'भ्रमण' : 'ट्रेक')),
                                'hi' => "{$fromDisplay} में " . ($type === 'activity' ? 'गतिविधि' : ($type === 'tour' ? 'भ्रमण' : 'ट्रेक')),
                                'zh' => "在{$fromDisplay}的" . ($type === 'activity' ? '活动' : ($type === 'tour' ? '游览' : '徒步')),
                                default => ucfirst($type) . " at {$fromDisplay}",
                            };
                        }
                        return match($locale) {
                            'np' => "{$fromDisplay} बाट {$targetWaypoint->name} सम्म",
                            'hi' => "{$fromDisplay} से {$targetWaypoint->name} तक",
                            'zh' => "从{$fromDisplay}到{$targetWaypoint->name}",
                            default => ucfirst($type) . " from {$fromDisplay} to {$targetWaypoint->name}",
                        };
                    })(),
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
            'description' => match($locale) {
    'np' => "आज ट्रेकिङ छैन। {$waypointName} मा आराम र अनुकूलन।",
    'hi' => "आज ट्रेकिंग नहीं। {$waypointName} में आराम और अनुकूलन।",
    'zh' => "今天不徒步。在{$waypointName}休息和适应。",
    default => "No trekking today. Rest and acclimatize at {$waypointName}.",
},
            'overnight_waypoint_id' => $waypointId,
            'distance_km' => 0,
            'estimated_time_hours' => 0,
            'altitude_m' => $altitude,
                        'items' => [
                [
                                                            'title' => match($locale) {
                        'np' => "{$waypointName} मा आराम दिन",
                        'hi' => "{$waypointName} में आराम दिवस",
                        'zh' => "在 {$waypointName} 休息日",
                        default => "Rest Day at {$waypointName}",
                    },
                    'description' => match($locale) {
                        'np' => "{$waypointName} मा आराम र अनुकूलन।",
                        'hi' => "{$waypointName} में आराम और अनुकूलन।",
                        'zh' => "在 {$waypointName} 休息和适应。",
                        default => "Rest and relax at {$waypointName}.",
                    },
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
    // ✅ GET SINGLE SERVICE FOR WAYPOINT (with style filter and formatService)
    // ==========================================
        protected function getServiceForWaypoint(Waypoint $waypoint, array $input): ?array
    {
        // ─── Guard 1: overnight eligibility ───
        if (!$waypoint->is_overnight_stop) {
            Log::info("⏭️ Skipping non-overnight waypoint: {$waypoint->name}");
            return null;
        }

                // ─── Guard 2: waypoint type ───
        // Phase 4R-fix-7: Some checkpoints (e.g. Machhapuchhre Base Camp, Api BC,
        // Makalu BC) have real lodges. Allow them through if a lodge service
        // exists at their location — otherwise apply the non-accommodation skip.
        $nonAccommodationTypes = ['pass', 'lake', 'viewpoint', 'landmark', 'checkpoint'];
        if (in_array($waypoint->type, $nonAccommodationTypes)) {
            $isCheckpointWithLodge = false;
            if ($waypoint->type === 'checkpoint' && $waypoint->location_id) {
                $isCheckpointWithLodge = Service::where('status', 'active')
                    ->where('location_id', $waypoint->location_id)
                    ->whereHas('category', fn($q) => $q->where('slug', 'hotel'))
                    ->exists();
            }
            if (!$isCheckpointWithLodge) {
                Log::info("⏭️ Skipping {$waypoint->type} waypoint: {$waypoint->name}");
                return null;
            }
            Log::info("✅ Checkpoint with lodge allowed: {$waypoint->name}");
        }

        // ─── Guard 3: location_id must exist ───
        if (!$waypoint->location_id) {
            Log::info("⏭️ No location_id for waypoint: {$waypoint->name}");
            return null;
        }

        $style = $input['travel_style'] ?? 'mid_range';
        Log::info("📍 getServiceForWaypoint called for: {$waypoint->name} (style: {$style})");

        try {
            // ─── Tier 1: Style-matched hotel at same location ───
            $hotel = Service::where('status', 'active')
                ->where('location_id', $waypoint->location_id)
                ->whereHas('category', fn($q) => $q->where('slug', 'hotel'))
                ->whereHas('provider.styles', fn($q) => $q->where('style_slug', $style))
                ->first();

            if ($hotel) {
                Log::info("✅ Style-matched hotel: {$hotel->name} for {$waypoint->name}");
                return $this->formatService($hotel);
            }

            // ─── Tier 2: Any hotel at same location ───
            $hotel = Service::where('status', 'active')
                ->where('location_id', $waypoint->location_id)
                ->whereHas('category', fn($q) => $q->where('slug', 'hotel'))
                ->first();

            if ($hotel) {
                Log::info("✅ Any hotel (style fallback): {$hotel->name} for {$waypoint->name}");
                return $this->formatService($hotel);
            }

            // ─── Tier 3: Guide service at same location ───
            $guide = Service::where('status', 'active')
                ->where('location_id', $waypoint->location_id)
                ->whereHas('category', fn($q) => $q->where('slug', 'guide'))
                ->first();

            if ($guide) {
                Log::info("✅ Guide fallback: {$guide->name} for {$waypoint->name}");
                return $this->formatService($guide);
            }

            // ─── No service found — NO LIKE fallback ───
            Log::info("❌ No service at location_id={$waypoint->location_id} for {$waypoint->name}");
            return null;

        } catch (\Exception $e) {
            Log::error("🔥 Error in getServiceForWaypoint: " . $e->getMessage());
            return null;
        }
    }

    // ✅ FORMAT SERVICE HELPER (MISSING METHOD ADDED)
    protected function formatService(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name ?? 'Unknown Service',
            'price' => (float) ($service->price ?? 0),
            'currency' => $service->currency ?? 'NPR',
            'provider' => $service->provider->name ?? 'TravelAI Partner',
            'location_id' => $service->location_id,
        ];
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

        $services = Service::where('status', 'active')
            ->where('location_id', $locationId)
            ->whereHas('category', function ($q) {
                $q->whereIn('slug', ['hotel', 'guide', 'transport', 'activity', 'experience']);
            })
            ->with(['category', 'provider.styles', 'reviews'])
            ->get();

        if ($services->isEmpty()) {
            return ['services' => collect(), 'diagnostic' => 'no_active_service'];
        }

        $priorityMap = ['hotel' => 1, 'guide' => 2, 'transport' => 3, 'activity' => 4, 'experience' => 5];
        $services = $services->sortBy(function($s) use ($priorityMap) {
            return $priorityMap[$s->category->slug ?? ''] ?? 99;
        });

        $filtered = $services->filter(function ($service) use ($style) {
            return $service->provider->styles->contains('style_slug', $style);
        });

        if ($filtered->isEmpty()) {
            $filtered = $services;
            Log::info("⚠️ No style match, using all services for location_id: {$locationId}");
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

    // ==========================================
    // HELPER: ensure tour segments
    // ==========================================
    protected function ensureSegmentsForTour(Route $route): void
{
    // ✅ Do nothing – segments already exist from seeder/Tinker
}

        protected function resolveRoute(?string $destination): ?Route
    {
        if (!$destination) {
            return Route::where('is_active', true)->orderBy('id')->first();
        }

        // ─── Tier 1: Exact slug match (deterministic, preferred) ───
        $route = Route::where('is_active', true)
            ->where('slug', $destination)
            ->first();
        if ($route) {
            Log::info("🎯 Route resolved by exact slug: {$route->slug}");
            return $route;
        }

        // ─── Tier 2: Exact name match (case-insensitive) ───
        $route = Route::where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [strtolower($destination)])
            ->first();
        if ($route) {
            Log::info("🎯 Route resolved by exact name: {$route->name}");
            return $route;
        }

        // ─── Tier 3: Fuzzy fallback (correctly grouped) ───
        $route = Route::where('is_active', true)
            ->where(function ($q) use ($destination) {
                $q->where('name', 'LIKE', "%{$destination}%")
                  ->orWhere('slug', 'LIKE', "%{$destination}%");
            })
            ->orderBy('id')
            ->first();

        if ($route) {
                        Log::info('Route resolved by fuzzy match', [
                'route_slug' => $route->slug,
            ]);
        } else {
                        Log::warning('Route not found');
        }

                return $route;
    }

        /**
     * Phase 4K: Public wrapper for Provider AI Draft route resolution.
     * ADDITIVE — existing resolveRoute() untouched (R5 कायम).
     *
     * 4K-F3b: Enhanced to handle real-world service name suffixes:
     *   - Strategy 1: exact via existing 3-tier resolveRoute()
     *   - Strategy 2: strip "— N Days" / "- N days" / " with ..." suffix → retry
     *   - Strategy 3: reverse-LIKE (service contains route name, longest wins)
     */
    public function resolveRouteForProvider(string $serviceName): ?Route
    {
        $serviceName = trim($serviceName);
        if ($serviceName === '') {
            return null;
        }

        // Strategy 1 — exact via existing 3-tier
        $route = $this->resolveRoute($serviceName);
        if ($route) {
            return $route;
        }

        // Strategy 2 — strip common suffixes and retry
        $normalized = preg_replace(
            '/\s*[—–-]\s*\d+\s*(days?|day|nights?|night).*$|\s+with\s+.*$/iu',
            '',
            $serviceName
        );
        $normalized = trim((string) $normalized);
        if ($normalized !== '' && $normalized !== $serviceName) {
            $route = $this->resolveRoute($normalized);
            if ($route) {
                return $route;
            }
        }

        // Strategy 3 — reverse-LIKE (service name contains route name)
        $haystack = $normalized !== '' ? $normalized : $serviceName;
        $route = Route::where('is_active', true)
            ->whereRaw('? LIKE CONCAT("%", name, "%")', [$haystack])
            ->orderByRaw('LENGTH(name) DESC')
            ->first();

        return $route;
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

    private function isTourRoute(Route $route): bool
{
    // Phase 4N.1b: Use route_type column (set by AssignRouteCategoriesSeeder)
    // instead of keyword matching. This eliminates the "Kanchenjunga Circuit"
    // false positive caused by the "Circuit" keyword.
    return $route->route_type === 'tour';
}
}
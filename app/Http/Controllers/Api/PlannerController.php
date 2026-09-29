<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlannerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PlannerController extends Controller
{
    protected PlannerService $planner;

    public function __construct(PlannerService $planner)
    {
        $this->planner = $planner;
    }

    public function generate(Request $request)
    {
        // ✅ Session बाट सिधै locale लिने (middleware ले सही नगरे पनि काम गर्छ)
        $locale = session('locale', 'en');

        Log::info('🔍 [PlannerController] Locale from session', [
            'locale' => $locale,
            'session_locale' => session('locale'),
            'app_locale' => app()->getLocale(),
            'request_locale' => $request->input('locale'),
        ]);

        try {
            $request->validate([
                'destination' => 'nullable|string|max:255',
                'days' => 'required|integer|min:1|max:30',
                'budget' => 'required|numeric|min:1',
                'travel_style' => 'nullable|in:budget,mid_range,luxury,backpacker',
                'interests' => 'nullable|array',
                'fitness_level' => 'nullable|in:easy,moderate,hard',
            ]);

            // ✅ session बाट लिइएको locale पास गर्ने
           $result = $this->planner->generate($request->all(), $locale);

// PHASE 5B: LLM narrative enrichment (R5 SAFE — PlannerService untouched)
$result['days'] = app(\App\Services\AI\PlannerNarrativeService::class)
    ->enrichCollection($result['days'], $locale, $request->input('destination'));

            // PHASE-5G-PADDING: honest banner for auto-added rest days
            $meta          = $result['metadata'] ?? [];
            $routeDataDays = (int) ($meta['route_data_days'] ?? 0);
            $requestedDays = (int) ($meta['requested_days'] ?? $request->input('days'));
            $paddingAdded  = max(0, $requestedDays - $routeDataDays);
            $notice        = null;
            if ($paddingAdded > 0) {
                // Category-aware unit from destination (safe — no relation dependency)
                $destination = strtolower((string) $request->input('destination', ''));
                $unit = match (true) {
                    str_contains($destination, 'trek')                                          => 'trekking days',
                    str_contains($destination, 'tour')                                          => 'tour days',
                    str_contains($destination, 'hotel') || str_contains($destination, 'stay')   => 'hotel nights',
                    str_contains($destination, 'transport') || str_contains($destination, 'transfer')
                                                                                                 => 'transport legs',
                    str_contains($destination, 'activity')                                       => 'activity days',
                    str_contains($destination, 'experience')                                     => 'experience days',
                    default                                                                       => 'route days',
                };

                $notice = "This route has {$routeDataDays} verified {$unit}. " .
                          "{$paddingAdded} rest day(s) added to match your {$requestedDays}-day trip length.";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'days' => $result['days'],
                    'notice' => $notice,
                    'total_cost' => $result['total_cost'],
                    'breakdown' => $result['breakdown'] ?? [],
                    'currency' => 'NPR',
                    'planner_result_id' => $result['result']->id ?? null, // ✅ NEW – Itinerary ID for quotation requests
                    'metadata' => $result['metadata'] ?? null,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
                } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
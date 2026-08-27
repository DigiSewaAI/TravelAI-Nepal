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

            return response()->json([
                'success' => true,
                'data' => [
                    'days' => $result['days'],
                    'total_cost' => $result['total_cost'],
                    'breakdown' => $result['breakdown'] ?? [],
                    'currency' => 'NPR',
                    'planner_result_id' => $result['result']->id ?? null, // ✅ NEW – Itinerary ID for quotation requests
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
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
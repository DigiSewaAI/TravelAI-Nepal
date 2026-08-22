<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlannerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlannerController extends Controller
{
    protected PlannerService $planner;

    public function __construct(PlannerService $planner)
    {
        $this->planner = $planner;
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'destination' => 'nullable|string|max:255',
                'days' => 'required|integer|min:1|max:30',
                'budget' => 'required|numeric|min:1',
                'travel_style' => 'nullable|in:budget,mid_range,luxury,backpacker',
                'interests' => 'nullable|array',
                'fitness_level' => 'nullable|in:easy,moderate,hard',
            ]);

            $result = $this->planner->generate($request->all());

            return response()->json([
    'success' => true,
    'data' => [
        'request' => $result['request'],
        'result' => $result['result'],
        'days' => $result['days'],
        'total_cost' => $result['total_cost'] ?? null,
        'breakdown' => $result['breakdown'] ?? [], // ✅ यो लाइन थप
        'currency' => 'NPR',
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
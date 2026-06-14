<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ItineraryGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItineraryController extends Controller
{
    public function generate(Request $request, ItineraryGenerator $generator)
    {
        $request->validate([
            'destination' => 'required|string|max:255',
            'days' => 'required|integer|min:1|max:30',
            'budget' => 'required|integer|min:100',
            'travel_style' => 'required|string|in:budget,mid-range,luxury,backpacker',
            'interests' => 'nullable|string|max:500',
        ]);

        try {
            $itinerary = $generator->generate($request->only([
                'destination', 'days', 'budget', 'travel_style', 'interests'
            ]));
            
            return response()->json([
                'success' => true,
                'itinerary' => nl2br(e($itinerary)),
            ]);
        } catch (\Exception $e) {
            Log::error('AI Itinerary Generation Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
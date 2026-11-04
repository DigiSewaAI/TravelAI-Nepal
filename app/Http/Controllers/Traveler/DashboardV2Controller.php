<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\Traveler\UnifiedDashboardService;

class DashboardV2Controller extends Controller
{
    /**
     * Display the V2 traveler dashboard.
     * Feature flag gate applied via route middleware.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->isTraveler()) {
            abort(403);
        }

        $data = app(UnifiedDashboardService::class)->getData($user->id);

        return view('traveler.dashboard-v2', $data);
    }
}
<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;

class DashboardV2Controller extends Controller
{
    /**
     * Display the V2 traveler dashboard.
     *
     * NOTE: Data service will be wired in Commit 2.
     * View will be created in Commit 4.
     * Feature flag gate applied via route middleware.
     */
    public function index()
    {
        // Stub: Data service + view wired in subsequent commits
        return response('Traveler Dashboard V2 — Commit 1 stub', 200);
    }
}
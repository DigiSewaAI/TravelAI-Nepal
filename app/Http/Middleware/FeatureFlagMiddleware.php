<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureFlagMiddleware
{
    /**
     * Handle feature flag gate.
     * Usage: ->middleware('feature.flag:traveler_dashboard_v2')
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        if (!config("features.{$flag}", false)) {
            abort(404);
        }

        return $next($request);
    }
}
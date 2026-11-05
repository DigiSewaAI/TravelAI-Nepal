<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureFlagMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Supports two flag formats:
     *   - "traveler_dashboard_v2" → checks config('features.traveler_dashboard_v2')
     *   - "claim.restaurant_enabled" → checks config('claim.restaurant_enabled')
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        $enabled = $this->isEnabled($flag);

        if (!$enabled) {
            abort(404);
        }

        return $next($request);
    }

    private function isEnabled(string $flag): bool
    {
        // Dot notation → direct config path
        if (str_contains($flag, '.')) {
            return (bool) config($flag, false);
        }

        // Legacy → features.{flag}
        return (bool) config("features.{$flag}", false);
    }
}
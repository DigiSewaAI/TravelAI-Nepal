<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFeatureAccess
{
    /**
     * FIX-05 Phase 3: Generic feature-access middleware.
     * Usage: ->middleware('feature:full_analytics')
     */
    public function handle(Request $request, Closure $next, string $feature)
    {
        $user = $request->user();

        if (!$user) {
            // FIX-02 auth middleware normally handles this,
            // but preserve safe fallback.
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $provider = $user->ownProvider();

        if (!$provider || !$provider->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Feature not available in your plan.',
                    'feature' => $feature,
                ], 403);
            }

            abort(403, 'Your plan does not include this feature. Please upgrade.');
        }

        return $next($request);
    }
}
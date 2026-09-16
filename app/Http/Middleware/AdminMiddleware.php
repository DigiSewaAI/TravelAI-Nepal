<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

                if (!Auth::user()->isSuperAdmin()) {
            Log::warning('Admin access denied', [
                'user_id' => Auth::id(),
                'role'    => Auth::user()->role,
                'ip_hash' => hash('sha256', $request->ip() ?? 'unknown'),
                'path'    => $request->path(),
            ]);
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
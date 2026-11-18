<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetUserTimezone
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: authenticated user को DB timezone > guest को session timezone > UTC
        if (auth()->check() && auth()->user()->timezone) {
            $tz = auth()->user()->timezone;
        } elseif (session('guest_timezone')) {
            $tz = session('guest_timezone');
        } else {
            $tz = config('app.timezone', 'UTC');
        }

        // request scope मा मात्र — global config कहिल्यै change नगर्ने
        $request->attributes->set('user_timezone', $tz);
        view()->share('userTimezone', $tz);

        return $next($request);
    }
}
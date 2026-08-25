<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Session मा 'locale' छ भने त्यो प्रयोग गर्ने, नभए Default
        $locale = session('locale', 'en');

        // 2. यदि Provider Guard ले Login गरेको छ भने Default Nepali राख्ने
        if (auth()->guard('provider')->check()) {
            // यदि Session मा locale छैन भने 'np' सेट गर्ने
            if (!session()->has('locale')) {
                $locale = 'np';
                session(['locale' => $locale]);
            }
        }

        // 3. Laravel को App Locale सेट गर्ने
        app()->setLocale($locale);

        return $next($request);
    }
}
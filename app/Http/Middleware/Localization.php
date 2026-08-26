<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Session बाट locale लिने, default 'en'
        $locale = session('locale', 'en');
        Log::info('🔍 [Localization Middleware] Step 1: Session locale', ['locale' => $locale]);

        // 2. यदि Provider Guard ले Login गरेको छ भने Default Nepali राख्ने
        if (auth()->guard('provider')->check()) {
            Log::info('🔍 [Localization Middleware] Provider guard is active');
            
            // यदि Session मा locale छैन भने 'np' सेट गर्ने
            if (!session()->has('locale')) {
                $locale = 'np';
                session(['locale' => $locale]);
                Log::info('🔍 [Localization Middleware] Step 2: Provider guard set locale to np', ['locale' => $locale]);
            } else {
                Log::info('🔍 [Localization Middleware] Step 2: Session has locale, keeping it', ['locale' => $locale]);
            }
        } else {
            Log::info('🔍 [Localization Middleware] Provider guard not active, using session locale');
        }

        // 3. Laravel को App Locale सेट गर्ने
        app()->setLocale($locale);
        Log::info('🔍 [Localization Middleware] Step 3: App locale set', ['locale' => $locale]);

        return $next($request);
    }
}
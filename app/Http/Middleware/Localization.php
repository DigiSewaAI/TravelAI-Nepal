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

        // 2. Provider routes मा English force, अरूमा session locale (public/traveler/admin)
        if ($request->is('provider') || $request->is('provider/*')) {
            $locale = 'en';
            Log::info('🔍 [Localization Middleware] Provider route — forcing English', ['locale' => $locale]);
        } else {
            $locale = session('locale', 'en');
            Log::info('🔍 [Localization Middleware] Non-provider route — using session locale', ['locale' => $locale]);
        }
        // 3. Laravel को App Locale सेट गर्ने
        app()->setLocale($locale);

        // 3b. Carbon locale mapping (Laravel 'np' → Carbon 'ne')
        $carbonLocale = match($locale) {
            'np' => 'ne',      // Nepali
            'hi' => 'hi',      // Hindi
            'zh' => 'zh_CN',   // Chinese Simplified
            'ru' => 'ru',       // Russian
            default => 'en',
        };
        \Carbon\Carbon::setLocale($carbonLocale);
        Log::info('🔍 [Localization Middleware] Step 3: App+Carbon locale set', ['app' => $locale, 'carbon' => $carbonLocale]);

        return $next($request);
    }
}
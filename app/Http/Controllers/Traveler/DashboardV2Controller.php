<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\Traveler\UnifiedDashboardService;
use Illuminate\Support\Facades\DB;

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

        // Base data
        $data = app(UnifiedDashboardService::class)->getData($user->id);

        // Header data
        $data['user'] = $user;
        $data['notifCount'] = $user->unreadNotifications()->count();
        $data['currentLocale'] = app()->getLocale();
        $data['currentCurrency'] = session('display_currency', 'NPR');

        // Exchange rates (5 currencies, live)
        try {
            $data['exchangeRates'] = DB::table('exchange_rates')
                ->where('target_currency', 'NPR')
                ->whereIn('base_currency', ['USD', 'EUR', 'GBP', 'INR', 'AUD'])
                ->orderBy('base_currency')
                ->get();
        } catch (\Throwable $e) {
            $data['exchangeRates'] = collect();
        }

        // Weather (Kathmandu default, try/catch — no crash if unavailable)
        $data['weatherData'] = null;
        try {
            $wmo = app(\App\Services\OpenMeteoService::class);
            if (method_exists($wmo, 'getCurrentWeather')) {
                $data['weatherData'] = $wmo->getCurrentWeather(27.7172, 85.3240);
            } elseif (method_exists($wmo, 'fetch')) {
                $data['weatherData'] = $wmo->fetch(27.7172, 85.3240);
            }
        } catch (\Throwable $e) {
            $data['weatherData'] = null;
        }

        // Reviews count
        try {
            $data['reviewsCount'] = DB::table('reviews')->where('user_id', $user->id)->count();
        } catch (\Throwable $e) {
            $data['reviewsCount'] = 0;
        }

        return view('traveler.dashboard-v2', $data);
    }
}
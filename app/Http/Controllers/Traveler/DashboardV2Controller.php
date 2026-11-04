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
            $data['weatherData'] = $wmo->getWeatherForCoords(27.7172, 85.3240);
        } catch (\Throwable $e) {
            $data['weatherData'] = null;
        }

        // Reviews count
        try {
            $data['reviewsCount'] = DB::table('reviews')->where('user_id', $user->id)->count();
        } catch (\Throwable $e) {
            $data['reviewsCount'] = 0;
        }

        // Passport data
        try {
            $passportService = app(\App\Services\Passport\DigitalTrekPassportService::class);
            $data['passportData'] = $passportService->getPassportData($user);
        } catch (\Throwable $e) {
            $data['passportData'] = null;
        }

        // Active booking (for "My Travels" summary)
        try {
            $data['activeBooking'] = \App\Models\Booking::where('traveler_id', $user->id)
                ->whereIn('status', ['confirmed', 'active'])
                ->with('service')
                ->latest()
                ->first();
        } catch (\Throwable $e) {
            $data['activeBooking'] = null;
        }

        // Passport stats
        try {
            $data['trekCount'] = \App\Models\Booking::where('traveler_id', $user->id)->count();
            $data['checkinCount'] = \App\Models\QrScan::whereHas('booking', function ($q) use ($user) {
                $q->where('traveler_id', $user->id);
            })->count();
        } catch (\Throwable $e) {
            $data['trekCount'] = 0;
            $data['checkinCount'] = 0;
        }

        
        // [AUTO-INJECTED] 6 Gaps Data
        $data['statusCounts'] = [
            'upcoming' => \App\Models\Booking::where('traveler_id', $user->id)->whereIn('status', ['pending', 'confirmed'])->count(),
            'active' => \App\Models\Booking::where('traveler_id', $user->id)->whereIn('status', ['checked_in', 'active', 'in_progress'])->count(),
            'completed' => \App\Models\Booking::where('traveler_id', $user->id)->whereIn('status', ['completed', 'checked_out'])->count(),
            'reviews' => $data['counts']['reviews'] ?? 0,
        ];
        $data['qr_scans'] = \App\Models\QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->count();
        $data['user_media_count'] = \Illuminate\Support\Facades\DB::table('user_media')->where('user_id', $user->id)->count();
        $data['wholesale_count'] = \Illuminate\Support\Facades\DB::table('wholesale_rfqs')->where('buyer_id', $user->id)->count();

        return view('traveler.dashboard-v2', $data);
    }
}
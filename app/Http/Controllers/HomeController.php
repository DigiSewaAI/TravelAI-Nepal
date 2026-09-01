<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\QrScan;
use App\Models\Route;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Show the application homepage with dynamic data.
     *
     * @return \Illuminate\View\View
     */
    public function index()
{
    // ✅ Get current app locale
    $locale = app()->getLocale();
    // ✅ Map 'np' to 'ne' for Carbon
    $carbonLocale = $locale === 'np' ? 'ne' : $locale;
    Carbon::setLocale($carbonLocale);


    // Fetch featured services (latest 6, with provider relationship)
    $featuredServices = Service::with(['provider', 'category', 'trekDetail'])
        ->where('status', 'active')
        ->latest()
        ->take(6)
        ->get();

    // Real-time stats from database
    $totalServices = Service::count();
    $totalProviders = Provider::count();
    $totalBookings = Booking::count();

    // Stats array for the banner
    $stats = [
        ['value' => $totalServices . '+', 'label' => 'Tourism Services'],
        ['value' => $totalProviders, 'label' => 'Trusted Providers'],
        ['value' => $totalBookings, 'label' => 'Happy Travelers'],
        ['value' => 'Zero Commission', 'label' => 'Smart Contracts Ready']
    ];

    // Recent Check‑ins (existing)
    $recentCheckins = QrScan::with(['booking.service.provider', 'booking.traveler'])
    ->latest('scanned_at')
    ->take(10)
    ->get()
    ->map(function ($scan) {
        $booking = $scan->booking;
        $service = $booking?->service;
        $provider = $service?->provider;

        $checkpoint = $scan->checkpoint_name ?: 'Checkpoint';
        $trekName = $service?->name ?? 'Trek';
        $agencyName = $provider?->name ?? 'Local Trek Partner';
        $travelerName = 'Anonymous';
        $timeAgo = $scan->scanned_at ? $scan->scanned_at->diffForHumans() : 'Just now';
        
        $coverImage = $service?->cover_image ? asset('storage/' . $service->cover_image) : null;

        return [
            'checkpoint'    => $checkpoint,
            'trek_name'     => $trekName,
            'agency_name'   => $agencyName,
            'trekker_name'  => $travelerName,
            'time_ago'      => $timeAgo,
            'cover_image'   => $coverImage,
        ];
    });

    // ✅ NEW: Fetch active routes for destination search
    $routes = Route::where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'slug', 'duration_days', 'max_altitude']);

    // Return view with all required variables
    return view('home', compact('featuredServices', 'stats', 'recentCheckins', 'routes'));
}
}
<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\QrScan;

class HomeController extends Controller
{
    /**
     * Show the application homepage with dynamic data.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
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

        // 🔥 FIX: Recent Check‑ins with proper data, fallbacks, and privacy
        $recentCheckins = QrScan::with(['booking.service.provider', 'booking.traveler'])
            ->latest('scanned_at')  // use scanned_at for ordering
            ->take(10)
            ->get()
            ->map(function ($scan) {
                // Safely extract relations
                $booking = $scan->booking;
                $service = $booking?->service;
                $provider = $service?->provider;

                // Checkpoint: use scanned checkpoint_name, fallback to 'Checkpoint'
                $checkpoint = $scan->checkpoint_name ?: 'Checkpoint';

                // Trek/Route name: from service, fallback to 'Trek'
                $trekName = $service?->name ?? 'Trek';

                // Provider/Agency name: from provider, fallback to 'Local Trek Partner'
                $agencyName = $provider?->name ?? 'Local Trek Partner';

                // Traveler identity: always Anonymous (privacy)
                $travelerName = 'Anonymous';

                // Time: relative from scanned_at, fallback to 'Just now'
                $timeAgo = $scan->scanned_at ? $scan->scanned_at->diffForHumans() : 'Just now';

                // Cover image: if service has one, use it; otherwise null
                $coverImage = $service?->cover_image
                    ? asset('storage/' . $service->cover_image)
                    : null;

                return [
                    'checkpoint'    => $checkpoint,
                    'trek_name'     => $trekName,
                    'agency_name'   => $agencyName,
                    'trekker_name'  => $travelerName,   // always Anonymous
                    'time_ago'      => $timeAgo,
                    'cover_image'   => $coverImage,
                ];
            });

        // Return view with all required variables
        return view('home', compact('featuredServices', 'stats', 'recentCheckins'));
    }
}
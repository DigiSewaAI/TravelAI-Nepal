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

        // Fetch latest 10 check-ins with related data
        $recentCheckins = QrScan::with(['booking.service.provider', 'booking.traveler'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($scan) {
                $service = $scan->booking->service;
                $provider = $service->provider ?? null;
                return [
                    'checkpoint'   => $scan->checkpoint_name,
                    'service_name' => $service->name ?? 'Unknown Service',
                    'provider_name' => $provider->name ?? 'Independent',
                    'traveler_name' => $scan->booking->traveler->name ?? 'Guest',
                    'time_ago'     => $scan->scanned_at->diffForHumans(),
                    'cover_image'  => $service->cover_image ? asset('storage/' . $service->cover_image) : null,
                ];
            });

        // Return view with all required variables
        return view('home', compact('featuredServices', 'stats', 'recentCheckins'));
    }
}
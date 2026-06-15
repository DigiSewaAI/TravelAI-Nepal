<?php

namespace App\Http\Controllers;

use App\Models\Trek;
use App\Models\Booking;
use App\Models\Agency;
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
        // Fetch featured treks (latest 6, with agency relationship)
        $featuredTreks = Trek::with('agency')
            ->latest()
            ->take(6)
            ->get();

        // Real-time stats from database
        $totalTreks = Trek::count();
        $totalAgencies = Agency::count();
        $totalBookings = Booking::count();

        // Stats array for the banner (matches the Blade template's @foreach)
        $stats = [
            ['value' => $totalTreks . '+', 'label' => 'Trek Packages'],
            ['value' => $totalAgencies, 'label' => 'Trusted Agencies'],
            ['value' => $totalBookings, 'label' => 'Happy Trekkers'],
            ['value' => 'Zero Commission', 'label' => 'Smart Contracts Ready']
        ];

        // Fetch latest 10 check-ins with related trek, agency, trekker, and cover image
        $recentCheckins = QrScan::with(['booking.trek.agency', 'booking.trekker'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($scan) {
                $trek = $scan->booking->trek;
                $agency = $trek->agency ?? null;
                return [
                    'checkpoint'   => $scan->checkpoint_name,
                    'trek_name'    => $trek->name ?? 'Unknown Trek',
                    'agency_name'  => $agency->name ?? 'Independent',
                    'trekker_name' => $scan->booking->trekker->name ?? 'Guest',
                    'time_ago'     => $scan->scanned_at->diffForHumans(),
                    'cover_image'  => $trek->cover_image ? asset('storage/' . $trek->cover_image) : null,
                ];
            });

        // Return view with all required variables
        return view('home', compact('featuredTreks', 'stats', 'recentCheckins'));
    }
}
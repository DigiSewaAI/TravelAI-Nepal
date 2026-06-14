<?php

namespace App\Http\Controllers;

use App\Models\Trek;
use App\Models\Booking;
use App\Models\Agency;

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

        // Return view with both required variables
        return view('home', compact('featuredTreks', 'stats'));
    }
}
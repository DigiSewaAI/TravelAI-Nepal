<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $totalProviders = Provider::count();
        $verifiedProviders = Provider::where('verification_status', 'verified')->count();
        $pendingProviders = Provider::where('verification_status', 'pending')->count();
        $totalServices = Service::count();
        $totalBookings = Booking::count();
        $totalPayments = Payment::where('status', 'success')->sum('amount');
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // Recent Providers
        $recentProviders = Provider::with('user')->latest()->take(5)->get();

        // Recent Bookings
        $recentBookings = Booking::with(['service.provider', 'traveler'])
            ->latest()
            ->take(5)
            ->get();

        // Bookings Trend (last 30 days)
        $bookingsTrend = Booking::selectRaw('DATE(start_date) as date, COUNT(*) as total')
            ->where('start_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Providers by Services
        $topProviders = Provider::withCount('services')
            ->orderBy('services_count', 'desc')
            ->take(5)
            ->get();

        // Top Services by Bookings
        $topServices = Service::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProviders',
            'verifiedProviders',
            'pendingProviders',
            'totalServices',
            'totalBookings',
            'totalPayments',
            'activeSubscriptions',
            'recentProviders',
            'recentBookings',
            'bookingsTrend',
            'topProviders',
            'topServices'
        ));
    }
}
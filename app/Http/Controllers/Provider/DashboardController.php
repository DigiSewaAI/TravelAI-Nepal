<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Service;
use App\Models\QrScan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Auth check
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'You do not have a provider account.');
        }

        // Get all services for this provider
        $services = $provider->services()->get();
        $serviceIds = $services->pluck('id');
        $totalServices = $services->count();

        // Get all bookings for this provider's services
        $bookingsQuery = Booking::whereIn('service_id', $serviceIds);
        $totalBookings = $bookingsQuery->count();
        $pendingBookings = $bookingsQuery->where('status', 'pending')->count();

        // Recent bookings
        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['traveler', 'service'])
            ->latest()
            ->take(10)
            ->get();

        // Bookings Trend (Last 30 Days)
        $bookingsTrend = Booking::whereIn('service_id', $serviceIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Top Services by Bookings
        $topServices = Service::whereIn('id', $serviceIds)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // 🔥 Recent Check-ins (QR Scans) for this provider's services
        $checkinHistory = QrScan::whereIn('booking_id', function ($query) use ($serviceIds) {
            $query->select('id')
                  ->from('bookings')
                  ->whereIn('service_id', $serviceIds);
        })->with(['booking.traveler', 'booking.service'])
          ->latest('scanned_at')
          ->take(20)
          ->get();

        return view('provider.dashboard', compact(
            'provider',
            'totalServices',
            'totalBookings',
            'pendingBookings',
            'recentBookings',
            'bookingsTrend',
            'topServices',
            'checkinHistory'
        ));
    }
}
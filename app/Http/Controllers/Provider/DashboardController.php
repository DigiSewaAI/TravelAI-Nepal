<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Service;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'You do not have a provider account.');
        }

        // Get all services for this provider
        $services = $provider->services()->get();
        $totalServices = $services->count();

        // Get all bookings for this provider's services
        $bookingIds = Booking::whereIn('service_id', $services->pluck('id'))->get();
        $totalBookings = $bookingIds->count();
        $pendingBookings = $bookingIds->where('status', 'pending')->count();

        // Recent bookings
        $recentBookings = Booking::whereIn('service_id', $services->pluck('id'))
            ->with(['traveler', 'service'])
            ->latest()
            ->take(10)
            ->get();

        return view('provider.dashboard', compact(
            'provider',
            'totalServices',
            'totalBookings',
            'pendingBookings',
            'recentBookings',
            'services'
        ));
    }
}
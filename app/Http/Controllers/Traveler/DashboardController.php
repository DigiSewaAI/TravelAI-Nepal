<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\QrScan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all bookings
        $bookings = Booking::where('traveler_id', $user->id)
            ->with(['service', 'review'])
            ->latest()
            ->get();
        
        // Stats
        $totalBookings = $bookings->count();
        $upcomingBookings = $bookings->where('status', 'pending')->count();
        $completedBookings = $bookings->where('status', 'completed')->count();
        $activeBookings = $bookings->where('status', 'confirmed')->count();
        
        // Active trip
        $activeTrip = $bookings->where('status', 'confirmed')->first();
        
        // Reviews
        $reviews = $user->reviews()->with('service')->latest()->get();
        
        // 🔥 QR Scan History (Check-ins)
        $qrScans = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->with(['booking.service'])->latest('scanned_at')->take(10)->get();
        
        $bookingStats = [
            'total' => $totalBookings,
            'upcoming' => $upcomingBookings,
            'completed' => $completedBookings,
            'active' => $activeBookings,
        ];
        
        $hasBookings = $totalBookings > 0;

        // ✅ NEW: Check if user has any passport data (scan history)
        $hasPassport = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->exists();
        
        $hour = Carbon::now()->hour;
        if ($hour < 12) {
            $greeting = 'Morning';
        } elseif ($hour < 17) {
            $greeting = 'Afternoon';
        } else {
            $greeting = 'Evening';
        }
        
        return view('traveler.dashboard', compact(
            'user',
            'bookings',
            'reviews',
            'qrScans',
            'bookingStats',
            'activeTrip',
            'hasBookings',
            'greeting',
            'hasPassport' // ✅ Add this
        ));
    }
}
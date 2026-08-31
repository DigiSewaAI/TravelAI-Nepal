<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\QrScan;
use App\Models\UserMedia;
use App\Models\Waypoint;
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

        // QR Scan History (Check-ins)
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
        $hasReplay = Booking::where('traveler_id', $user->id)->exists();

        // Check if user has any passport data (scan history)
        $hasPassport = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->exists();

        // ✅ NEW: User Media for Memories section
        $userMedia = UserMedia::where('user_id', $user->id)
            ->with(['waypoint'])
            ->orderBy('created_at', 'desc')
            ->get();

        $mediaByCheckpoint = $userMedia->groupBy('waypoint_id');

        // ✅ NEW: All waypoints the user has checked into (for dropdown)
        $waypointIds = QrScan::whereHas('booking', function ($q) use ($user) {
    $q->where('traveler_id', $user->id);
})->whereNotNull('waypoint_id')->pluck('waypoint_id')->unique()->values();

$userWaypoints = Waypoint::whereIn('id', $waypointIds)->get();

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
            'hasPassport',
            'hasReplay',
            'userMedia',          // ✅ NEW
            'mediaByCheckpoint',  // ✅ NEW
            'userWaypoints'       // ✅ NEW
        ));
    }
}
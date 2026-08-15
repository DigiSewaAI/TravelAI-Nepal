<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
        
        // Active trip (first confirmed booking)
        $activeTrip = $bookings->where('status', 'confirmed')->first();
        
        // Reviews
        $reviews = $user->reviews()->with('service')->latest()->get();
        
        // Booking stats for dashboard
        $bookingStats = [
            'total' => $totalBookings,
            'upcoming' => $upcomingBookings,
            'completed' => $completedBookings,
            'active' => $activeBookings,
        ];
        
        // Check if user has any booking
        $hasBookings = $totalBookings > 0;
        
        // Greeting based on time
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
            'bookingStats',
            'activeTrip',
            'hasBookings',
            'greeting'
        ));
    }
}
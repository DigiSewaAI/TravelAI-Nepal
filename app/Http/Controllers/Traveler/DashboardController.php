<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bookings = Booking::where('traveler_id', $user->id)
            ->with(['service', 'review'])
            ->latest()
            ->paginate(10);

        $reviews = $user->reviews()->with('service')->latest()->get();

        return view('traveler.dashboard', compact('bookings', 'reviews'));
    }
}
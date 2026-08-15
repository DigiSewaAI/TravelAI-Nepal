<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function show(Booking $booking)
    {
        // Ensure booking belongs to logged-in traveler
        if ($booking->traveler_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        
        // Load relationships
        $booking->load([
            'service', 
            'service.provider',  // ✅ Correct: provider through service
            'review',
            'qrScans'            // ✅ For check-in history
        ]);
        
        return view('traveler.bookings.show', compact('booking'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\QrScan;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /**
     * Show the check-in page for a booking (scan QR code).
     */
    public function show(Booking $booking)
    {
        return view('checkin.scan', compact('booking'));
    }

    /**
     * Record a check-in at a checkpoint.
     */
    public function checkin(Request $request, Booking $booking)
    {
        $request->validate([
            'checkpoint' => 'required|string|max:255',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        QrScan::create([
            'booking_id'      => $booking->id,
            'checkpoint_name' => $request->checkpoint,
            'scanned_at'      => now(),
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
        ]);

        return response()->json(['success' => true, 'message' => 'Check-in recorded successfully.']);
    }
}
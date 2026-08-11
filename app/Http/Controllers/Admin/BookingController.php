<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

// 🔥 Import the notification class
use App\Notifications\BookingStatusUpdated;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['traveler', 'service', 'service.provider'])
            ->latest()
            ->paginate(20);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['traveler', 'service', 'service.provider', 'qrScans']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->status = $request->status;
        $booking->save();

        // 🔥 Send notification to the traveler
        if ($booking->traveler) {
            $booking->traveler->notify(new BookingStatusUpdated($booking));
        }

        return back()->with('success', 'Booking status updated and traveler notified.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }
}
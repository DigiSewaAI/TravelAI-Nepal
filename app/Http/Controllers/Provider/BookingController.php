<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // ✅ Import trait

// 🔥 Import the notification class
use App\Notifications\BookingStatusUpdated;

class BookingController extends Controller
{
    use AuthorizesRequests; // ✅ Use trait for authorization

    public function index()
    {
        $provider = Auth::user()->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $bookings = Booking::whereHas('service', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })->with(['traveler', 'service'])->latest()->get();

        return view('provider.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        return view('provider.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->update(['status' => $request->status]);

        // 🔥 Send notification to the traveler
        if ($booking->traveler) {
            $booking->traveler->notify(new BookingStatusUpdated($booking));
        }

        return back()->with('success', 'Booking status updated and traveler notified.');
    }
}
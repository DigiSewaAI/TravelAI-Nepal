<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// 🔥 Note: Notification is now sent from Admin/ReviewController@approve
// No need to import NewReviewReceived here anymore

class ReviewController extends Controller
{
    public function create(Booking $booking)
    {
        // Ensure booking belongs to logged-in user and is completed
        if ($booking->traveler_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->with('error', 'You can only review completed bookings.');
        }

        if ($booking->review) {
            return back()->with('error', 'You already reviewed this booking.');
        }

        return view('traveler.reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Authorize
        if ($booking->traveler_id !== Auth::id() || $booking->status !== 'completed') {
            abort(403);
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'service_id' => $booking->service_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending', // 🔥 Admin approval required first
        ]);

        // 🔥 Notification is now sent from Admin/ReviewController@approve
        // No notifications sent here - admin will approve/reject first

        return redirect()->route('traveler.dashboard')->with('success', 'Your review has been submitted for admin approval.');
    }
}
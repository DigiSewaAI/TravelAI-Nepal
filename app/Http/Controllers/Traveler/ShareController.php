<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareController extends Controller
{
    private function authorizeBooking(Booking $booking)
    {
        // ✅ FIXED: user_id → traveler_id
        if ($booking->traveler_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    public function toggleVisibility(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);
        $request->validate(['visibility' => 'required|in:private,link,public']);

        if ($request->visibility === 'private') {
            $booking->revokeShare();
            $booking->visibility = 'private';
            $booking->save();
        } else {
            $booking->enableSharing($request->visibility);
        }

        return back()->with('success', 'Sharing settings updated.');
    }

    public function revoke(Booking $booking)
    {
        $this->authorizeBooking($booking);
        $booking->revokeShare();
        return back()->with('success', 'Sharing disabled.');
    }

    public function regenerateToken(Booking $booking)
    {
        $this->authorizeBooking($booking);
        $booking->share_token = $booking->generateShareToken();
        $booking->save();
        return back()->with('success', 'New share link generated.');
    }
}
<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // कुनै __construct() छैन – मिडलवेयर रूट मार्फत लागू हुन्छ

    public function index()
    {
        $agencyId = Auth::guard('agency')->id();

        $bookings = Booking::whereHas('trek', function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->with(['trekker', 'trek'])->latest()->get();

        return view('agency.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->trek->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        $scans = $booking->qrScans()->latest()->get();

        return view('agency.bookings.show', compact('booking', 'scans'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        if ($booking->trek->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Booking status updated.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    DB::transaction(function () use ($booking, $request) {
        $locked = Booking::where('id', $booking->id)->lockForUpdate()->first();

        $oldStatus = $locked->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $wasCancelled = in_array($oldStatus, ['cancelled', 'rejected'], true);
        $nowCancelled = in_array($newStatus, ['cancelled', 'rejected'], true);

        $locked->status = $newStatus;
        $locked->save();

        if (!$wasCancelled && $nowCancelled) {
            app(\App\Services\BookingLimitService::class)->release(
                $locked->service->provider,
                $locked->quota_month
            );
        }
    });

    if ($booking->fresh()->traveler) {
        $booking->fresh()->traveler->notify(new BookingStatusUpdated($booking->fresh()));
    }

    return back()->with('success', 'Booking status updated and traveler notified.');
}

    public function destroy(Booking $booking)
{
    DB::transaction(function () use ($booking) {
        $locked = Booking::where('id', $booking->id)->lockForUpdate()->first();

        $wasConsuming = in_array($locked->status, ['pending', 'confirmed', 'completed'], true);

        $quotaMonth = $locked->quota_month;
        $providerId = $locked->service->provider_id;

        $locked->delete();

        if ($wasConsuming && $quotaMonth) {
            app(\App\Services\BookingLimitService::class)->release(
                \App\Models\Provider::find($providerId),
                $quotaMonth
            );
        }
    });

    return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
}
}
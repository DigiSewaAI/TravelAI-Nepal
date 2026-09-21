<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// 🔥 Import the notification class
use App\Notifications\BookingStatusUpdated;
use Illuminate\Support\Facades\Log;

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
        // PROVIDER-ITINERARY-09B-04: Canonical lock order
        // Departure lock FIRST if this booking is departure-bound.
        $departureId = Booking::where('id', $booking->id)->value('departure_id');
        if ($departureId !== null) {
            \App\Models\Departure::where('id', $departureId)->lockForUpdate()->first();
        }

        $locked = Booking::where('id', $booking->id)->lockForUpdate()->first();

        $oldStatus = $locked->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        // PROVIDER-ITINERARY-09B-04: Enforce transition rules (was missing)
        if (!\App\Support\BookingStatusTransitions::canTransition($oldStatus, $newStatus)) {
            throw new \DomainException(
                "Cannot transition from '{$oldStatus}' to '{$newStatus}'."
            );
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

        $bookingId = $locked->id;
        $providerId = $locked->service->provider_id;
        $oldStatusCapture = $oldStatus;
        $newStatusCapture = $newStatus;

        DB::afterCommit(function () use ($bookingId, $providerId, $oldStatusCapture, $newStatusCapture) {
            Log::info('Admin booking status transitioned', [
                'booking_id'  => $bookingId,
                'provider_id' => $providerId,
                'old_status'  => $oldStatusCapture,
                'new_status'  => $newStatusCapture,
            ]);
        });
    });

    if ($booking->fresh()->traveler) {
        $booking->fresh()->traveler->notify(new BookingStatusUpdated($booking->fresh()));
    }

    return back()->with('success', 'Booking status updated and traveler notified.');
}

    public function destroy(Booking $booking)
{
        DB::transaction(function () use ($booking) {
        // PROVIDER-ITINERARY-09B-04: Canonical lock order
        // Departure lock FIRST if this booking is departure-bound.
        $departureId = Booking::where('id', $booking->id)->value('departure_id');
        if ($departureId !== null) {
            \App\Models\Departure::where('id', $departureId)->lockForUpdate()->first();
        }

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

        $bookingIdCapture = $booking->id;
        $wasConsumingCapture = $wasConsuming;
        $quotaMonthCapture = $quotaMonth;

        DB::afterCommit(function () use ($bookingIdCapture, $providerId, $wasConsumingCapture, $quotaMonthCapture) {
            Log::info('Admin booking deleted', [
                'booking_id'    => $bookingIdCapture,
                'provider_id'   => $providerId,
                'was_consuming' => $wasConsumingCapture,
                'quota_month'   => $quotaMonthCapture,
            ]);
        });
    });

    return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
}
}
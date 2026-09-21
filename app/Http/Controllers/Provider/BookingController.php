<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
// 🔥 Import the notification class
use App\Notifications\BookingStatusUpdated;
use Illuminate\Support\Facades\Log;

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
        'status' => 'required|in:pending,confirmed,completed,cancelled,rejected',
    ]);

    $newStatus = $request->status;

        DB::transaction(function () use ($booking, $newStatus) {
    // PROVIDER-ITINERARY-09B-04: Canonical lock order
    // Departure lock FIRST if this booking is departure-bound.
    $departureId = Booking::where('id', $booking->id)->value('departure_id');
    if ($departureId !== null) {
        \App\Models\Departure::where('id', $departureId)->lockForUpdate()->first();
    }

    $locked = Booking::where('id', $booking->id)->lockForUpdate()->first();

    $oldStatus = $locked->status;

    if ($oldStatus === $newStatus) {
        return;  // idempotent no-op
    }

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
        Log::info('Booking status transitioned', [
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
}
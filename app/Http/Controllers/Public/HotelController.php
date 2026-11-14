<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\HotelRoom;
use App\Models\HotelBooking; // 🆕 यो लाइन थप्नुस्
use App\Models\ProviderOffer;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function show($slug)
    {
        $service = Service::with(['provider', 'category', 'hotelDetail'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $provider = $service->provider;
        $hotelDetail = $service->hotelDetail;

        $rooms = HotelRoom::where('provider_id', $service->provider_id)
            ->orderBy('room_number')
            ->get();

        $offers = ProviderOffer::where('provider_id', $service->provider_id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->get();

        return view('public.hotel.show', compact('service', 'provider', 'hotelDetail', 'rooms', 'offers'));
    }

    public function book(Request $request, $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();
        $provider = $service->provider;

        $validated = $request->validate([
            'room_id' => 'required|exists:hotel_rooms,id',
            'guest_name' => 'required|string|max:100',
            'guest_email' => 'required|email|max:150',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $room = HotelRoom::findOrFail($validated['room_id']);
        abort_unless($room->provider_id === $provider->id, 403);

        if ($room->status !== 'available' && $room->status !== 'free') {
            return back()->withErrors(['room_id' => 'This room is no longer available.']);
        }

        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $amount = ($room->base_price ?? 0) * $nights;

        $booking = HotelBooking::create([
            'provider_id' => $provider->id,
            'room_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights' => $nights > 0 ? $nights : 1,
            'amount' => $amount,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $room->update(['status' => 'reserved']);

        // Send Email & Notification
        try {
            \Mail::to($booking->guest_email)->queue(new \App\Mail\HotelBookingCreatedMail($booking));
        } catch (\Throwable $e) {
            \Log::warning('Hotel booking email failed', ['id' => $booking->id, 'error' => $e->getMessage()]);
        }

        try {
            $providerUser = $provider->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\NewHotelBookingNotification($booking));
            }
        } catch (\Throwable $e) {
            \Log::warning('Hotel booking notify failed', ['id' => $booking->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Booking confirmed! Check your email for details.');
    }
}
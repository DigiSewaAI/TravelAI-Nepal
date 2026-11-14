<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TransportVehicle;
use App\Models\TransportBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{
    public function show($slug)
    {
        $service = Service::with([
                'provider', 'category',
                'transportDetail.fromLocation',
                'transportDetail.toLocation',
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $provider = $service->provider;

        $vehicles = TransportVehicle::where('provider_id', $service->provider_id)
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_number')
            ->get();

        $offers = \App\Models\ProviderOffer::where('provider_id', $service->provider_id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until')
            ->get();

        return view('public.transport.show', compact('service', 'provider', 'vehicles', 'offers'));
    }

    public function book(Request $request, $slug)
    {
        $service = Service::with('provider', 'transportDetail')->where('slug', $slug)->firstOrFail();
        $provider = $service->provider;
        abort_unless($provider, 404);

        $validated = $request->validate([
            'vehicle_id'     => 'required|exists:transport_vehicles,id',
            'passenger_name' => 'required|string|max:100',
            'passenger_email'=> 'required|email|max:150',
            'route_name'     => 'required|string|max:150',
            'travel_date'    => 'required|date|after_or_equal:today',
            'travel_time'    => 'nullable|date_format:H:i',
        ]);

        // Vehicle ownership guard
        $vehicle = TransportVehicle::where('id', $validated['vehicle_id'])
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        // Overlap guard (same as provider manual flow)
        $conflict = DB::table('transport_bookings')
            ->where('provider_id', $provider->id)
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('travel_date', $validated['travel_date'])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('travel_time')
            ->where('travel_time', $validated['travel_time'] ?? null)
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'vehicle_id' => __('messages.public_transport_conflict') ?? 'This vehicle is already booked for the selected date and time.'
            ])->withInput();
        }

        // Amount from transportDetail (if available)
        $amount = 0;
        if ($service->transportDetail) {
            $amount = (float) ($service->transportDetail->price_per_person
                ?? $service->transportDetail->price_per_vehicle
                ?? 0);
        }

        // Sequential booking number: TR-YYYY-NNNN
        $year = date('Y');
        $lastRef = DB::table('transport_bookings')
            ->where('booking_ref', 'like', 'TR-' . $year . '-%')
            ->orderByDesc('id')
            ->value('booking_ref');
        $nextSeq = $lastRef ? ((int) substr($lastRef, -4)) + 1 : 1;
        $bookingNumber = 'TR-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        $validated['provider_id']      = $provider->id;
        $validated['booking_ref']      = $bookingNumber;
        $validated['booking_number']   = $bookingNumber;
        $validated['amount']           = $amount;
        $validated['status']           = 'pending';

        $booking = TransportBooking::create($validated);

        // Welcome email to customer
        if ($booking->passenger_email) {
            try {
                \Mail::to($booking->passenger_email)->queue(
                    new \App\Mail\TransportBookingCreatedMail($booking)
                );
            } catch (\Throwable $e) {
                \Log::warning('Transport created mail failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
            }
        }

        // Provider bell notification
        try {
            $providerUser = $provider->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\NewTransportBookingNotification($booking));
            }
        } catch (\Throwable $e) {
            \Log::warning('Transport provider notify failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
        }

        return back()->with('success', __('messages.public_transport_booking_success') ?? 'Your booking request has been sent! The operator will confirm shortly.');
    }
}
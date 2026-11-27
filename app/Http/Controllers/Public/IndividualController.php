<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\IndividualBookingCreatedMail;
use App\Models\IndividualBooking;
use App\Models\Service;
use App\Notifications\NewIndividualBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class IndividualController extends Controller
{
    public function show($slug)
    {
        $service = Service::with(['provider.individualDetail'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('slug', 'guide'))
            ->firstOrFail();

        $provider = $service->provider;
        abort_unless($provider, 404, 'Provider not found.');

        $detail = $provider->individualDetail;

        // Compute price data
        $pricePerDay = (float) ($detail->price_per_day ?? $service->base_price ?? 0);

        return view('public.individual.show', compact('service', 'provider', 'detail', 'pricePerDay'));
    }

    public function book(Request $request, $slug)
    {
        $service = Service::with(['provider.individualDetail'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->whereHas('category', fn($q) => $q->where('slug', 'guide'))
            ->firstOrFail();

        $provider = $service->provider;
        abort_unless($provider, 404);
        $detail = $provider->individualDetail;

        $validated = $request->validate([
            'client_name'    => 'required|string|max:100',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'start_date'     => 'required|date|after_or_equal:today',
            'duration_days'  => 'required|integer|min:1|max:365',
            'pax'            => 'required|integer|min:1|max:50',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $pricePerDay = (float) ($detail->price_per_day ?? $service->base_price ?? 0);
        $amount = $pricePerDay > 0 ? ($pricePerDay * (int) $validated['duration_days'] * (int) $validated['pax']) : 0;

        $booking = IndividualBooking::create([
            'provider_id'           => $provider->id,
            'service_id'            => $service->id,
            'service_type'          => $detail->specialties[0] ?? 'Guide Service',
            'client_name'           => $validated['client_name'],
            'customer_email'        => $validated['customer_email'],
            'customer_phone'        => $validated['customer_phone'] ?? null,
            'title'                 => $service->name,
            'pax'                   => (int) $validated['pax'],
            'start_date'            => $validated['start_date'],
            'duration'              => $validated['duration_days'] . ' days',
            'notes'                 => $validated['notes'] ?? null,
            'amount'                => $amount,
            'price_per_day_snapshot'=> $pricePerDay,
            'status'                => 'pending',
            'payment_status'        => 'pending',
        ]);

        $booking->load('provider');

        // 1. Customer confirmation email
        try {
            Mail::to($booking->customer_email)->queue(new IndividualBookingCreatedMail($booking));
        } catch (\Throwable $e) {
            \Log::warning('Individual booking created mail failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }

        // 2. Provider bell notification
        try {
            $providerUser = $provider->user;
            if ($providerUser) {
                $providerUser->notify(new NewIndividualBookingNotification($booking));
            }
        } catch (\Throwable $e) {
            \Log::warning('Individual booking notification failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Booking recorded! Confirmation email sent to ' . $validated['customer_email'] . '. Booking ID: ' . $booking->booking_number);
    }
}
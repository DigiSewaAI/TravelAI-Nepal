<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\TransportVehicle;
use App\Models\TransportBooking;
use App\Models\TransportRoute;
use App\Models\TransportTrip;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{
    public function show(Request $request, $slug)
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

        $travelDate = $request->query('date');
        $fromLocId = $request->query('from');
        $toLocId   = $request->query('to');
        $hasSearch = $travelDate && $fromLocId && $toLocId;

        $routes = TransportRoute::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->with(['fromLocation:id,city', 'toLocation:id,city'])
            ->get();

        $locations = Location::where('is_habitable', true)
            ->orderBy('city')->get(['id', 'city', 'state']);

        $vehicles = TransportVehicle::where('provider_id', $provider->id)
            ->orderBy('vehicle_type')->orderBy('vehicle_number')->get();

        $trips = collect();
        $allBookedOut = false;
        if ($hasSearch) {
            $routeIds = $routes->where('from_location_id', $fromLocId)
                ->where('to_location_id', $toLocId)->pluck('id');

            if ($routeIds->isNotEmpty()) {
                $allScheduled = TransportTrip::where('provider_id', $provider->id)
                    ->whereIn('route_id', $routeIds)
                    ->whereDate('departure_date', $travelDate)
                    ->where('status', 'scheduled')
                    ->count();

                $trips = TransportTrip::with([
                        'vehicle:id,vehicle_number,vehicle_type,capacity',
                        'route:id,name,from_location_id,to_location_id,duration_minutes',
                        'route.fromLocation:id,city',
                        'route.toLocation:id,city',
                    ])
                    ->where('provider_id', $provider->id)
                    ->whereIn('route_id', $routeIds)
                    ->whereDate('departure_date', $travelDate)
                    ->where('status', 'scheduled')
                    ->where('seats_available', '>', 0)
                    ->orderBy('departure_time')
                    ->get();

                $allBookedOut = ($allScheduled > 0 && $trips->isEmpty());
            }
        }

        $offers = \App\Models\ProviderOffer::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until')->get();

        // ✅ Chunk 2 Edit 3A — payment methods + model
        $paymentMethods = \App\Models\ProviderPaymentMethod::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get();

        $paymentModel = $provider->transport_payment_model ?? 'both';
        $hasActiveMethods = $paymentMethods->isNotEmpty();

        $tripsForJs = $trips->map(fn($t) => [
            'id' => $t->id,
            'departure_time' => substr($t->departure_time, 0, 5),
            'departure_date' => $t->departure_date->format('M d, Y'),
            'vehicle_type' => $t->vehicle->vehicle_type ?? '',
            'vehicle_number' => $t->vehicle->vehicle_number ?? '',
            'route_name' => $t->route->name ?? '',
            'price_per_seat' => (float) $t->price_per_seat,
            'price_per_vehicle' => $t->price_per_vehicle ? (float) $t->price_per_vehicle : null,
            'seats_available' => $t->seats_available,
            'pickup_point' => $t->pickup_point,
            'driver_name' => $t->driver_name,
            'duration_minutes' => $t->route->duration_minutes ?? 0,
        ])->values();

        return view('public.transport.show', compact(
            'service', 'provider', 'vehicles', 'routes', 'locations', 'trips', 'tripsForJs',
            'hasSearch', 'travelDate', 'fromLocId', 'toLocId', 'offers',
            'allBookedOut',
            'paymentMethods', 'paymentModel', 'hasActiveMethods'
        ));
    }

    public function book(Request $request, $slug)
    {
        $service = Service::with('provider')->where('slug', $slug)->firstOrFail();
        $provider = $service->provider;
        abort_unless($provider, 404);

        // ✅ Chunk 2 Edit 3B — payment_model validation
        $validated = $request->validate([
            'trip_id'         => 'required|exists:transport_trips,id',
            'passenger_name'  => 'required|string|max:100',
            'passenger_email' => 'required|email|max:150',
            'passenger_count' => 'required|integer|min:1',
            'booking_type'    => 'required|in:per_seat,full_vehicle',
            'pickup_point'    => 'nullable|string|max:255',
            'payment_model'   => 'nullable|in:advance,pay_on_arrival',
        ]);

        // Resolve payment_model from provider settings
        $providerModel = $provider->transport_payment_model ?? 'both';
        if ($providerModel === 'both') {
            $chosenModel = $validated['payment_model'] ?? 'advance';
        } else {
            $chosenModel = $providerModel;
        }
        unset($validated['payment_model']);

        // Advance requires active payment method
        if ($chosenModel === 'advance') {
            $hasActive = \App\Models\ProviderPaymentMethod::where('provider_id', $provider->id)
                ->where('is_active', true)->exists();
            if (!$hasActive) {
                return back()->withErrors([
                    'payment_model' => 'This provider has no active payment methods. Please choose Pay on Arrival.'
                ])->withInput();
            }
        }

        // ✅ FIX 1: $chosenModel closure मा use गर्नुस्
        return DB::transaction(function () use ($provider, $validated, $chosenModel) {
            $trip = TransportTrip::where('id', $validated['trip_id'])
                ->where('provider_id', $provider->id)
                ->where('status', 'scheduled')
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                return back()->withErrors(['trip_id' => 'This departure is no longer available.'])->withInput();
            }

            $seatsNeeded = $validated['booking_type'] === 'full_vehicle'
                ? $trip->seats_total
                : (int) $validated['passenger_count'];

            if ($trip->seats_available < $seatsNeeded) {
                return back()->withErrors(['trip_id' => "Only {$trip->seats_available} seats left on this departure."])->withInput();
            }

            if ($validated['booking_type'] === 'full_vehicle' && !$trip->price_per_vehicle) {
                return back()->withErrors(['booking_type' => 'Full vehicle booking is not available for this departure.'])->withInput();
            }

            $pricePerUnit = $validated['booking_type'] === 'full_vehicle'
                ? (float) $trip->price_per_vehicle
                : (float) $trip->price_per_seat;

            $totalAmount = $validated['booking_type'] === 'full_vehicle'
                ? $pricePerUnit
                : $validated['passenger_count'] * $pricePerUnit;

            $year = date('Y');
            $lastRef = DB::table('transport_bookings')
                ->where('booking_ref', 'like', 'TR-' . $year . '-%')
                ->orderByDesc('id')->value('booking_ref');
            $nextSeq = $lastRef ? ((int) substr($lastRef, -4)) + 1 : 1;
            $bookingNumber = 'TR-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $booking = TransportBooking::create([
                'provider_id' => $provider->id,
                'trip_id' => $trip->id,
                'vehicle_id' => $trip->vehicle_id,
                'passenger_name' => $validated['passenger_name'],
                'passenger_email' => $validated['passenger_email'],
                'passenger_count' => $validated['passenger_count'],
                'booking_type' => $validated['booking_type'],
                'pickup_point' => $validated['pickup_point'] ?? $trip->pickup_point,
                'price_per_unit' => $pricePerUnit,
                'total_amount' => $totalAmount,
                'booking_ref' => $bookingNumber,
                'booking_number' => $bookingNumber,
                'route_name' => $trip->route->name,
                'travel_date' => $trip->departure_date,
                'travel_time' => $trip->departure_time,
                'amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_model' => $chosenModel,   // ✅ FIX 2: snapshot
            ]);

            $trip->decrement('seats_available', $seatsNeeded);

            // ✅ FIX 3: Dispatch with payment CTA based on model
            if ($chosenModel === 'advance') {
                // Email already includes a "Complete Payment" CTA (see email view)
            }

            try {
                \Mail::to($booking->passenger_email)->queue(new \App\Mail\TransportBookingCreatedMail($booking));
            } catch (\Throwable $e) {
                \Log::warning('Transport created mail failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
            }

            try {
                $providerUser = $provider->user;
                if ($providerUser) {
                    $providerUser->notify(new \App\Notifications\NewTransportBookingNotification($booking));
                }
            } catch (\Throwable $e) {
                \Log::warning('Transport provider notify failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
            }

            if ($chosenModel === 'advance') {
                $paymentMethods = \App\Models\ProviderPaymentMethod::where('provider_id', $provider->id)
                    ->where('is_active', true)
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->get();

                session()->flash('payment_booking', [
                    'id' => $booking->id,
                    'ref' => $booking->booking_ref,
                    'email' => $booking->passenger_email,
                    'amount' => $booking->total_amount,
                    'methods' => $paymentMethods->map(fn($m) => [
                        'id' => $m->id,
                        'type' => $m->type,
                        'label' => $m->label ?: ucfirst($m->type),
                        'identifier' => $m->identifier,
                        'account_name' => $m->account_name,
                        'account_number' => $m->account_number,
                        'bank_name' => $m->bank_name,
                        'qr_url' => $m->qr_image_path ? \Storage::url($m->qr_image_path) : null,
                        'instructions' => $m->instructions,
                        'is_primary' => $m->is_primary,
                    ])->toArray(),
                ]);
            }

            return back()->with('success', __('messages.public_transport_booking_success') ?? 'Your booking request has been sent!');
        });
    }
        public function uploadPublicProof(Request $request, $bookingId)
    {
        $booking = TransportBooking::findOrFail($bookingId);

        // Guard: email must match
        $email = $request->input('email');
        if (!$email || strtolower($email) !== strtolower($booking->passenger_email)) {
            abort(403, 'Unauthorized.');
        }

        if ($booking->payment_status !== 'pending') {
            return back()->withErrors(['payment' => 'Payment proof already submitted.']);
        }

        if (($booking->payment_model ?? 'pay_on_arrival') !== 'advance') {
            return back()->withErrors(['payment' => 'This booking does not require advance payment.']);
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:provider_payment_methods,id',
            'payment_ref'       => 'required|string|max:100',
            'payment_proof'     => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $method = \App\Models\ProviderPaymentMethod::where('id', $validated['payment_method_id'])
            ->where('provider_id', $booking->provider_id)
            ->firstOrFail();

        $proofPath = $request->file('payment_proof')->store('transport-payment-proofs/' . $booking->id, 'public');

        $booking->update([
            'payment_status'       => 'submitted',
            'payment_method'       => $method->type,
            'payment_ref'          => $validated['payment_ref'],
            'payment_proof'        => $proofPath,
            'payment_submitted_at' => now(),
        ]);

        try {
            $providerUser = $booking->provider?->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\PaymentProofSubmittedNotification($booking));
            }
        } catch (\Throwable $e) {
            \Log::warning('Payment proof notify failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
        }

        return redirect()
            ->route('public.transport.show', $booking->trip?->route?->fromLocation?->city ? $booking->provider?->services?->first()?->slug : 'himalayan-transport-service')
            ->with('success', 'Payment proof submitted! The provider will verify shortly.');
    }
}
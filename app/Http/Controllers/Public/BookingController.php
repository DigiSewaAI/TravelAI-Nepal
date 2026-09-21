<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Departure;
use App\Models\User;
use App\Support\QuotaPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function create($serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'active')
            ->firstOrFail();

        // PROVIDER-ITINERARY-09B-04: Load eligible future departures
        // Eligible = scheduled + end_date >= today
        $eligibleDepartures = collect();
        if ($service->isItineraryPublished() && $service->itineraryDays()->exists()) {
            $eligibleDepartures = $service->departures()
                ->where('status', 'scheduled')
                ->where('end_date', '>=', now()->toDateString())
                ->withSum(['bookings as reserved_seats' => function ($q) {
                    $q->whereIn('status', ['pending', 'confirmed', 'completed']);
                }], 'guest_count')
                ->orderBy('start_date')
                ->get();
        }

        return view('public.booking.create', compact('service', 'eligibleDepartures'));
    }

    public function store(Request $request, $serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'active')
            ->firstOrFail();

        // PROVIDER-ITINERARY-09B-04: Departure-scoped validation
        // Determine if this service has eligible departures
        $hasEligibleDepartures = $service->isItineraryPublished()
            && $service->itineraryDays()->exists()
            && $service->departures()
                ->where('status', 'scheduled')
                ->where('end_date', '>=', now()->toDateString())
                ->exists();

        $rules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'message'    => 'nullable|string|max:500',
        ];

        if ($hasEligibleDepartures) {
            $rules['departure_id'] = 'required|integer|exists:departures,id';
            $rules['guest_count']  = 'required|integer|min:1';
        } else {
            $rules['departure_id'] = 'nullable|integer';
            $rules['guest_count']  = 'nullable|integer|min:1';
        }

        $validated = $request->validate($rules);

        // Normalize: legacy flow → departure_id null, guest_count 1
        $departureId = $validated['departure_id'] ?? null;
        $guestCount  = $departureId ? (int) $validated['guest_count'] : 1;

        try {
            $booking = DB::transaction(function () use ($service, $validated, $departureId, $guestCount) {
                $provider = $service->provider;

                                // PROVIDER-ITINERARY-09B-04: Departure-bound flow (LOCK #1)
                $departure = null;
                if ($departureId !== null) {
                    // Re-verify service eligibility inside transaction
                    // (defends against user-supplied departure_id on draft/unpublished services)
                    $freshService = Service::where('id', $service->id)->first();
                    if (!$freshService
                        || !$freshService->isItineraryPublished()
                        || $freshService->itineraryDays()->count() === 0
                    ) {
                        throw new \DomainException('This service does not accept departure-based bookings.');
                    }

                    $departure = Departure::where('id', $departureId)
                        ->lockForUpdate()
                        ->first();

                    // Server-side ownership + eligibility re-verify under lock
                    if (!$departure
                        || $departure->service_id !== $service->id
                        || $departure->status !== 'scheduled'
                        || $departure->end_date->lt(now()->startOfDay())
                    ) {
                        throw new \DomainException('This departure is not available.');
                    }

                    // Capacity re-check under lock
                    $reserved = Booking::where('departure_id', $departure->id)
                        ->whereIn('status', ['pending', 'confirmed', 'completed'])
                        ->sum('guest_count');

                    if (($reserved + $guestCount) > $departure->capacity) {
                        throw new \DomainException('This departure does not have enough remaining capacity.');
                    }
                }

                // Provider monthly quota reservation (atomic SQL)
                app(\App\Services\BookingLimitService::class)->reserve($provider);

                $traveler = $this->resolveTraveler($validated);

                return Booking::create([
                    'traveler_id'  => $traveler->id,
                    'service_id'   => $service->id,
                    'departure_id' => $departure?->id,
                    'guest_count'  => $guestCount,
                    'booking_date' => now(),
                    'start_date'   => $validated['start_date'],
                    'status'       => 'pending',
                    'qr_code'      => Str::random(32),
                    'quota_month'  => QuotaPeriod::current(),
                ]);
            });
        } catch (\DomainException $e) {
            Log::warning('Public booking rejected by domain rule', [
                'service_id'  => $service->id,
                'provider_id' => $service->provider_id,
                'reason'      => 'domain_rule',
            ]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            Log::error('Public booking creation failed', [
                'service_id' => $service->id,
                'error_class' => get_class($e),
            ]);

            return back()
                ->withErrors(['error' => 'We could not complete your booking. Please try again.'])
                ->withInput();
        }

        $signedUrl = URL::temporarySignedRoute(
            'public.booking.confirmation',
            now()->addHours(24),
            ['booking' => $booking->id]
        );

        return redirect($signedUrl)->with('success', 'Booking created successfully!');
    }

    public function confirmation(Request $request, Booking $booking)
    {
        $user = Auth::user();

        $isOwner = $user && (int) $user->id === (int) $booking->traveler_id;

        $isProvider = $user
            && method_exists($user, 'isProviderOwner')
            && $user->isProviderOwner()
            && $user->ownProvider()
            && $booking->service
            && $booking->service->provider_id === $user->ownProvider()->id;

        if (!$isOwner && !$isProvider && !$request->hasValidSignature()) {
            abort(403, 'You are not authorized to view this booking confirmation.');
        }

        $booking->load(['service.provider', 'traveler', 'departure']);

        return view('public.booking.confirmation', compact('booking'));
    }

    /**
     * Resolve traveler per FIX-03 rules:
     * - traveler role → attach
     * - other roles  → reject
     * - new email    → create traveler
     */
    private function resolveTraveler(array $data): User
    {
        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->role !== 'traveler') {
                throw new \DomainException(
                    'This email is registered under a different account type. Please use a different email.'
                );
            }
            return $existing;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => 'traveler',
            'password' => bcrypt(Str::random(40)),
        ]);
    }
}
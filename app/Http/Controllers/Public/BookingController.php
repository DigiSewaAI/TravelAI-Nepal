<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Booking;
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

        return view('public.booking.create', compact('service'));
    }

    public function store(Request $request, $serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            $booking = DB::transaction(function () use ($service, $validated) {
                $traveler = $this->resolveTraveler($validated);

                return Booking::create([
                    'traveler_id' => $traveler->id,
                    'service_id' => $service->id,
                    'booking_date' => now(),
                    'start_date' => $validated['start_date'],
                    'status' => 'pending',
                    'qr_code' => Str::random(32),
                    'quota_month' => QuotaPeriod::current(),
                ]);
            });
        } catch (\DomainException $e) {
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

        $booking->load(['service.provider', 'traveler']);

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
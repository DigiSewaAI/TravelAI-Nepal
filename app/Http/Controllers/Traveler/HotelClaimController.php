<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\HotelBooking;
use App\Services\Claim\OtpService;
use App\Services\Claim\RateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotelClaimController extends Controller
{
    private function assertTraveler(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'traveler') abort(403);
    }

    public function showForm()
    {
        $this->assertTraveler();
        return view('traveler.hotel.claim-form');
    }

    public function initiate(Request $request, RateLimitService $rateLimiter)
    {
        $this->assertTraveler();
        $user = Auth::user();

        $validated = $request->validate([
            'booking_id' => 'required|integer|min:1',
            'email' => 'required|email|max:150',
        ]);

        $booking = HotelBooking::find((int) $validated['booking_id']);

        try {
            $rateLimiter->assertCanAttempt($user->id, HotelBooking::class, $booking->id ?? 0, $request->ip());
        } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
            return back()->withInput()->withErrors(['error' => __('messages.hotel_err_rate_limit')]);
        }

        if (!$booking) {
            $this->logEvent($user->id, 'initiated', null, ['reason' => 'not_found']);
            return back()->withInput()->withErrors(['error' => __('messages.hotel_err_generic')]);
        }

        $existing = BookingClaim::where('claimable_type', HotelBooking::class)
            ->where('claimable_id', $booking->id)->first();
        if ($existing) {
            if ($existing->traveler_id === $user->id) {
                return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.hotel_info_already_claimed'));
            }
            $this->logEvent($user->id, 'duplicate_attempt', $booking->id);
            return back()->withInput()->withErrors(['error' => __('messages.hotel_err_generic')]);
        }

        $bookingEmail = strtolower(trim($booking->guest_email ?? ''));
        $inputEmail = strtolower(trim($validated['email']));
        if (empty($bookingEmail) || $bookingEmail !== $inputEmail) {
            $this->logEvent($user->id, 'initiated', $booking->id, ['reason' => 'email_mismatch']);
            return back()->withInput()->withErrors(['error' => __('messages.hotel_err_generic')]);
        }

        $otpSvc = app(OtpService::class);
        $otp = $otpSvc->generate();

        $attempt = BookingClaimAttempt::create([
            'claimable_type' => HotelBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => $user->id,
            'contact_method' => 'email',
            'contact_value' => $inputEmail,
            'otp_hash' => $otpSvc->hash($otp),
            'otp_expires_at' => now()->addMinutes($otpSvc->expiryMinutes()),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 250),
        ]);


        try {
            \Mail::raw(
                "Your TravelAI Nepal hotel booking verification code is: {$otp}\n\nExpires in 5 minutes.\nBooking ID: #{$booking->id}",
                fn($m) => $m->to($inputEmail)->subject('TravelAI Nepal — Hotel Booking Verification')
            );
        } catch (\Exception $e) {
            $attempt->update(['status' => 'cancelled']);
            return back()->withInput()->withErrors(['error' => __('messages.hotel_err_email_failed')]);
        }

        $this->logEvent($user->id, 'otp_sent', $booking->id);
        return redirect()->route('traveler.hotel.claim.verify.form', ['token' => $attempt->id])
            ->with('info', __('messages.hotel_info_otp_sent'));
    }

    public function showVerify($token)
    {
        $this->assertTraveler();
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.hotel.claim.form')->withErrors(['error' => __('messages.hotel_err_generic')]);
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return redirect()->route('traveler.hotel.claim.form')->withErrors(['error' => __('messages.hotel_err_generic')]);
        }
        return view('traveler.hotel.claim-verify', ['attempt' => $attempt]);
    }

    public function verify(Request $request, $token)
    {
        $this->assertTraveler();
        $user = Auth::user();
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== $user->id || $attempt->status !== 'pending') {
            return redirect()->route('traveler.hotel.claim.form');
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return back()->withErrors(['error' => __('messages.hotel_err_generic')]);
        }

        $validated = $request->validate(['otp' => 'required|string|size:6']);
        $otpSvc = app(OtpService::class);

        if (!$otpSvc->verify($validated['otp'], (string) $attempt->otp_hash)) {
            $attempt->increment('otp_attempts');
            $this->logEvent($user->id, 'otp_wrong', $attempt->claimable_id);
            if ($attempt->otp_attempts >= $otpSvc->maxAttempts()) {
                $attempt->update(['status' => 'locked', 'otp_hash' => null]);
                return redirect()->route('traveler.hotel.claim.form')->withErrors(['error' => __('messages.hotel_err_otp_max')]);
            }
            return back()->withErrors(['error' => __('messages.hotel_err_otp_wrong')]);
        }

        $booking = HotelBooking::find($attempt->claimable_id);
        if (!$booking) return redirect()->route('traveler.hotel.claim.form');

        try {
            DB::transaction(function () use ($booking, $user, $attempt) {
                if (BookingClaim::where('claimable_type', HotelBooking::class)->where('claimable_id', $booking->id)->lockForUpdate()->exists()) {
                    throw new \RuntimeException('Already claimed');
                }
                BookingClaim::create([
                    'claimable_type' => HotelBooking::class,
                    'claimable_id' => $booking->id,
                    'traveler_id' => $user->id,
                    'claim_email' => $booking->guest_email,
                    'claimed_at' => now(),
                ]);
                BookingClaimAttempt::where('claimable_type', HotelBooking::class)
                    ->where('claimable_id', $booking->id)->where('status', 'pending')
                    ->update(['status' => 'cancelled', 'otp_hash' => null]);
                $attempt->update(['status' => 'verified', 'otp_hash' => null]);
                BookingClaimEvent::create([
                    'traveler_id' => $user->id,
                    'event_type' => 'claimed',
                    'claimable_type' => HotelBooking::class,
                    'claimable_id' => $booking->id,
                    'metadata' => ['method' => 'email'],
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.hotel_info_already_claimed'));
            }
            throw $e;
        } catch (\RuntimeException $e) {
            return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.hotel_info_already_claimed'));
        }

        return redirect()->route('traveler.dashboard-v2')->with('success', __('messages.hotel_success_verified'));
    }

    private function logEvent(int $userId, string $eventType, ?int $bookingId, array $meta = []): void
    {
        try {
            BookingClaimEvent::create([
                'traveler_id' => $userId,
                'event_type' => $eventType,
                'claimable_type' => $bookingId ? HotelBooking::class : null,
                'claimable_id' => $bookingId,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent() ?? '', 250),
                'metadata' => $meta,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) { \Log::warning('Hotel event log failed', ['error' => $e->getMessage()]); }
    }
}
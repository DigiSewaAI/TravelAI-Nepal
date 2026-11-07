<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\IndividualBooking;
use App\Services\Claim\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndividualClaimController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showForm()
    {
        return view('traveler.individual.claim-form');
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|string',
            'email' => 'required|email',
        ]);

        // 🔒 SECURITY: email must match logged-in user
        if (strtolower(Auth::user()->email) !== strtolower($validated['email'])) {
            return back()->withErrors(['email' => 'This email does not match your logged-in account.'])->withInput();
        }

        // Accept either booking_number (IND-YYYY-XXXX) or numeric id
        $bookingIdInput = trim($validated['booking_id']);
        $booking = IndividualBooking::where(function ($q) use ($bookingIdInput) {
            $q->where('booking_number', $bookingIdInput);
            if (is_numeric($bookingIdInput)) {
                $q->orWhere('id', (int) $bookingIdInput);
            }
        })->where('customer_email', $validated['email'])->first();

        if (!$booking) {
            return back()->withErrors(['error' => __('messages.claim_err_not_found')])->withInput();
        }

        $existing = BookingClaim::where('claimable_type', IndividualBooking::class)
            ->where('claimable_id', $booking->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => __('messages.claim_err_already_claimed')])->withInput();
        }

        $otp = $this->otpService->generate();
        $hash = $this->otpService->hash($otp);

        if (app()->environment('local')) {
            session(['debug_otp' => $otp]);
        }
        $attempt = BookingClaimAttempt::create([
            'claimable_type' => IndividualBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => Auth::id(),
            'contact_method' => 'email',
            'contact_value' => $validated['email'],
            'otp_hash' => $this->otpService->hash($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'status' => 'pending',
        ]);

        // Send OTP via email
        try {
            \Mail::raw(
                "Namaste,\n\nYour TravelAI Nepal verification code is:\n\n    {$otp}\n\nThis code expires in 5 minutes.\n\nIf you did not request this, ignore this email.\n\n— TravelAI Nepal",
                function ($m) use ($validated) {
                    $m->to($validated['email'])->subject('TravelAI Nepal — Booking Claim Code');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Individual claim OTP mail failed');
            $attempt->update(['status' => 'cancelled']);
            return back()->withErrors(['error' => 'Unable to send OTP email. Please try again.'])->withInput();
        }

        return redirect()->route('traveler.individual.claim.verify.form', $attempt->id)
            ->with('info', 'OTP sent to your email.');
    }

    public function showVerify($token)
    {
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.individual.claim.form');
        }
        return view('traveler.individual.claim-verify', ['attempt' => $attempt]);
    }

    public function verify(Request $request, $token)
    {
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.individual.claim.form');
        }

        $validated = $request->validate(['otp' => 'required|string|size:6']);

        if (!$this->otpService->verify($validated['otp'], (string) $attempt->otp_hash)) {
            $attempt->increment('otp_attempts');
            return back()->withErrors(['error' => __('messages.claim_err_otp_wrong')]);
        }

        DB::transaction(function () use ($attempt) {
            $booking = IndividualBooking::find($attempt->claimable_id);
            BookingClaim::create([
                'claimable_type' => IndividualBooking::class,
                'claimable_id' => $booking->id,
                'traveler_id' => Auth::id(),
                'claim_email' => $attempt->contact_value,
                'claimed_at' => now(),
            ]);
            $attempt->update(['status' => 'verified']);
        });

        return redirect()->route('traveler.dashboard-v2')->with('success', 'Booking claimed!');
    }

    public function show($booking)
    {
        $claim = BookingClaim::where('claimable_type', IndividualBooking::class)
            ->where('claimable_id', $booking)
            ->where('traveler_id', Auth::id())
            ->first();
        if (!$claim) abort(404);

        $booking = IndividualBooking::with(['provider'])->findOrFail($booking);
        return view('traveler.individual.claim-show', ['booking' => $booking, 'claim' => $claim]);
    }
}
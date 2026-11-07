<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\ExchangeTransaction;
use App\Services\Claim\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoneyChangerClaimController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showForm()
    {
        return view('traveler.moneychanger.claim-form');
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'transaction_number' => 'required|string',
            'email' => 'required|email',
        ]);

        // 🔒 SECURITY: email must match logged-in user
        if (strtolower(Auth::user()->email) !== strtolower($validated['email'])) {
            return back()->withErrors(['email' => 'This email does not match your logged-in account.'])->withInput();
        }

        $tx = ExchangeTransaction::where('transaction_number', $validated['transaction_number'])
            ->where('customer_email', $validated['email'])
            ->first();

        if (!$tx) {
            return back()->withErrors(['error' => __('messages.claim_err_not_found')])->withInput();
        }

        $existing = BookingClaim::where('claimable_type', ExchangeTransaction::class)
            ->where('claimable_id', $tx->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => __('messages.claim_err_already_claimed')])->withInput();
        }

        $otp = $this->otpService->generate();
        $hash = $this->otpService->hash($otp);

        // (debug for local dev)
        if (app()->environment('local')) {
            session(['debug_otp' => $otp]);
        }
        $attempt = BookingClaimAttempt::create([
            'claimable_type' => ExchangeTransaction::class,
            'claimable_id' => $tx->id,
            'traveler_id' => Auth::id(),
            'contact_method' => 'email',
            'contact_value' => $validated['email'],
            'otp_hash' => $this->otpService->hash($otp),
            'otp_expires_at' => now()->addMinutes(5),
            'status' => 'pending',
        ]);

        // Send OTP via email (synchronous, like Restaurant)
        try {
            \Mail::raw(
                "Namaste,\n\nYour TravelAI Nepal verification code is:\n\n    {$otp}\n\nThis code expires in 5 minutes.\n\nIf you did not request this, ignore this email.\n\n— TravelAI Nepal",
                function ($m) use ($validated) {
                    $m->to($validated['email'])->subject('TravelAI Nepal — Transaction Claim Code');
                }
            );
        } catch (\Exception $e) {
            \Log::error('MoneyChanger claim OTP mail failed', ['attempt_id' => $attempt->id]);
            $attempt->update(['status' => 'cancelled']);
            return back()->withErrors(['error' => 'Unable to send OTP email. Please try again.'])->withInput();
        }

        return redirect()->route('traveler.moneychanger.claim.verify.form', $attempt->id)
            ->with('info', 'OTP sent to your email.');
    }

    public function showVerify($token)
    {
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.moneychanger.claim.form');
        }
        return view('traveler.moneychanger.claim-verify', ['attempt' => $attempt]);
    }

    public function verify(Request $request, $token)
    {
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.moneychanger.claim.form');
        }

        $validated = $request->validate(['otp' => 'required|string|size:6']);

        if (!$this->otpService->verify($validated['otp'], (string) $attempt->otp_hash)) {
            $attempt->increment('otp_attempts');
            return back()->withErrors(['error' => __('messages.claim_err_otp_wrong')]);
        }

        DB::transaction(function () use ($attempt) {
            $tx = ExchangeTransaction::find($attempt->claimable_id);
            BookingClaim::create([
                'claimable_type' => ExchangeTransaction::class,
                'claimable_id' => $tx->id,
                'traveler_id' => Auth::id(),
                'claim_email' => $attempt->contact_value,
                'claimed_at' => now(),
            ]);
            $attempt->update(['status' => 'verified']);
        });

        return redirect()->route('traveler.dashboard-v2')->with('success', 'Transaction claimed!');
    }

    public function show($tx)
    {
        $claim = BookingClaim::where('claimable_type', ExchangeTransaction::class)
            ->where('claimable_id', $tx)
            ->where('traveler_id', Auth::id())
            ->first();
        if (!$claim) abort(404);

        $tx = ExchangeTransaction::with(['provider'])->findOrFail($tx);
        return view('traveler.moneychanger.claim-show', ['tx' => $tx, 'claim' => $claim]);
    }
}
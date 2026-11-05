<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\CustomOrder;
use App\Services\Claim\OtpService;
use App\Services\Claim\RateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtCraftClaimController extends Controller
{
    private function assertTraveler(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'traveler') abort(403);
    }

    public function showForm()
    {
        $this->assertTraveler();
        return view('traveler.artcraft.claim-form');
    }

    public function initiate(Request $request, RateLimitService $rateLimiter)
    {
        $this->assertTraveler();
        $user = Auth::user();

        $validated = $request->validate([
            'order_id' => 'required|integer|min:1',
            'email' => 'required|email|max:150',
        ]);

        $order = CustomOrder::find((int) $validated['order_id']);

        try {
            $rateLimiter->assertCanAttempt($user->id, CustomOrder::class, $order->id ?? 0, $request->ip());
        } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
            return back()->withInput()->withErrors(['error' => __('messages.artcraft_err_rate_limit')]);
        }

        if (!$order) {
            $this->logEvent($user->id, 'initiated', null, ['reason' => 'not_found']);
            return back()->withInput()->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }

        $existing = BookingClaim::where('claimable_type', CustomOrder::class)
            ->where('claimable_id', $order->id)->first();
        if ($existing) {
            if ($existing->traveler_id === $user->id) {
                return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.artcraft_info_already_claimed'));
            }
            $this->logEvent($user->id, 'duplicate_attempt', $order->id);
            return back()->withInput()->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }

        $orderEmail = strtolower(trim($order->client_email ?? ''));
        $inputEmail = strtolower(trim($validated['email']));
        if (empty($orderEmail) || $orderEmail !== $inputEmail) {
            $this->logEvent($user->id, 'initiated', $order->id, ['reason' => 'email_mismatch']);
            return back()->withInput()->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }

        $otpSvc = app(OtpService::class);
        $otp = $otpSvc->generate();

        $attempt = BookingClaimAttempt::create([
            'claimable_type' => CustomOrder::class,
            'claimable_id' => $order->id,
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
                "Your TravelAI Nepal ArtCraft order verification code is: {$otp}\n\nExpires in 5 minutes.\nOrder ID: #{$order->id}",
                fn($m) => $m->to($inputEmail)->subject('TravelAI Nepal — ArtCraft Order Verification')
            );
        } catch (\Exception $e) {
            $attempt->update(['status' => 'cancelled']);
            return back()->withInput()->withErrors(['error' => __('messages.artcraft_err_email_failed')]);
        }

        $this->logEvent($user->id, 'otp_sent', $order->id);
        return redirect()->route('traveler.artcraft.claim.verify.form', ['token' => $attempt->id])
            ->with('info', __('messages.artcraft_info_otp_sent'));
    }

    public function showVerify($token)
    {
        $this->assertTraveler();
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.artcraft.claim.form')->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return redirect()->route('traveler.artcraft.claim.form')->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }
        return view('traveler.artcraft.claim-verify', ['attempt' => $attempt]);
    }

    public function verify(Request $request, $token)
    {
        $this->assertTraveler();
        $user = Auth::user();
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== $user->id || $attempt->status !== 'pending') {
            return redirect()->route('traveler.artcraft.claim.form');
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return back()->withErrors(['error' => __('messages.artcraft_err_generic')]);
        }

        $validated = $request->validate(['otp' => 'required|string|size:6']);
        $otpSvc = app(OtpService::class);

        if (!$otpSvc->verify($validated['otp'], (string) $attempt->otp_hash)) {
            $attempt->increment('otp_attempts');
            $this->logEvent($user->id, 'otp_wrong', $attempt->claimable_id);
            if ($attempt->otp_attempts >= $otpSvc->maxAttempts()) {
                $attempt->update(['status' => 'locked', 'otp_hash' => null]);
                return redirect()->route('traveler.artcraft.claim.form')->withErrors(['error' => __('messages.artcraft_err_otp_max')]);
            }
            return back()->withErrors(['error' => __('messages.artcraft_err_otp_wrong')]);
        }

        $order = CustomOrder::find($attempt->claimable_id);
        if (!$order) return redirect()->route('traveler.artcraft.claim.form');

        try {
            DB::transaction(function () use ($order, $user, $attempt) {
                if (BookingClaim::where('claimable_type', CustomOrder::class)->where('claimable_id', $order->id)->lockForUpdate()->exists()) {
                    throw new \RuntimeException('Already claimed');
                }
                BookingClaim::create([
                    'claimable_type' => CustomOrder::class,
                    'claimable_id' => $order->id,
                    'traveler_id' => $user->id,
                    'claim_email' => $order->client_email,
                    'claimed_at' => now(),
                ]);
                BookingClaimAttempt::where('claimable_type', CustomOrder::class)
                    ->where('claimable_id', $order->id)->where('status', 'pending')
                    ->update(['status' => 'cancelled', 'otp_hash' => null]);
                $attempt->update(['status' => 'verified', 'otp_hash' => null]);
                BookingClaimEvent::create([
                    'traveler_id' => $user->id,
                    'event_type' => 'claimed',
                    'claimable_type' => CustomOrder::class,
                    'claimable_id' => $order->id,
                    'metadata' => ['method' => 'email'],
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.artcraft_info_already_claimed'));
            }
            throw $e;
        } catch (\RuntimeException $e) {
            return redirect()->route('traveler.dashboard-v2')->with('info', __('messages.artcraft_info_already_claimed'));
        }

        return redirect()->route('traveler.dashboard-v2')->with('success', __('messages.artcraft_success_verified'));
    }

    private function logEvent(int $userId, string $eventType, ?int $orderId, array $meta = []): void
    {
        try {
            BookingClaimEvent::create([
                'traveler_id' => $userId,
                'event_type' => $eventType,
                'claimable_type' => $orderId ? CustomOrder::class : null,
                'claimable_id' => $orderId,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent() ?? '', 250),
                'metadata' => $meta,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) { \Log::warning('ArtCraft event log failed', ['error' => $e->getMessage()]); }
    }
}
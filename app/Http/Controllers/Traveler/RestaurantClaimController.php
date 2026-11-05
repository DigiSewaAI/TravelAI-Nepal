<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\RestaurantOrder;
use App\Services\Claim\OtpService;
use App\Services\Claim\RateLimitService;
use App\Services\Claim\ReceiptCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantClaimController extends Controller
{
    public function showForm()
    {
        $this->assertTraveler();
        return view('traveler.restaurant.claim-form');
    }

    private function assertTraveler(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'traveler') {
            abort(403);
        }
    }

    public function initiate(Request $request, RateLimitService $rateLimiter)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'traveler') {
            abort(403);
        }

        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'method' => 'required|in:email,receipt',
            'email' => 'nullable|required_if:method,email|email|max:190',
            'receipt_code' => 'nullable|required_if:method,receipt|string|max:20',
        ]);

        $order = RestaurantOrder::where('order_number', $validated['order_number'])->first();

        // Rate limit (per-IP)
        try {
            $rateLimiter->assertCanAttempt($user->id, RestaurantOrder::class, $order->id ?? 0, $request->ip());
        } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_rate_limit')]);
        }

        // Generic error for all not-found cases (enumeration prevention)
        if (!$order) {
            $this->logEvent($user->id, 'initiated', null, ['reason' => 'order_not_found']);
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_generic')]);
        }

        // Already claimed?
        $existingClaim = BookingClaim::where('claimable_type', RestaurantOrder::class)
            ->where('claimable_id', $order->id)
            ->first();

        if ($existingClaim) {
            if ($existingClaim->traveler_id === $user->id) {
                return redirect()->route('traveler.dashboard-v2')
                    ->with('info', __('messages.claim_info_already_claimed'));
            }
            $this->logEvent($user->id, 'duplicate_attempt', $order->id);
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_generic')]);
        }

        // Check receipt code used
        if ($order->receipt_code_used_at) {
            $this->logEvent($user->id, 'duplicate_attempt', $order->id);
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_generic')]);
        }

        // Method: receipt
        if ($validated['method'] === 'receipt') {
            try {
                $svc = app(ReceiptCodeService::class);
            } catch (\RuntimeException $e) {
                return back()->withInput()->withErrors(['error' => __('messages.claim_err_config')]);
            }
            if (!$order->receipt_code_hash || !$svc->verify($validated['receipt_code'], $order->receipt_code_hash)) {
                $this->logEvent($user->id, 'receipt_wrong', $order->id);
                return back()->withInput()->withErrors(['error' => __('messages.claim_err_generic')]);
            }

            // Success path for receipt
            return $this->finalizeClaim($order, $user, 'receipt');
        }

        // Method: email
        $bookingEmail = strtolower(trim($order->customer_email ?? ''));
        $inputEmail = strtolower(trim($validated['email'] ?? ''));

        if (empty($bookingEmail) || $bookingEmail !== $inputEmail) {
            $this->logEvent($user->id, 'initiated', $order->id, ['reason' => 'email_mismatch']);
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_generic')]);
        }

        // Generate OTP
        $otpService = app(OtpService::class);
        $otp = $otpService->generate();
        $hash = $otpService->hash($otp);

        $attempt = BookingClaimAttempt::create([
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $user->id,
            'contact_method' => 'email',
            'contact_value' => $inputEmail,
            'otp_hash' => $hash,
            'otp_expires_at' => now()->addMinutes($otpService->expiryMinutes()),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 250),
        ]);

        // Send OTP via mail (synchronous)

        try {
            \Mail::raw(
                "Your TravelAI Nepal verification code is: {$otp}\n\nThis code expires in 5 minutes.\n\nIf you did not request this, ignore this email.",
                function ($m) use ($inputEmail) {
                    $m->to($inputEmail)->subject('TravelAI Nepal — Order Claim Code');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Restaurant claim OTP mail failed', ['attempt_id' => $attempt->id]);
            $attempt->update(['status' => 'cancelled']);
            return back()->withInput()->withErrors(['error' => __('messages.claim_err_email_failed')]);
        }

        $this->logEvent($user->id, 'otp_sent', $order->id);

        return redirect()->route('traveler.restaurant.claim.verify.form', ['token' => $attempt->id])
            ->with('info', __('messages.claim_info_otp_sent'));
    }

    public function showVerify(Request $request, $token)
    {
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== Auth::id() || $attempt->status !== 'pending') {
            return redirect()->route('traveler.restaurant.claim.form')
                ->withErrors(['error' => __('messages.claim_err_invalid_session')]);
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return redirect()->route('traveler.restaurant.claim.form')
                ->withErrors(['error' => __('messages.claim_err_otp_expired')]);
        }
        return view('traveler.restaurant.claim-verify', ['attempt' => $attempt]);
    }

    public function verify(Request $request, $token)
    {
        $user = Auth::user();
        $attempt = BookingClaimAttempt::find($token);
        if (!$attempt || $attempt->traveler_id !== $user->id) {
            return redirect()->route('traveler.restaurant.claim.form');
        }
        if ($attempt->status !== 'pending') {
            return redirect()->route('traveler.restaurant.claim.form')
                ->withErrors(['error' => __('messages.claim_err_session_invalid')]);
        }
        if ($attempt->otp_expires_at && $attempt->otp_expires_at->isPast()) {
            $attempt->update(['status' => 'expired']);
            return back()->withErrors(['error' => __('messages.claim_err_otp_expired')]);
        }

        $validated = $request->validate(['otp' => 'required|string|size:6']);
        $otpService = app(OtpService::class);

        if (!$otpService->verify($validated['otp'], (string) $attempt->otp_hash)) {
            $attempt->increment('otp_attempts');
            $this->logEvent($user->id, 'otp_wrong', $attempt->claimable_id);

            if ($attempt->otp_attempts >= $otpService->maxAttempts()) {
                $attempt->update(['status' => 'locked', 'otp_hash' => null]);
                return redirect()->route('traveler.restaurant.claim.form')
                    ->withErrors(['error' => __('messages.claim_err_otp_max_attempts')]);
            }
            return back()->withErrors(['error' => __('messages.claim_err_otp_wrong')]);
        }

        $order = RestaurantOrder::find($attempt->claimable_id);
        if (!$order) {
            return redirect()->route('traveler.restaurant.claim.form');
        }

        return $this->finalizeClaim($order, $user, 'email', $attempt);
    }

    private function finalizeClaim(RestaurantOrder $order, $user, string $method, ?BookingClaimAttempt $attempt = null)
    {
        try {
            DB::transaction(function () use ($order, $user, $method, $attempt) {
                // Double-check claim does not exist
                $exists = BookingClaim::where('claimable_type', RestaurantOrder::class)
                    ->where('claimable_id', $order->id)
                    ->lockForUpdate()
                    ->first();
                if ($exists) {
                    throw new \RuntimeException('Already claimed');
                }

                BookingClaim::create([
                    'claimable_type' => RestaurantOrder::class,
                    'claimable_id' => $order->id,
                    'traveler_id' => $user->id,
                    'claim_email' => $order->customer_email,
                    'claim_phone' => $order->customer_phone,
                    'claimed_at' => now(),
                ]);

                // Invalidate receipt code
                $order->update([
                    'receipt_code_used_at' => now(),
                    'receipt_code_hash' => null,
                ]);

                // Cancel pending attempts
                BookingClaimAttempt::where('claimable_type', RestaurantOrder::class)
                    ->where('claimable_id', $order->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled', 'otp_hash' => null]);

                if ($attempt) {
                    $attempt->update(['status' => 'verified', 'otp_hash' => null]);
                }

                BookingClaimEvent::create([
                    'traveler_id' => $user->id,
                    'event_type' => 'claimed',
                    'claimable_type' => RestaurantOrder::class,
                    'claimable_id' => $order->id,
                    'metadata' => ['method' => $method],
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('traveler.dashboard-v2')
                    ->with('info', __('messages.claim_info_already_claimed'));
            }
            throw $e;
        } catch (\RuntimeException $e) {
            return redirect()->route('traveler.dashboard-v2')
                ->with('info', __('messages.claim_info_already_claimed'));
        }

        return redirect()->route('traveler.dashboard-v2')
            ->with('success', __('messages.claim_success_verified'));
    }

    private function logEvent(int $userId, string $eventType, ?int $orderId, array $meta = []): void
    {
        try {
            BookingClaimEvent::create([
                'traveler_id' => $userId,
                'event_type' => $eventType,
                'claimable_type' => $orderId ? RestaurantOrder::class : null,
                'claimable_id' => $orderId,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent() ?? '', 250),
                'metadata' => $meta,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Claim event log failed', ['error' => $e->getMessage()]);
        }
    }
}
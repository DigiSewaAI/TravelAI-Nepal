<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

/**
 * PHASE 7A — Stripe decommissioned.
 *
 * This service is retained as a stub so existing callers (e.g. Provider\PaymentController)
 * do not fatally break. All methods return safe "not configured" defaults.
 *
 * Phase 7B/7C will replace this with the new manual-verification subscription flow.
 */
class PaymentService
{
    /**
     * PHASE 7A STUB — old Stripe payment intent creation.
     * Replaced by manual bank/eSewa/Khalti + admin verification in Phase 7C.
     */
    public function createSubscriptionPayment(Subscription $subscription, ?array $paymentMethod = null): array
    {
        Log::info('PaymentService::createSubscriptionPayment — stub (Stripe removed)', [
            'subscription_id' => $subscription->id,
        ]);

        return [
            'success' => false,
            'message' => 'Payment gateway not configured. Please use manual payment methods.',
        ];
    }

    /**
     * PHASE 7A STUB — payment record creation.
     */
    protected function createPaymentRecord(Subscription $subscription, string $gateway, float $amount, array $metadata = []): ?Payment
    {
        return null;
    }

    /**
     * PHASE 7A STUB — old Stripe payment confirmation.
     */
    public function confirmPayment(string $paymentIntentId): bool
    {
        return false;
    }

    /**
     * PHASE 7A STUB — old Stripe webhook handler.
     */
    public function handleWebhook(array $payload): bool
    {
        return false;
    }

    /**
     * PHASE 7A STUB — status lookup.
     */
    public function getStatus(Payment $payment): string
    {
        return $payment->status ?? 'pending';
    }

    /**
     * PHASE 7A STUB — old Stripe intent retrieval.
     */
    public function getIntent(string $paymentIntentId)
    {
        return null;
    }

    /**
     * PHASE 7A STUB — refund (no-op).
     */
    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        Log::info('PaymentService::refundPayment — stub (Stripe removed)', [
            'payment_id' => $payment->id,
        ]);

        return false;
    }

    /**
     * PHASE 7A STUB — can activate subscription?
     * Phase 7C will replace with manual verification logic.
     */
    public function canActivateSubscription(Subscription $subscription): bool
    {
        return $subscription->status === 'active';
    }
}
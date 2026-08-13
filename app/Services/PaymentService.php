<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PaymentService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret_key'));
    }

    /**
     * Create a payment intent for subscription
     */
    public function createSubscriptionPayment(Subscription $subscription, array $paymentMethod = null)
    {
        $plan = $subscription->plan;
        $provider = $subscription->provider;
        $interval = $subscription->billing_interval ?? 'monthly'; // ✅ Get interval

        // Determine price based on interval
        $amount = ($interval === 'yearly') ? $plan->price_yearly : $plan->price_monthly;

        if (is_null($amount) || $amount == 0) {
            // Free plan - no payment needed
            return $this->createPaymentRecord($subscription, 'free', 0);
        }

        try {
            // Create Stripe payment intent
            $stripePayment = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // in cents/paisa
                'currency' => 'npr',
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'provider_id' => $provider->id,
                    'plan_name' => $plan->name,
                    'billing_interval' => $interval, // ✅ Store interval in metadata
                ],
                'description' => "Subscription: {$plan->name} ({$interval}) for {$provider->name}",
                'payment_method_types' => ['card'],
            ]);

            // Create payment record
            $payment = $this->createPaymentRecord(
                $subscription,
                'stripe',
                $amount,
                [
                    'payment_intent_id' => $stripePayment->id,
                    'client_secret' => $stripePayment->client_secret,
                    'billing_interval' => $interval,
                    'plan_name' => $plan->name,
                    'provider_name' => $provider->name,
                ]
            );

            return [
                'success' => true,
                'payment' => $payment,
                'client_secret' => $stripePayment->client_secret,
                'payment_intent_id' => $stripePayment->id,
            ];

        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a payment record in database
     */
    protected function createPaymentRecord(Subscription $subscription, string $gateway, float $amount, array $metadata = [])
    {
        $paymentId = $metadata['payment_intent_id'] ?? 'free_' . uniqid() . '_' . time();

        return Payment::create([
            'payable_type' => get_class($subscription),
            'payable_id' => $subscription->id,
            'provider_id' => $subscription->provider_id,
            'user_id' => $subscription->provider->user_id ?? null,
            'payment_id' => $paymentId,
            'gateway' => $gateway,
            'amount' => $amount,
            'currency' => 'NPR',
            'status' => $amount > 0 ? 'pending' : 'success',
            'metadata' => $metadata,
            'paid_at' => $amount > 0 ? null : now(),
        ]);
    }

    /**
     * Confirm a payment (webhook or callback)
     */
    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            $stripePayment = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            $payment = Payment::where('payment_id', $paymentIntentId)->first();
            if (!$payment) {
                Log::error("Payment not found: {$paymentIntentId}");
                return false;
            }

            if ($stripePayment->status === 'succeeded') {
                $payment->markAsSuccess();

                // Activate subscription if this is for a subscription
                if ($payment->payable_type === Subscription::class) {
                    $subscription = $payment->payable;
                    if ($subscription) {
                        $subscription->status = 'active';
                        $subscription->start_date = now();

                        // ✅ Set end_date based on billing interval
                        $interval = $subscription->billing_interval ?? 'monthly';
                        $subscription->end_date = ($interval === 'yearly')
                            ? now()->addYear()
                            : now()->addMonth();

                        $subscription->save();

                        Log::info("Subscription activated: {$subscription->id} for provider {$subscription->provider_id}, interval: {$interval}");
                    }
                }

                return true;
            }

            if ($stripePayment->status === 'failed') {
                $payment->markAsFailed();
                return false;
            }

            if ($stripePayment->status === 'canceled') {
                $payment->markAsFailed();
                return false;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(array $payload): bool
    {
        $eventType = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? null;

        if (!$eventType || !$data) {
            Log::error('Invalid webhook payload');
            return false;
        }

        Log::info("Webhook received: {$eventType}", ['data' => $data]);

        switch ($eventType) {
            case 'payment_intent.succeeded':
                return $this->confirmPayment($data['id']);

            case 'payment_intent.payment_failed':
                $payment = Payment::where('payment_id', $data['id'])->first();
                if ($payment) {
                    $payment->markAsFailed();
                    Log::info("Payment marked as failed: {$data['id']}");
                }
                return true;

            case 'payment_intent.canceled':
                $payment = Payment::where('payment_id', $data['id'])->first();
                if ($payment) {
                    $payment->markAsFailed();
                    Log::info("Payment marked as canceled: {$data['id']}");
                }
                return true;

            default:
                Log::info("Unhandled webhook event: {$eventType}");
                return true;
        }
    }

    /**
     * Get payment status from gateway
     */
    public function getStatus(Payment $payment): string
    {
        if ($payment->gateway === 'stripe' && $payment->status === 'pending') {
            try {
                $stripePayment = $this->stripe->paymentIntents->retrieve($payment->payment_id);
                
                if ($stripePayment->status === 'succeeded') {
                    $payment->markAsSuccess();
                    return 'success';
                }
                if ($stripePayment->status === 'canceled' || $stripePayment->status === 'failed') {
                    $payment->markAsFailed();
                    return 'failed';
                }
                return 'pending';
            } catch (\Exception $e) {
                Log::error('Failed to retrieve payment status: ' . $e->getMessage());
                return $payment->status;
            }
        }
        return $payment->status;
    }

    /**
     * Get payment intent details from Stripe
     */
    public function getIntent(string $paymentIntentId)
    {
        try {
            return $this->stripe->paymentIntents->retrieve($paymentIntentId);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment intent: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, float $amount = null): bool
    {
        if ($payment->status !== 'success') {
            Log::error('Cannot refund non-successful payment: ' . $payment->id);
            return false;
        }

        if ($payment->gateway === 'stripe') {
            try {
                $refundAmount = $amount ?? $payment->amount;
                
                $this->stripe->refunds->create([
                    'payment_intent' => $payment->payment_id,
                    'amount' => $refundAmount * 100,
                ]);

                $payment->markAsRefunded();
                Log::info("Payment refunded: {$payment->id} for amount {$refundAmount}");
                return true;

            } catch (\Exception $e) {
                Log::error('Refund failed: ' . $e->getMessage());
                return false;
            }
        }

        Log::error('Refund not supported for gateway: ' . $payment->gateway);
        return false;
    }

    /**
     * Check if subscription can be activated (payment successful)
     */
    public function canActivateSubscription(Subscription $subscription): bool
    {
        $latestPayment = $subscription->latestPayment;
        
        if (!$latestPayment) {
            return false;
        }

        return $latestPayment->isSuccessful();
    }
}
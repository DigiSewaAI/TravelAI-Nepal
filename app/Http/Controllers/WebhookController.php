<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected StripeWebhookService $stripeWebhookService
    ) {}

    public function stripe(Request $request)
    {
        // 1. Verify Stripe signature (raw body required)
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $eventId = $event->id;

        // 2. Claim / lease
        $claim = $this->stripeWebhookService->claim($eventId);

        if ($claim['status'] === 'processed') {
            return response()->json(['received' => true, 'duplicate' => true], 200);
        }

        if ($claim['status'] === 'in_progress') {
            return response()->json(['received' => true, 'in_progress' => true], 200);
        }

        if ($claim['status'] !== 'claimed') {
            return response()->json(['error' => 'Claim failed'], 500);
        }

        $token = $claim['token'];

        // 3. Process business logic inside a DB transaction
        try {
            DB::transaction(function () use ($eventId, $token, $event) {
                if (!$this->stripeWebhookService->stillOwned($eventId, $token)) {
                    throw new \RuntimeException('Claim ownership lost');
                }

                $ok = $this->paymentService->handleWebhook($event->toArray());

                if (!$ok) {
                    throw new \RuntimeException('Business handler returned failure');
                }

                $this->stripeWebhookService->finalize($eventId, $token);
            });

            return response()->json(['received' => true], 200);
        } catch (\Throwable $e) {
            $this->stripeWebhookService->fail($eventId, $token);

            Log::error('Stripe webhook processing failed', [
                'event_id'    => $eventId,
                'error_class' => get_class($e),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
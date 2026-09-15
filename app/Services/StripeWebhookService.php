<?php

namespace App\Services;

use App\Models\StripeWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeWebhookService
{
    public const LEASE_SECONDS = 60;

    /**
     * Atomically claim an event. Returns:
     *   ['status' => 'claimed', 'token' => <uuid>]  → proceed with processing
     *   ['status' => 'processed']                    → no-op (return 200)
     *   ['status' => 'in_progress']                  → no-op (return 200)
     *   ['status' => 'unknown']                      → race; caller retries or 500
     */
    public function claim(string $eventId): array
    {
        $token = (string) Str::uuid();
        $now = now();
        $lease = $now->copy()->addSeconds(self::LEASE_SECONDS);

        // 1. Try to insert a fresh claim
        try {
            DB::table('stripe_webhook_events')->insert([
                'event_id'         => $eventId,
                'state'            => StripeWebhookEvent::STATE_CLAIMED,
                'claim_token'      => $token,
                'claimed_at'       => $now,
                'lease_expires_at' => $lease,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            return ['status' => 'claimed', 'token' => $token];
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Event already exists — inspect state
        }

        $existing = DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->first();

        if (!$existing) {
            return ['status' => 'unknown'];
        }

        if ($existing->state === StripeWebhookEvent::STATE_PROCESSED) {
            return ['status' => 'processed'];
        }

        if ($existing->state === StripeWebhookEvent::STATE_CLAIMED
            && $existing->lease_expires_at !== null
            && strtotime($existing->lease_expires_at) > $now->getTimestamp()) {
            return ['status' => 'in_progress'];
        }

        // Stale claim OR failed → attempt atomic takeover
        $affected = DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->where('state', StripeWebhookEvent::STATE_CLAIMED)
                       ->where('lease_expires_at', '<=', $now);
                })->orWhere('state', StripeWebhookEvent::STATE_FAILED);
            })
            ->update([
                'state'            => StripeWebhookEvent::STATE_CLAIMED,
                'claim_token'      => $token,
                'claimed_at'       => $now,
                'lease_expires_at' => $lease,
                'updated_at'       => $now,
            ]);

        if ($affected === 1) {
            return ['status' => 'claimed', 'token' => $token];
        }

        // Lost takeover race
        return ['status' => 'in_progress'];
    }

    /**
     * Ownership re-verification inside the processing transaction.
     */
    public function stillOwned(string $eventId, string $token): bool
    {
        return DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->where('claim_token', $token)
            ->where('state', StripeWebhookEvent::STATE_CLAIMED)
            ->exists();
    }

    /**
     * Mark event as processed (terminal success).
     */
    public function finalize(string $eventId, string $token): void
    {
        DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->where('claim_token', $token)
            ->where('state', StripeWebhookEvent::STATE_CLAIMED)
            ->update([
                'state'        => StripeWebhookEvent::STATE_PROCESSED,
                'processed_at' => now(),
                'updated_at'   => now(),
            ]);
    }

    /**
     * Mark event as failed (retryable).
     * processed_at intentionally kept NULL per Master decision.
     */
    public function fail(string $eventId, string $token): void
    {
        DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->where('claim_token', $token)
            ->where('state', StripeWebhookEvent::STATE_CLAIMED)
            ->update([
                'state'        => StripeWebhookEvent::STATE_FAILED,
                'processed_at' => null,
                'updated_at'   => now(),
            ]);
    }
}
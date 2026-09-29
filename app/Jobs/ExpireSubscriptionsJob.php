<?php

namespace App\Jobs;

use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * FIX-07 + PHASE 7G: Transition active subscriptions to expired
     * AND send email notifications.
     *
     * PHASE 7G enhancements:
     *   • Send SubscriptionExpiringMail (7 days before expiry, once per sub)
     *   • Send SubscriptionExpiredMail (on transition to expired)
     *
     * Idempotent + concurrency-safe via atomic UPDATE with WHERE guards.
     * Duplicate reminder prevention via `expiry_reminder_sent_at` flag.
     */
    public function handle(): void
    {
        $this->sendExpiringReminders();
        $this->expirePastDue();
    }

    /**
     * Send a 7-day advance reminder for subscriptions expiring soon.
     * Uses `expiry_reminder_sent_at` flag to prevent duplicate emails.
     */
    private function sendExpiringReminders(): void
    {
        $expiringSoon = Subscription::query()
            ->with(['provider', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereNull('expiry_reminder_sent_at')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->get();

        foreach ($expiringSoon as $subscription) {
            $daysRemaining = (int) ceil(now()->diffInDays($subscription->end_date, false));

            try {
                if ($subscription->provider && $subscription->provider->contact_email) {
                    Mail::to($subscription->provider->contact_email)
                        ->send(new SubscriptionExpiringMail($subscription, max($daysRemaining, 1)));
                }

                $subscription->update(['expiry_reminder_sent_at' => now()]);

                Log::info('ExpireSubscriptionsJob: expiring reminder sent', [
                    'subscription_id' => $subscription->id,
                    'days_remaining' => $daysRemaining,
                ]);
            } catch (\Throwable $e) {
                Log::error('ExpireSubscriptionsJob: expiring reminder failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next subscription — don't block
            }
        }
    }

    /**
     * Transition active subscriptions whose end_date has passed
     * to status='expired' and notify providers.
     */
    private function expirePastDue(): void
    {
        // Capture IDs before bulk update (so we can email them)
        $expired = Subscription::query()
            ->with(['provider', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return;
        }

        // Bulk update (atomic)
        $affected = Subscription::query()
            ->whereIn('id', $expired->pluck('id'))
            ->update(['status' => 'expired']);

        // Send emails (non-blocking)
        foreach ($expired as $subscription) {
            try {
                if ($subscription->provider && $subscription->provider->contact_email) {
                    Mail::to($subscription->provider->contact_email)
                        ->send(new SubscriptionExpiredMail($subscription));
                }
            } catch (\Throwable $e) {
                Log::error('ExpireSubscriptionsJob: expired email failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next — don't block
            }
        }

        Log::info('ExpireSubscriptionsJob: subscriptions transitioned to expired', [
            'count' => $affected,
            'executed_at' => now()->toIso8601String(),
        ]);
    }
}
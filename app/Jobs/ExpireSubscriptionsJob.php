<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * FIX-07: Transition active subscriptions whose end_date has passed
     * to status='expired'.
     *
     * Idempotent + concurrency-safe via single atomic UPDATE with
     * WHERE status='active' guard. Repeated runs are no-ops.
     *
     * NULL end_date subscriptions are NOT touched.
     * Cancelled/pending subscriptions are NOT touched.
     * No new Free subscription is created.
     */
    public function handle(): void
    {
        $affected = Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->update(['status' => 'expired']);

        if ($affected > 0) {
            Log::info('ExpireSubscriptionsJob: subscriptions transitioned to expired', [
                'count' => $affected,
                'executed_at' => now()->toIso8601String(),
            ]);
        }
    }
}
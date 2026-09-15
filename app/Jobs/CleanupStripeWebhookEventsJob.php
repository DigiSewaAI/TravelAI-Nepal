<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupStripeWebhookEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * FIX-08 retention: delete stripe_webhook_events processed >90 days ago.
     * Failed events (processed_at = NULL) are intentionally preserved.
     */
    public function handle(): void
    {
        $cutoff = now()->subDays(90);

        $deleted = DB::table('stripe_webhook_events')
            ->whereNotNull('processed_at')
            ->where('processed_at', '<', $cutoff)
            ->delete();

        if ($deleted > 0) {
            Log::info('CleanupStripeWebhookEventsJob: pruned old events', [
                'count'  => $deleted,
                'cutoff' => $cutoff->toIso8601String(),
            ]);
        }
    }
}
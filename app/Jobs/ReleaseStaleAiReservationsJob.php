<?php

namespace App\Jobs;

use App\Models\AiReservation;
use App\Services\AiReservationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FIX-12: Releases stale AI reservations whose TTL has elapsed.
 *
 * Master-approved atomic claim pattern:
 *   UPDATE ai_reservations
 *      SET status='expired', released_at=NOW()
 *    WHERE id=? AND status='reserved' AND expires_at <= NOW()
 *
 * Only if affected === 1 do we decrement the corresponding counter.
 * Concurrent workers race on the status UPDATE; only one wins.
 *
 * Logging: WARNING level, no prompt/API key/PII.
 */
class ReleaseStaleAiReservationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function handle(AiReservationService $service): void
    {
        $released = 0;
        $skipped  = 0;

        AiReservation::where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($service, &$released, &$skipped) {
                foreach ($rows as $reservation) {
                    $didClaim = $this->claimAndDecrement($reservation, $service);

                    if ($didClaim) {
                        $released++;
                    } else {
                        $skipped++;
                    }
                }
            });

        if ($released > 0 || $skipped > 0) {
            Log::warning('FIX-12: stale AI reservations processed', [
                'released'     => $released,
                'skipped'      => $skipped,
                'executed_at'  => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Atomic claim: only transition reserved -> expired if expires_at has passed.
     * Decrement counter only when status transition actually affected 1 row.
     */
    protected function claimAndDecrement(AiReservation $reservation, AiReservationService $service): bool
    {
        return DB::transaction(function () use ($reservation, $service) {
            // Atomic claim — concurrent workers race, only one wins.
            $affected = AiReservation::where('id', $reservation->id)
                ->where('status', 'reserved')
                ->where('expires_at', '<=', now())
                ->update([
                    'status'      => 'expired',
                    'released_at' => now(),
                    'updated_at'  => now(),
                ]);

            if ($affected === 0) {
                return false;
            }

            $service->decrementCounter($reservation);

            // No prompt, no API key, no PII, no request payload.
            Log::warning('FIX-12: stale AI reservation expired', [
                'reservation_id' => $reservation->id,
                'provider_id'    => $reservation->provider_id,
                'guest'          => $reservation->guest_ip_hash !== null,
                'period'         => $reservation->period,
                'age_minutes'    => $reservation->reserved_at?->diffInMinutes(now()),
                'expired_at'     => now()->toIso8601String(),
            ]);

            return true;
        });
    }
}
<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\AiGuestUsage;
use App\Models\AiReservation;
use App\Models\Provider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FIX-12: Crash-safe AI reservation lifecycle.
 *
 * Lifecycle:
 *   reserve() -> reserved -> [finalize() | release()]
 *                        -> [cleanup job -> expired]
 *
 * Master-approved invariants:
 *  - Never hold a DB transaction while calling the LLM.
 *  - Idempotent within a request-derived window.
 *  - Race-safe first-row creation (FIX-06 pattern).
 *  - Atomic state transitions (WHERE status='reserved').
 *  - Guest identity = normalized IP hash only.
 */
class AiReservationService
{
    public const GUEST_DAILY_LIMIT = 5;
    public const TTL_MINUTES = 15;
    public const IDEMPOTENCY_WINDOW_MINUTES = 1;

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    /**
     * Reserve one AI slot for a provider (monthly quota).
     *
     * @throws AiQuotaExceededException
     */
    public function reserveForProvider(
        Provider $provider,
        string $endpoint,
        string $idempotencyKey
    ): AiReservation {
        return $this->reserve(
            providerId: $provider->id,
            guestIpHash: null,
            period: $this->providerPeriod(),
            max: $this->providerMax($provider),
            endpoint: $endpoint,
            idempotencyKey: $idempotencyKey,
            usageTable: 'ai_usage',
            usageWhere: ['provider_id' => $provider->id, 'month' => $this->providerPeriod()],
            usageInsert: [
                'provider_id' => $provider->id,
                'month'       => $this->providerPeriod(),
                'count'       => 1,
            ],
            quotaException: fn () => AiQuotaExceededException::forProvider($this->providerMax($provider)),
        );
    }

    /**
     * Reserve one AI slot for a guest (daily quota, per normalized IP hash).
     *
     * @throws AiQuotaExceededException
     */
    public function reserveForGuest(
        string $guestIpHash,
        string $endpoint,
        string $idempotencyKey
    ): AiReservation {
        $period = $this->guestPeriod();

        return $this->reserve(
            providerId: null,
            guestIpHash: $guestIpHash,
            period: $period,
            max: self::GUEST_DAILY_LIMIT,
            endpoint: $endpoint,
            idempotencyKey: $idempotencyKey,
            usageTable: 'ai_guest_usage',
            usageWhere: ['guest_ip_hash' => $guestIpHash, 'period' => $period],
            usageInsert: [
                'guest_ip_hash' => $guestIpHash,
                'period'        => $period,
                'count'         => 1,
            ],
            quotaException: fn () => AiQuotaExceededException::forGuest(self::GUEST_DAILY_LIMIT),
        );
    }

    /**
     * Mark a reservation as completed. Atomic; safe against double-finalize.
     */
    public function finalize(AiReservation $reservation): void
    {
        AiReservation::where('id', $reservation->id)
            ->where('status', 'reserved')
            ->update([
                'status'       => 'completed',
                'finalized_at' => now(),
                'updated_at'   => now(),
            ]);
    }

    /**
     * Release a reservation (explicit failure). Atomic claim + counter decrement.
     * Safe against double-release (only one wins the status UPDATE).
     */
    public function release(AiReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            // Atomic claim: only one worker flips reserved -> released.
            $affected = AiReservation::where('id', $reservation->id)
                ->where('status', 'reserved')
                ->update([
                    'status'      => 'released',
                    'released_at' => now(),
                    'updated_at'  => now(),
                ]);

            if ($affected === 0) {
                // Someone else already finalized/released/expired this row.
                return;
            }

            $this->decrementCounter($reservation);
        });
    }

    /**
     * Idempotency key derived from identity + endpoint + request payload + minute window.
     * Same request within same minute -> same key. After that, key rotates.
     */
    public static function generateIdempotencyKey(
        string $identity,
        string $endpoint,
        array $requestPayload
    ): string {
        $canonical = self::canonicalize($requestPayload);
        $requestHash = hash('sha256', json_encode($canonical, JSON_UNESCAPED_SLASHES));
        $window = now()->format('YmdHi'); // YYYYMMDDHHMM

        return hash('sha256', $identity . '|' . $endpoint . '|' . $requestHash . '|' . $window);
    }

    /**
     * Normalize a raw client IP and return a stable SHA256 hex.
     * Master decision D2: IP only; User-Agent is auxiliary metadata.
     */
    public static function normalizeIpHash(string $rawIp): string
    {
        $normalized = strtolower(trim($rawIp));

        // Strip IPv6 zone identifier (fe80::1%eth0 -> fe80::1)
        if (str_contains($normalized, '%')) {
            $normalized = explode('%', $normalized, 2)[0];
        }

        // Normalize IPv4-mapped IPv6 (::ffff:1.2.3.4 -> 1.2.3.4)
        if (str_starts_with($normalized, '::ffff:')) {
            $normalized = substr($normalized, 7);
        }

        return hash('sha256', $normalized);
    }

    public static function providerPeriod(): string
    {
        return CarbonImmutable::now('Asia/Kathmandu')->format('Y-m');
    }

    public static function guestPeriod(): string
    {
        return CarbonImmutable::now('Asia/Kathmandu')->format('Y-m-d');
    }

    // =========================================================================
    // INTERNAL
    // =========================================================================

    /**
     * Core race-safe reservation with idempotency handling.
     * All three concerns (idempotency, first-row race, atomic increment)
     * are handled inside a single short-lived transaction.
     */
    protected function reserve(
        ?int $providerId,
        ?string $guestIpHash,
        string $period,
        int $max,
        string $endpoint,
        string $idempotencyKey,
        string $usageTable,
        array $usageWhere,
        array $usageInsert,
        callable $quotaException
    ): AiReservation {
        // Step 1: Idempotency check (outside transaction — read-only)
        $existing = AiReservation::where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            if ($existing->isReserved() || $existing->isCompleted()) {
                // Master directive #1: reserved -> in-flight; completed -> duplicate.
                // Return same reservation; caller decides response.
                return $existing;
            }
            // released / expired -> allow new reservation
            // Delete old row to free the unique idempotency_key slot.
            $existing->delete();
        }

        // Unlimited plan -> still create a reservation (for audit) but skip counter.
        $isUnlimited = ($max === -1);

        return DB::transaction(function () use (
            $providerId, $guestIpHash, $period, $max, $endpoint, $idempotencyKey,
            $usageTable, $usageWhere, $usageInsert, $quotaException, $isUnlimited
        ) {
            if (!$isUnlimited) {
                $this->atomicIncrement($usageTable, $usageWhere, $usageInsert, $max, $quotaException);
            }

            return AiReservation::create([
                'provider_id'     => $providerId,
                'guest_ip_hash'   => $guestIpHash,
                'period'          => $period,
                'status'          => 'reserved',
                'reserved_at'     => now(),
                'expires_at'      => now()->addMinutes(self::TTL_MINUTES),
                'idempotency_key' => $idempotencyKey,
                'endpoint'        => $endpoint,
            ]);
        });
    }

    /**
     * Atomic check+increment with FIX-06 first-row race handling.
     */
    protected function atomicIncrement(
        string $table,
        array $where,
        array $insert,
        int $max,
        callable $quotaException
    ): void {
        // Attempt atomic increment first (works when row already exists).
        $affected = DB::table($table)
            ->where($where)
            ->whereRaw('count < ?', [$max])
            ->increment('count');

        if ($affected > 0) {
            return;
        }

        // Either limit reached, or no row exists yet.
        $exists = DB::table($table)->where($where)->exists();

        if ($exists) {
            throw $quotaException();
        }

        // First-row race — use unique constraint + retry (FIX-06 pattern).
        try {
            DB::table($table)->insert(array_merge($insert, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            return;
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Another worker inserted first — retry the increment path.
            $affected = DB::table($table)
                ->where($where)
                ->whereRaw('count < ?', [$max])
                ->increment('count');

            if ($affected === 0) {
                throw $quotaException();
            }
        }
    }

    /**
     * Decrement the appropriate counter for a released/expired reservation.
     * Used by both release() and the cleanup job.
     */
    public function decrementCounter(AiReservation $reservation): void
    {
        if ($reservation->provider_id) {
            DB::table('ai_usage')
                ->where('provider_id', $reservation->provider_id)
                ->where('month', $reservation->period)
                ->where('count', '>', 0)
                ->decrement('count');
        } elseif ($reservation->guest_ip_hash) {
            DB::table('ai_guest_usage')
                ->where('guest_ip_hash', $reservation->guest_ip_hash)
                ->where('period', $reservation->period)
                ->where('count', '>', 0)
                ->decrement('count');
        }
    }

    protected function providerMax(Provider $provider): int
    {
        $plan = $provider->getActivePlanAttribute();
        return (int) ($plan?->limits['max_ai_requests'] ?? 5);
    }

    /**
     * Canonicalize request payload for stable hashing.
     * Recursively sorts keys; ignores volatile fields like timestamps.
     */
    protected static function canonicalize(array $data): array
    {
        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::canonicalize($value);
            }
        }
        return $data;
    }
}
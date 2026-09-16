<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\AiReservation;
use App\Models\AiUsage;
use App\Models\Provider;

/**
 * FIX-12: Provider-facing AI quota facade.
 *
 * Delegates the crash-safe lifecycle to AiReservationService.
 * Retains getUsage() for dashboard display.
 *
 * Lifecycle contract:
 *   reservation = reserve(provider, endpoint, payload)   [short TX]
 *   try:    LLM call
 *           finalize(reservation)
 *   catch:  release(reservation)
 *
 * NEVER hold a DB transaction across the LLM call.
 */
class AiLimitService
{
    public function __construct(
        protected AiReservationService $reservations
    ) {}

    /**
     * Reserve one AI slot. Idempotent within a 1-minute window
     * keyed by identity + endpoint + payload.
     *
     * @throws AiQuotaExceededException
     */
    public function reserve(Provider $provider, string $endpoint, array $payload): AiReservation
    {
        $identity = 'provider:' . $provider->id;
        $key = AiReservationService::generateIdempotencyKey($identity, $endpoint, $payload);

        return $this->reservations->reserveForProvider($provider, $endpoint, $key);
    }

    /**
     * Mark reservation as completed. Atomic; safe against double-finalize.
     */
    public function finalize(AiReservation $reservation): void
    {
        $this->reservations->finalize($reservation);
    }

    /**
     * Release a reservation on explicit failure. Atomic claim + counter decrement.
     * Safe against double-release (only one worker wins the status UPDATE).
     */
    public function release(AiReservation $reservation): void
    {
        $this->reservations->release($reservation);
    }

    /**
     * Read current usage + limit for UI display. Non-mutating.
     */
    public function getUsage(Provider $provider): array
    {
        $month = AiReservationService::providerPeriod();

        $usage = AiUsage::firstOrCreate(
            ['provider_id' => $provider->id, 'month' => $month],
            ['count' => 0]
        );

        $plan = $provider->getActivePlanAttribute();
        $limit = $plan ? ($plan->limits['max_ai_requests'] ?? 5) : 5;

        return [
            'used'         => (int) $usage->count,
            'limit'        => (int) $limit,
            'remaining'    => $limit == -1 ? -1 : max(0, $limit - $usage->count),
            'is_unlimited' => $limit == -1,
        ];
    }
}
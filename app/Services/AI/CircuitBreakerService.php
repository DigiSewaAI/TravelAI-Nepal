<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 4J-EXT Phase 2: Circuit Breaker for LLM providers.
 *
 * Tracks rate-limited providers via file cache.
 * Skip rate-limited provider for TTL seconds (default 60s).
 */
class CircuitBreakerService
{
    protected const PREFIX      = 'circuit_breaker:';
    protected const DEFAULT_TTL = 60;

    /**
     * Check if provider is available (not circuit-broken).
     */
    public function isAvailable(string $providerKey): bool
    {
        return !Cache::has(self::PREFIX . $providerKey);
    }

    /**
     * Mark provider as rate-limited for $seconds.
     */
    public function markRateLimited(string $providerKey, int $seconds = self::DEFAULT_TTL): void
    {
        $ttl = max($seconds, self::DEFAULT_TTL);
        Cache::put(self::PREFIX . $providerKey, time() + $ttl, $ttl);

        Log::info('4J-Parallel: circuit breaker OPEN', [
            'provider' => $providerKey,
            'ttl'      => $ttl,
        ]);
    }

    /**
     * Manually clear circuit breaker for a provider.
     */
    public function clear(string $providerKey): void
    {
        Cache::forget(self::PREFIX . $providerKey);
    }
}
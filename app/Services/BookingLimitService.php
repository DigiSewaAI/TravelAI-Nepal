<?php

namespace App\Services;

use App\Models\Provider;
use App\Support\QuotaPeriod;
use Illuminate\Support\Facades\DB;

class BookingLimitService
{
    /**
     * Reserve one booking slot for the provider.
     * Returns the quota period used, or null for unlimited.
     * MUST be called inside a DB transaction for atomicity.
     */
    public function reserve(Provider $provider): ?string
    {
        $max = $this->maxFor($provider);
        if ($max === -1) return null;

        $month = QuotaPeriod::current();

        $affected = DB::table('booking_usage')
            ->where('provider_id', $provider->id)
            ->where('month', $month)
            ->whereRaw('count < ?', [$max])
            ->increment('count');

        if ($affected > 0) {
            return $month;
        }

        $exists = DB::table('booking_usage')
            ->where('provider_id', $provider->id)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            throw new \DomainException("Booking limit reached ({$max}/month).");
        }

        try {
            DB::table('booking_usage')->insert([
                'provider_id' => $provider->id,
                'month' => $month,
                'count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $month;
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return $this->reserve($provider);
        }
    }

    /**
     * Release one slot from the ORIGINAL quota month.
     */
    public function release(Provider $provider, string $quotaMonth): void
    {
        DB::table('booking_usage')
            ->where('provider_id', $provider->id)
            ->where('month', $quotaMonth)
            ->where('count', '>', 0)
            ->decrement('count');
    }

    /**
     * Read current usage for UI display.
     */
    public function getUsage(Provider $provider): array
    {
        $max = $this->maxFor($provider);
        $month = QuotaPeriod::current();

        $row = DB::table('booking_usage')
            ->where('provider_id', $provider->id)
            ->where('month', $month)
            ->first();

        $used = $row ? (int) $row->count : 0;

        return [
            'used' => $used,
            'limit' => $max,
            'remaining' => $max === -1 ? -1 : max(0, $max - $used),
            'is_unlimited' => $max === -1,
        ];
    }

    /**
     * Resolve monthly booking limit for a provider's current plan.
     */
    private function maxFor(Provider $provider): int
    {
        $plan = $provider->getActivePlanAttribute();
        return (int) ($plan?->limits['max_bookings'] ?? 10);
    }
}
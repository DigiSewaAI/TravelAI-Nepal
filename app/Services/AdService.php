<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdClick;
use App\Models\AdImpression;
use Illuminate\Support\Facades\Cache;

class AdService
{
    const CACHE_KEY = 'ads:featured:';
    const CACHE_TTL = 1800; // 30 min

    /**
     * Get active ads for the traveler dashboard.
     * MVP: returns all active ads (targeting added Phase 2).
     */
    public function getFeaturedAds(int $limit = 5)
    {
        $cacheKey = self::CACHE_KEY . 'dashboard';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            return Ad::active()
                ->forPlacement('dashboard_featured')
                ->with('provider')
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Track an ad impression (once per session per ad).
     */
    public function trackImpression(Ad $ad, ?int $userId = null, ?string $ip = null): void
    {
        AdImpression::create([
            'ad_id'      => $ad->id,
            'user_id'    => $userId,
            'ip_address' => $ip,
            'viewed_at'  => now(),
        ]);

        Ad::where('id', $ad->id)->increment('impressions');
    }

    /**
     * Track an ad click.
     */
    public function trackClick(Ad $ad, ?int $userId = null, ?string $ip = null): void
    {
        AdClick::create([
            'ad_id'      => $ad->id,
            'user_id'    => $userId,
            'ip_address' => $ip,
            'clicked_at' => now(),
        ]);

        Ad::where('id', $ad->id)->increment('clicks');
    }

    /**
     * Clear cached active ads (called after create/update/approve).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY . 'dashboard');
    }

    /**
     * Calculate price by duration.
     */
    public function calculatePrice(int $days): int
    {
        return Ad::calculatePrice($days);
    }
}
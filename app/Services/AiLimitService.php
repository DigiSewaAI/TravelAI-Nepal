<?php

namespace App\Services;

use App\Models\AiUsage;
use App\Models\Provider;
use Illuminate\Support\Facades\Log;

class AiLimitService
{
    /**
     * Check if the provider has reached their AI request limit.
     * If not, increment the usage count.
     * 
     * @throws \Exception
     */
    public function checkAndIncrement(Provider $provider): void
    {
        $plan = $provider->getActivePlanAttribute();
        
        if (!$plan) {
            // Free plan default: 5 requests per month
            $maxRequests = 5;
        } else {
            $maxRequests = $plan->limits['max_ai_requests'] ?? 5;
        }

        // Unlimited (-1)
        if ($maxRequests == -1) {
            return;
        }

        $month = now()->format('Y-m');
        
        $usage = AiUsage::firstOrCreate([
            'provider_id' => $provider->id,
            'month' => $month,
        ]);

        if ($usage->count >= $maxRequests) {
            throw new \Exception("You have reached your AI request limit of {$maxRequests} for this month. Please upgrade your plan to continue using AI features.");
        }

        $usage->increment('count');
        
        Log::info('AI usage incremented', [
            'provider_id' => $provider->id,
            'month' => $month,
            'new_count' => $usage->fresh()->count,
        ]);
    }

    /**
     * Get current usage and limit for a provider.
     */
    public function getUsage(Provider $provider): array
    {
        $month = now()->format('Y-m');
        $usage = AiUsage::firstOrCreate([
            'provider_id' => $provider->id,
            'month' => $month,
        ]);

        $plan = $provider->getActivePlanAttribute();
        $limit = $plan ? ($plan->limits['max_ai_requests'] ?? 5) : 5;

        return [
            'used' => $usage->count,
            'limit' => $limit,
            'remaining' => $limit == -1 ? -1 : max(0, $limit - $usage->count),
            'is_unlimited' => $limit == -1,
        ];
    }
}
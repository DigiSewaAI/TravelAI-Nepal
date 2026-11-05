<?php

namespace App\Services\Claim;

use App\Models\BookingClaimEvent;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimitService
{
    public function assertCanAttempt(
        int $userId,
        string $claimableType,
        int $claimableId,
        string $ip
    ): void {
        $this->checkIpLimit($ip);
        $this->checkOrderLimit($claimableType, $claimableId);
        $this->checkUserLimit($userId);
    }

    private function checkIpLimit(string $ip): void
    {
        $max = (int) config('claim.rate_limit.ip_per_15min', 5);
        $key = 'claim:ip:' . $ip;
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new TooManyRequestsHttpException();
        }
        RateLimiter::hit($key, 900);
    }

    private function checkOrderLimit(string $type, int $id): void
    {
        $max = (int) config('claim.rate_limit.order_per_day', 3);
        $count = BookingClaimEvent::where('claimable_type', $type)
            ->where('claimable_id', $id)
            ->whereIn('event_type', ['otp_wrong', 'receipt_wrong'])
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($count >= $max) {
            throw new TooManyRequestsHttpException();
        }
    }

    private function checkUserLimit(int $userId): void
    {
        $max = (int) config('claim.rate_limit.user_per_day', 10);
        $count = BookingClaimEvent::where('traveler_id', $userId)
            ->whereIn('event_type', ['otp_wrong', 'receipt_wrong'])
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($count >= $max) {
            throw new TooManyRequestsHttpException();
        }
    }
}
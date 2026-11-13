<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        // Only recalc on approve/reject transitions (status changed)
        if ($review->wasChanged('status') || $review->wasRecentlyCreated) {
            $this->recalcService($review);
            $this->recalcProvider($review);
        }
    }

    public function deleted(Review $review): void
    {
        $this->recalcService($review);
        $this->recalcProvider($review);
    }

    protected function recalcService(Review $review): void
    {
        if ($review->service_id) {
            $review->service?->recalculateRating();
        }
    }

    protected function recalcProvider(Review $review): void
    {
        $provider = $review->provider
            ?? $review->service?->provider
            ?? null;
        $provider?->recalculateRating();
    }
}
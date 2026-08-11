<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Booking;
use App\Models\User;
use App\Models\AiRecommendation;
use Illuminate\Support\Facades\Log;

class AiRecommendationService
{
    /**
     * Get personalized service recommendations for a user
     */
    public function getRecommendations(User $user, int $limit = 6): array
    {
        // Get user's booking history
        $bookedServiceIds = Booking::where('traveler_id', $user->id)
            ->pluck('service_id')
            ->toArray();

        // Get user's preferred categories from bookings
        $preferredCategories = Service::whereIn('id', $bookedServiceIds)
            ->pluck('service_category_id')
            ->toArray();

        // Get user's average price range
        $avgPrice = Service::whereIn('id', $bookedServiceIds)->avg('price') ?? 5000;

        // Build recommendation query
        $query = Service::with(['provider', 'category', 'reviews'])
            ->where('status', 'active');

        // Exclude already booked services
        if (!empty($bookedServiceIds)) {
            $query->whereNotIn('id', $bookedServiceIds);
        }

        // Prioritize preferred categories
        if (!empty($preferredCategories)) {
            $query->whereIn('service_category_id', $preferredCategories);
        }

        // Price range: ±30% of average
        $minPrice = $avgPrice * 0.7;
        $maxPrice = $avgPrice * 1.3;
        $query->whereBetween('price', [$minPrice, $maxPrice]);

        // Order by rating and popularity
        $services = $query->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit($limit * 2)
            ->get();

        // Score and rank
        $scored = $services->map(function ($service) use ($preferredCategories) {
            $score = 0;
            // Category match
            if (in_array($service->service_category_id, $preferredCategories)) {
                $score += 10;
            }
            // Rating bonus
            $avgRating = $service->averageRating();
            if ($avgRating >= 4.5) $score += 5;
            elseif ($avgRating >= 4.0) $score += 3;
            // Popularity bonus
            $score += $service->bookings_count * 0.5;
            return ['service' => $service, 'score' => $score];
        });

        $sorted = $scored->sortByDesc('score')->take($limit)->pluck('service')->toArray();

        // Store recommendations
        $this->storeRecommendations($user, $sorted);

        return $sorted;
    }

    /**
     * Get recommendations for guest (session-based)
     */
    public function getGuestRecommendations(string $sessionId, int $limit = 6): array
    {
        // Default recommendations based on popular services
        $services = Service::with(['provider', 'category', 'reviews'])
            ->where('status', 'active')
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        // Store for guest
        $this->storeGuestRecommendations($sessionId, $services);

        return $services;
    }

    /**
     * Store recommendations for user
     */
    protected function storeRecommendations(User $user, array $services): void
    {
        AiRecommendation::create([
            'user_id' => $user->id,
            'type' => 'service',
            'recommendations' => collect($services)->pluck('id')->toArray(),
            'metadata' => [
                'count' => count($services),
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Store recommendations for guest
     */
    protected function storeGuestRecommendations(string $sessionId, array $services): void
    {
        AiRecommendation::create([
            'session_id' => $sessionId,
            'type' => 'service',
            'recommendations' => collect($services)->pluck('id')->toArray(),
            'metadata' => [
                'count' => count($services),
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Get trending services
     */
    public function getTrendingServices(int $limit = 10): array
    {
        return Service::with(['provider', 'category'])
            ->where('status', 'active')
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get services similar to a given service
     */
    public function getSimilarServices(Service $service, int $limit = 4): array
    {
        return Service::with(['provider', 'category'])
            ->where('status', 'active')
            ->where('service_category_id', $service->service_category_id)
            ->where('id', '!=', $service->id)
            ->whereBetween('price', [$service->price * 0.5, $service->price * 1.5])
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
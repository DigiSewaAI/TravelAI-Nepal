<?php

namespace App\Services\JourneyReplay;

use App\Models\User;
use App\Models\Booking;
use App\Services\LlmService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JourneyReplayService
{
    protected $llm;

    public function __construct(LlmService $llm)
    {
        $this->llm = $llm;
    }

    /**
     * Get the journey replay data for a user.
     */
    public function getReplay(User $user): array
    {
        $cacheKey = 'journey_replay_' . $user->id;
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->buildReplayData($user);
        });
    }

    /**
     * Build replay data from database.
     */
    protected function buildReplayData(User $user): array
    {
        // 1. Fetch all bookings with eager loading
        $bookings = Booking::where('traveler_id', $user->id)
            ->with([
                'service',
                'service.provider',
                'service.location',
                'service.trekDetail',
                'service.tourDetail',
                'service.hotelDetail',
                'service.category',
                'qrScans.waypoint',
                'review',
            ])
            ->orderBy('start_date', 'asc')
            ->get();

        if ($bookings->isEmpty()) {
            return $this->emptyReplayData();
        }

        // 2. Build timeline items
        $timeline = [];
        $totalCheckins = 0;
        $highestAltitude = 0;
        $destinations = [];
        $allWaypoints = [];

        foreach ($bookings as $booking) {
            $service = $booking->service;
            if (!$service) {
                continue;
            }

            $type = $this->getServiceType($service);

            $checkins = $booking->qrScans->map(function ($scan) {
                return [
                    'checkpoint' => $scan->checkpoint_name,
                    'scanned_at' => $scan->scanned_at,
                    'waypoint' => $scan->waypoint ? [
                        'name' => $scan->waypoint->name,
                        'latitude' => $scan->waypoint->latitude,
                        'longitude' => $scan->waypoint->longitude,
                        'altitude' => $scan->waypoint->altitude,
                    ] : null,
                ];
            });

            $totalCheckins += $checkins->count();

            foreach ($booking->qrScans as $scan) {
                if ($scan->waypoint && $scan->waypoint->latitude && $scan->waypoint->longitude) {
                    $allWaypoints[] = [
                        'name' => $scan->waypoint->name,
                        'lat' => (float) $scan->waypoint->latitude,
                        'lng' => (float) $scan->waypoint->longitude,
                        'altitude' => $scan->waypoint->altitude,
                        'scanned_at' => $scan->scanned_at,
                        'checkpoint' => $scan->checkpoint_name,
                    ];
                    if ($scan->waypoint->altitude > $highestAltitude) {
                        $highestAltitude = $scan->waypoint->altitude;
                    }
                }
            }

            $location = $service->location;
            $destName = $location ? $location->city : null;
            if ($destName && !in_array($destName, $destinations)) {
                $destinations[] = $destName;
            }

            $item = [
                'type' => $type,
                'service_name' => $service->name,
                'provider' => $service->provider ? $service->provider->name : null,
                'location' => $destName,
                'start_date' => $booking->start_date,
                'duration' => $this->getDuration($service, $type),
                'cover_image' => $service->cover_image,
                'service_category' => $service->category ? $service->category->name : null,
                'checkins' => $checkins,
                'waypoints' => $checkins->pluck('waypoint')->filter()->values(),
                'status' => $booking->status,
                'booking_id' => $booking->id,
                'rating' => $booking->review ? $booking->review->rating : null,
            ];

            if ($type === 'trek' && $service->trekDetail) {
                $item['difficulty'] = $service->trekDetail->difficulty;
                $item['max_altitude'] = $service->trekDetail->max_altitude;
            }
            if ($type === 'hotel' && $service->hotelDetail) {
                $item['star_rating'] = $service->hotelDetail->star_rating;
                $item['amenities'] = $service->hotelDetail->amenities;
                $item['check_in_time'] = $service->hotelDetail->check_in_time;
                $item['check_out_time'] = $service->hotelDetail->check_out_time;
            }

            $timeline[] = $item;
        }

        $stats = [
            'total_bookings' => $bookings->count(),
            'total_checkins' => $totalCheckins,
            'unique_destinations' => count($destinations),
            'highest_altitude' => $highestAltitude,
            'journey_start' => $bookings->first()->start_date,
            'journey_end' => $bookings->last()->start_date,
        ];

        $mapPoints = collect($allWaypoints)->sortBy('scanned_at')->values()->toArray();

        // 3. Generate AI Story
        $storyInput = [
            'destinations' => $destinations,
            'bookings' => collect($timeline)->map(fn($item) => [
                'type' => $item['type'],
                'name' => $item['service_name'],
                'location' => $item['location'] ?? 'Unknown',
            ])->toArray(),
            'total_checkins' => $totalCheckins,
            'highest_altitude' => $highestAltitude,
            'total_bookings' => $bookings->count(),
            'journey_start' => $bookings->first()->start_date->format('M d, Y'),
            'journey_end' => $bookings->last()->start_date->format('M d, Y'),
        ];

        $story = $this->generateStory($storyInput);

        return [
            'timeline' => $timeline,
            'stats' => $stats,
            'map_points' => $mapPoints,
            'story' => $story,
            'has_data' => true,
        ];
    }

    /**
     * Generate AI story using LlmService.
     * Uses Groq AI (already integrated in project).
     */
    /**
 * Generate AI story using LlmService.
 * Uses Groq AI (already integrated in project).
 */
protected function generateStory(array $input): ?string
{
    try {
        $prompt = $this->buildStoryPrompt($input);
        // Get current locale for language support
        $locale = app()->getLocale();
        // Call LlmService with extract=false to get raw content
        $response = $this->llm->generateItinerary(
            prompt: $prompt,
            locale: $locale,
            model: null,
            extract: false,
            maxTokens: 200 // short story, limit tokens
        );
        
        // The response is ['content' => '...', 'raw' => true]
        $story = $response['content'] ?? null;
        return $this->cleanStoryResponse($story);
    } catch (\Exception $e) {
        Log::warning('AI story generation failed: ' . $e->getMessage());
        return null;
    }
}

    /**
     * Build the prompt for AI story generation.
     */
    protected function buildStoryPrompt(array $input): string
    {
        $destinations = implode(' → ', $input['destinations']);
        $bookingsList = '';
        foreach ($input['bookings'] as $b) {
            $bookingsList .= "- " . ucfirst($b['type']) . ": " . $b['name'] . " (" . ($b['location'] ?? 'Unknown') . ")\n";
        }

        return "You are a travel storyteller. Write a short, warm, cinematic journey summary based ONLY on the supplied facts.\n\n" .
               "Destinations: " . $destinations . "\n" .
               "Journey: " . $input['journey_start'] . " to " . $input['journey_end'] . "\n" .
               "Total bookings: " . $input['total_bookings'] . "\n" .
               "Total check-ins: " . $input['total_checkins'] . "\n" .
               "Highest altitude reached: " . $input['highest_altitude'] . "m\n\n" .
               "Bookings:\n" . $bookingsList . "\n" .
               "Write a warm, inspirational summary (max 120-150 words). Do NOT invent places, activities, dates, achievements, or experiences. If information is limited, write a simple factual narrative instead of guessing. " .
               "Start directly with the story. Do not add any preface or introduction like 'Here is your story:'";
    }

    /**
     * Clean and validate AI response.
     */
    protected function cleanStoryResponse(?string $response): ?string
    {
        if (!$response) {
            return null;
        }

        // Remove any markdown or extra formatting if present
        $cleaned = trim($response);
        $cleaned = preg_replace('/^["\']+|["\']+$/', '', $cleaned);
        $cleaned = preg_replace('/^Here is your story:?\s*/i', '', $cleaned);

        // Limit to 250 words (avoid very long responses)
        $words = str_word_count($cleaned, 1);
        if (count($words) > 250) {
            $cleaned = implode(' ', array_slice($words, 0, 250)) . '...';
        }

        return $cleaned ?: null;
    }

    /**
     * Determine service type using existing relations.
     */
    protected function getServiceType($service): string
    {
        if ($service->trekDetail) {
            return 'trek';
        }
        if ($service->tourDetail) {
            return 'tour';
        }
        if ($service->hotelDetail) {
            return 'hotel';
        }
        if ($service->category) {
            $cat = strtolower($service->category->name);
            if (str_contains($cat, 'trek')) return 'trek';
            if (str_contains($cat, 'tour')) return 'tour';
            if (str_contains($cat, 'hotel')) return 'hotel';
        }
        return 'other';
    }

    protected function getDuration($service, string $type): ?int
    {
        if ($type === 'trek' && $service->trekDetail) {
            return $service->trekDetail->duration_days;
        }
        if ($type === 'tour' && $service->tourDetail) {
            return $service->tourDetail->duration_days;
        }
        return null;
    }

    protected function emptyReplayData(): array
    {
        return [
            'timeline' => [],
            'stats' => [
                'total_bookings' => 0,
                'total_checkins' => 0,
                'unique_destinations' => 0,
                'highest_altitude' => 0,
                'journey_start' => null,
                'journey_end' => null,
            ],
            'map_points' => [],
            'story' => null,
            'has_data' => false,
        ];
    }

    /**
     * Clear cache for a user (call this when booking/scan changes).
     */
    public function clearCache(User $user): void
    {
        Cache::forget('journey_replay_' . $user->id);
    }
}
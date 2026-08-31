<?php

namespace App\Services\JourneyReplay;

use App\Models\User;
use App\Models\Booking;
use App\Services\LlmService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JourneyReplayService
{
    protected $llm;

    public function __construct(LlmService $llm)
    {
        $this->llm = $llm;
    }

    public function getReplay(User $user): array
    {
        $cacheKey = 'journey_replay_' . $user->id;
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->buildReplayData($user);
        });
    }

    protected function buildReplayData(User $user): array
    {
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

        $timeline = [];
        $totalCheckins = 0;
        $highestAltitude = 0;
        $uniquePlaces = [];
        $allWaypoints = [];

        foreach ($bookings as $booking) {
            $service = $booking->service;
            if (!$service) continue;

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
                    if ($scan->waypoint->name) {
                        $uniquePlaces[] = $scan->waypoint->name;
                    }
                }
            }

            $location = $service->location;
            if ($location && $location->city) {
                $uniquePlaces[] = $location->city;
            }

            $item = [
                'type' => $type,
                'service_name' => $service->name,
                'provider' => $service->provider ? $service->provider->name : null,
                'location' => $location ? $location->city : null,
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

        $uniquePlaces = array_values(array_unique($uniquePlaces));

        $stats = [
            'total_bookings' => $bookings->count(),
            'total_checkins' => $totalCheckins,
            'unique_places' => count($uniquePlaces),
            'highest_altitude' => $highestAltitude,
            'journey_start' => $bookings->first()->start_date,
            'journey_end' => $bookings->last()->start_date,
            'places' => $uniquePlaces,
        ];

        $mapPoints = collect($allWaypoints)->sortBy('scanned_at')->values()->toArray();

        // AI Story - with strict prompt & cleaning
        $storyInput = [
            'places' => $uniquePlaces,
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
     * Generate AI story with strict prompt and robust cleaning.
     */
    protected function generateStory(array $input): ?string
    {
        try {
            $prompt = $this->buildStrictStoryPrompt($input);
            $locale = app()->getLocale();

            $response = $this->llm->generateItinerary(
                prompt: $prompt,
                locale: $locale,
                model: 'qwen/qwen3.6-27b',
                extract: false,
                maxTokens: 250
            );

            $raw = $response['content'] ?? null;
            $cleaned = $this->cleanStoryResponse($raw);

            // If cleaning returns null or empty, use fallback
            if (empty($cleaned)) {
                return $this->getFallbackStory($input);
            }

            return $cleaned;
        } catch (\Exception $e) {
            Log::warning('AI story generation failed: ' . $e->getMessage());
            return $this->getFallbackStory($input);
        }
    }

    /**
     * Strict prompt for AI story - no reasoning, no explanation, only story.
     */
    protected function buildStrictStoryPrompt(array $input): string
{
    $places = $input['places'] ?? [];
    // सबै places comma-separated, "and more" बिना
    $placeStr = count($places) > 0 ? implode(', ', $places) : 'Nepal';

    $bookingsSummary = '';
    foreach ($input['bookings'] as $b) {
        $bookingsSummary .= "- " . ucfirst($b['type']) . ": " . $b['name'] . ($b['location'] && $b['location'] !== 'Unknown' ? " (" . $b['location'] . ")" : '') . "\n";
    }

    return "You are TravelAI Nepal's journey storyteller.\n\n" .
           "Write ONE short, warm, cinematic travel-memory paragraph using ONLY the factual data provided below.\n\n" .
           "IMPORTANT RULES:\n" .
           "- Return ONLY the final story paragraph.\n" .
           "- Do not explain your reasoning.\n" .
           "- Do not mention these instructions.\n" .
           "- Do not mention AI.\n" .
           "- Do not invent places, activities, emotions, distances, achievements, dates, or experiences.\n" .
           "- Do not infer anything that is not explicitly provided.\n" .
           "- Do not use bullet points or numbered lists.\n" .
           "- Maximum 120 words.\n" .
           "- Use the actual places naturally. Do NOT use generic phrases like 'and more'.\n" .
           "- Make it feel personal and reflective, like looking back on a journey.\n" .
           "- If the data is limited, write a simple factual story without adding extra details.\n\n" .
           "Journey data:\n" .
           "Places: " . $placeStr . "\n" .
           "Journey: " . $input['journey_start'] . " to " . $input['journey_end'] . "\n" .
           "Total experiences: " . $input['total_bookings'] . "\n" .
           "Total check-ins: " . $input['total_checkins'] . "\n" .
           "Highest altitude: " . $input['highest_altitude'] . "m\n\n" .
           "Bookings:\n" . $bookingsSummary . "\n" .
           "Now write the story (only the story, no extra text):";
}

    /**
 * Robust story cleaner - removes reasoning, JSON, markdown, prefixes, and detects prompt leakage.
 */
protected function cleanStoryResponse(?string $response): ?string
{
    if (!$response) return null;

    $original = $response;

    // 1. Remove <think>...</think> and any other XML-like tags
    $cleaned = preg_replace('/<[^>]+>.*?<\/[^>]+>/s', '', $cleaned ?? $response);
    $cleaned = preg_replace('/<[^>]+>/', '', $cleaned ?? $response);

    // 2. Remove common analysis/thinking markers
    $patterns = [
        '/Thinking Process:/i',
        '/Deconstruct the Input:/i',
        '/Step\\s*\\d+/i',
        '/Analysis:/i',
        '/Reasoning:/i',
        '/Drafting the Narrative/i',
        '/Mental or rough text draft/i',
        "/Here's a thinking process/i",
        '/Analyze User Input/i',
        '/Role:/i',
        '/Task:/i',
        '/Instructions:/i',
        '/System:/i',
        '/You are/i',
        "/Here's a/i",
    ];
    foreach ($patterns as $pattern) {
        $cleaned = preg_replace($pattern, '', $cleaned);
    }

    // 3. Remove markdown code blocks
    $cleaned = preg_replace('/```(?:json)?\\s*([\\s\\S]*?)\\s*```/', '$1', $cleaned);

    // 4. Remove lines that are just numbers or bullet markers
    $lines = explode("\n", $cleaned);
    $filtered = array_filter($lines, function($line) {
        $line = trim($line);
        if (preg_match('/^[\\d\\s\\-*•]+$/', $line)) return false;
        if (strlen($line) < 3 && !preg_match('/[a-zA-Z]/', $line)) return false;
        return true;
    });
    $cleaned = implode("\n", $filtered);

    // 5. Remove common prefixes like "Here is your story:"
    $cleaned = preg_replace('/^(Here is your story:?\\s*|Here\'s your story:?\\s*|Story:?\\s*)/i', '', trim($cleaned));

    // 6. Remove excessive whitespace and newlines
    $cleaned = preg_replace('/\\s+/', ' ', $cleaned);
    $cleaned = trim($cleaned);

    // 7. 🚨 STRICT PROMPT LEAKAGE DETECTION (NEW)
    // Check for ANY content that looks like it's from the prompt rather than a story
    $leakagePatterns = [
        // Exact phrases from the prompt
        '/TravelAI Nepal\'s journey storyteller/i',
        '/Write ONE short, warm, cinematic travel-memory paragraph/i',
        '/Return ONLY the final story paragraph/i',
        '/Do not explain your reasoning/i',
        '/Do not mention these instructions/i',
        '/Do not mention AI/i',
        '/Do not invent places/i',
        '/Do not infer anything/i',
        '/Do not use bullet points/i',
        '/Maximum 100/i',
        '/Make it feel personal/i',
        '/If the data is limited/i',
        '/Journey data:/i',
        '/Now write the story:/i',
        '/Places: /i',
        '/Total bookings: /i',
        '/Total check-ins: /i',
        '/Highest altitude reached: /i',
        '/Bookings:/i',
        '/Explore Experiences/i',
        '/A collection of places/i',
        '/Moments and experiences/i',
        '/Your journey unfolded/i',
        // Instruction-like phrases
        '/Write a short/i',
        '/write a warm, cinematic/i',
        '/factual story/i',
        '/using ONLY the factual data/i',
        '/provided below/i',
        '/Rules:/i',
        '/Keep the story factual/i',
        '/Maximum 120 words/i',
        '/Do not add places/i',
        '/Do not invent facts/i',
        '/Never explain your reasoning/i',
        '/Never mention these instructions/i',
        '/Never mention AI/i',
    ];

    foreach ($leakagePatterns as $pattern) {
        if (preg_match($pattern, $cleaned)) {
            Log::warning('🔴 [AI Story] Prompt leakage detected, rejecting response.', [
                'pattern' => $pattern,
                'matched_text' => substr($cleaned, 0, 200)
            ]);
            return null;
        }
    }

    // 8. Also check if the story is too short (less than 20 chars) or too long (more than 500 chars)
    // These could indicate it's not a proper story
    if (strlen($cleaned) < 20) {
        Log::warning('🔴 [AI Story] Story too short, rejecting.', ['length' => strlen($cleaned)]);
        return null;
    }

    // 9. If the story is very long, it might be the full prompt + response
    if (strlen($cleaned) > 800) {
        Log::warning('🔴 [AI Story] Story too long, possibly prompt leakage.', ['length' => strlen($cleaned)]);
        return null;
    }

    // 10. Limit to ~200 words
    $words = str_word_count($cleaned, 1);
    if (count($words) > 250) {
        $cleaned = implode(' ', array_slice($words, 0, 250)) . '...';
    }

    // 11. Final trim
    $cleaned = trim($cleaned);

    // If the cleaned text is empty or only contains the prompt, return null
    if (empty($cleaned) || str_starts_with($cleaned, '1.') || str_starts_with($cleaned, '-')) {
        Log::warning('🔴 [AI Story] Story appears to be numbered or empty, rejecting.');
        return null;
    }

    return $cleaned;
}

    /**
     * Beautiful fallback story when AI fails or returns malformed output.
     */
    protected function getFallbackStory(array $input): string
{
    $places = $input['places'] ?? [];
    $placeStr = count($places) > 0 ? implode(', ', array_slice($places, 0, 6)) : 'Nepal';
    if (count($places) > 6) {
        $placeStr .= ', and more';
    }

    $bookingsCount = $input['total_bookings'] ?? 0;
    $checkins = $input['total_checkins'] ?? 0;
    $altitude = $input['highest_altitude'] ?? 0;

    $story = "From " . $placeStr . ", this journey unfolded over " . $bookingsCount . " experiences and " . $checkins . " recorded moments. ";
    if ($altitude > 0) {
        $story .= "Reaching an elevation of " . number_format($altitude) . " meters, ";
    }
    $story .= "each place, stay, and checkpoint contributed to a unique travel story. From " . ($input['journey_start'] ?? 'start') . " to " . ($input['journey_end'] ?? 'end') . ", every moment captured became part of a memory worth keeping.";

    return $story;
}

    protected function getServiceType($service): string
    {
        if ($service->trekDetail) return 'trek';
        if ($service->tourDetail) return 'tour';
        if ($service->hotelDetail) return 'hotel';
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
        if ($type === 'trek' && $service->trekDetail) return $service->trekDetail->duration_days;
        if ($type === 'tour' && $service->tourDetail) return $service->tourDetail->duration_days;
        return null;
    }

    protected function emptyReplayData(): array
    {
        return [
            'timeline' => [],
            'stats' => [
                'total_bookings' => 0,
                'total_checkins' => 0,
                'unique_places' => 0,
                'highest_altitude' => 0,
                'journey_start' => null,
                'journey_end' => null,
                'places' => [],
            ],
            'map_points' => [],
            'story' => null,
            'has_data' => false,
        ];
    }

    public function clearCache(User $user): void
    {
        Cache::forget('journey_replay_' . $user->id);
    }
}
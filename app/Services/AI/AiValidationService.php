<?php

namespace App\Services\AI;

use App\Models\Waypoint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PHASE E1 — Narrative place validation for public AI Planner.
 *
 * R5 SAFE: reuses 4K patterns (cached waypoint list, Levenshtein tolerance)
 * for free-text narrative validation (different problem from 4K title validator).
 */
class AiValidationService
{
    /**
     * Verified place names (lowercase) from waypoints table.
     * Same cache key as 4K (waypoint_names_lc) — reuse.
     */
    public function getValidPlaces(): array
    {
        return Cache::remember('waypoint_names_lc', 3600, function () {
            return Waypoint::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->filter()
                ->values()
                ->toArray();
        });
    }

    /**
     * Generic geographic terms (not places, but acceptable in text).
     */
    public function getWhitelist(): array
    {
        return [
            'nepal', 'nepali', 'himalaya', 'himalayas', 'everest region',
            'annapurna region', 'khumbu', 'pokhara valley',
            'kathmandu valley', 'nepal himalaya', 'himalayan',
        ];
    }

    /**
     * Extract capitalized proper-noun candidates from free text.
     * Strategy: 1-3 capitalized words in a row, min length 4, stop-words filtered.
     */
    public function extractPlaceCandidates(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        // Match sequences of 1-3 capitalized words (proper nouns)
        preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2})\b/u', $text, $m);

        $stopWords = [
            'the', 'this', 'that', 'and', 'but', 'or', 'day', 'days',
            'morning', 'evening', 'night', 'afternoon', 'today', 'tomorrow',
            'trek', 'trekking', 'hike', 'hiking', 'walk', 'walking',
            'trail', 'route', 'view', 'views', 'mountain', 'mountains',
            'valley', 'region', 'village', 'villages', 'finally', 'then',
            'after', 'before', 'begin', 'beginning', 'continue', 'enjoy',
            'arrive', 'reach', 'leave', 'start', 'wake', 'wake up',
            'along', 'during', 'through', 'across', 'with', 'from',
            'your', 'you', 'your', 'their', 'this', 'that', 'these',
            'those', 'sometimes', 'often', 'gradually', 'shortly',
        ];

        $candidates = [];
        foreach ($m[1] as $candidate) {
            $candidate = trim($candidate);
            $lower = strtolower($candidate);
            if (mb_strlen($candidate) < 4) continue;
            if (in_array($lower, $stopWords, true)) continue;
            $candidates[] = $candidate;
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Return candidates NOT recognized as valid places.
     * Uses prefix + Levenshtein ≤ 2 tolerance (matches 4K logic).
     */
    public function findUnknownPlaces(array $candidates, array $extraWhitelist = []): array
    {
        $validPlaces = $this->getValidPlaces();
        $whitelist   = array_merge(
            $this->getWhitelist(),
            array_map('strtolower', $extraWhitelist)
        );
        $placeMap = array_flip($validPlaces);

        $unknowns = [];
        foreach ($candidates as $candidate) {
            $lower = strtolower(trim($candidate));
            if ($lower === '') continue;

            if (in_array($lower, $whitelist, true)) continue;
            if (isset($placeMap[$lower])) continue;

            // Prefix + Levenshtein fuzzy match
            $prefix = substr($lower, 0, 4);
            $found  = false;
            foreach ($placeMap as $valid => $idx) {
                if (str_starts_with($valid, $prefix)) {
                    if (levenshtein($lower, $valid) <= 2) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found) continue;

            $unknowns[] = $candidate;
        }

        return $unknowns;
    }

    /**
     * Extract route-specific place names from day titles ("X to Y").
     * Used for prompt injection — focused whitelist.
     */
    public function extractPlacesFromTitles(iterable $days): array
    {
        $places = [];
        foreach ($days as $day) {
            $title = trim((string) ($day->title ?? ''));
            if ($title === '') continue;

            // Try "X to Y" / "X → Y" / "X - Y" pattern
            if (preg_match('/^(.+?)\s+(?:to|→|–|—|-)\s+(.+)$/iu', $title, $m)) {
                $places[] = trim($m[1]);
                $places[] = trim($m[2]);
            } else {
                $places[] = $title;
            }
        }

        return array_values(array_unique(array_filter($places)));
    }
}
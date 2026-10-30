<?php

namespace App\Services\AI;

use App\Services\LlmService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * PHASE 5B — Hybrid narrative layer for public AI Travel Planner.
 *
 * R5 SAFE: PlannerService = READ-ONLY. This service only ENRICHES
 * the day descriptions in-place; all other fields (title, distance,
 * overnight_waypoint_id, items/costs) remain untouched.
 *
 * On any failure: original collection returned unchanged (silent fallback).
 */
class PlannerNarrativeService
{
    public function __construct(
        protected LlmService $llm,
        protected AiValidationService $validator,
    ) {}

    /**
     * Enrich a Collection of ItineraryDay models with LLM narratives.
     */
    public function enrichCollection(Collection $days, string $locale = 'en', ?string $routeName = null): Collection
    {
        if ($days->isEmpty() || !$this->isEnabled()) {
            return $days;
        }

        try {
            $routePlaces = $this->validator->extractPlacesFromTitles($days);
            $prompt      = $this->buildPrompt($days, $locale, $routePlaces);
            $result = $this->llm->generateItineraryParallel(
                prompt:      $prompt,
                locale:      $locale,
                model:       null,
                extract:     true,
                maxTokens:   $this->estimateMaxTokens($days->count()),
                temperature: 0.4,
            );

            $narratives = $this->extractNarratives($result, $days->count());
            if (empty($narratives)) {
                return $days;
            }

            $originals = $this->applyNarratives($days, $narratives);

            // E1: post-narrative place validation (log only, non-blocking)
            try {
                $this->validateNarratives($days, $routeName, $originals);
            } catch (\Throwable $e) {
                Log::warning('E1: Narrative validation failed (non-blocking)', [
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('PlannerNarrativeService: enrichment applied', [
                'days_count' => $days->count(),
                'locale'     => $locale,
                'narratives' => count($narratives),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PlannerNarrativeService: enrichment failed — using DB output', [
                'error' => $e->getMessage(),
                'days'  => $days->count(),
            ]);
        }

        return $days;
    }

    protected function isEnabled(): bool
    {
        return (bool) config('services.ai.planner_narrative_enabled', true);
    }

    protected function estimateMaxTokens(int $dayCount): int
    {
        // ~120 tokens per narrative + overhead, cap at 6000
        return (int) min(max($dayCount * 130, 800), 6000);
    }

    protected function buildPrompt(Collection $days, string $locale, array $routePlaces = []): string
    {
        $dayLines = '';
        foreach ($days as $day) {
            $dayNum = (int) ($day->day_number ?? 0);
            $title  = trim((string) ($day->title ?? ''));

            // Strip leading "Day N:" prefix if present (already provided via $dayNum)
            $title = preg_replace('/^\s*Day\s+' . preg_quote((string) $dayNum, '/') . '\s*[:\-–]\s*/iu', '', $title);
            $title = trim($title);

            $alt      = $day->altitude_m !== null ? " ({$day->altitude_m}m)" : '';
            $dist     = $day->distance_km !== null ? " {$day->distance_km}km" : '';
            $timeHrs  = $day->estimated_time_hours !== null ? " {$day->estimated_time_hours}h" : '';

            $dayLines .= "Day {$dayNum}: {$title}{$alt}{$dist}{$timeHrs}\n";
        }

        $localeInstruction = match ($locale) {
            'np'    => 'Write ALL descriptions in Nepali (Devanagari script).',
            'hi'    => 'Write ALL descriptions in Hindi (Devanagari script).',
            'zh'    => 'Write ALL descriptions in Simplified Chinese.',
            default => 'Write ALL descriptions in English.',
        };

        $verifiedPlacesBlock = '';
        if (!empty($routePlaces)) {
            $list = implode(', ', $routePlaces);
            $verifiedPlacesBlock = <<<BLOCK

VERIFIED PLACES (you may ONLY mention these in descriptions):
{$list}

If unsure about a place name, use generic terms like "the trail", "the region", "the village" instead of inventing names.
BLOCK;
        }

        return <<<PROMPT
You are a Nepal trekking narrative expert. Rewrite the day-by-day
descriptions for the itinerary below.

ITINERARY (Day | From→To | altitude | distance | time):
{$dayLines}
{$verifiedPlacesBlock}

RULES (STRICT):
1. Return ONE JSON object: {"days": [{ "day_number": N, "description": "..." }, ...]}
2. EXACTLY one entry per day above. No more, no less.
3. Each description: 2-3 sentences, vivid, atmospheric, factual.
4. Reference ONLY the "From→To" places shown above. Do NOT invent places.
5. Do NOT repeat distance/time numbers in the description (they are shown separately).
6. NEVER mention prices, currency, or money.
7. {$localeInstruction}
8. Output ONLY the JSON object. No markdown, no explanation, no preamble.

Now generate the JSON.
PROMPT;
    }

    /**
     * E1: Validate narratives for hallucinated place names (log only).
     */
    protected function validateNarratives(Collection $days, ?string $routeName, array $originals = []): void
    {
        $allText = $days->pluck('description')->filter()->implode(' ');
        if (trim($allText) === '') {
            return;
        }

        $candidates = $this->validator->extractPlaceCandidates($allText);
        if (empty($candidates)) {
            return;
        }

        $unknowns = $this->validator->findUnknownPlaces($candidates);

        if (!empty($unknowns)) {
            Log::info('E1: Narrative place validation', [
                'route'          => $routeName ?? 'unknown',
                'days_count'     => $days->count(),
                'unknown_count'  => count($unknowns),
                'unknown_sample' => array_slice($unknowns, 0, 3),
            ]);
        }

        // Bug 2 fix: repair narratives that mention places outside their day's route.
        if (empty($originals)) {
            return;
        }

        // Option B: only landmark waypoints (peak/viewpoint/pass/lake) trigger repair.
        // Villages, rivers, valleys, forests = allowed as contextual mentions.
        $allWpNames = \App\Models\Waypoint::whereNotNull('name')
            ->whereIn('type', ['peak', 'viewpoint', 'pass', 'lake'])
            ->pluck('name')
            ->filter(fn($n) => mb_strlen((string) $n) >= 4)
            ->values()
            ->toArray();

        $repaired = 0;
        foreach ($days as $day) {
            $n = (int) ($day->day_number ?? 0);
            if (!isset($originals[$n])) {
                continue;
            }
            $desc = (string) ($day->description ?? '');
            if ($desc === '') {
                continue;
            }

            $allowed = $this->extractTitlePlaces((string) ($day->title ?? ''));
            $hallucinated = $this->findHallucinatedPlaces($desc, $allowed, $allWpNames, $unknowns);

            if (!empty($hallucinated)) {
                $day->description = $originals[$n];
                $repaired++;
                Log::warning('Bug 2 fix: narrative repaired', [
                    'day'                 => $n,
                    'hallucinated_places' => $hallucinated,
                ]);
            }
        }

        if ($repaired > 0) {
            Log::info('Bug 2 fix: narratives repaired', [
                'count' => $repaired,
                'route' => $routeName ?? 'unknown',
            ]);
        }
    }

    protected function extractTitlePlaces(string $title): array
    {
        $t = preg_replace('/^\s*Day\s+\d+\s*[:\-–]\s*/iu', '', $title);
        $parts = preg_split('/\s*(?:→|->|–)\s*/u', (string) $t);
        return array_values(array_filter(array_map('trim', $parts ?? [])));
    }

    protected function findHallucinatedPlaces(string $desc, array $allowed, array $allWp, array $unknowns): array
    {
        $hallucinated = [];
        $allowedLower = array_map(fn($a) => strtolower(trim($a)), $allowed);
        $descLower = strtolower($desc);

        // Option B: $unknowns are logged in validateNarratives() but NOT used as
        // repair triggers. Legitimate geographic mentions (Bhote Koshi, Thame Valley,
        // rhododendron forests) that aren't in the waypoints table won't force repair.

        // Check: known typo aliases (catches "Kala Pattar" typo variants)
        // These are common misspellings that AI generates from training data.
        $typoAliases = [
            'kala pattar' => 'Kala Patthar',
            'kala patar'  => 'Kala Patthar',
            'khumbu icefal' => 'Khumbu Icefall',
        ];
        foreach ($typoAliases as $typo => $canonical) {
            if (stripos($desc, $typo) === false) continue;
            $canonicalLower = strtolower($canonical);
            $isAllowed = false;
            foreach ($allowedLower as $a) {
                if ($canonicalLower === $a
                    || strpos($a, $canonicalLower) !== false
                    || strpos($canonicalLower, $a) !== false) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                $hallucinated[] = 'typo:' . $canonical;
            }
        }

        // Check: landmark places that don't belong to this day's route
        foreach ($allWp as $wp) {
            $wpLower = strtolower((string) $wp);
            if ($wpLower === '' || strpos($descLower, $wpLower) === false) {
                continue;
            }
            $isAllowed = false;
            foreach ($allowedLower as $a) {
                if ($a === $wpLower || strpos($a, $wpLower) !== false || strpos($wpLower, $a) !== false) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                $hallucinated[] = 'wrong-day:' . $wp;
            }
        }

        return array_values(array_unique($hallucinated));
    }

    /**
     * @return array<int,string> day_number => description
     */
    protected function extractNarratives(array $llmResult, int $expectedCount): array
    {
        $rows = $llmResult['days'] ?? null;

        // If extractJson returned a raw string (unlikely — extract=true), bail
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $n   = (int) ($row['day_number'] ?? 0);
            $d   = trim((string) ($row['description'] ?? ''));
            if ($n > 0 && $d !== '') {
                $out[$n] = $d;
            }
        }

        // Sanity: require at least 60% of expected days
        if (count($out) < max(1, (int) ceil($expectedCount * 0.6))) {
            return [];
        }

        return $out;
    }

    protected function applyNarratives(Collection $days, array $narratives): array
    {
        $originals = [];
        foreach ($days as $day) {
            $n = (int) ($day->day_number ?? 0);
            if (isset($narratives[$n])) {
                $originals[$n] = $day->description;
                $day->description = $narratives[$n];
            }
        }
        return $originals;
    }
}
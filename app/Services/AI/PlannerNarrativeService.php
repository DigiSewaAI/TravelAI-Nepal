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
    public function __construct(protected LlmService $llm) {}

    /**
     * Enrich a Collection of ItineraryDay models with LLM narratives.
     */
    public function enrichCollection(Collection $days, string $locale = 'en'): Collection
    {
        if ($days->isEmpty() || !$this->isEnabled()) {
            return $days;
        }

        try {
            $prompt = $this->buildPrompt($days, $locale);
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

            $this->applyNarratives($days, $narratives);

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

    protected function buildPrompt(Collection $days, string $locale): string
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

        return <<<PROMPT
You are a Nepal trekking narrative expert. Rewrite the day-by-day
descriptions for the itinerary below.

ITINERARY (Day | From→To | altitude | distance | time):
{$dayLines}

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

    protected function applyNarratives(Collection $days, array $narratives): void
    {
        foreach ($days as $day) {
            $n = (int) ($day->day_number ?? 0);
            if (isset($narratives[$n])) {
                $day->description = $narratives[$n];
            }
        }
    }
}
<?php

namespace App\Http\Controllers\Provider;

use App\Exceptions\AiQuotaExceededException;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceItineraryDay;
use App\Models\ServiceItineraryItem;
use App\Services\AiReservationService;
use App\Services\LlmService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiItineraryDraftController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected LlmService $llm,
        protected AiReservationService $reservations,
    ) {}

    /**
     * PHASE X-01: Generate AI itinerary draft (preview only).
     *
     * - Quota-gated via AiReservationService::reserveForProvider()
     * - LLM call is OUTSIDE any DB transaction (FIX-12 pattern)
     * - Draft stored in session with 30-min TTL
     * - No DB writes to service_itinerary_days yet (apply is separate)
     */
    public function draft(Request $request, Service $service): JsonResponse
    {
        $start = microtime(true);
        $this->authorize('update', $service);

        $validated = $request->validate([
            'days'  => 'required|integer|min:1|max:21',
            'notes' => 'nullable|string|max:500',
        ]);

        $days = (int) $validated['days'];
        $notes = trim((string) ($validated['notes'] ?? ''));

        $provider = $service->provider;
        if (!$provider) {
            return response()->json([
                'error' => __('messages.ai_draft_error_generic'),
            ], 500);
        }

                // Phase 4G: token budget 300/day, floor 2000, cap 8000
        $adjustedMaxTokens = min(max($days * 300, 2000), 8000);

        // Derived prompt inputs (explicit fallback chain — Issue 3)
        $destination = $service->location?->name
                    ?? $service->category?->name
                    ?? 'Nepal';

        $difficulty = $service->trekDetail?->difficulty
                   ?? 'moderate';

        $duration = $service->trekDetail?->duration_days
                 ?? $service->tourDetail?->duration_days
                 ?? $days;

        $description = Str::limit((string) $service->description, 800, '…');

        // Idempotency key (Gap 1 — explicit generation)
        $idempotencyKey = AiReservationService::generateIdempotencyKey(
            'provider:' . $provider->id,
            'provider.ai.itinerary_draft',
            [
                'service_id' => $service->id,
                'days'       => $days,
                'notes_hash' => md5($notes),
                'ts_min'     => now()->format('YmdHi'),
            ]
        );

        // Step 1 — Quota reserve (short tx inside AiReservationService)
        $reservation = null;
        try {
            $reservation = $this->reservations->reserveForProvider(
                $provider,
                'provider.ai.itinerary_draft',
                $idempotencyKey
            );
        } catch (AiQuotaExceededException $e) {
            Log::info('AI draft quota exceeded', [
                'service_id'  => $service->id,
                'provider_id' => $provider->id,
            ]);
            return response()->json([
                'error' => __('messages.ai_draft_error_quota'),
            ], 429);
        } catch (\Throwable $e) {
            Log::error('AI draft reservation failed', [
                'service_id' => $service->id,
                'error_class' => get_class($e),
            ]);
            return response()->json([
                'error' => __('messages.ai_draft_error_generic'),
            ], 500);
        }

                // Step 2 — LLM call (OUTSIDE transaction)
        try {
            $validDraft = null;
            $lastError  = null;

            // Phase 4H: chunking for days > 5 (OTPM workaround)
            if ($days > 5) {
                $validDraft = $this->chunkAndGenerate(
                    $service,
                    $days,
                    $destination,
                    $difficulty,
                    $duration,
                    $description,
                    $notes
                );

                if ($validDraft === null) {
                    $lastError = 'Chunked generation failed';
                    Log::warning('Phase 4H chunking failed', [
                        'service_id' => $service->id,
                        'days'       => $days,
                    ]);
                }
            } else {
                // Single-call path (days ≤ 5) — Phase 4G logic
                $prompt = $this->buildPrompt(
                    $service, $days, $destination, $difficulty, $duration, $description, $notes
                );

                $maxAttempts = 2;
                $attempt     = 0;
                $adjustedMaxTokens = min(max($days * 300, 2000), 8000);

                while ($attempt < $maxAttempts) {
                    $attempt++;
                    try {
                        $candidate = $this->llm->generateItinerary(
                            prompt:      $prompt,
                            locale:      'en',
                            model:       'qwen/qwen3.8-27b',
                            extract:     true,
                            maxTokens:   $adjustedMaxTokens,
                            temperature: 0.5,
                        );

                        $this->validateDraftStructure($candidate, $days);
                        $validDraft = $candidate;
                        break;

                    } catch (\InvalidArgumentException $e) {
                        $lastError = $e->getMessage();
                        Log::info('AI draft quality fail', [
                            'attempt'    => $attempt,
                            'error'      => $lastError,
                            'service_id' => $service->id,
                        ]);
                        if ($attempt < $maxAttempts) sleep(5);

                    } catch (\Throwable $e) {
                        $lastError = $e->getMessage();
                        if (str_contains($lastError, 'rate_limit') ||
                            str_contains($lastError, 'Request too large') ||
                            str_contains($lastError, 'tokens per minute')) {
                            $this->reservations->release($reservation);
                            return response()->json([
                                'error' => __('messages.ai_draft_error_ratelimit'),
                            ], 429);
                        }
                        Log::error('AI draft unexpected fail', [
                            'attempt' => $attempt,
                            'error'   => $lastError,
                        ]);
                        if ($attempt < $maxAttempts) sleep(5);
                    }
                }
            }

            if ($validDraft === null) {
                try {
                    $this->reservations->release($reservation);
                } catch (\Throwable $releaseError) {
                    Log::error('AI draft quota release failed', [
                        'service_id' => $service->id,
                    ]);
                }

                return response()->json([
                    'error'  => __('messages.ai_draft_error_quality'),
                    'detail' => $lastError,
                ], 422);
            }

            $draft = $validDraft;

            // Step 4 — Session storage (30-min TTL)
            $draftId = (string) Str::uuid();
            session()->put("ai_draft:{$draftId}", [
                'service_id'  => $service->id,
                'provider_id' => $provider->id,
                'days'        => $draft['days'],
                'created_at'  => now()->timestamp,
            ]);

            // Step 5 — Finalize quota
            $this->reservations->finalize($reservation);

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            Log::info('AI draft generated', [
                'service_id'  => $service->id,
                'provider_id' => $provider->id,
                'days_count'  => count($draft['days']),
                'duration_ms' => $durationMs,
            ]);

            return response()->json([
                'draft_id'   => $draftId,
                'preview'    => $draft['days'],
                'days_count' => count($draft['days']),
            ]);

        } catch (\Throwable $e) {
            // Guaranteed quota release (Issue 4)
            try {
                $this->reservations->release($reservation);
            } catch (\Throwable $releaseError) {
                Log::error('AI draft quota release failed', [
                    'service_id'     => $service->id,
                    'reservation_id' => $reservation?->id,
                ]);
            }

            Log::warning('AI draft generation failed', [
                'service_id'  => $service->id,
                'provider_id' => $provider->id,
                'error_class' => get_class($e),
            ]);

            return response()->json([
                'error' => __('messages.ai_draft_error_regenerate'),
            ], 422);
        }
    }

    /**
     * PHASE X-01: Apply stored AI draft to service itinerary (append-only).
     *
     * - No LLM call → no throttle (Issue 5)
     * - Server-assigned day_number = MAX+1 (C1 binding)
     * - Waypoint IDs = NULL (R2 mitigation)
     * - Existing days untouched (R12 append-only)
     * - `itinerary_status` untouched (R10)
     */
    public function apply(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'draft_id' => 'required|string|uuid',
        ]);

        $draftKey = 'ai_draft:' . $validated['draft_id'];
        $draft = session()->get($draftKey);

        // Session expiry check (Issue 6 — 30 min TTL)
        if (!$draft) {
            return redirect()
                ->route('provider.services.itinerary.index', $service)
                ->withErrors(['ai_draft' => __('messages.ai_draft_error_expired')]);
        }

        $age = now()->timestamp - (int) ($draft['created_at'] ?? 0);
        if ($age > 1800) {
            session()->forget($draftKey);
            return redirect()
                ->route('provider.services.itinerary.index', $service)
                ->withErrors(['ai_draft' => __('messages.ai_draft_error_expired')]);
        }

        // Ownership validation
        if ((int) $draft['service_id'] !== $service->id
            || (int) $draft['provider_id'] !== $service->provider_id) {
            abort(403);
        }

        $insertedDays = 0;

        DB::transaction(function () use ($service, $draft, &$insertedDays) {
            // Lock service row (canonical order — same as ItineraryDayController)
            $locked = Service::where('id', $service->id)->lockForUpdate()->first();
            if (!$locked) {
                abort(404);
            }

            // Server-assigned start day_number (C1 binding)
            $startDay = (int) DB::table('service_itinerary_days')
                ->where('service_id', $locked->id)
                ->max('day_number') + 1;

            foreach ($draft['days'] as $dayIndex => $dayData) {
                $day = ServiceItineraryDay::create([
                    'service_id'            => $locked->id,
                    'day_number'            => $startDay + $dayIndex,
                    'title'                 => Str::limit((string) ($dayData['title'] ?? 'Untitled Day'), 255, ''),
                    'description'           => $dayData['description'] ?? null,
                    // Waypoint IDs intentionally NULL
                    'start_waypoint_id'     => null,
                    'end_waypoint_id'       => null,
                    'overnight_waypoint_id' => null,
                    'distance_km'           => $dayData['distance_km'] ?? null,
                    'estimated_time_hours'  => $dayData['estimated_time_hours'] ?? null,
                    'elevation_gain_m'      => null,
                    'elevation_loss_m'      => null,
                    'altitude_m'            => $dayData['altitude_m'] ?? null,
                    'meals_included'        => $dayData['meals_included'] ?? null,
                    'accommodation'         => $dayData['accommodation'] ?? null,
                ]);

                $items = $dayData['items'] ?? [];
                foreach ($items as $sortIndex => $itemData) {
                    ServiceItineraryItem::create([
                        'day_id'       => $day->id,
                        'title'        => Str::limit((string) ($itemData['title'] ?? 'Activity'), 255, ''),
                        'description'  => $itemData['description'] ?? null,
                        'time_of_day'  => $itemData['time_of_day'] ?? 'morning',
                        'sort_order'   => $sortIndex,
                        'is_optional'  => (bool) ($itemData['is_optional'] ?? false),
                        'metadata'     => null,
                    ]);
                }

                $insertedDays++;
            }

            session()->forget('ai_draft:' . request()->input('draft_id'));
        });

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', __('messages.ai_draft_applied', ['count' => $insertedDays]));
    }

            /**
     * Build enhanced LLM prompt with strict structural rules (Phase 4G).
     */
                private function buildPrompt(
        Service $service,
        int $days,
        string $destination,
        string $difficulty,
        int|string $duration,
        string $description,
        string $notes,
        ?int $startDay = null,
        ?int $endDay = null,
        ?int $totalDays = null,
        ?string $previousEndpoint = null,
        ?array $visitedEndpoints = null,
        ?string $journeyPhase = null,
        ?array $visitedTitles = null
    ): string {
        $notesLine  = $notes !== '' ? $notes : 'None';
        $shortDesc  = \Str::limit($description, 500, '');

        // Phase 4H: chunk context
        $chunkContext = '';
        if ($startDay !== null && $endDay !== null && $totalDays !== null) {
            $chunkContext = <<<CTX

═══════════════════════════════════════
CHUNK CONTEXT (CRITICAL)
═══════════════════════════════════════
This is days {$startDay} to {$endDay} of a {$totalDays}-day itinerary.
Generate ONLY these {$days} days.
Day numbers MUST be {$startDay} through {$endDay} (absolute).
CTX;
            if ($previousEndpoint !== null) {
                $chunkContext .= "\nPrevious day ended at: {$previousEndpoint}\n";
                $chunkContext .= "Day {$startDay} MUST start from {$previousEndpoint}\n";
            }
            $chunkContext .= "\n";
        }

                        // Phase 4H iter-3: overall journey context with full titles list
        $journeyContext = '';
        if (!empty($visitedEndpoints) && $journeyPhase !== null) {
            $visitedList   = implode(', ', $visitedEndpoints);
            $daysCompleted = $startDay - 1;

            $journeyContext = <<<JC

═══════════════════════════════════════
OVERALL JOURNEY CONTEXT (MANDATORY)
═══════════════════════════════════════
Days completed: 1-{$daysCompleted} of {$totalDays}
Places already visited: {$visitedList}
Current phase: {$journeyPhase}

CONSTRAINTS (NON-NEGOTIABLE):
  - Do NOT repeat any route from previous days
  - Do NOT re-visit: {$visitedList}
  - Follow {$journeyPhase} phase — do not restart ascent if in descend
  - If summit was already reached, continue descent ONLY
JC;
            $journeyContext .= "\n";

            // Phase 4H iter-3: explicit titles list
            if (!empty($visitedTitles)) {
                $titlesList = '';
                foreach ($visitedTitles as $idx => $title) {
                    $dayNum = $idx + 1;
                    $titlesList .= "  Day {$dayNum}: {$title}\n";
                }

                $journeyContext .= <<<TL

═══════════════════════════════════════
TITLES ALREADY GENERATED (DO NOT REPEAT)
═══════════════════════════════════════
{$titlesList}
RULE: Every title above is LOCKED. Any duplicate title in your output will be REJECTED.
You must generate ONLY new, unique routes.
TL;
                $journeyContext .= "\n";
            }
        }
        // Phase 4H: dynamic day-number rule
        $dayNumberRule = ($startDay !== null && $endDay !== null)
            ? "Day numbers MUST be {$startDay} through {$endDay}."
            : "Day numbers MUST be 1 through {$days} sequentially.";

        return <<<PROMPT
You are a Nepal trekking itinerary expert.

Create a realistic {$days}-day itinerary for:
Service: {$service->name}
Category: {$service->category?->name}
Region: {$destination}
Duration: {$duration} days
Difficulty: {$difficulty}

DESCRIPTION CONTEXT:
{$shortDesc}

Provider notes: {$notesLine}
{$chunkContext}{$journeyContext}
STRICT STRUCTURAL RULES (VIOLATION = REJECTED):

1. COUNT: EXACTLY {$days} days. No more. No less.
   {$dayNumberRule}

2. ANTI-REPETITION (MANDATORY):
   - NEVER repeat a route. "Place A to Place B" may appear ONLY ONCE.
   - Each day's title MUST be UNIQUE.
   - Each day must introduce a NEW destination or landmark.

3. GEOGRAPHIC CONTINUITY (MANDATORY):
   - Day N+1 title MUST start from Day N's endpoint.
   - Example: If Day 3 = "A to B", then Day 4 = "B to C".
   - NEVER restart from beginning.

4. PROGRESSION:
   - Progressive trek — no loops back to start.
   - Natural arc: approach → high point → return (if applicable).

5. GEOGRAPHIC ACCURACY:
   - Use ONLY places mentioned in description or region context.
   - Do NOT introduce places from other Nepal regions.
   - Altitude gain per day must be realistic (< 1000m/day typical).

6. OUTPUT FORMAT — Valid JSON only, no markdown:
{
  "days": [
    {
      "day_number": 1,
      "title": "Place A to Place B",
      "description": "1-3 sentence overview",
      "distance_km": 8.5,
      "estimated_time_hours": 5.0,
      "altitude_m": 2800,
      "accommodation": "Teahouse",
      "meals_included": ["B","L","D"],
      "items": [
        {
          "title": "Activity",
          "description": "Short description",
          "time_of_day": "morning",
          "is_optional": false
        }
      ]
    }
  ]
}

7. FINAL CHECK before output:
   - Count = {$days} exactly
   - All titles unique
   - Chain continuous (Day N end = Day N+1 start)
   - No markdown fences

Now generate the itinerary.
PROMPT;
    }
        /**
     * Phase 4H iter-3: Orchestrator with auto-retry on duplicate detection.
     * Tries full draft up to 2 times before giving up.
     */
    private function chunkAndGenerate(
        Service $service,
        int $totalDays,
        string $destination,
        string $difficulty,
        int|string $duration,
        string $description,
        string $notes,
        int $chunkSize = 3
    ): ?array {
        $maxDraftAttempts = 2;

        for ($draftAttempt = 1; $draftAttempt <= $maxDraftAttempts; $draftAttempt++) {
            $result = $this->generateAllChunks(
                $service,
                $totalDays,
                $destination,
                $difficulty,
                $duration,
                $description,
                $notes,
                $chunkSize
            );

            if ($result !== null) {
                return $result;
            }

            if ($draftAttempt < $maxDraftAttempts) {
                Log::info('Phase 4H full draft retry', [
                    'attempt'    => $draftAttempt,
                    'max'        => $maxDraftAttempts,
                    'service_id' => $service->id,
                ]);
                sleep(5);
            }
        }

        Log::warning('Phase 4H all draft attempts exhausted', [
            'service_id' => $service->id,
            'total_days' => $totalDays,
        ]);

        return null;
    }

    /**
     * Phase 4H iter-3: Core chunking with full context accumulation.
     * Tracks ALL endpoints + ALL titles across chunks.
     */
    private function generateAllChunks(
        Service $service,
        int $totalDays,
        string $destination,
        string $difficulty,
        int|string $duration,
        string $description,
        string $notes,
        int $chunkSize = 3
    ): ?array {
        $totalChunks      = (int) ceil($totalDays / $chunkSize);
        $allDays          = [];
        $previousEndpoint = null;
        $visitedEndpoints = [];
        $visitedTitles    = [];

        Log::info('Phase 4H chunking start', [
            'total_days'   => $totalDays,
            'total_chunks' => $totalChunks,
            'service_id'   => $service->id,
        ]);

        for ($i = 0; $i < $totalChunks; $i++) {
            $startDay  = $i * $chunkSize + 1;
            $endDay    = min(($i + 1) * $chunkSize, $totalDays);
            $chunkDays = $endDay - $startDay + 1;

            $progress     = $startDay / $totalDays;
            $journeyPhase = $progress < 0.4 ? 'ascend'
                          : ($progress < 0.7 ? 'summit' : 'descend');

            $chunkPrompt = $this->buildPrompt(
                $service,
                $chunkDays,
                $destination,
                $difficulty,
                $duration,
                $description,
                $notes,
                $startDay,
                $endDay,
                $totalDays,
                $previousEndpoint,
                $visitedEndpoints,
                $journeyPhase,
                $visitedTitles
            );

            $chunkResult = $this->generateChunkWithRetry(
                $chunkPrompt, $chunkDays, $startDay, $endDay
            );

            if ($chunkResult === null) {
                Log::warning('Phase 4H chunk failed', [
                    'chunk'      => $i + 1,
                    'total'      => $totalChunks,
                    'start_day'  => $startDay,
                    'end_day'    => $endDay,
                    'service_id' => $service->id,
                ]);
                return null;
            }

            // Phase 4H iter-3: track ALL endpoints + titles per day
            foreach ($chunkResult['days'] as $day) {
                $title = trim((string) ($day['title'] ?? ''));

                if ($title !== '' && !in_array($title, $visitedTitles, true)) {
                    $visitedTitles[] = $title;
                }

                $ep = $this->extractEndpoint($title);
                if ($ep !== '' && !in_array($ep, $visitedEndpoints, true)) {
                    $visitedEndpoints[] = $ep;
                }
            }

            $lastDay          = end($chunkResult['days']);
            $previousEndpoint = $this->extractEndpoint($lastDay['title'] ?? '');

            $allDays = array_merge($allDays, $chunkResult['days']);

            if ($i < $totalChunks - 1) {
                sleep(60);
            }
        }

        // Phase 4H: cross-chunk duplicate validation (safety net)
        $allTitlesLower = array_map(
            fn($d) => strtolower(trim($d['title'] ?? '')),
            $allDays
        );
        if (count($allTitlesLower) !== count(array_unique($allTitlesLower))) {
            Log::warning('Phase 4H cross-chunk duplicates detected', [
                'service_id' => $service->id,
                'total_days' => count($allDays),
            ]);
            return null;
        }

        Log::info('Phase 4H chunking complete', [
            'days_generated' => count($allDays),
            'visited_count'  => count($visitedEndpoints),
            'titles_count'   => count($visitedTitles),
            'service_id'     => $service->id,
        ]);

        return ['days' => $allDays];
    }

    /**
     * Phase 4H: Generate single chunk with retry.
     */
    private function generateChunkWithRetry(
        string $prompt,
        int $chunkDays,
        int $startDay,
        int $endDay
    ): ?array {
        $maxAttempts       = 2;
        $adjustedMaxTokens = max($chunkDays * 300, 900);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $candidate = $this->llm->generateItinerary(
                    prompt:      $prompt,
                    locale:      'en',
                    model:       'qwen/qwen3.8-27b',
                    extract:     true,
                    maxTokens:   $adjustedMaxTokens,
                    temperature: 0.5,
                );

                $this->validateChunkStructure($candidate, $startDay, $endDay);
                return $candidate;

            } catch (\InvalidArgumentException $e) {
                Log::info('Phase 4H chunk validation fail', [
                    'attempt'   => $attempt,
                    'start_day' => $startDay,
                    'end_day'   => $endDay,
                    'error'     => $e->getMessage(),
                ]);
                if ($attempt < $maxAttempts) sleep(5);

            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                Log::error('Phase 4H chunk error', [
                    'attempt'   => $attempt,
                    'start_day' => $startDay,
                    'error'     => $msg,
                ]);

                if (str_contains($msg, 'rate_limit') ||
                    str_contains($msg, 'Request too large') ||
                    str_contains($msg, 'tokens per minute')) {
                    return null;
                }
                if ($attempt < $maxAttempts) sleep(5);
            }
        }

        return null;
    }

    /**
     * Phase 4H: Extract endpoint location from title.
     */
    private function extractEndpoint(string $title): string
    {
        $title = trim($title);
        if (preg_match('/^.+?\s+to\s+(.+)$/i', $title, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^(.+?):/i', $title, $m)) {
            return trim($m[1]);
        }
        return $title;
    }

    /**
     * Phase 4H: Validate single chunk structure.
     */
    private function validateChunkStructure(array $chunk, int $startDay, int $endDay): void
    {
        if (!isset($chunk['days']) || !is_array($chunk['days']) || empty($chunk['days'])) {
            throw new \InvalidArgumentException('Missing days array');
        }

        $expectedCount = $endDay - $startDay + 1;
        if (count($chunk['days']) !== $expectedCount) {
            throw new \InvalidArgumentException(
                "Chunk count mismatch: expected {$expectedCount}, got " . count($chunk['days'])
            );
        }

        // Phase 4H: verify absolute day numbers
        foreach ($chunk['days'] as $idx => $day) {
            $expectedDayNum = $startDay + $idx;
            if ((int) ($day['day_number'] ?? 0) !== $expectedDayNum) {
                throw new \InvalidArgumentException(
                    "Chunk day_number mismatch at index {$idx}: expected {$expectedDayNum}"
                );
            }
        }

        $titles = array_map(fn($d) => strtolower(trim($d['title'] ?? '')), $chunk['days']);
        if (count($titles) !== count(array_unique($titles))) {
            throw new \InvalidArgumentException('Duplicate titles within chunk');
        }

        foreach ($chunk['days'] as $idx => $day) {
            if (!is_array($day)) {
                throw new \InvalidArgumentException("Chunk day {$idx} not array");
            }
            if (empty($day['title']) || !is_string($day['title']) || strlen($day['title']) > 255) {
                throw new \InvalidArgumentException("Chunk day {$idx} invalid title");
            }
            if (isset($day['items']) && !is_array($day['items'])) {
                throw new \InvalidArgumentException("Chunk day {$idx} items invalid");
            }
        }
    }

        /**
     * Validate LLM draft structure + Phase 4G quality checks.
     * Throws InvalidArgumentException on failure.
     */
    private function validateDraftStructure(array $draft, int $expectedCount = 0): void
    {
        if (!isset($draft['days']) || !is_array($draft['days']) || empty($draft['days'])) {
            throw new \InvalidArgumentException('Missing days array');
        }

        // Phase 4G — Count validation
        if ($expectedCount > 0 && count($draft['days']) !== $expectedCount) {
            throw new \InvalidArgumentException(
                "Day count mismatch: expected {$expectedCount}, got " . count($draft['days'])
            );
        }

        // Phase 4G — Duplicate title detection
        $titles = [];
        foreach ($draft['days'] as $day) {
            if (isset($day['title']) && is_string($day['title'])) {
                $titles[] = strtolower(trim($day['title']));
            }
        }
        if (count($titles) !== count(array_unique($titles))) {
            throw new \InvalidArgumentException('Duplicate day titles detected');
        }

        // Phase 4G — Basic geographic continuity check
        $dayCount = count($draft['days']);
        for ($i = 1; $i < $dayCount; $i++) {
            $prevTitle = $draft['days'][$i - 1]['title'] ?? '';
            $currTitle = $draft['days'][$i]['title']     ?? '';

            if (preg_match('/^(.+?)\s+to\s+(.+)$/i', $prevTitle, $m1) &&
                preg_match('/^(.+?)\s+to\s+(.+)$/i', $currTitle, $m2)) {

                $prevEnd   = strtolower(trim($m1[2]));
                $currStart = strtolower(trim($m2[1]));

                $similar = (str_contains($currStart, $prevEnd) ||
                            str_contains($prevEnd, $currStart) ||
                            levenshtein($prevEnd, $currStart) <= 3);

                if (!$similar) {
                    throw new \InvalidArgumentException(
                        "Day " . ($i + 1) . " does not continue from Day {$i} endpoint"
                    );
                }
            }
        }

        // Per-day structural checks (existing)
        foreach ($draft['days'] as $i => $day) {
            if (!is_array($day)) {
                throw new \InvalidArgumentException("Day {$i} is not an array");
            }
            if (empty($day['title']) || !is_string($day['title']) || strlen($day['title']) > 255) {
                throw new \InvalidArgumentException("Day {$i} title invalid");
            }
            if (isset($day['items']) && !is_array($day['items'])) {
                throw new \InvalidArgumentException("Day {$i} items invalid");
            }
            foreach ($day['items'] ?? [] as $j => $item) {
                if (empty($item['title']) || !is_string($item['title'])) {
                    throw new \InvalidArgumentException("Day {$i} item {$j} title invalid");
                }
                $tod = $item['time_of_day'] ?? 'morning';
                if (!in_array($tod, ['morning', 'afternoon', 'evening'], true)) {
                    throw new \InvalidArgumentException("Day {$i} item {$j} time_of_day invalid");
                }
            }
            if (isset($day['meals_included']) && is_array($day['meals_included'])) {
                foreach ($day['meals_included'] as $m) {
                    if (!in_array($m, ['B', 'L', 'D'], true)) {
                        throw new \InvalidArgumentException("Day {$i} meal invalid");
                    }
                }
            }
        }
    }
}
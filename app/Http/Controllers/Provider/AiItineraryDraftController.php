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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiItineraryDraftController extends Controller
{
    use AuthorizesRequests;

        public function __construct(
        protected LlmService $llm,
        protected AiReservationService $reservations,
        protected \App\Services\PlannerService $planner,
        ) {}

    /** @var array<int, array<int, array{from:string,to:string,distance:?string}>> */
    protected array $routeWaypointsCache = [];

    /**
     * Phase 4K: Fetch verified route waypoints for the service.
     * Returns empty array if no route match (fallback to generic prompt).
     */
    protected function fetchRouteWaypoints(Service $service): array
    {
        if (isset($this->routeWaypointsCache[$service->id])) {
            return $this->routeWaypointsCache[$service->id];
        }

        $waypoints = [];
        try {
            $route = $this->planner->resolveRouteForProvider($service->name);
            if ($route) {
                                $waypoints = $route->segments()
                    ->with(['fromWaypoint', 'toWaypoint'])
                    ->orderBy('sequence')
                    ->get()
                    ->map(fn($s) => [
                        'from'          => $s->fromWaypoint->name ?? 'Unknown',
                        'to'            => $s->toWaypoint->name   ?? 'Unknown',
                        'distance'      => $s->distance_km,
                        'time'          => $s->estimated_time_hours,
                        'altitude_from' => $s->fromWaypoint->altitude ?? null,
                        'altitude_to'   => $s->toWaypoint->altitude ?? null,
                    ])
                    ->toArray();

                Log::info('4K: Route matched for service', [
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'route_id'     => $route->id,
                    'route_name'   => $route->name,
                    'waypoints'    => count($waypoints),
                ]);
            } else {
                Log::info('4K: No route match — generic prompt', [
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('4K: Route lookup failed', [
                'service_id' => $service->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return $this->routeWaypointsCache[$service->id] = $waypoints;
    }

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
                            model:       null,   // 4K-F4d-fix: use provider pool models
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
                // 4K-F4c: Template fallback from route_segments (guaranteed output)
                $templateDays = $this->buildTemplateFromRoute($service, $days);
                if ($templateDays !== null) {
                    Log::info('4K-F4c: LLM failed, using template fallback', [
                        'service_id' => $service->id,
                        'days'       => count($templateDays),
                    ]);
                    $validDraft = ['days' => $templateDays];
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
            'update_service_duration' => 'nullable|boolean',
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
                    // Phase 4H iter-3: sanitize time_of_day (defensive)
                    $tod = $itemData['time_of_day'] ?? 'morning';
                    if (!in_array($tod, ['morning', 'afternoon', 'evening'], true)) {
                        $tod = 'morning';
                    }

                    ServiceItineraryItem::create([
                        'day_id'       => $day->id,
                        'title'        => Str::limit((string) ($itemData['title'] ?? 'Activity'), 255, ''),
                        'description'  => $itemData['description'] ?? null,
                        'time_of_day'  => $tod,
                        'sort_order'   => $sortIndex,
                        'is_optional'  => (bool) ($itemData['is_optional'] ?? false),
                        'metadata'     => null,
                    ]);
                }

                                $insertedDays++;
            }

            // 4K-F4: Update detail table duration if requested (category-aware)
            if ($request->boolean('update_service_duration', false) && $insertedDays > 0) {
                if ($service->trekDetail) {
                    $service->trekDetail->update(['duration_days' => $insertedDays]);
                } elseif ($service->tourDetail) {
                    $service->tourDetail->update(['duration_days' => $insertedDays]);
                }
            }

            session()->forget('ai_draft:' . request()->input('draft_id'));
        });

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', __('messages.ai_draft_applied', ['count' => $insertedDays]));
    }

        /**
     * 4K-F4c: Template fallback from route_segments DB.
     * Triggered when ALL LLM providers fail.
     * Ensures user ALWAYS gets a valid itinerary.
     */
    private function buildTemplateFromRoute(Service $service, int $days): ?array
    {
        $routeWaypoints = $this->fetchRouteWaypoints($service);
        if (empty($routeWaypoints)) {
            return null;   // No route = can't template
        }

        $segments = $routeWaypoints;
        $segmentsCount = count($segments);
        $result = [];
        $segmentIndex = 0;

        for ($dayNum = 1; $dayNum <= $days; $dayNum++) {
            if ($segmentIndex >= $segmentsCount) {
                $seg = $segments[$segmentsCount - 1];
                $title = $seg['to'] . ' — Rest / Exploration';
                $desc = 'A rest and exploration day at ' . $seg['to'] . '.';
                $distKm = 0.0;
                $timeHrs = 0.0;
                $alt = $seg['altitude_to'] ?? null;
                $fromName = $seg['from'] ?? '';
                $toName = $seg['to'] ?? '';
            } else {
                $seg = $segments[$segmentIndex];
                $from = $seg['from'] ?? '';
                $to = $seg['to'] ?? '';

                if ($from === $to) {
                    $title = $to . ' — Acclimatization';
                    $desc = 'Acclimatization day at ' . $to . '. Rest and explore the village.';
                } else {
                    $title = $from . ' to ' . $to;
                    $desc = 'Trek from ' . $from . ' to ' . $to . '.';
                }
                $distKm = (float) ($seg['distance'] ?? 0.0);
                $timeHrs = (float) ($seg['time'] ?? 0.0);
                $alt = $seg['altitude_to'] ?? null;
                $fromName = $from;
                $toName = $to;
                $segmentIndex++;
            }

            $result[] = [
                'day_number'           => $dayNum,
                'title'                => $title,
                'description'          => $desc,
                'distance_km'          => $distKm,
                'estimated_time_hours' => $timeHrs,
                'altitude_m'           => $alt,
                'accommodation'        => 'Teahouse',
                'meals_included'       => ['B', 'L', 'D'],
                'items'                => [
                    [
                        'title'        => (!empty($fromName) ? $fromName . ' Departure' : 'Morning Departure'),
                        'description'  => 'Start the day from ' . ($fromName !== '' ? $fromName : 'your location') . '.',
                        'time_of_day'  => 'morning',
                        'is_optional'  => false,
                    ],
                    [
                        'title'        => 'Arrival at ' . ($toName !== '' ? $toName : 'destination'),
                        'description'  => 'Reach ' . ($toName !== '' ? $toName : 'destination') . ' and rest for the night.',
                        'time_of_day'  => 'afternoon',
                        'is_optional'  => false,
                    ],
                ],
            ];
        }

        Log::warning('4K-F4c: Template fallback generated', [
            'service_id' => $service->id,
            'days'       => $days,
            'source'     => 'route_segments',
        ]);

        return $result;
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
        ?array $visitedTitles = null,
        array $routeWaypoints = []
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

🔴 DESCENT SECTIONS (NON-NEGOTIABLE):
- If this chunk is during DESCENT (after summit), DO NOT compress days.
- Descent sections ALSO require EXACTLY {$days} day objects.
- Each day = one distinct From→To waypoint pair.
- Do NOT merge descent days, even if narrative feels repetitive.
- Every day from {$startDay} through {$endDay} MUST appear in the array.
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

        // Phase 4K: build VERIFIED ROUTE block if route matched
        $verifiedRouteBlock = '';
        if (!empty($routeWaypoints)) {
            $seqLines = '';
            foreach ($routeWaypoints as $i => $s) {
                $seqLines .= ($i + 1) . ". {$s['from']} → {$s['to']}\n";
            }
            $verifiedRouteBlock = <<<VR

═══════════════════════════════════════
🔴 VERIFIED ROUTE (NON-NEGOTIABLE)
═══════════════════════════════════════
This is the OFFICIAL waypoint sequence for this trek.
You MUST use ONLY these waypoints, in this exact order.
Do NOT invent places, do NOT backtrack, do NOT skip any.
Every day title must be "From → To" using adjacent waypoints below.

{$seqLines}
VR;
            $verifiedRouteBlock .= "\n";
        }

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
{$chunkContext}{$journeyContext}{$verifiedRouteBlock}
STRICT STRUCTURAL RULES (VIOLATION = REJECTED):

1. COUNT: EXACTLY {$days} days. No more. No less.
   {$dayNumberRule}
   🔴 CRITICAL: Count your days array before finalizing.
   If you have fewer or more than {$days} days, REGENERATE.

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

5b. DESCRIPTION ACCURACY (MANDATORY):
   - Day description MUST reference ONLY the From/To waypoints in that day's title.
   - Do NOT mention ANY other place name in the description — even real Nepal locations.
   - Landmarks, cultural notes, and terrain features must be tied to the specific
     From/To villages. Use "the trail", "river valley", "ridge", or "forest" if unsure.
   - Do NOT introduce side-trek villages (e.g., Koto, Birethanti) that are NOT in
     the VERIFIED ROUTE sequence above. Doing so will cause rejection.

6. NO REASONING OUTPUT (CRITICAL):
   - Do NOT include reasoning, thinking, planning, or meta-commentary.
   - Do NOT write "Let me correct...", "However...", "Actually...", "Wait...".
   - If you realize an error mid-output, STOP and regenerate the full JSON.
   - Output ONLY the final JSON object. No explanations before/after.

7. SELF-CHECK BEFORE RESPONSE (MANDATORY):
   - Verify day count = EXACTLY {$days}.
   - Verify no duplicate titles.
   - Verify geographic continuity (Day N+1 starts from Day N endpoint).
   - If any check fails, regenerate internally before responding.

8. OUTPUT FORMAT — Valid JSON only, no markdown:
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
                // 4H-EXT: Wait 30s before retry (OTPM window reset)
                sleep(30);
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
                $visitedTitles,
                $this->fetchRouteWaypoints($service)
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
                sleep(55);  // 4K-F3b: increase 40s→55s (reduce rate limit cascade)
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

        // Phase 4K: Validate route adherence (if route matched)
        $routeWaypoints = $this->routeWaypointsCache[$service->id] ?? [];
        if (!empty($routeWaypoints)) {
            try {
                $this->validateRouteWaypoints($allDays, $routeWaypoints);
            } catch (\InvalidArgumentException $e) {
                Log::warning('4K: Route validation failed — retrying', [
                    'service_id' => $service->id,
                    'error'      => $e->getMessage(),
                ]);
                return null;  // triggers chunkAndGenerate retry
            }
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
                    model:       null,   // 4K-F4d-fix: use provider pool models
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
                if ($attempt < $maxAttempts) sleep(45);   // 4H-EXT-2: honor OTPM window

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
                    // 4J-Fix + 4K-F4b: honor OTPM window — sleep + retry
                    // BUT cap at 60s — longer waits = bad UX + PHP timeout risk
                    if ($attempt < $maxAttempts) {
                        $retryAfter = null;
                        if (preg_match('/retry_after=(\d+)/i', $msg, $m)) {
                            $retryAfter = (int) $m[1];
                        } elseif (preg_match('/wait (\d+) seconds/i', $msg, $m)) {
                            $retryAfter = (int) $m[1];
                        }

                        // 4K-F4b: Fail fast if wait > 60s (quota exhausted, not burst)
                        if ($retryAfter !== null && $retryAfter > 60) {
                            Log::warning('4K-F4b: rate_limit wait too long — failing fast', [
                                'attempt'     => $attempt,
                                'retry_after' => $retryAfter,
                            ]);
                            return null;
                        }

                        $waitSec = $retryAfter !== null ? max($retryAfter + 5, 30) : 60;
                        Log::info('4J-Fix: rate_limit retry', [
                            'attempt' => $attempt,
                            'wait'    => $waitSec,
                        ]);
                        sleep($waitSec);
                        continue;
                    }
                    return null;
                }
                if ($attempt < $maxAttempts) sleep(45);   // 4H-EXT-2: honor OTPM window
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
                        foreach ($day['items'] ?? [] as $j => $item) {
                if (empty($item['title']) || !is_string($item['title'])) {
                    throw new \InvalidArgumentException("Chunk day {$idx} item {$j} title invalid");
                }
                                // 4H-EXT-2: Sanitize (trim + lowercase) — do not reject
                $tod = strtolower(trim((string)($item['time_of_day'] ?? 'morning')));
                if (!in_array($tod, ['morning', 'afternoon', 'evening'], true)) {
                    $tod = 'morning';   // fallback (invalid → default)
                }
                $chunk['days'][$idx]['items'][$j]['time_of_day'] = $tod;
            }
        }
        }

    /**
     * Phase 4K: Post-generation route adherence validation.
     * Soft: 1 off-route day = warning. 2+ off-route = reject (retry).
     */
            private function validateRouteWaypoints(array $allDays, array $routeWaypoints): void
    {
        $validEndpoints = [];
        foreach ($routeWaypoints as $s) {
            $validEndpoints[] = strtolower(trim($s['from']));
            $validEndpoints[] = strtolower(trim($s['to']));
        }
        $validEndpoints = array_values(array_unique(array_filter($validEndpoints)));

        // 4K-F3: Load ALL active waypoint names once (for off-route detection)
        $allWaypoints = \Illuminate\Support\Facades\Cache::remember(
            'waypoint_names_lc_v2',
            3600,
            function () {
                return \App\Models\Waypoint::whereNull('deleted_at')
                    ->pluck('name')
                    ->map(fn($n) => strtolower(trim((string) $n)))
                    ->filter(fn($n) => strlen($n) >= 4)
                    ->unique()
                    ->values()
                    ->toArray();
            }
        );

        $offRoute = [];
        $routeContextMentions = [];   // 4K-F3c: route-endpoint context (soft only)
        $externalPlaces = [];          // 4K-F3c: real off-route (hard reject)
        foreach ($allDays as $i => $day) {
            $title = strtolower(trim($day['title'] ?? ''));
            if (!preg_match('/^(.+?)\s+to\s+(.+)$/i', $title, $m)) {
                continue;   // non "A to B" title = skip
            }
            $from = strtolower(trim($m[1]));
            $to   = strtolower(trim($m[2]));

            $onRoute = $this->endpointMatches($from, $validEndpoints)
                    || $this->endpointMatches($to,   $validEndpoints);

            if (!$onRoute) {
                $offRoute[] = $i + 1;
                continue;
            }

            // 4K-F3: description check (only when title is on route)
            $desc = strtolower(trim($day['description'] ?? ''));
            if ($desc === '') {
                continue;
            }

            $dayEndpoints = [$from, $to];
            $dayRouteContext = [];
            $dayExternal = [];

            // A) 4K-F3c: Route endpoints mentioned in other days → SOFT WARN only
            foreach ($validEndpoints as $vp) {
                if (in_array($vp, $dayEndpoints, true)) continue;
                if (preg_match('/\b' . preg_quote($vp, '/') . '\b/u', $desc)) {
                    $dayRouteContext[] = $vp;
                }
            }

            // B) 4K-F3: External places NOT on route → HARD REJECT at 2+
            foreach ($allWaypoints as $wp) {
                if (in_array($wp, $validEndpoints, true)) continue;   // on route = OK
                if (preg_match('/\b' . preg_quote($wp, '/') . '\b/u', $desc)) {
                    $dayExternal[] = $wp . '(off-route)';
                }
            }

            // HARD REJECT only on external places (not route endpoints)
            if (count($dayExternal) >= 2) {
                throw new \InvalidArgumentException(
                    "Day " . ($i + 1) . ": description mentions external off-route places: "
                    . implode(', ', $dayExternal)
                );
            }
            if (count($dayExternal) === 1) {
                $externalPlaces[] = ($i + 1) . ':' . $dayExternal[0];
            }

            // SOFT WARN for route-endpoint context mentions (never reject)
            if (!empty($dayRouteContext)) {
                $routeContextMentions[] = ($i + 1) . ':' . implode(',', $dayRouteContext);
            }
        }

        if (count($offRoute) >= 2) {
            throw new \InvalidArgumentException(
                'Days off-route: ' . implode(', ', $offRoute)
            );
        }
        if (count($offRoute) === 1) {
            Log::info('4K: Single off-route day (soft warn)', [
                'day' => $offRoute[0],
            ]);
        }
        if (!empty($externalPlaces)) {
            Log::info('4K-F3c: External off-route place in description (soft warn)', [
                'days' => $externalPlaces,
            ]);
        }
        if (!empty($routeContextMentions)) {
            Log::info('4K-F3c: Route-endpoint context mention (soft warn, no reject)', [
                'days' => $routeContextMentions,
            ]);
        }
    }

    /**
     * Phase 4K: fuzzy endpoint match (exact / prefix / levenshtein ≤ 2).
     */
    private function endpointMatches(string $needle, array $haystack): bool
    {
        if ($needle === '') return false;
        if (in_array($needle, $haystack, true)) return true;

        $prefix = substr($needle, 0, 4);
        foreach ($haystack as $valid) {
            if (str_starts_with($valid, $prefix) && levenshtein($needle, $valid) <= 2) {
                return true;
            }
        }
        return false;
    }

    /**
     * Phase 4K-F2: Layer 3 — Place existence validation.
     * Hard reject if 2+ unknown places in single day.
     * Soft warn if 1 unknown place (new place may be legit).
     */
    private function validatePlaceExistence(array $draft): void
    {
        $validPlaces = Cache::remember('waypoint_names_lc', 3600, function () {
            return \App\Models\Waypoint::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->filter()
                ->values()
                ->toArray();
        });

        $placeMap = array_flip($validPlaces);

        $whitelist = [
            'nepal', 'himalaya', 'himalayas', 'everest region',
            'annapurna region', 'khumbu', 'pokhara valley',
            'kathmandu valley', 'nepal himalaya',
        ];

        foreach ($draft['days'] as $i => $day) {
            $title = strtolower(trim($day['title'] ?? ''));
            if (!preg_match('/^(.+?)\s+to\s+(.+)$/i', $title, $m)) {
                continue;
            }

            $unknownCount = 0;
            $unknownPlaces = [];

            foreach ([$m[1], $m[2]] as $place) {
                $place = strtolower(trim($place));

                if (in_array($place, $whitelist, true)) continue;

                if (isset($placeMap[$place])) continue;

                $prefix = substr($place, 0, 4);
                $found = false;
                foreach ($placeMap as $valid => $idx) {
                    if (str_starts_with($valid, $prefix)) {
                        if (levenshtein($place, $valid) <= 2) {
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found) continue;

                $unknownCount++;
                $unknownPlaces[] = $place;
            }

            if ($unknownCount >= 2) {
                Log::warning('4K-F2: Multiple unknown places — likely hallucination', [
                    'day'      => $i + 1,
                    'title'    => $day['title'],
                    'unknowns' => $unknownPlaces,
                ]);
                throw new \InvalidArgumentException(
                    "Day " . ($i + 1) . ": multiple unknown places (" . implode(', ', $unknownPlaces) . ")"
                );
            }

            if ($unknownCount === 1) {
                Log::info('4K-F2: Single unknown place — soft warn (may be legit new place)', [
                    'day'     => $i + 1,
                    'title'   => $day['title'],
                    'unknown' => $unknownPlaces[0],
                ]);
            }
        }
    }

    /**
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
                                // 4H-EXT-2: Sanitize (trim + lowercase) — do not reject
                $tod = strtolower(trim((string)($item['time_of_day'] ?? 'morning')));
                if (!in_array($tod, ['morning', 'afternoon', 'evening'], true)) {
                    $tod = 'morning';   // fallback (invalid → default)
                }
                $draft['days'][$i]['items'][$j]['time_of_day'] = $tod;
            }
                        if (isset($day['meals_included']) && is_array($day['meals_included'])) {
                foreach ($day['meals_included'] as $m) {
                    if (!in_array($m, ['B', 'L', 'D'], true)) {
                        throw new \InvalidArgumentException("Day {$i} meal invalid");
                    }
                }
            }
        }

        // Phase 4K-F2: Layer 3 — place existence check
        $this->validatePlaceExistence($draft);
    }
}
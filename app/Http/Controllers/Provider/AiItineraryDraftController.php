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

        // Dynamic token budget: 200/day, floor 1500, cap 4000
        $maxTokens = min(max($days * 200, 1500), 4000);

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
            $prompt = $this->buildPrompt(
                $service, $days, $destination, $difficulty, $duration, $description, $notes
            );

            $draft = $this->llm->generateItinerary(
                prompt: $prompt,
                locale: 'en',
                model: null,
                extract: true,
                maxTokens: $maxTokens,
            );

            // Step 3 — Structure validation
            $this->validateDraftStructure($draft);

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
     * Build LLM prompt (explicit fallback chain).
     */
    private function buildPrompt(
        Service $service,
        int $days,
        string $destination,
        string $difficulty,
        int|string $duration,
        string $description,
        string $notes
    ): string {
        $notesLine = $notes !== '' ? $notes : 'None';

                return <<<PROMPT
Create a {$days}-day itinerary for: {$service->name}
Category: {$service->category?->name}
Destination: {$destination}
Duration: {$duration} days
Difficulty: {$difficulty}

SERVICE DESCRIPTION (geographic source of truth):
{$description}

Provider notes: {$notesLine}

GEOGRAPHIC RULE (critical):
- Use ONLY places mentioned in the description above
- Do NOT add places from other Nepal regions (e.g., no Everest area unless mentioned)
- Day titles should be "Place A to Place B" format

JSON output:
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

Rules: exactly {$days} days, day_number 1..{$days}, no markdown.
PROMPT;
    }

    /**
     * Validate LLM structure. Throws InvalidArgumentException on failure.
     */
    private function validateDraftStructure(array $draft): void
    {
        if (!isset($draft['days']) || !is_array($draft['days']) || empty($draft['days'])) {
            throw new \InvalidArgumentException('Missing days array');
        }

        foreach ($draft['days'] as $i => $day) {
            if (!is_array($day)) {
                throw new \InvalidArgumentException("Day {$i} not array");
            }
            if (empty($day['title']) || !is_string($day['title']) || strlen($day['title']) > 255) {
                throw new \InvalidArgumentException("Day {$i} has invalid title");
            }
            if (isset($day['items']) && !is_array($day['items'])) {
                throw new \InvalidArgumentException("Day {$i} items not array");
            }
            foreach ($day['items'] ?? [] as $j => $item) {
                if (empty($item['title']) || !is_string($item['title'])) {
                    throw new \InvalidArgumentException("Day {$i} item {$j} invalid title");
                }
                $tod = $item['time_of_day'] ?? 'morning';
                if (!in_array($tod, ['morning', 'afternoon', 'evening'], true)) {
                    throw new \InvalidArgumentException("Day {$i} item {$j} invalid time_of_day");
                }
            }
            if (isset($day['meals_included']) && is_array($day['meals_included'])) {
                foreach ($day['meals_included'] as $m) {
                    if (!in_array($m, ['B', 'L', 'D'], true)) {
                        throw new \InvalidArgumentException("Day {$i} invalid meal: {$m}");
                    }
                }
            }
        }
    }
}
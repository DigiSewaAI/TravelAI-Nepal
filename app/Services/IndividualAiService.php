<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IndividualAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Guide Matching — match upcoming bookings to best-fit guide.
     */
    public function guideMatching(Provider $provider): array
    {
        $reservation = null;
        try {
            $guides = DB::table('provider_staff')->where('provider_id', $provider->id)->get();
            if ($guides->isEmpty()) return ['error' => 'no_guides', 'message' => 'Add guides first.'];

            $bookings = DB::table('individual_bookings')->where('provider_id', $provider->id)->get();
            $upcoming = $bookings->where('start_date', '>=', now()->format('Y-m-d'))->sortBy('start_date');

            $guideStats = [];
            foreach ($guides as $g) {
                $guideStats[] = [
                    'user_id' => (int) $g->user_id,
                    'skills' => (string) ($g->skills ?? ''),
                    'languages' => (string) ($g->languages ?? ''),
                    'experience_years' => (int) ($g->experience_years ?? 0),
                    'availability' => (string) ($g->availability ?? 'available'),
                ];
            }

            // ChatGPT fix — real scheduling: date overlap + duration + pax capacity
            $guideActiveWindows = []; // [userId => [[start, end], ...]]
            foreach ($guides as $g) $guideActiveWindows[$g->user_id] = [];

            $matches = [];
            foreach ($upcoming as $b) {
                $durStr = (string) $b->duration; // e.g. "14 days"
                preg_match('/(\d+)/', $durStr, $dm);
                $durationDays = max(1, (int) ($dm[1] ?? 1));
                $startDate = substr((string) $b->start_date, 0, 10);
                $endDate = date('Y-m-d', strtotime($startDate . ' +' . ($durationDays - 1) . ' days'));
                $pax = (int) $b->pax;

                $bestGuide = null;
                $bestScore = -999;
                $conflictGuides = [];
                foreach ($guides as $g) {
                    if ($g->availability !== 'available') continue;
                    // Check date overlap with this guide's existing assignments
                    $hasOverlap = false;
                    foreach ($guideActiveWindows[$g->user_id] ?? [] as $win) {
                        if (!($endDate < $win[0] || $startDate > $win[1])) { $hasOverlap = true; break; }
                    }
                    if ($hasOverlap) { $conflictGuides[] = $g->user_id; continue; }

                    $score = 0;
                    if (stripos((string) $g->skills, 'trekking') !== false && stripos((string) $b->title, 'trek') !== false) $score += 2;
                    $score += min(5, (int) ($g->experience_years ?? 0) / 3);
                    $score -= count($guideActiveWindows[$g->user_id] ?? []) * 3; // load balance
                    if ($score > $bestScore) { $bestScore = $score; $bestGuide = $g; }
                }

                $assignedId = $bestGuide ? (int) $bestGuide->user_id : null;
                if ($assignedId) {
                    $guideActiveWindows[$assignedId][] = [$startDate, $endDate];
                }
                $matches[] = [
                    'booking' => (string) $b->title,
                    'client' => (string) $b->client_name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_days' => $durationDays,
                    'pax' => $pax,
                    'suggested_guide_user_id' => $assignedId,
                    'date_overlap_conflicts' => $conflictGuides,
                    'reason' => $bestGuide
                        ? sprintf('Assigned: %s→%s (%dd, %d pax) · %dy exp%s',
                            $startDate, $endDate, $durationDays, $pax, (int) ($bestGuide->experience_years ?? 0),
                            !empty($conflictGuides) ? ' · ' . count($conflictGuides) . ' guide(s) skipped (date conflict)' : '')
                        : 'Unassigned — all available guides have date conflicts',
                ];
            }

            // Real demand-vs-supply: only count bookings that couldn't be assigned
            $unassigned = count(array_filter($matches, fn($m) => $m['suggested_guide_user_id'] === null));

            // Supply demand analysis
            $totalGuides = count($guideStats);
            $availableGuides = count(array_filter($guideStats, fn($g) => $g['availability'] === 'available'));
            $warn = $unassigned > 0
                ? sprintf('⚠️ %d booking(s) cannot be scheduled (guide date conflicts) — consider hiring.', $unassigned)
                : (count($matches) > 0 ? 'All bookings assigned with date-compatible guides. No hiring needed for these specific dates.' : null);

            $reservation = $this->aiLimit->reserve($provider, 'individual.guide_matching', []);
            if (!$reservation) {
                return ['guides' => $guideStats, 'matches' => $matches,
                    'summary' => 'Deterministic baseline (quota reached).', 'data_warning' => $warn, 'fallback' => true];
            }

            $prompt = "You are a trekking agency dispatcher.\n"
                . "Guides: " . json_encode($guideStats) . "\n"
                . "Upcoming bookings: " . json_encode($matches) . "\n\n"
                . "Provide ONE summary sentence on guide allocation & any hiring need. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1500, 0.3, true);
            $data = $this->parseJson($raw);

            $detSummary = sprintf('%d guides (%d available) · %d upcoming bookings %s.',
                $totalGuides, $availableGuides, count($matches),
                $unassigned > 0 ? "({$unassigned} unassigned)" : 'all assigned by date-compatible guides'
            );

            // ChatGPT fix — only append AI summary if unassigned exists (avoid contradiction)
            if ($unassigned > 0 && $data && !empty($data['summary'])) {
                $detSummary .= ' ' . $data['summary'];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'guides' => $guideStats,
                'matches' => $matches,
                'summary' => $detSummary,
                'data_warning' => $warn,
                'assumptions' => ['match_rule' => 'Date-overlap aware (duration included), load-balanced across compatible guides', 'capacity_note' => 'Guide max 6 pax recommended (not enforced — schema has no cap)', 'availability' => 'Only available guides considered'],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('IndividualAiService guideMatching failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Guide matching unavailable.'];
        }
    }

    /**
     * Profile Builder — generate professional bio for each guide.
     */
    public function profileBuilder(Provider $provider): array
    {
        $reservation = null;
        try {
            $guides = DB::table('provider_staff')->where('provider_id', $provider->id)->get();
            if ($guides->isEmpty()) return ['error' => 'no_guides', 'message' => 'Add guides first.'];

            $profiles = [];
            foreach ($guides as $g) {
                $user = DB::table('users')->where('id', $g->user_id)->first();
                $profiles[] = [
                    'name' => (string) ($user->name ?? 'Guide'),
                    'skills' => (string) ($g->skills ?? ''),
                    'languages' => (string) ($g->languages ?? ''),
                    'experience_years' => (int) ($g->experience_years ?? 0),
                    'current_bio' => (string) ($g->bio ?? ''),
                ];
            }

            $reservation = $this->aiLimit->reserve($provider, 'individual.profile_builder', []);
            if (!$reservation) {
                return ['profiles' => $profiles, 'summary' => 'Deterministic baseline (quota reached).', 'fallback' => true];
            }

            $prompt = "You are a professional bio writer for trekking guides.\n"
                . "Guides: " . json_encode($profiles) . "\n\n"
                . "For each guide, write a 2-3 sentence professional bio (max 60 words). "
                . "Highlight: skills, languages, experience, specialization. NEVER mention certifications (IFMGA, NMA, etc.) or specific places (Kathmandu Valley, Everest region, Annapurna region) unless explicitly listed in guide's skills. Use ONLY the skills/languages/experience from data.\n"
                . "Return JSON: {\"profiles\":[{\"name\":\"..\",\"bio\":\"..\"}],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2000, 0.4, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['profiles'])) {
                $this->aiLimit->release($reservation);
                return ['profiles' => $profiles, 'summary' => 'Baseline (AI failed).', 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'profiles' => $data['profiles'],
                'summary' => (string) ($data['summary'] ?? 'Profiles ready.'),
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('IndividualAiService profileBuilder failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Profile builder unavailable.'];
        }
    }

    protected function parseJson(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}
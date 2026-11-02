<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * HotelAiService — Occupancy Forecast + Room Pricing Assistant.
 *
 * Data-source agnostic: works on seed data (demo) AND real data later.
 * Reuses LlmService + AiLimitService + existing ai_usage/ai_reservations.
 * No new tables.
 */
class HotelAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Predict occupancy for next $days.
     */
    public function occupancyForecast(Provider $provider, int $days = 7): array
    {
        $reservation = null;
        try {
            $history = HotelBooking::where('provider_id', $provider->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->groupBy('d')->orderBy('d')->get()
                ->map(fn($r) => ['date' => $r->d, 'count' => (int) $r->c])
                ->toArray();

            if (empty($history)) {
                return ['error' => 'no_history', 'message' => 'Not enough booking history to forecast.'];
            }

            $reservation = $this->aiLimit->reserve($provider, 'hotel.occupancy_forecast', ['days' => $days]);
            if (!$reservation) {
                return ['error' => 'quota_exceeded', 'message' => 'AI quota reached for this month.'];
            }

            $prompt = "Analyze hotel daily booking counts (last 30 days):\n" . json_encode($history)
                    . "\n\nPredict next {$days} days. Return STRICT JSON only:\n"
                    . '{"days":[{"date":"YYYY-MM-DD","predicted_bookings":N,"confidence":"low|med|high"}],'
                    . '"avg_rate":N,"summary":"one sentence"}';

            $raw = $this->llm->generateRawTextPooled(
                $prompt,
                'You are a hotel analytics assistant. Return only valid JSON.',
                null,
                3000,
                0.7,
                true
            );

            $data = $this->parseJson($raw);
            if (!$data) {
                $this->aiLimit->release($reservation);
                return ['error' => 'parse_failed', 'message' => 'AI returned invalid format.'];
            }

            $this->aiLimit->finalize($reservation);

            return [
                'days'     => $data['days'] ?? [],
                'avg_rate' => (int) ($data['avg_rate'] ?? 0),
                'summary'  => (string) ($data['summary'] ?? ''),
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('HotelAiService occupancyForecast failed', ['provider_id' => $provider->id, 'error' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Forecast temporarily unavailable.'];
        }
    }

    /**
     * Suggest pricing per room type.
     */
    public function pricingSuggestions(Provider $provider): array
    {
        $reservation = null;
        try {
            $rooms = HotelRoom::where('provider_id', $provider->id)->get();
            if ($rooms->isEmpty()) {
                return ['error' => 'no_rooms', 'message' => 'Add rooms first.'];
            }

            $stats = $rooms->map(function ($room) use ($provider) {
                $b = HotelBooking::where('provider_id', $provider->id)->where('room_id', $room->id)->get();
                $c = $b->count();
                return [
                    'room_number' => $room->room_number,
                    'room_type'   => $room->room_type,
                    'bookings'    => $c,
                    'avg_rate'    => $c > 0 ? (int) round((float) $b->sum('amount') / $c) : 0,
                    'nightly_rate' => (int) $b->sum('nights') > 0 ? (int) round((float) $b->sum('amount') / (int) $b->sum('nights')) : 0,
                ];
            })->values()->toArray();

            $reservation = $this->aiLimit->reserve($provider, 'hotel.pricing_suggestions', []);
            if (!$reservation) {
                return ['error' => 'quota_exceeded', 'message' => 'AI quota reached for this month.'];
            }

            $prompt = "Hotel room performance:\n" . json_encode($stats)
                    . "\n\nSuggest per-room NIGHTLY rate in NPR (per night, not total booking). Return STRICT JSON only:\n"
                    . '{"rooms":[{"room_number":"...","current_rate":N,"suggested_rate":N,"reason":"short"}],'
                    . '"summary":"one sentence"}';

            $raw = $this->llm->generateRawTextPooled(
                $prompt,
                'You are a hotel pricing assistant. Return only valid JSON.',
                null,
                3000,
                0.7,
                true
            );

            $data = $this->parseJson($raw);
            if (!$data) {
                $this->aiLimit->release($reservation);
                return ['error' => 'parse_failed', 'message' => 'AI returned invalid format.'];
            }

            $this->aiLimit->finalize($reservation);

            return [
                'rooms'   => $data['rooms'] ?? [],
                'summary' => (string) ($data['summary'] ?? ''),
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('HotelAiService pricingSuggestions failed', ['provider_id' => $provider->id, 'error' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Pricing suggestion temporarily unavailable.'];
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
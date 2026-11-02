<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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
            $history = DB::table('hotel_bookings')->where('provider_id', $provider->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->select('check_in_date', DB::raw('COUNT(*) cnt'))->groupBy('check_in_date')
                ->get()->map(fn($r) => ['date' => $r->check_in_date, 'count' => (int) $r->cnt])->toArray();

            $future = DB::table('hotel_bookings')->where('provider_id', $provider->id)
                ->where('check_in_date', '>', now()->format('Y-m-d'))
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->select('check_in_date', DB::raw('COUNT(*) cnt'))->groupBy('check_in_date')
                ->get()->map(fn($r) => ['date' => $r->check_in_date, 'count' => (int) $r->cnt])->toArray();

            $weekday = array_fill(0, 7, 0);
            foreach ($history as $h) { $weekday[\Carbon\Carbon::parse($h['date'])->dayOfWeek] += $h['count']; }

            $totalRooms = DB::table('hotel_rooms')->where('provider_id', $provider->id)->count();
            $todayOcc = DB::table('hotel_bookings')->where('provider_id', $provider->id)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->where('check_in_date', '<=', now()->format('Y-m-d'))
                ->where('check_out_date', '>=', now()->format('Y-m-d'))->count();

            $totalBookings = array_sum(array_column($history, 'count'));
            $warn = $totalBookings < 10 ? "Sample too small ({$totalBookings} bookings in 30 days) — forecast is low-confidence." : null;

            if (empty($history) && empty($future)) {
                return ['error'=>'no_history','message'=>'Not enough history.','data_warning'=>$warn,
                    'total_rooms'=>$totalRooms,'current_occupancy_pct'=>$totalRooms>0?(int)round($todayOcc/$totalRooms*100):0];
            }

            $reservation = $this->aiLimit->reserve($provider, 'hotel.occupancy_forecast', ['days'=>$days]);
            if (!$reservation) return ['error'=>'quota_exceeded','message'=>'AI quota reached.'];

            $prompt = "Hotel occupancy forecast. {$totalRooms} rooms, currently {$todayOcc} occupied.\n"
                . "History(30d): " . json_encode($history) . "\n"
                . "Future confirmed: " . json_encode($future) . "\n"
                . "Weekday 0=Sun..6=Sat: " . json_encode($weekday) . "\n\n"
                . "Today is 2026-11-02. START forecast from tomorrow (2026-11-03) for {$days} consecutive days. Do NOT use past dates. " . "Per day return: date(YYYY-MM-DD starting tomorrow), predicted_bookings(int), predicted_occupancy_pct(int 0-100), confidence(low/med/high). "
                . "Return JSON: {\"days\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2000, 0.3, true);
            $data = $this->parseJson($raw);
            if (!$data) {
                $this->aiLimit->release($reservation);
                return $this->fallbackOccupancy($provider, $totalRooms, $todayOcc, $warn);
            }
            $this->aiLimit->finalize($reservation);

            // FIX #2 — enforce low confidence if insufficient data
            if ($totalBookings < 10) {
                foreach (($data['days'] ?? []) as &$d) {
                    $d['confidence'] = 'low';
                }
                unset($d);
            }

            return ['days'=>$data['days']??[], 'summary'=>(string)($data['summary']??''),
                'data_warning'=>$warn, 'total_rooms'=>$totalRooms,
                'current_occupancy_pct'=>$totalRooms>0?(int)round($todayOcc/$totalRooms*100):0,
                'booking_semantics'=>'1 booking = 1 room (multi-room bookings not supported)'];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('occupancyForecast failed', ['pid'=>$provider->id,'err'=>$e->getMessage()]);
            $tr = DB::table('hotel_rooms')->where('provider_id', $provider->id)->count();
            $to = DB::table('hotel_bookings')->where('provider_id', $provider->id)
                ->whereIn('status', ['confirmed','checked_in'])
                ->where('check_in_date','<=',now()->format('Y-m-d'))
                ->where('check_out_date','>=',now()->format('Y-m-d'))->count();
            return $this->fallbackOccupancy($provider, $tr, $to, 'AI unavailable — baseline.');
        }
    }

    /**
     * Suggest pricing per room type.
     */
    public function pricingSuggestions(Provider $provider): array
    {
        $reservation = null;
        try {
            $rooms = DB::table('hotel_rooms')->where('provider_id', $provider->id)
                ->select('id','room_number','room_type','base_price')->get();
            if ($rooms->isEmpty()) return ['error'=>'no_rooms','message'=>'Add rooms first.'];

            $stats = [];
            foreach ($rooms as $room) {
                $b = DB::table('hotel_bookings')->where('provider_id', $provider->id)->where('room_id', $room->id)
                    ->select(DB::raw('COUNT(*) total'), DB::raw('SUM(amount) revenue'), DB::raw('SUM(nights) nights_sum'))->first();
                $stats[] = ['room_number'=>(string)$room->room_number,'room_type'=>(string)$room->room_type,
                    'base_price'=>(float)($room->base_price??0),'total_bookings'=>(int)($b->total??0),
                    'revenue'=>(float)($b->revenue??0),'nights_sum'=>(int)($b->nights_sum??0),'per_night'=>($b->nights_sum??0)>0?(int)round(($b->revenue??0)/($b->nights_sum??1)):(int)($room->base_price??0)];
            }

            $warn = count(array_filter(array_column($stats,'total_bookings'))) === 0
                ? 'No booking history — suggestions based on base_price + room_type only.' : null;

            $reservation = $this->aiLimit->reserve($provider, 'hotel.pricing_suggestions', []);
            if (!$reservation) return ['error'=>'quota_exceeded','message'=>'AI quota reached.'];

            $prompt = "Hotel revenue manager. Rooms with base_price + stats:\n" . json_encode($stats) . "\n\n"
                . "CRITICAL: revenue=total booking amount across all nights. per_night=revenue/nights_sum (actual per-night rate). Rooms with 0 bookings have per_night=base_price. " . "Per room return: room_number, suggested_rate(NPR/night between 0.8x-1.5x base_price, anchor to per_night if bookings exist), reason(1 short sentence quoting ACTUAL per_night value, NOT total revenue). "
                . "Respect tiers: Suite>Premium>Deluxe>Superior>Standard. "
                . "Return JSON: {\"rooms\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 3000, 0.4, true);
            $data = $this->parseJson($raw);
            if (!$data) {
                $this->aiLimit->release($reservation);
                return $this->fallbackPricing($provider, $warn);
            }
            $this->aiLimit->finalize($reservation);

            return ['rooms'=>$data['rooms']??[],'summary'=>(string)($data['summary']??''),'data_warning'=>$warn];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('pricingSuggestions failed', ['pid'=>$provider->id,'err'=>$e->getMessage()]);
            return $this->fallbackPricing($provider, 'AI unavailable — baseline.');
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

    protected function fallbackOccupancy(Provider $provider, int $totalRooms = 0, int $todayOcc = 0, ?string $warn = null): array
    {
        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $d = now()->addDays($i);
            $days[] = [
                'date' => $d->format('Y-m-d'),
                'predicted_bookings' => 1,
                'predicted_occupancy_pct' => $totalRooms > 0 ? (int) round(1 / $totalRooms * 100) : 0,
                'confidence' => 'low',
            ];
        }
        return [
            'days' => $days,
            'summary' => 'Baseline (AI unavailable).',
            'data_warning' => $warn,
            'total_rooms' => $totalRooms,
            'current_occupancy_pct' => $totalRooms > 0 ? (int) round($todayOcc / $totalRooms * 100) : 0,
            'fallback' => true,
        ];
    }

    protected function fallbackPricing(Provider $provider, ?string $warn = null): array
    {
        $rooms = DB::table('hotel_rooms')->where('provider_id', $provider->id)->get();
        $rows = [];
        foreach ($rooms as $r) {
            $rows[] = [
                'room_number' => (string) $r->room_number,
                'suggested_rate' => (int) ($r->base_price ?? 8000),
                'reason' => 'Base price (AI unavailable)',
            ];
        }
        return [
            'rooms' => $rows,
            'summary' => 'Baseline from base_price.',
            'data_warning' => $warn,
            'fallback' => true,
        ];
    }
}

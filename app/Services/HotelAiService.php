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

            // ChatGPT fix v2 — robust confirmed-booked marking (direct array index)
            if (!empty($data['days']) && is_array($data['days'])) {
                $confirmedDates = DB::table('hotel_bookings')
                    ->where('provider_id', $provider->id)
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->where('check_in_date', '>', now()->format('Y-m-d'))
                    ->pluck('check_in_date')
                    ->map(fn($dt) => substr((string) $dt, 0, 10))
                    ->toArray();
                foreach ($data['days'] as $idx => $dayRow) {
                    $dayDate = substr((string) ($dayRow['date'] ?? ''), 0, 10);
                    $data['days'][$idx]['is_confirmed'] = in_array($dayDate, $confirmedDates, true);
                }
            }

            // v4: recalc occupancy from confirmed overlap + forecast, add day names
            if (!empty($data['days']) && is_array($data['days'])) {
                $totalRooms2 = DB::table('hotel_rooms')->where('provider_id', $provider->id)->count();
                $conf2 = DB::table('hotel_bookings')
                    ->where('provider_id', $provider->id)
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->where('check_out_date', '>=', now()->format('Y-m-d'))
                    ->get(['check_in_date', 'check_out_date']);
                foreach ($data['days'] as $idx => $dayRow) {
                    $dd = substr((string) ($dayRow['date'] ?? ''), 0, 10);
                    if (!$dd) continue;
                    $occConf = 0;
                    foreach ($conf2 as $b) {
                        $ci = substr((string)$b->check_in_date, 0, 10);
                        $co = substr((string)$b->check_out_date, 0, 10);
                        if ($ci <= $dd && $co >= $dd) $occConf++;
                    }
                    $aiBk = (int) ($dayRow['predicted_bookings'] ?? 0);
                    $extra = max(0, $aiBk - $occConf);
                    $tot = $occConf + $extra;
                    $data['days'][$idx]['confirmed_occupied'] = $occConf;
                    $data['days'][$idx]['predicted_extra'] = $extra;
                    $data['days'][$idx]['predicted_occupancy_pct'] = $totalRooms2 > 0 ? (int) round($tot / $totalRooms2 * 100) : 0;
                    $data['days'][$idx]['day_name'] = \Carbon\Carbon::parse($dd)->format('D');
                }
                // Override AI summary with server-generated (correct day names)
                $confDays = 0;
                $peak = null;
                foreach ($data['days'] as $d2) {
                    if (!empty($d2['is_confirmed'])) $confDays++;
                    if ($peak === null || ($d2['predicted_occupancy_pct'] ?? 0) > ($peak['predicted_occupancy_pct'] ?? 0)) $peak = $d2;
                }
                if ($peak) {
                    $peakDay = \Carbon\Carbon::parse($peak['date'])->format('D, M j');
                    $data['summary'] = sprintf(
                        'Next 7 days: peak occupancy %d%% on %s. %s',
                        $peak['predicted_occupancy_pct'] ?? 0,
                        $peakDay,
                        $confDays > 0 ? "Confirmed bookings on {$confDays} day(s)." : 'All days forecast.'
                    );
                }
            }
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
                ->select('id','room_number','room_type','base_price','min_price','max_price')->get();
            if ($rooms->isEmpty()) return ['error'=>'no_rooms','message'=>'Add rooms first.'];

            $stats = [];
            foreach ($rooms as $room) {
                $b = DB::table('hotel_bookings')->where('provider_id', $provider->id)
                    ->where('room_id', $room->id)->where('status','!=','cancelled')
                    ->select(DB::raw('COUNT(*) total'), DB::raw('SUM(amount) revenue'), DB::raw('SUM(nights) nights_sum'))->first();
                $total = (int)($b->total ?? 0);
                $nights = (int)($b->nights_sum ?? 0);
                $revenue = (float)($b->revenue ?? 0);
                $perNight = $nights > 0 ? (int)round($revenue / $nights) : (int)($room->base_price ?? 0);
                $occ = $nights > 0 ? (int)round(($nights/30)*100) : 0;
                $base = (float)($room->base_price ?? 0);
                $minP = $room->min_price ? (float)$room->min_price : null;
                $maxP = $room->max_price ? (float)$room->max_price : null;

                $det = $this->deterministicPricing($base, $total, $nights, $perNight, (string)$room->room_type);
                if ($minP && $det['suggested'] < $minP) { $det['suggested'] = $minP; $det['reason'] .= ' [clamped min]'; }
                if ($maxP && $det['suggested'] > $maxP) { $det['suggested'] = $maxP; $det['reason'] .= ' [clamped max]'; }

                $stats[] = [
                    'room_number'=>(string)$room->room_number,'room_type'=>(string)$room->room_type,
                    'base_price'=>$base,'min_price'=>$minP,'max_price'=>$maxP,
                    'total_bookings'=>$total,'nights_sold'=>$nights,'occupancy_pct'=>$occ,
                    'per_night'=>$perNight,'det_suggested'=>(int)$det['suggested'],'det_reason'=>$det['reason'],
                ];
            }

            $warn = array_sum(array_column($stats,'total_bookings')) < 5
                ? 'Very low booking data — AI refinement limited to ±10%; deterministic baseline shown.' : null;

            $reservation = $this->aiLimit->reserve($provider, 'hotel.pricing_suggestions', []);
            if (!$reservation) {
                return ['rooms'=>$this->pricingMerge($stats, null), 'summary'=>'Deterministic baseline (AI quota reached).', 'data_warning'=>$warn];
            }

            $prompt = "You are a hotel revenue manager. Each room has a DETERMINISTIC suggested rate from data.\n"
                . "Your job: confirm or refine within ±10% of det_suggested. Do NOT force change if justified.\n"
                . "Respect min_price/max_price. Respect tier: Suite>Deluxe>Standard.\n"
                . "Reason must quote actual data (bookings, occupancy%).\n\n"
                . json_encode($stats) . "\n\n"
                . "Return JSON: {\"rooms\":[{\"room_number\":\"..\",\"suggested_rate\":N,\"reason\":\"short\"}],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 4000, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['rooms'])) {
                $this->aiLimit->release($reservation);
                return ['rooms'=>$this->pricingMerge($stats, null), 'summary'=>'Deterministic baseline (AI refinement skipped — baseline rates shown).', 'data_warning'=>$warn];
            }

            $this->aiLimit->finalize($reservation);
            return ['rooms'=>$this->pricingMerge($stats, $data['rooms']), 'summary'=>(string)($data['summary']??'Pricing ready.'), 'data_warning'=>$warn];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('pricingSuggestions v2 failed', ['pid'=>$provider->id,'err'=>$e->getMessage()]);
            return $this->fallbackPricing($provider, 'AI unavailable — deterministic baseline.');
        }
    }

    protected function deterministicPricing(float $base, int $bookings, int $nights, float $perNight, string $type): array
    {
        // v4: base_price already reflects room type — NO tier multiplier
        $occ = $nights / 30;
        $tierNote = $type !== 'Standard' ? " ({$type})" : '';

        if ($bookings === 0) {
            $sug = $base * 0.90;
            return ['suggested'=>$sug, 'reason'=>sprintf('No bookings 30d — 10%% discount%s → Rs %s', $tierNote, number_format($sug))];
        }
        if ($occ > 0.5) {
            $sug = $base * 1.20;
            return ['suggested'=>$sug, 'reason'=>sprintf('High occ %.0f%% (%d n) +20%%%s → Rs %s', $occ*100, $nights, $tierNote, number_format($sug))];
        }
        if ($occ > 0.25) {
            $sug = $base * 1.10;
            return ['suggested'=>$sug, 'reason'=>sprintf('Moderate occ %.0f%% (%d n) +10%%%s → Rs %s', $occ*100, $nights, $tierNote, number_format($sug))];
        }
        if ($perNight > $base * 1.05) {
            $sug = $base * 1.05;
            return ['suggested'=>$sug, 'reason'=>sprintf('Per-night Rs %s > base +5%% → Rs %s', number_format($perNight), number_format($sug))];
        }
        if ($perNight < $base * 0.85) {
            $sug = $base * 0.95;
            return ['suggested'=>$sug, 'reason'=>sprintf('Per-night Rs %s < base −5%% → Rs %s', number_format($perNight), number_format($sug))];
        }
        return ['suggested'=>$base, 'reason'=>'Per-night matches base — hold rate (no change recommended)'];
    }

    protected function pricingMerge(array $stats, ?array $aiRooms): array
    {
        $aiByNum = [];
        if ($aiRooms) foreach ($aiRooms as $ar) $aiByNum[(string)($ar['room_number']??'')] = $ar;
        $out = [];
        foreach ($stats as $s) {
            $final = (int)$s['det_suggested'];
            $reason = $s['det_reason'];
            $refined = false;
            if (isset($aiByNum[$s['room_number']]) && isset($aiByNum[$s['room_number']]['suggested_rate'])) {
                $aiRate = (float)$aiByNum[$s['room_number']]['suggested_rate'];
                $low = $s['det_suggested'] * 0.9; $high = $s['det_suggested'] * 1.1;
                $aiRate = max($low, min($high, $aiRate));
                if ($s['min_price']) $aiRate = max($s['min_price'], $aiRate);
                if ($s['max_price']) $aiRate = min($s['max_price'], $aiRate);
                if ((int)round($aiRate) !== (int)$s['det_suggested']) {
                    $final = (int)round($aiRate);
                    $reason = (string)($aiByNum[$s['room_number']]['reason'] ?? $reason);
                    $refined = true;
                }
            }
            $out[] = ['room_number'=>$s['room_number'],'room_type'=>$s['room_type'],
                'current_rate'=>(int)$s['base_price'],'suggested_rate'=>$final,
                'deterministic_rate'=>(int)$s['det_suggested'],'reason'=>$reason,
                'ai_refined'=>$refined,'min_price'=>$s['min_price'],'max_price'=>$s['max_price']];
        }
        return $out;
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

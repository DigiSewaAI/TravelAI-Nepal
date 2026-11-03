<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransportAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Vehicle Assignment — fleet utilization + best vehicle per upcoming booking.
     */
    public function vehicleAssignment(Provider $provider): array
    {
        $reservation = null;
        try {
            $vehicles = DB::table('transport_vehicles')->where('provider_id', $provider->id)->get();
            if ($vehicles->isEmpty()) {
                return ['error' => 'no_vehicles', 'message' => 'Add vehicles first.'];
            }
            $bookings = DB::table('transport_bookings')->where('provider_id', $provider->id)->get();

            $stats = [];
            foreach ($vehicles as $v) {
                $vBookings = $bookings->where('vehicle_id', $v->id);
                $totalBk = $vBookings->count();
                $rev = $vBookings->sum('amount');
                // ChatGPT fix — booking allocation ratio (NOT passenger utilization)
                // ChatGPT fix — NO cap; >100% flagged as over_capacity
                $allocationPct = $v->capacity > 0
                    ? round(($totalBk / $v->capacity) * 100, 1) : 0;
                $status = 'healthy';
                if ($totalBk === 0) $status = 'idle';
                elseif ($allocationPct > 100) $status = 'over_capacity';
                elseif ($allocationPct >= 80) $status = 'high_demand';
                elseif ($allocationPct < 30) $status = 'underused';
                $stats[] = [
                    'vehicle_number' => (string) $v->vehicle_number,
                    'vehicle_type' => (string) $v->vehicle_type,
                    'capacity' => (int) $v->capacity,
                    'bookings' => $totalBk,
                    'revenue' => (int) $rev,
                    'booking_count' => $totalBk,
                    'seat_capacity' => (int) $v->capacity,
                    'booking_allocation_pct' => $allocationPct,
                    'status' => $status,
                ];
            }

            // Suggest for upcoming bookings (load balance)
            // ChatGPT fix — load-balance with running counts
            $runningLoad = [];
            foreach ($vehicles as $v) {
                $runningLoad[$v->id] = $bookings->where('vehicle_id', $v->id)->count();
            }
            $suggestions = [];
            $upcoming = $bookings->where('travel_date', '>=', now()->format('Y-m-d'))
                ->sortBy('travel_date')->take(10);
            foreach ($upcoming as $b) {
                $matched = $vehicles->where('route_name', $b->route_name)->where('status', 'available');
                if ($matched->isEmpty()) {
                    $suggestions[] = [
                        'booking_ref' => (string) $b->booking_ref,
                        'route' => (string) $b->route_name,
                        'date' => (string) $b->travel_date,
                        'suggested_vehicle' => null,
                        'reason' => 'No vehicle available on this route',
                    ];
                    continue;
                }
                // Pick vehicle with LOWEST RUNNING load (already incremented for previous assignments)
                $best = $matched->sortBy(function ($v) use ($runningLoad) {
                    return $runningLoad[$v->id] ?? 0;
                })->first();
                $runningLoad[$best->id] = ($runningLoad[$best->id] ?? 0) + 1;
                $suggestions[] = [
                    'booking_ref' => (string) $b->booking_ref,
                    'route' => (string) $b->route_name,
                    'date' => (string) $b->travel_date,
                    'suggested_vehicle' => (string) $best->vehicle_number,
                    'vehicle_type' => (string) $best->vehicle_type,
                    'capacity' => (int) $best->capacity,
                    'reason' => sprintf('%d proposed bookings — %s cap %d seats',
                        $runningLoad[$best->id], $best->vehicle_type, $best->capacity),
                ];
            }

            $warn = count($suggestions) === 0 ? 'No upcoming bookings to assign.' : null;

            $reservation = $this->aiLimit->reserve($provider, 'transport.vehicle_assignment', []);
            if (!$reservation) {
                return ['vehicles' => $stats, 'suggestions' => $suggestions,
                    'summary' => 'Deterministic baseline (AI quota reached).', 'data_warning' => $warn, 'fallback' => true];
            }

            $prompt = "You are a fleet manager. Vehicles with utilization:\n" . json_encode($stats)
                . "\nUpcoming booking assignments:\n" . json_encode($suggestions)
                . "\n\nProvide ONE summary sentence highlighting best/worst utilization and rebalancing recommendation. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1500, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['summary'])) {
                $this->aiLimit->release($reservation);
                return ['vehicles' => $stats, 'suggestions' => $suggestions,
                    'summary' => 'Deterministic baseline (AI parse failed).', 'data_warning' => $warn, 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'vehicles' => $stats,
                'suggestions' => $suggestions,
                'summary' => (string) $data['summary'],
                'data_warning' => $warn,
                'assumptions' => ['seat_assumption'=>'1 booking = 1 seat (schema has no passenger_count)','balance_rule'=>'Load-balanced by booking count across available vehicles'],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('TransportAiService vehicleAssignment failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Vehicle assignment temporarily unavailable.'];
        }
    }

    /**
     * Cost Estimator — per-vehicle per-trip cost vs full-capacity revenue.
     */
    public function costEstimator(Provider $provider): array
    {
        $reservation = null;
        try {
            $vehicles = DB::table('transport_vehicles')->where('provider_id', $provider->id)->get();
            if ($vehicles->isEmpty()) {
                return ['error' => 'no_vehicles', 'message' => 'Add vehicles first.'];
            }

            $fuelPerKm = ['Bike'=>3, 'Car'=>12, 'Jeep'=>18, 'Van'=>20, 'Bus'=>35];
            $maintPerKm = ['Bike'=>1, 'Car'=>3, 'Jeep'=>4, 'Van'=>5, 'Bus'=>8];
            $distanceKm = 200; // Kathmandu-Pokhara road distance
            $driverCostPerDay = 2000;

            $estimates = [];
            foreach ($vehicles as $v) {
                $fuel = ($fuelPerKm[$v->vehicle_type] ?? 12) * $distanceKm;
                $maint = ($maintPerKm[$v->vehicle_type] ?? 3) * $distanceKm;
                $totalCost = $fuel + $maint + $driverCostPerDay;

                $avgRate = (float) (DB::table('transport_bookings')
                    ->where('provider_id', $provider->id)
                    ->where('route_name', $v->route_name)
                    ->avg('amount') ?? 0);

                $fullRev = $avgRate * $v->capacity;
                $margin = $fullRev - $totalCost;
                // ChatGPT fix — margin% = profit / full_revenue (standard gross margin)
                $marginPct = $fullRev > 0 ? round(($margin / $fullRev) * 100, 1) : 0;
                $markupPct = $totalCost > 0 ? round(($margin / $totalCost) * 100, 1) : 0;
                $cost_status = $margin < 0 ? 'loss' : ($marginPct < 15 ? 'thin_margin' : 'healthy');

                $estimates[] = [
                    'vehicle_number' => (string) $v->vehicle_number,
                    'vehicle_type' => (string) $v->vehicle_type,
                    'capacity' => (int) $v->capacity,
                    'route' => (string) $v->route_name,
                    'distance_km' => $distanceKm,
                    'fuel_cost' => (int) $fuel,
                    'maintenance_cost' => (int) $maint,
                    'driver_cost' => $driverCostPerDay,
                    'total_trip_cost' => (int) $totalCost,
                    'avg_seat_rate' => (int) $avgRate,
                    'full_trip_revenue' => (int) $fullRev,
                    'margin_per_full_trip' => (int) $margin,
                    'margin_pct' => $marginPct,
                    'markup_pct' => $markupPct,
                    'cost_status' => $cost_status,
                ];
            }

            $reservation = $this->aiLimit->reserve($provider, 'transport.cost_estimator', []);
            if (!$reservation) {
                return ['estimates' => $estimates, 'assumptions' => ['distance_km'=>$distanceKm,'driver_cost'=>$driverCostPerDay,'trip_type'=>'one-way','seats_assumption'=>'1 booking = 1 seat assumed'],
                    'summary' => 'Deterministic baseline (AI quota reached).', 'fallback' => true];
            }

            $prompt = "You are a transport cost analyst. Estimates per trip (50km, driver Rs 2000/day):\n"
                . json_encode($estimates)
                . "\n\nProvide ONE summary sentence on profitability. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1500, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['summary'])) {
                $this->aiLimit->release($reservation);
                return ['estimates' => $estimates, 'assumptions' => ['distance_km'=>$distanceKm,'driver_cost'=>$driverCostPerDay,'trip_type'=>'one-way','seats_assumption'=>'1 booking = 1 seat assumed'],
                    'summary' => 'Deterministic baseline (AI parse failed).', 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'estimates' => $estimates,
                'assumptions' => ['distance_km'=>$distanceKm,'driver_cost'=>$driverCostPerDay,'trip_type'=>'one-way','seats_assumption'=>'1 booking = 1 seat assumed'],
                'summary' => (string) $data['summary'],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('TransportAiService costEstimator failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Cost estimator temporarily unavailable.'];
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
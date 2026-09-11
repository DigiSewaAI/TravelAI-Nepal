<?php

namespace App\Services;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\RouteSegment;
use App\Models\RouteCost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RouteDataHelper
{
    /**
     * Phase 4N: Determine is_overnight_stop based on waypoint type.
     * Rule: village/city = true; everything else = false.
     * Exception list is applied separately via seeder/tinker.
     */
    protected function isOvernightByType(string $type): bool
    {
        return in_array($type, ['village', 'city'], true);
    }

    /**
     * Seed a complete route with waypoints, segments, and costs.
     */
    public function seedRoute(array $data): Route
    {
        return DB::transaction(function () use ($data) {
            // 1. Create waypoints
            $wpIds = [];
            foreach ($data['waypoints'] as $wp) {
                $type = $wp['type'] ?? 'village';

                $model = Waypoint::updateOrCreate(
                    ['slug' => $wp['slug']],
                    [
                        'name' => $wp['name'],
                        'type' => $type,
                        'latitude' => $wp['lat'],
                        'longitude' => $wp['lng'],
                        'altitude' => $wp['alt'],
                        'description' => $wp['description'] ?? null,
                        'metadata' => $wp['metadata'] ?? null,
                        // ✅ Phase 4N: Explicit overnight flag — never trust DB default
                        'is_overnight_stop' => $this->isOvernightByType($type),
                    ]
                );
                $wpIds[$wp['slug']] = $model->id;
            }

            // 2. Create route
            $route = Route::updateOrCreate(
                ['slug' => $data['route']['slug']],
                [
                    'name' => $data['route']['name'],
                    'description' => $data['route']['description'],
                    'difficulty' => $data['route']['difficulty'],
                    'duration_days' => $data['route']['duration_days'],
                    'max_altitude' => $data['route']['max_altitude'],
                    'season' => $data['route']['season'] ?? 'Spring/Autumn',
                    'is_active' => true,
                ]
            );

            // 3. Create segments
            foreach ($data['segments'] as $i => $seg) {
                RouteSegment::updateOrCreate(
                    [
                        'route_id' => $route->id,
                        'sequence' => $i + 1,
                    ],
                    [
                        'from_waypoint_id' => $wpIds[$seg['from']],
                        'to_waypoint_id' => $wpIds[$seg['to']],
                        'distance_km' => $seg['dist'],
                        'estimated_time_hours' => $seg['time'],
                        'elevation_gain_m' => $seg['gain'] ?? 0,
                        'elevation_loss_m' => $seg['loss'] ?? 0,
                    ]
                );
            }

            // 4. Create costs
            // ✅ FIX: 'name' added to lookup key — allows multiple permits per route
            //    (ACAP + TIMS + Manang Special can coexist instead of overwriting)
            foreach ($data['costs'] ?? [] as $cost) {
                RouteCost::updateOrCreate(
                    [
                        'route_id' => $route->id,
                        'type' => $cost['type'],
                        'name' => $cost['name'],                          // ✅ ADDED
                        'effective_from' => $cost['from'] ?? '2026-01-01',
                    ],
                    [
                        'amount' => $cost['amount'],
                        'currency' => 'USD',
                        'unit' => $cost['unit'] ?? 'per_person',
                        'is_mandatory' => $cost['mandatory'] ?? true,
                        'effective_until' => $cost['until'] ?? '2026-12-31',
                        'metadata' => $cost['metadata'] ?? null,
                    ]
                );
            }

            return $route;
        });
    }

    /**
     * Create a simple tour/experience route (no segments, just costs + waypoints).
     */
    public function seedTour(array $data): Route
    {
        return DB::transaction(function () use ($data) {
            // 1. Create waypoints
            $wpIds = [];
            foreach ($data['waypoints'] ?? [] as $wp) {
                $type = $wp['type'] ?? 'village';

                $model = Waypoint::updateOrCreate(
                    ['slug' => $wp['slug']],
                    [
                        'name' => $wp['name'],
                        'type' => $type,
                        'latitude' => $wp['lat'],
                        'longitude' => $wp['lng'],
                        'altitude' => $wp['alt'] ?? 0,
                        // ✅ Phase 4N: Explicit overnight flag — never trust DB default
                        'is_overnight_stop' => $this->isOvernightByType($type),
                    ]
                );
                $wpIds[$wp['slug']] = $model->id;
            }

            // 2. Create route
            $route = Route::updateOrCreate(
                ['slug' => $data['route']['slug']],
                [
                    'name' => $data['route']['name'],
                    'description' => $data['route']['description'],
                    'difficulty' => $data['route']['difficulty'] ?? 'easy',
                    'duration_days' => $data['route']['duration_days'],
                    'max_altitude' => $data['route']['max_altitude'] ?? 0,
                    'season' => $data['route']['season'] ?? 'All Year',
                    'is_active' => true,
                ]
            );

            // 3. Create segments if provided
            if (isset($data['segments']) && is_array($data['segments'])) {
                // Delete existing segments first
                RouteSegment::where('route_id', $route->id)->delete();

                foreach ($data['segments'] as $i => $seg) {
                    RouteSegment::create([
                        'route_id' => $route->id,
                        'sequence' => $i + 1,
                        'from_waypoint_id' => $wpIds[$seg['from']],
                        'to_waypoint_id' => $wpIds[$seg['to']],
                        'distance_km' => $seg['dist'],
                        'estimated_time_hours' => $seg['time'],
                        'elevation_gain_m' => $seg['gain'] ?? 0,
                        'elevation_loss_m' => $seg['loss'] ?? 0,
                    ]);
                }
            }

            // 4. Create costs
            // ✅ FIX: 'name' added to lookup key — allows multiple permits per route
            foreach ($data['costs'] ?? [] as $cost) {
                RouteCost::updateOrCreate(
                    [
                        'route_id' => $route->id,
                        'type' => $cost['type'],
                        'name' => $cost['name'],                          // ✅ ADDED
                        'effective_from' => $cost['from'] ?? '2026-01-01',
                    ],
                    [
                        'amount' => $cost['amount'],
                        'currency' => 'USD',
                        'unit' => $cost['unit'] ?? 'per_person',
                        'is_mandatory' => $cost['mandatory'] ?? false,
                        'effective_until' => $cost['until'] ?? '2026-12-31',
                        'metadata' => $cost['metadata'] ?? null,
                    ]
                );
            }

            return $route;
        });
    }
}
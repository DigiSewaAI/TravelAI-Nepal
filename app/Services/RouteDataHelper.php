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
     * Seed a complete route with waypoints, segments, and costs.
     */
    public function seedRoute(array $data): Route
    {
        return DB::transaction(function () use ($data) {
            // 1. Create waypoints
            $wpIds = [];
            foreach ($data['waypoints'] as $wp) {
                $model = Waypoint::updateOrCreate(
                    ['slug' => $wp['slug']],
                    [
                        'name' => $wp['name'],
                        'type' => $wp['type'] ?? 'village',
                        'latitude' => $wp['lat'],
                        'longitude' => $wp['lng'],
                        'altitude' => $wp['alt'],
                        'description' => $wp['description'] ?? null,
                        'metadata' => $wp['metadata'] ?? null,
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
            foreach ($data['costs'] ?? [] as $cost) {
                RouteCost::updateOrCreate(
                    [
                        'route_id' => $route->id,
                        'type' => $cost['type'],
                        'effective_from' => $cost['from'] ?? '2026-01-01',
                    ],
                    [
                        'name' => $cost['name'],
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
            // 1. Create waypoints (if any)
            $wpIds = [];
            foreach ($data['waypoints'] ?? [] as $wp) {
                $model = Waypoint::updateOrCreate(
                    ['slug' => $wp['slug']],
                    [
                        'name' => $wp['name'],
                        'type' => $wp['type'] ?? 'village',
                        'latitude' => $wp['lat'],
                        'longitude' => $wp['lng'],
                        'altitude' => $wp['alt'] ?? 0,
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

            // 3. Create costs
            foreach ($data['costs'] ?? [] as $cost) {
                RouteCost::updateOrCreate(
                    [
                        'route_id' => $route->id,
                        'type' => $cost['type'],
                        'effective_from' => $cost['from'] ?? '2026-01-01',
                    ],
                    [
                        'name' => $cost['name'],
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
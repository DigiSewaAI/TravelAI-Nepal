<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\RouteSegment;
use App\Models\RouteCost;
use Illuminate\Database\Seeder;

class EbcRouteSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // STEP 1: WAYPOINTS (Approximate coordinates)
        // ==========================================
        $waypoints = [
            ['name' => 'Lukla', 'slug' => 'lukla', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            ['name' => 'Phakding', 'slug' => 'phakding', 'type' => 'village', 'lat' => 27.7408, 'lng' => 86.7125, 'alt' => 2610],
            ['name' => 'Namche Bazaar', 'slug' => 'namche-bazaar', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
            ['name' => 'Tengboche', 'slug' => 'tengboche', 'type' => 'village', 'lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
            ['name' => 'Dingboche', 'slug' => 'dingboche', 'type' => 'village', 'lat' => 27.8927, 'lng' => 86.8242, 'alt' => 4410],
            ['name' => 'Lobuche', 'slug' => 'lobuche', 'type' => 'village', 'lat' => 27.9358, 'lng' => 86.8087, 'alt' => 4940],
            ['name' => 'Gorak Shep', 'slug' => 'gorak-shep', 'type' => 'village', 'lat' => 27.9812, 'lng' => 86.8274, 'alt' => 5140],
            ['name' => 'Everest Base Camp', 'slug' => 'ebc', 'type' => 'peak', 'lat' => 28.0057, 'lng' => 86.8294, 'alt' => 5364],
        ];

        $wpIds = [];
        foreach ($waypoints as $wp) {
            $model = Waypoint::updateOrCreate(
                ['slug' => $wp['slug']],
                [
                    'name' => $wp['name'],
                    'type' => $wp['type'],
                    'latitude' => $wp['lat'],
                    'longitude' => $wp['lng'],
                    'altitude' => $wp['alt'],
                ]
            );
            $wpIds[$wp['slug']] = $model->id;
        }

        // ==========================================
        // STEP 2: ROUTE (duration = informational)
        // ==========================================
        $route = Route::updateOrCreate(
            ['slug' => 'everest-base-camp'],
            [
                'name' => 'Everest Base Camp Trek',
                'description' => 'Classic EBC trek via Lukla with full return path. Approx 12-14 days with acclimatization.',
                'difficulty' => 'hard',
                'duration_days' => 14,
                'max_altitude' => 5364,
                'season' => 'Spring/Autumn',
                'is_active' => true,
            ]
        );

        // ==========================================
        // STEP 3: ROUTE SEGMENTS (Forward + Return)
        // ==========================================
        $segments = [
            // Forward (1-7)
            ['from' => 'lukla', 'to' => 'phakding', 'dist' => 8.0, 'time' => 3.0, 'gain' => 0, 'loss' => 250],
            ['from' => 'phakding', 'to' => 'namche-bazaar', 'dist' => 10.5, 'time' => 5.0, 'gain' => 830, 'loss' => 0],
            ['from' => 'namche-bazaar', 'to' => 'tengboche', 'dist' => 9.0, 'time' => 5.0, 'gain' => 420, 'loss' => 0],
            ['from' => 'tengboche', 'to' => 'dingboche', 'dist' => 10.0, 'time' => 5.0, 'gain' => 550, 'loss' => 0],
            ['from' => 'dingboche', 'to' => 'lobuche', 'dist' => 9.5, 'time' => 5.5, 'gain' => 530, 'loss' => 0],
            ['from' => 'lobuche', 'to' => 'gorak-shep', 'dist' => 5.5, 'time' => 3.5, 'gain' => 200, 'loss' => 0],
            ['from' => 'gorak-shep', 'to' => 'ebc', 'dist' => 3.5, 'time' => 2.5, 'gain' => 224, 'loss' => 0],
            // Return (8-14)
            ['from' => 'ebc', 'to' => 'gorak-shep', 'dist' => 3.5, 'time' => 2.5, 'gain' => 0, 'loss' => 224],
            ['from' => 'gorak-shep', 'to' => 'lobuche', 'dist' => 5.5, 'time' => 3.5, 'gain' => 0, 'loss' => 200],
            ['from' => 'lobuche', 'to' => 'dingboche', 'dist' => 9.5, 'time' => 5.0, 'gain' => 0, 'loss' => 530],
            ['from' => 'dingboche', 'to' => 'tengboche', 'dist' => 10.0, 'time' => 5.0, 'gain' => 0, 'loss' => 550],
            ['from' => 'tengboche', 'to' => 'namche-bazaar', 'dist' => 9.0, 'time' => 5.0, 'gain' => 0, 'loss' => 420],
            ['from' => 'namche-bazaar', 'to' => 'phakding', 'dist' => 10.5, 'time' => 5.0, 'gain' => 0, 'loss' => 830],
            ['from' => 'phakding', 'to' => 'lukla', 'dist' => 8.0, 'time' => 3.0, 'gain' => 250, 'loss' => 0],
        ];

        foreach ($segments as $i => $seg) {
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
                    'elevation_gain_m' => $seg['gain'],
                    'elevation_loss_m' => $seg['loss'],
                ]
            );
        }

        // ==========================================
        // STEP 4: ROUTE COSTS (NPR only)
        // ==========================================
        // Verified: Sagarmatha National Park (source: NTB)
        // Verified: Khumbu Pasang Lhamu Rural Municipality
        // Approximate: daily food
        // NOT seeded: flight price (dynamic)
        $costs = [
            [
                'type' => 'permit',
                'name' => 'Sagarmatha National Park Permit (verified)',
                'amount' => 3000,
                'unit' => 'per_person',
                'mandatory' => true,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => true, 'source' => 'NTB']
            ],
            [
                'type' => 'permit',
                'name' => 'Khumbu Pasang Lhamu Permit (verified)',
                'amount' => 2000,
                'unit' => 'per_person',
                'mandatory' => true,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => true, 'source' => 'NTB']
            ],
            [
                'type' => 'food_estimate',
                'name' => 'Daily Food Budget (approx)',
                'amount' => 3000,
                'unit' => 'per_day',
                'mandatory' => false,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => false, 'source' => 'common estimate']
            ],
        ];

        foreach ($costs as $cost) {
            RouteCost::updateOrCreate(
                [
                    'route_id' => $route->id,
                    'type' => $cost['type'],
                    'effective_from' => $cost['from'],
                ],
                [
                    'name' => $cost['name'],
                    'amount' => $cost['amount'],
                    'currency' => 'NPR',
                    'unit' => $cost['unit'],
                    'is_mandatory' => $cost['mandatory'],
                    'effective_until' => $cost['until'],
                    'metadata' => $cost['meta'],
                ]
            );
        }

        $this->command->info('✅ EBC Route seeded successfully (14 segments, forward + return).');
    }
}
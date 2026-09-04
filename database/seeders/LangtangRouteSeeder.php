<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\RouteSegment;
use App\Models\RouteCost;
use Illuminate\Database\Seeder;

class LangtangRouteSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // STEP 1: WAYPOINTS
        // ==========================================
        $waypoints = [
            ['name' => 'Syabrubesi', 'slug' => 'syabrubesi', 'type' => 'village', 'lat' => 28.1579, 'lng' => 85.3378, 'alt' => 1503],
            ['name' => 'Lama Hotel', 'slug' => 'lama-hotel', 'type' => 'village', 'lat' => 28.2028, 'lng' => 85.3806, 'alt' => 2470],
            ['name' => 'Langtang Village', 'slug' => 'langtang-village', 'type' => 'village', 'lat' => 28.2219, 'lng' => 85.5147, 'alt' => 3430],
            ['name' => 'Kyangjin Gompa', 'slug' => 'kyangjin-gompa', 'type' => 'checkpoint', 'lat' => 28.2253, 'lng' => 85.5708, 'alt' => 3870],
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
        // STEP 2: ROUTE
        // ==========================================
        $route = Route::updateOrCreate(
            ['slug' => 'langtang-valley'],
            [
                'name' => 'Langtang Valley Trek',
                'description' => 'Beautiful Langtang Valley trek with full return path. Approx 7 days with acclimatization.',
                'difficulty' => 'moderate',
                'duration_days' => 7,
                'max_altitude' => 3870,
                'season' => 'Spring/Autumn',
                'is_active' => true,
            ]
        );

        // ==========================================
        // STEP 3: SEGMENTS (Forward + Rest + Return)
        // ==========================================
        // ✅ Rest day segment added: Kyangjin Gompa → Kyangjin Gompa (distance 0)
        $segments = [
            // Forward (1-3)
            ['from' => 'syabrubesi', 'to' => 'lama-hotel', 'dist' => 12.0, 'time' => 6.0, 'gain' => 967, 'loss' => 0],
            ['from' => 'lama-hotel', 'to' => 'langtang-village', 'dist' => 10.0, 'time' => 5.0, 'gain' => 960, 'loss' => 0],
            ['from' => 'langtang-village', 'to' => 'kyangjin-gompa', 'dist' => 7.5, 'time' => 4.0, 'gain' => 440, 'loss' => 0],
            // Rest Day at Kyangjin Gompa (sequence 4)
            ['from' => 'kyangjin-gompa', 'to' => 'kyangjin-gompa', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
            // Return (5-7)
            ['from' => 'kyangjin-gompa', 'to' => 'langtang-village', 'dist' => 7.5, 'time' => 4.0, 'gain' => 0, 'loss' => 440],
            ['from' => 'langtang-village', 'to' => 'lama-hotel', 'dist' => 10.0, 'time' => 5.0, 'gain' => 0, 'loss' => 960],
            ['from' => 'lama-hotel', 'to' => 'syabrubesi', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 967],
        ];

        // Delete existing segments for this route to avoid sequence conflicts
        RouteSegment::where('route_id', $route->id)->delete();

        // Insert new segments with correct sequence
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
        // STEP 4: COSTS
        // ==========================================
        $costs = [
            [
                'type' => 'permit',
                'name' => 'Langtang National Park Permit (verified)',
                'amount' => 3000,
                'unit' => 'per_person',
                'mandatory' => true,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => true, 'source' => 'NTB']
            ],
            [
                'type' => 'permit',
                'name' => 'TIMS Card (approx)',
                'amount' => 2000,
                'unit' => 'per_person',
                'mandatory' => true,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => false, 'source' => 'common practice']
            ],
            [
                'type' => 'food_estimate',
                'name' => 'Daily Food Budget (approx)',
                'amount' => 2500,
                'unit' => 'per_day',
                'mandatory' => false,
                'from' => '2026-01-01',
                'until' => '2026-12-31',
                'meta' => ['verified' => false, 'source' => 'estimate']
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

        $this->command->info('✅ Langtang Route seeded successfully (7 segments: forward, rest day, return).');
    }
}
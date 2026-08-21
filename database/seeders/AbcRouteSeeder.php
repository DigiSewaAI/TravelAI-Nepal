<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use App\Models\Route;
use App\Models\RouteSegment;
use App\Models\RouteCost;
use Illuminate\Database\Seeder;

class AbcRouteSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // STEP 1: WAYPOINTS
        // ==========================================
        $waypoints = [
            ['name' => 'Nayapul', 'slug' => 'nayapul', 'type' => 'village', 'lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
            ['name' => 'Birethanti', 'slug' => 'birethanti', 'type' => 'village', 'lat' => 28.4245, 'lng' => 83.7564, 'alt' => 1025],
            ['name' => 'Tikhedhunga', 'slug' => 'tikhedhunga', 'type' => 'village', 'lat' => 28.4387, 'lng' => 83.7105, 'alt' => 1540],
            ['name' => 'Ulleri', 'slug' => 'ulleri', 'type' => 'village', 'lat' => 28.4412, 'lng' => 83.7221, 'alt' => 1960],
            ['name' => 'Ghorepani', 'slug' => 'ghorepani', 'type' => 'village', 'lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
            ['name' => 'Tadapani', 'slug' => 'tadapani', 'type' => 'village', 'lat' => 28.5107, 'lng' => 83.7435, 'alt' => 2630],
            ['name' => 'Chhomrong', 'slug' => 'chhomrong', 'type' => 'village', 'lat' => 28.5332, 'lng' => 83.7589, 'alt' => 2170],
            ['name' => 'Sinuwa', 'slug' => 'sinuwa', 'type' => 'village', 'lat' => 28.5436, 'lng' => 83.7651, 'alt' => 2360],
            ['name' => 'Bamboo', 'slug' => 'bamboo', 'type' => 'village', 'lat' => 28.5549, 'lng' => 83.7722, 'alt' => 2335],
            ['name' => 'Dovan', 'slug' => 'dovan', 'type' => 'village', 'lat' => 28.5658, 'lng' => 83.7786, 'alt' => 2500],
            ['name' => 'Himalaya', 'slug' => 'himalaya', 'type' => 'village', 'lat' => 28.5753, 'lng' => 83.7834, 'alt' => 2920],
            ['name' => 'Deurali', 'slug' => 'deurali', 'type' => 'village', 'lat' => 28.5844, 'lng' => 83.7893, 'alt' => 3230],
            ['name' => 'Machhapuchhre Base Camp', 'slug' => 'mbc', 'type' => 'checkpoint', 'lat' => 28.5923, 'lng' => 83.7956, 'alt' => 3700],
            ['name' => 'Annapurna Base Camp', 'slug' => 'abc', 'type' => 'peak', 'lat' => 28.6005, 'lng' => 83.8001, 'alt' => 4130],
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
            ['slug' => 'annapurna-base-camp'],
            [
                'name' => 'Annapurna Base Camp Trek',
                'description' => 'Classic ABC trek via Nayapul – moderate difficulty, 9 days.',
                'difficulty' => 'moderate',
                'duration_days' => 9,
                'max_altitude' => 4130,
                'season' => 'Spring/Autumn',
                'is_active' => true,
            ]
        );

        // ==========================================
        // STEP 3: ROUTE SEGMENTS
        // ==========================================
        $segments = [
            ['from' => 'nayapul', 'to' => 'birethanti', 'dist' => 10.5, 'time' => 4.5, 'gain' => 500, 'loss' => 0],
            ['from' => 'birethanti', 'to' => 'tikhedhunga', 'dist' => 8.2, 'time' => 3.5, 'gain' => 515, 'loss' => 0],
            ['from' => 'tikhedhunga', 'to' => 'ulleri', 'dist' => 5.8, 'time' => 2.5, 'gain' => 420, 'loss' => 0],
            ['from' => 'ulleri', 'to' => 'ghorepani', 'dist' => 9.1, 'time' => 4.0, 'gain' => 900, 'loss' => 0],
            ['from' => 'ghorepani', 'to' => 'tadapani', 'dist' => 7.4, 'time' => 3.5, 'gain' => 0, 'loss' => 230],
            ['from' => 'tadapani', 'to' => 'chhomrong', 'dist' => 8.6, 'time' => 4.0, 'gain' => 0, 'loss' => 460],
            ['from' => 'chhomrong', 'to' => 'sinuwa', 'dist' => 5.9, 'time' => 2.5, 'gain' => 190, 'loss' => 0],
            ['from' => 'sinuwa', 'to' => 'bamboo', 'dist' => 4.2, 'time' => 2.0, 'gain' => 0, 'loss' => 25],
            ['from' => 'bamboo', 'to' => 'dovan', 'dist' => 3.8, 'time' => 2.0, 'gain' => 165, 'loss' => 0],
            ['from' => 'dovan', 'to' => 'himalaya', 'dist' => 4.5, 'time' => 2.5, 'gain' => 420, 'loss' => 0],
            ['from' => 'himalaya', 'to' => 'deurali', 'dist' => 3.6, 'time' => 2.0, 'gain' => 310, 'loss' => 0],
            ['from' => 'deurali', 'to' => 'mbc', 'dist' => 4.2, 'time' => 2.5, 'gain' => 470, 'loss' => 0],
            ['from' => 'mbc', 'to' => 'abc', 'dist' => 3.8, 'time' => 2.0, 'gain' => 430, 'loss' => 0],
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
        // STEP 4: ROUTE COSTS (System-level)
        // ==========================================
        $costs = [
            ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 2000, 'unit' => 'per_person', 'mandatory' => true, 'from' => '2026-01-01', 'until' => '2026-12-31'],
            ['type' => 'conservation_fee', 'name' => 'ACAP Permit', 'amount' => 3000, 'unit' => 'per_person', 'mandatory' => true, 'from' => '2026-01-01', 'until' => '2026-12-31'],
            ['type' => 'local_transport', 'name' => 'Pokhara → Nayapul Bus', 'amount' => 1000, 'unit' => 'per_person', 'mandatory' => false, 'from' => '2026-01-01', 'until' => '2026-12-31'],
            ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 2500, 'unit' => 'per_day', 'mandatory' => false, 'from' => '2026-01-01', 'until' => '2026-12-31'],
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
                ]
            );
        }

        $this->command->info('✅ ABC Route seeded successfully!');
    }
}
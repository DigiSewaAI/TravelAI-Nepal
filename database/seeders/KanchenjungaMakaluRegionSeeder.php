<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class KanchenjungaMakaluRegionSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏔️ Seeding Kanchenjunga & Makalu Region...');

        // ==========================================
        // 1. KANCHENJUNGA BASE CAMP (NORTH)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Kanchenjunga Base Camp (North) Trek',
                'slug' => 'kanchenjunga-north',
                'description' => 'Trek to the north base camp of the world\'s third highest mountain, Kanchenjunga (8586m).',
                'difficulty' => 'hard',
                'duration_days' => 18,
                'max_altitude' => 5140,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Suketar', 'slug' => 'suketar', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
                ['name' => 'Kabeli', 'slug' => 'kabeli', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
                ['name' => 'Chirwa', 'slug' => 'chirwa', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1270],
                ['name' => 'Sakathum', 'slug' => 'sakathum', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.7789, 'alt' => 1650],
                ['name' => 'Amjilosa', 'slug' => 'amjilosa', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.8012, 'alt' => 2510],
                ['name' => 'Gyabla', 'slug' => 'gyabla', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.8234, 'alt' => 2730],
                ['name' => 'Ghunsa', 'slug' => 'ghunsa', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 3430],
                ['name' => 'Kambachen', 'slug' => 'kambachen', 'type' => 'village', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4040],
                ['name' => 'Lhonak', 'slug' => 'lhonak', 'type' => 'village', 'lat' => 27.6123, 'lng' => 87.8901, 'alt' => 4780],
                ['name' => 'Kanchenjunga North Base Camp', 'slug' => 'kanchenjunga-north-bc', 'type' => 'checkpoint', 'lat' => 27.6456, 'lng' => 87.9123, 'alt' => 5140],
            ],
            'segments' => [
                // ── Forward (9) ──
                ['from' => 'suketar', 'to' => 'kabeli', 'dist' => 8.0, 'time' => 4.0, 'loss' => 720],
                ['from' => 'kabeli', 'to' => 'chirwa', 'dist' => 8.0, 'time' => 4.0, 'loss' => 430],
                ['from' => 'chirwa', 'to' => 'sakathum', 'dist' => 10.0, 'time' => 5.0, 'gain' => 380],
                ['from' => 'sakathum', 'to' => 'amjilosa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 860],
                ['from' => 'amjilosa', 'to' => 'gyabla', 'dist' => 6.0, 'time' => 3.0, 'gain' => 220],
                ['from' => 'gyabla', 'to' => 'ghunsa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                // ── Rest day at Ghunsa (acclimatization) ──
                ['from' => 'ghunsa', 'to' => 'ghunsa', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'ghunsa', 'to' => 'kambachen', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'kambachen', 'to' => 'lhonak', 'dist' => 7.0, 'time' => 4.0, 'gain' => 740],
                ['from' => 'lhonak', 'to' => 'kanchenjunga-north-bc', 'dist' => 4.0, 'time' => 2.5, 'gain' => 360],
                // ── Rest day at Base Camp ──
                ['from' => 'kanchenjunga-north-bc', 'to' => 'kanchenjunga-north-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // ── Return (7) ──
                ['from' => 'kanchenjunga-north-bc', 'to' => 'lhonak', 'dist' => 4.0, 'time' => 2.5, 'loss' => 360],
                ['from' => 'lhonak', 'to' => 'kambachen', 'dist' => 7.0, 'time' => 3.5, 'loss' => 740],
                ['from' => 'kambachen', 'to' => 'ghunsa', 'dist' => 8.0, 'time' => 4.0, 'loss' => 610],
                ['from' => 'ghunsa', 'to' => 'amjilosa', 'dist' => 14.0, 'time' => 7.0, 'loss' => 700],
                ['from' => 'amjilosa', 'to' => 'sakathum', 'dist' => 8.0, 'time' => 4.0, 'loss' => 860],
                ['from' => 'sakathum', 'to' => 'kabeli', 'dist' => 10.0, 'time' => 5.0, 'loss' => 380],
                ['from' => 'kabeli', 'to' => 'suketar', 'dist' => 8.0, 'time' => 4.0, 'gain' => 720],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Kanchenjunga Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'Kanchenjunga Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kanchenjunga North Base Camp seeded.');

        // ==========================================
        // 2. KANCHENJUNGA BASE CAMP (SOUTH)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Kanchenjunga Base Camp (South) Trek',
                'slug' => 'kanchenjunga-south',
                'description' => 'Trek to the south base camp of Kanchenjunga through the Yalung valley.',
                'difficulty' => 'hard',
                'duration_days' => 18,
                'max_altitude' => 4500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Suketar', 'slug' => 'suketar', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
                ['name' => 'Mamanke', 'slug' => 'mamanke', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
                ['name' => 'Yamphudin', 'slug' => 'yamphudin', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1670],
                ['name' => 'Torotong', 'slug' => 'torotong', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.7789, 'alt' => 2990],
                ['name' => 'Lamite', 'slug' => 'lamite', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.8012, 'alt' => 3430],
                ['name' => 'Cheram', 'slug' => 'cheram', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.8234, 'alt' => 3870],
                ['name' => 'Ramche', 'slug' => 'ramche', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 4180],
                ['name' => 'Kanchenjunga South Base Camp', 'slug' => 'kanchenjunga-south-bc', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4500],
            ],
                        'segments' => [
                // ── Forward (7) ──
                ['from' => 'suketar', 'to' => 'mamanke', 'dist' => 6.0, 'time' => 3.0, 'gain' => 300],
                ['from' => 'mamanke', 'to' => 'yamphudin', 'dist' => 6.0, 'time' => 3.0, 'gain' => 380],
                ['from' => 'yamphudin', 'to' => 'torotong', 'dist' => 10.0, 'time' => 5.0, 'gain' => 500],
                ['from' => 'torotong', 'to' => 'torotong', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'torotong', 'to' => 'lamite', 'dist' => 6.0, 'time' => 3.0, 'gain' => 400],
                ['from' => 'lamite', 'to' => 'cheram', 'dist' => 6.0, 'time' => 3.5, 'gain' => 450],
                // ── Rest at Cheram (acclimatization) ──
                ['from' => 'cheram', 'to' => 'cheram', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'cheram', 'to' => 'ramche', 'dist' => 5.0, 'time' => 3.0, 'gain' => 350],
                // ── Rest at Ramche (acclimatization) ──
                ['from' => 'ramche', 'to' => 'ramche', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'ramche', 'to' => 'kanchenjunga-south-bc', 'dist' => 4.0, 'time' => 2.0, 'gain' => 320],
                // ── Rest at Base Camp ──
                ['from' => 'kanchenjunga-south-bc', 'to' => 'kanchenjunga-south-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // ── Return (7) ──
                ['from' => 'kanchenjunga-south-bc', 'to' => 'ramche', 'dist' => 4.0, 'time' => 2.0, 'loss' => 320],
                ['from' => 'ramche', 'to' => 'cheram', 'dist' => 5.0, 'time' => 2.5, 'loss' => 350],
                ['from' => 'cheram', 'to' => 'lamite', 'dist' => 6.0, 'time' => 3.0, 'loss' => 450],
                ['from' => 'lamite', 'to' => 'torotong', 'dist' => 6.0, 'time' => 3.0, 'loss' => 400],
                ['from' => 'torotong', 'to' => 'yamphudin', 'dist' => 10.0, 'time' => 5.0, 'loss' => 500],
                ['from' => 'yamphudin', 'to' => 'mamanke', 'dist' => 6.0, 'time' => 3.0, 'loss' => 380],
                ['from' => 'mamanke', 'to' => 'suketar', 'dist' => 6.0, 'time' => 3.0, 'loss' => 300],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Kanchenjunga Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Kanchenjunga Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kanchenjunga South Base Camp seeded.');

        // ==========================================
        // 3. KANCHENJUNGA CIRCUIT
        // ==========================================
                $this->helper->seedRoute([
            'route' => [
                'name' => 'Kanchenjunga Circuit',
                'slug' => 'kanchenjunga-circuit',
                'description' => 'Complete circuit of Kanchenjunga, crossing the Mirgin La pass and visiting both north and south base camps.',
                'difficulty' => 'hard',
                'duration_days' => 23,
                'max_altitude' => 5500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                // North-side waypoints (shared with Kanchenjunga North seeder)
                ['name' => 'Suketar', 'slug' => 'suketar', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
                ['name' => 'Kabeli', 'slug' => 'kabeli', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
                ['name' => 'Chirwa', 'slug' => 'chirwa', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1270],
                ['name' => 'Sakathum', 'slug' => 'sakathum', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.7789, 'alt' => 1650],
                ['name' => 'Amjilosa', 'slug' => 'amjilosa', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.8012, 'alt' => 2510],
                ['name' => 'Gyabla', 'slug' => 'gyabla', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.8234, 'alt' => 2730],
                ['name' => 'Ghunsa', 'slug' => 'ghunsa', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 3430],
                ['name' => 'Kambachen', 'slug' => 'kambachen', 'type' => 'village', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4040],
                ['name' => 'Lhonak', 'slug' => 'lhonak', 'type' => 'village', 'lat' => 27.6123, 'lng' => 87.8901, 'alt' => 4780],
                ['name' => 'Kanchenjunga North Base Camp', 'slug' => 'kanchenjunga-north-bc', 'type' => 'checkpoint', 'lat' => 27.6456, 'lng' => 87.9123, 'alt' => 5140],
                // Crossing Mirgin La — new waypoints
                ['name' => 'Sele La', 'slug' => 'sele-la', 'type' => 'pass', 'lat' => 27.5321, 'lng' => 88.0123, 'alt' => 4280],
                ['name' => 'Mirgin La', 'slug' => 'mirgin-la', 'type' => 'pass', 'lat' => 27.5089, 'lng' => 88.0456, 'alt' => 4663],
                ['name' => 'Tseram', 'slug' => 'tseram', 'type' => 'village', 'lat' => 27.5234, 'lng' => 88.0234, 'alt' => 3770],
                // South-side waypoints (shared with Kanchenjunga South seeder)
                ['name' => 'Ramche', 'slug' => 'ramche', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 4180],
                ['name' => 'Kanchenjunga South Base Camp', 'slug' => 'kanchenjunga-south-bc', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4500],
                ['name' => 'Torotong', 'slug' => 'torotong', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.7789, 'alt' => 2990],
                ['name' => 'Yamphudin', 'slug' => 'yamphudin', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1670],
                ['name' => 'Mamanke', 'slug' => 'mamanke', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
            ],
            'segments' => [
                // ─── Forward: North approach (days 1-10) ───
                ['from' => 'suketar', 'to' => 'kabeli', 'dist' => 8.0, 'time' => 4.0, 'loss' => 720],
                ['from' => 'kabeli', 'to' => 'chirwa', 'dist' => 8.0, 'time' => 4.0, 'loss' => 430],
                ['from' => 'chirwa', 'to' => 'sakathum', 'dist' => 10.0, 'time' => 5.0, 'gain' => 380],
                ['from' => 'sakathum', 'to' => 'amjilosa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 860],
                ['from' => 'amjilosa', 'to' => 'gyabla', 'dist' => 6.0, 'time' => 3.0, 'gain' => 220],
                ['from' => 'gyabla', 'to' => 'ghunsa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                // ─── Rest day at Ghunsa ───
                ['from' => 'ghunsa', 'to' => 'ghunsa', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'ghunsa', 'to' => 'kambachen', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'kambachen', 'to' => 'kambachen', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'kambachen', 'to' => 'lhonak', 'dist' => 7.0, 'time' => 4.0, 'gain' => 740],
                ['from' => 'lhonak', 'to' => 'kanchenjunga-north-bc', 'dist' => 4.0, 'time' => 2.5, 'gain' => 360],
                // ─── Rest day at North BC ───
                ['from' => 'kanchenjunga-north-bc', 'to' => 'kanchenjunga-north-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // ─── Cross back to Ghunsa (days 12-14) ───
                ['from' => 'kanchenjunga-north-bc', 'to' => 'lhonak', 'dist' => 4.0, 'time' => 2.5, 'loss' => 360],
                ['from' => 'lhonak', 'to' => 'kambachen', 'dist' => 7.0, 'time' => 3.5, 'loss' => 740],
                ['from' => 'kambachen', 'to' => 'ghunsa', 'dist' => 8.0, 'time' => 4.0, 'loss' => 610],
                // ─── Cross Mirgin La (days 15-16) ───
                ['from' => 'ghunsa', 'to' => 'sele-la', 'dist' => 12.0, 'time' => 6.5, 'gain' => 850],
                ['from' => 'sele-la', 'to' => 'tseram', 'dist' => 18.0, 'time' => 9.0, 'gain' => 383, 'loss' => 893],
                // ─── Rest day at Tseram ───
                ['from' => 'tseram', 'to' => 'tseram', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // ─── South BC exploration (days 18-19) ───
                ['from' => 'tseram', 'to' => 'ramche', 'dist' => 7.0, 'time' => 3.5, 'gain' => 410],
                ['from' => 'ramche', 'to' => 'kanchenjunga-south-bc', 'dist' => 4.0, 'time' => 2.0, 'gain' => 320],
                ['from' => 'kanchenjunga-south-bc', 'to' => 'ramche', 'dist' => 4.0, 'time' => 2.0, 'loss' => 320],
                // ─── Rest day at Ramche ───
                ['from' => 'ramche', 'to' => 'ramche', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // ─── Return via South (days 21-22) ───
                ['from' => 'ramche', 'to' => 'torotong', 'dist' => 10.0, 'time' => 5.0, 'loss' => 1190],
                ['from' => 'torotong', 'to' => 'suketar', 'dist' => 22.0, 'time' => 10.0, 'loss' => 570],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Kanchenjunga Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Kanchenjunga Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 45, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kanchenjunga Circuit seeded.');

                // ==========================================
        // 4. MAKALU BASE CAMP
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Makalu Base Camp Trek',
                'slug' => 'makalu-base-camp',
                'description' => 'Trek to the base camp of Mount Makalu (8463m), the fifth highest mountain in the world.',
                'difficulty' => 'hard',
                'duration_days' => 17,
                'max_altitude' => 4870,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Tumlingtar', 'slug' => 'tumlingtar', 'type' => 'village', 'lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
                ['name' => 'Chichila', 'slug' => 'chichila', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.2456, 'alt' => 1930],
                ['name' => 'Num', 'slug' => 'num', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.2678, 'alt' => 1490],
                ['name' => 'Sedua', 'slug' => 'sedua', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.2901, 'alt' => 1540],
                ['name' => 'Tashigaon', 'slug' => 'tashigaon', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.3123, 'alt' => 2100],
                ['name' => 'Kharkadanda', 'slug' => 'kharkadanda', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.3345, 'alt' => 2800],
                ['name' => 'Mumbuk', 'slug' => 'mumbuk', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.3567, 'alt' => 3400],
                ['name' => 'Yangri Kharka', 'slug' => 'yangri-kharka', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.3789, 'alt' => 3770],
                ['name' => 'Makalu Base Camp', 'slug' => 'makalu-bc', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.4012, 'alt' => 4870],
            ],
            'segments' => [
                // Forward (8)
                ['from' => 'tumlingtar', 'to' => 'chichila', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1480, 'loss' => 0],
                ['from' => 'chichila', 'to' => 'num', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 440],
                ['from' => 'num', 'to' => 'sedua', 'dist' => 5.0, 'time' => 2.5, 'gain' => 50, 'loss' => 0],
                ['from' => 'sedua', 'to' => 'tashigaon', 'dist' => 6.0, 'time' => 3.0, 'gain' => 560, 'loss' => 0],
                ['from' => 'tashigaon', 'to' => 'kharkadanda', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700, 'loss' => 0],
                ['from' => 'kharkadanda', 'to' => 'mumbuk', 'dist' => 7.0, 'time' => 3.5, 'gain' => 600, 'loss' => 0],
                ['from' => 'mumbuk', 'to' => 'yangri-kharka', 'dist' => 6.0, 'time' => 3.0, 'gain' => 370, 'loss' => 0],
                ['from' => 'yangri-kharka', 'to' => 'makalu-bc', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1100, 'loss' => 0],
                // Rest / acclimatization at BC (2)
                ['from' => 'makalu-bc', 'to' => 'makalu-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'makalu-bc', 'to' => 'makalu-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // Return (7)
                ['from' => 'makalu-bc', 'to' => 'yangri-kharka', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 1100],
                ['from' => 'yangri-kharka', 'to' => 'mumbuk', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 370],
                ['from' => 'mumbuk', 'to' => 'kharkadanda', 'dist' => 7.0, 'time' => 3.5, 'gain' => 0, 'loss' => 600],
                ['from' => 'kharkadanda', 'to' => 'tashigaon', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 700],
                ['from' => 'tashigaon', 'to' => 'sedua', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 560],
                ['from' => 'sedua', 'to' => 'num', 'dist' => 5.0, 'time' => 2.5, 'gain' => 0, 'loss' => 50],
                ['from' => 'num', 'to' => 'tumlingtar', 'dist' => 16.0, 'time' => 7.0, 'gain' => 0, 'loss' => 1040],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Makalu-Barun National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Makalu Restricted Area Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Makalu Base Camp seeded (17 segments, 2 rest days).');

                // ==========================================
        // 5. MAKALU–BARUN VALLEY — Extended Makalu BC with Barun Pokhari side trip
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Makalu–Barun Valley Trek',
                'slug' => 'makalu-barun',
                'description' => 'Extended Makalu trek with overnight at Makalu Base Camp, rest day, and side trip to Barun Pokhari (Barun Lake) — the source of the Barun River in the Makalu-Barun National Park.',
                'difficulty' => 'hard',
                'duration_days' => 19,
                'max_altitude' => 5000,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Tumlingtar', 'slug' => 'tumlingtar', 'type' => 'village', 'lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
                ['name' => 'Chichila', 'slug' => 'chichila', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.2456, 'alt' => 1930],
                ['name' => 'Num', 'slug' => 'num', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.2678, 'alt' => 1490],
                ['name' => 'Sedua', 'slug' => 'sedua', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.2901, 'alt' => 1540],
                ['name' => 'Tashigaon', 'slug' => 'tashigaon', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.3123, 'alt' => 2100],
                ['name' => 'Kharkadanda', 'slug' => 'kharkadanda', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.3345, 'alt' => 2800],
                ['name' => 'Mumbuk', 'slug' => 'mumbuk', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.3567, 'alt' => 3400],
                ['name' => 'Yangri Kharka', 'slug' => 'yangri-kharka', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.3789, 'alt' => 3770],
                ['name' => 'Langmale Kharka', 'slug' => 'langmale-kharka', 'type' => 'village', 'lat' => 27.5600, 'lng' => 87.3900, 'alt' => 4600],
                ['name' => 'Makalu Base Camp', 'slug' => 'makalu-bc', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.4012, 'alt' => 4870],
                ['name' => 'Barun Valley', 'slug' => 'barun-valley', 'type' => 'landmark', 'lat' => 27.6200, 'lng' => 87.4300, 'alt' => 4600],
            ],
            'segments' => [
                // Outbound (Days 1-9)
                ['from' => 'tumlingtar', 'to' => 'chichila', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1480, 'loss' => 0],
                ['from' => 'chichila', 'to' => 'num', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 440],
                ['from' => 'num', 'to' => 'sedua', 'dist' => 5.0, 'time' => 2.5, 'gain' => 50, 'loss' => 0],
                ['from' => 'sedua', 'to' => 'tashigaon', 'dist' => 6.0, 'time' => 3.0, 'gain' => 560, 'loss' => 0],
                ['from' => 'tashigaon', 'to' => 'kharkadanda', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700, 'loss' => 0],
                ['from' => 'kharkadanda', 'to' => 'mumbuk', 'dist' => 7.0, 'time' => 3.5, 'gain' => 600, 'loss' => 0],
                ['from' => 'mumbuk', 'to' => 'yangri-kharka', 'dist' => 6.0, 'time' => 3.0, 'gain' => 370, 'loss' => 0],
                ['from' => 'yangri-kharka', 'to' => 'langmale-kharka', 'dist' => 6.0, 'time' => 4.0, 'gain' => 830, 'loss' => 0],
                ['from' => 'langmale-kharka', 'to' => 'makalu-bc', 'dist' => 6.0, 'time' => 4.0, 'gain' => 270, 'loss' => 0],
                // Rest at Makalu BC (Day 10)
                ['from' => 'makalu-bc', 'to' => 'makalu-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // Barun Valley side trip (Days 11-13)
                ['from' => 'makalu-bc', 'to' => 'barun-valley', 'dist' => 5.0, 'time' => 3.0, 'gain' => 0, 'loss' => 270],
                ['from' => 'barun-valley', 'to' => 'barun-valley', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'barun-valley', 'to' => 'makalu-bc', 'dist' => 5.0, 'time' => 2.5, 'gain' => 270, 'loss' => 0],
                // Return (Days 14-19)
                ['from' => 'makalu-bc', 'to' => 'yangri-kharka', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1100],
                ['from' => 'yangri-kharka', 'to' => 'mumbuk', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 370],
                ['from' => 'mumbuk', 'to' => 'kharkadanda', 'dist' => 7.0, 'time' => 3.5, 'gain' => 0, 'loss' => 600],
                ['from' => 'kharkadanda', 'to' => 'tashigaon', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 700],
                ['from' => 'tashigaon', 'to' => 'num', 'dist' => 11.0, 'time' => 5.0, 'gain' => 0, 'loss' => 610],
                ['from' => 'num', 'to' => 'tumlingtar', 'dist' => 16.0, 'time' => 7.0, 'gain' => 0, 'loss' => 1040],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Makalu-Barun National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Makalu Restricted Area Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Makalu–Barun Valley seeded (19 segs, 2 rest, Tumlingtar→BC→Barun Valley→return).');

        $this->command->info('🎉 Kanchenjunga & Makalu Region Complete! 5 destinations seeded.');
    }
}
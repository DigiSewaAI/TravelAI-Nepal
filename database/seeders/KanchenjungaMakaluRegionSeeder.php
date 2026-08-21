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
                ['name' => 'Suketar', 'slug' => 'suketar-north-return', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
            ],
            'segments' => [
                ['from' => 'suketar', 'to' => 'kabeli', 'dist' => 8.0, 'time' => 4.0, 'loss' => 720],
                ['from' => 'kabeli', 'to' => 'chirwa', 'dist' => 8.0, 'time' => 4.0, 'loss' => 430],
                ['from' => 'chirwa', 'to' => 'sakathum', 'dist' => 10.0, 'time' => 5.0, 'gain' => 380],
                ['from' => 'sakathum', 'to' => 'amjilosa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 860],
                ['from' => 'amjilosa', 'to' => 'gyabla', 'dist' => 6.0, 'time' => 3.0, 'gain' => 220],
                ['from' => 'gyabla', 'to' => 'ghunsa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                ['from' => 'ghunsa', 'to' => 'kambachen', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'kambachen', 'to' => 'lhonak', 'dist' => 7.0, 'time' => 4.0, 'gain' => 740],
                ['from' => 'lhonak', 'to' => 'kanchenjunga-north-bc', 'dist' => 4.0, 'time' => 2.5, 'gain' => 360],
                ['from' => 'kanchenjunga-north-bc', 'to' => 'suketar-north-return', 'dist' => 65.0, 'time' => 24.0, 'loss' => 2720],
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
                ['name' => 'Suketar', 'slug' => 'suketar-south', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
                ['name' => 'Mamanke', 'slug' => 'mamanke', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
                ['name' => 'Yamphudin', 'slug' => 'yamphudin', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1670],
                ['name' => 'Torotong', 'slug' => 'torotong', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.7789, 'alt' => 2990],
                ['name' => 'Lamite', 'slug' => 'lamite', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.8012, 'alt' => 3430],
                ['name' => 'Cheram', 'slug' => 'cheram', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.8234, 'alt' => 3870],
                ['name' => 'Ramche', 'slug' => 'ramche', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 4180],
                ['name' => 'Kanchenjunga South Base Camp', 'slug' => 'kanchenjunga-south-bc', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4500],
                ['name' => 'Suketar', 'slug' => 'suketar-south-return', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
            ],
            'segments' => [
                ['from' => 'suketar-south', 'to' => 'mamanke', 'dist' => 6.0, 'time' => 3.0, 'loss' => 720],
                ['from' => 'mamanke', 'to' => 'yamphudin', 'dist' => 6.0, 'time' => 3.0, 'loss' => 30],
                ['from' => 'yamphudin', 'to' => 'torotong', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1320],
                ['from' => 'torotong', 'to' => 'lamite', 'dist' => 6.0, 'time' => 3.0, 'gain' => 440],
                ['from' => 'lamite', 'to' => 'cheram', 'dist' => 6.0, 'time' => 3.0, 'gain' => 440],
                ['from' => 'cheram', 'to' => 'ramche', 'dist' => 5.0, 'time' => 2.5, 'gain' => 310],
                ['from' => 'ramche', 'to' => 'kanchenjunga-south-bc', 'dist' => 4.0, 'time' => 2.0, 'gain' => 320],
                ['from' => 'kanchenjunga-south-bc', 'to' => 'suketar-south-return', 'dist' => 43.0, 'time' => 16.0, 'loss' => 2080],
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
                'duration_days' => 22,
                'max_altitude' => 5500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Suketar', 'slug' => 'suketar-circuit', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
                ['name' => 'Kabeli', 'slug' => 'kabeli-circuit', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.7345, 'alt' => 1700],
                ['name' => 'Ghunsa', 'slug' => 'ghunsa-circuit', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.8456, 'alt' => 3430],
                ['name' => 'Kanchenjunga North BC', 'slug' => 'kanchenjunga-north-circuit', 'type' => 'checkpoint', 'lat' => 27.6456, 'lng' => 87.9123, 'alt' => 5140],
                ['name' => 'Mirgin La', 'slug' => 'mirgin-la', 'type' => 'pass', 'lat' => 27.6789, 'lng' => 87.9345, 'alt' => 5500],
                ['name' => 'Kanchenjunga South BC', 'slug' => 'kanchenjunga-south-circuit', 'type' => 'checkpoint', 'lat' => 27.5789, 'lng' => 87.8678, 'alt' => 4500],
                ['name' => 'Yamphudin', 'slug' => 'yamphudin-circuit', 'type' => 'village', 'lat' => 27.4123, 'lng' => 87.7567, 'alt' => 1670],
                ['name' => 'Suketar', 'slug' => 'suketar-circuit-return', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.7123, 'alt' => 2420],
            ],
            'segments' => [
                ['from' => 'suketar-circuit', 'to' => 'kabeli-circuit', 'dist' => 8.0, 'time' => 4.0, 'loss' => 720],
                ['from' => 'kabeli-circuit', 'to' => 'ghunsa-circuit', 'dist' => 22.0, 'time' => 10.0, 'gain' => 1730],
                ['from' => 'ghunsa-circuit', 'to' => 'kanchenjunga-north-circuit', 'dist' => 16.0, 'time' => 8.0, 'gain' => 1710],
                ['from' => 'kanchenjunga-north-circuit', 'to' => 'mirgin-la', 'dist' => 8.0, 'time' => 4.5, 'gain' => 360],
                ['from' => 'mirgin-la', 'to' => 'kanchenjunga-south-circuit', 'dist' => 14.0, 'time' => 7.0, 'loss' => 1000],
                ['from' => 'kanchenjunga-south-circuit', 'to' => 'yamphudin-circuit', 'dist' => 16.0, 'time' => 7.0, 'loss' => 2830],
                ['from' => 'yamphudin-circuit', 'to' => 'suketar-circuit-return', 'dist' => 14.0, 'time' => 6.0, 'gain' => 750],
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
                ['name' => 'Tumlingtar', 'slug' => 'tumlingtar-return', 'type' => 'village', 'lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
            ],
            'segments' => [
                ['from' => 'tumlingtar', 'to' => 'chichila', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1480],
                ['from' => 'chichila', 'to' => 'num', 'dist' => 6.0, 'time' => 3.0, 'loss' => 440],
                ['from' => 'num', 'to' => 'sedua', 'dist' => 5.0, 'time' => 2.5, 'gain' => 50],
                ['from' => 'sedua', 'to' => 'tashigaon', 'dist' => 6.0, 'time' => 3.0, 'gain' => 560],
                ['from' => 'tashigaon', 'to' => 'kharkadanda', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                ['from' => 'kharkadanda', 'to' => 'mumbuk', 'dist' => 7.0, 'time' => 3.5, 'gain' => 600],
                ['from' => 'mumbuk', 'to' => 'yangri-kharka', 'dist' => 6.0, 'time' => 3.0, 'gain' => 370],
                ['from' => 'yangri-kharka', 'to' => 'makalu-bc', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1100],
                ['from' => 'makalu-bc', 'to' => 'tumlingtar-return', 'dist' => 56.0, 'time' => 22.0, 'loss' => 4420],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Makalu-Barun National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Makalu Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Makalu Base Camp seeded.');

        // ==========================================
        // 5. MAKALU–BARUN VALLEY
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Makalu–Barun Valley Trek',
                'slug' => 'makalu-barun',
                'description' => 'Trek through the pristine Makalu–Barun Valley, a biodiversity hotspot with rhododendron forests and alpine meadows.',
                'difficulty' => 'hard',
                'duration_days' => 19,
                'max_altitude' => 4600,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Tumlingtar', 'slug' => 'tumlingtar-mb', 'type' => 'village', 'lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
                ['name' => 'Chichila', 'slug' => 'chichila-mb', 'type' => 'village', 'lat' => 27.3456, 'lng' => 87.2456, 'alt' => 1930],
                ['name' => 'Num', 'slug' => 'num-mb', 'type' => 'village', 'lat' => 27.3789, 'lng' => 87.2678, 'alt' => 1490],
                ['name' => 'Tashigaon', 'slug' => 'tashigaon-mb', 'type' => 'village', 'lat' => 27.4456, 'lng' => 87.3123, 'alt' => 2100],
                ['name' => 'Kharkadanda', 'slug' => 'kharkadanda-mb', 'type' => 'village', 'lat' => 27.4789, 'lng' => 87.3345, 'alt' => 2800],
                ['name' => 'Mumbuk', 'slug' => 'mumbuk-mb', 'type' => 'village', 'lat' => 27.5123, 'lng' => 87.3567, 'alt' => 3400],
                ['name' => 'Yangri Kharka', 'slug' => 'yangri-mb', 'type' => 'village', 'lat' => 27.5456, 'lng' => 87.3789, 'alt' => 3770],
                ['name' => 'Barun Valley', 'slug' => 'barun-valley', 'type' => 'village', 'lat' => 27.5789, 'lng' => 87.4012, 'alt' => 3800],
                ['name' => 'Tumlingtar', 'slug' => 'tumlingtar-mb-return', 'type' => 'village', 'lat' => 27.3123, 'lng' => 87.2234, 'alt' => 450],
            ],
            'segments' => [
                ['from' => 'tumlingtar-mb', 'to' => 'chichila-mb', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1480],
                ['from' => 'chichila-mb', 'to' => 'num-mb', 'dist' => 6.0, 'time' => 3.0, 'loss' => 440],
                ['from' => 'num-mb', 'to' => 'tashigaon-mb', 'dist' => 10.0, 'time' => 5.0, 'gain' => 610],
                ['from' => 'tashigaon-mb', 'to' => 'kharkadanda-mb', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                ['from' => 'kharkadanda-mb', 'to' => 'mumbuk-mb', 'dist' => 7.0, 'time' => 3.5, 'gain' => 600],
                ['from' => 'mumbuk-mb', 'to' => 'yangri-mb', 'dist' => 6.0, 'time' => 3.0, 'gain' => 370],
                ['from' => 'yangri-mb', 'to' => 'barun-valley', 'dist' => 6.0, 'time' => 3.0, 'gain' => 30],
                ['from' => 'barun-valley', 'to' => 'tumlingtar-mb-return', 'dist' => 54.0, 'time' => 20.0, 'loss' => 3350],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Makalu-Barun National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Makalu Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Makalu–Barun Valley seeded.');

        $this->command->info('🎉 Kanchenjunga & Makalu Region Complete! 5 destinations seeded.');
    }
}
<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class RemoteTreksSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏔️ Seeding Remote & Off-the-Beaten-Path Treks...');

        // ==========================================
        // 1. RARA LAKE TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Rara Lake Trek',
                'slug' => 'rara-lake',
                'description' => 'Trek to the pristine Rara Lake, the largest lake in Nepal, surrounded by alpine forests and mountains.',
                'difficulty' => 'moderate',
                'duration_days' => 9,
                'max_altitude' => 2990,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla-rara', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Gothichaur', 'slug' => 'gothichaur', 'type' => 'village', 'lat' => 29.3123, 'lng' => 82.2345, 'alt' => 2600],
                ['name' => 'Chautha', 'slug' => 'chautha', 'type' => 'village', 'lat' => 29.3456, 'lng' => 82.3123, 'alt' => 2800],
                ['name' => 'Rara Lake', 'slug' => 'rara-lake', 'type' => 'landmark', 'lat' => 29.3789, 'lng' => 82.3891, 'alt' => 2990],
                ['name' => 'Jumla', 'slug' => 'jumla-rara-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'segments' => [
                ['from' => 'jumla-rara', 'to' => 'gothichaur', 'dist' => 8.0, 'time' => 4.0, 'gain' => 260],
                ['from' => 'gothichaur', 'to' => 'chautha', 'dist' => 8.0, 'time' => 4.0, 'gain' => 200],
                ['from' => 'chautha', 'to' => 'rara-lake', 'dist' => 8.0, 'time' => 4.0, 'gain' => 190],
                ['from' => 'rara-lake', 'to' => 'jumla-rara-return', 'dist' => 24.0, 'time' => 10.0, 'loss' => 650],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Rara National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Rara Lake seeded.');

                // ==========================================
        // 2. BARDIYA NATIONAL PARK TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Bardiya National Park Trek',
                'slug' => 'bardiya-trek',
                'description' => 'Jungle safari in Nepal\'s largest national park, home to Bengal tigers, one-horned rhinos, and wild elephants. Base camp at Thakurdwara with excursions to Baghaura Phanta grassland and the Karnali River floodplain.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 200,
                'season' => 'October–June',
            ],
            'waypoints' => [
                ['name' => 'Thakurdwara', 'slug' => 'thakurdwara', 'type' => 'village', 'lat' => 28.3123, 'lng' => 81.4234, 'alt' => 150],
                ['name' => 'Bagaura Phanta', 'slug' => 'bagaura-phanta', 'type' => 'village', 'lat' => 28.3456, 'lng' => 81.4567, 'alt' => 180],
                ['name' => 'Karnali River', 'slug' => 'karnali-river', 'type' => 'landmark', 'lat' => 28.3789, 'lng' => 81.4891, 'alt' => 200],
            ],
            'segments' => [
                // Outbound (Days 1-2)
                ['from' => 'thakurdwara', 'to' => 'bagaura-phanta', 'dist' => 8.0, 'time' => 4.0, 'gain' => 30, 'loss' => 0],
                ['from' => 'bagaura-phanta', 'to' => 'karnali-river', 'dist' => 8.0, 'time' => 4.0, 'gain' => 20, 'loss' => 0],
                // Rest at Karnali (Days 3-4 — rafting, jeep safari, wildlife viewing)
                ['from' => 'karnali-river', 'to' => 'karnali-river', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'karnali-river', 'to' => 'karnali-river', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // Return (Days 5-6)
                ['from' => 'karnali-river', 'to' => 'bagaura-phanta', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 20],
                ['from' => 'bagaura-phanta', 'to' => 'thakurdwara', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 30],
                // Departure (Day 7)
                ['from' => 'thakurdwara', 'to' => 'thakurdwara', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Bardiya National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'activity', 'name' => 'Jungle Safari (Jeep)', 'amount' => 40, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Karnali River Rafting', 'amount' => 25, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'guide_estimate', 'name' => 'Guide Service', 'amount' => 15, 'currency' => 'USD', 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bardiya National Park seeded (7 segs, 3 rest, Thakurdwara↔Karnali River).');

        // ==========================================
        // 3. PANCH POKHARI TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Panch Pokhari Trek',
                'slug' => 'panch-pokhari',
                'description' => 'Trek to the sacred Panch Pokhari (Five Lakes) in the Sindhupalchok district, an important pilgrimage site.',
                'difficulty' => 'moderate',
                'duration_days' => 8,
                'max_altitude' => 4100,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Chautara', 'slug' => 'chautara', 'type' => 'village', 'lat' => 27.7567, 'lng' => 85.7123, 'alt' => 1400],
                ['name' => 'Dhunge', 'slug' => 'dhunge', 'type' => 'village', 'lat' => 27.7891, 'lng' => 85.7345, 'alt' => 2000],
                ['name' => 'Panch Pokhari', 'slug' => 'panch-pokhari', 'type' => 'landmark', 'lat' => 27.8234, 'lng' => 85.7567, 'alt' => 4100],
                ['name' => 'Chautara', 'slug' => 'chautara-return', 'type' => 'village', 'lat' => 27.7567, 'lng' => 85.7123, 'alt' => 1400],
            ],
            'segments' => [
                ['from' => 'chautara', 'to' => 'dhunge', 'dist' => 10.0, 'time' => 5.0, 'gain' => 600],
                ['from' => 'dhunge', 'to' => 'panch-pokhari', 'dist' => 12.0, 'time' => 6.0, 'gain' => 2100],
                ['from' => 'panch-pokhari', 'to' => 'chautara-return', 'dist' => 22.0, 'time' => 9.0, 'loss' => 2700],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Panch Pokhari seeded.');

                // ==========================================
        // 4. ROLWALING VALLEY (TSO ROLPA) — Via Tamakoshi valley
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Rolwaling Valley (Tso Rolpa) Trek',
                'slug' => 'rolwaling',
                'description' => 'Trek through the remote Rolwaling Valley along the Tamakoshi River to Tso Rolpa, one of the largest glacial lakes in Nepal, with views of Gauri Shankar and Melungtse.',
                'difficulty' => 'hard',
                'duration_days' => 13,
                'max_altitude' => 4540,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Dolakha', 'slug' => 'dolakha', 'type' => 'village', 'lat' => 27.6123, 'lng' => 86.2234, 'alt' => 1500],
                ['name' => 'Singati', 'slug' => 'singati', 'type' => 'village', 'lat' => 27.68, 'lng' => 86.20, 'alt' => 950],
                ['name' => 'Jagat', 'slug' => 'jagat', 'type' => 'village', 'lat' => 27.70, 'lng' => 86.23, 'alt' => 1150],
                ['name' => 'Simigaon', 'slug' => 'simigaon', 'type' => 'village', 'lat' => 27.73, 'lng' => 86.25, 'alt' => 2000],
                ['name' => 'Dongang', 'slug' => 'dongang', 'type' => 'village', 'lat' => 27.76, 'lng' => 86.26, 'alt' => 2800],
                ['name' => 'Beding', 'slug' => 'beding', 'type' => 'village', 'lat' => 27.6456, 'lng' => 86.2456, 'alt' => 3690],
                ['name' => 'Na', 'slug' => 'na', 'type' => 'village', 'lat' => 27.6789, 'lng' => 86.2678, 'alt' => 4180],
                ['name' => 'Tso Rolpa', 'slug' => 'tso-rolpa', 'type' => 'landmark', 'lat' => 27.7123, 'lng' => 86.2901, 'alt' => 4540],
            ],
            'segments' => [
                ['from' => 'dolakha', 'to' => 'singati', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 550],
                ['from' => 'singati', 'to' => 'jagat', 'dist' => 10.0, 'time' => 4.5, 'gain' => 200, 'loss' => 0],
                ['from' => 'jagat', 'to' => 'simigaon', 'dist' => 8.0, 'time' => 5.0, 'gain' => 850, 'loss' => 0],
                ['from' => 'simigaon', 'to' => 'dongang', 'dist' => 10.0, 'time' => 5.5, 'gain' => 800, 'loss' => 0],
                ['from' => 'dongang', 'to' => 'beding', 'dist' => 10.0, 'time' => 6.0, 'gain' => 890, 'loss' => 0],
                ['from' => 'beding', 'to' => 'beding', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'beding', 'to' => 'na', 'dist' => 6.0, 'time' => 4.0, 'gain' => 490, 'loss' => 0],
                ['from' => 'na', 'to' => 'na', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'na', 'to' => 'tso-rolpa', 'dist' => 6.0, 'time' => 3.5, 'gain' => 360, 'loss' => 0],
                ['from' => 'tso-rolpa', 'to' => 'na', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 360],
                ['from' => 'na', 'to' => 'beding', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 490],
                ['from' => 'beding', 'to' => 'simigaon', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1690],
                ['from' => 'simigaon', 'to' => 'jagat', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 850],
                ['from' => 'jagat', 'to' => 'dolakha', 'dist' => 14.0, 'time' => 6.0, 'gain' => 550, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Rolwaling Restricted Area Permit', 'amount' => 50, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Rolwaling seeded (13 segs, 1 rest, Dolakha↔Tso Rolpa).');

                // ==========================================
        // 5. HUMLA TREK — Simikot → Hilsa via Karnali valley
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Humla Trek',
                'slug' => 'humla',
                'description' => 'Remote trek through the Humla valley along the Karnali River from Simikot to the Tibetan border post of Hilsa, visiting ancient Buddhist monasteries and traditional villages.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 3720,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Simikot', 'slug' => 'simikot', 'type' => 'village', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
                ['name' => 'Chhipra', 'slug' => 'chhipra', 'type' => 'village', 'lat' => 29.92, 'lng' => 81.95, 'alt' => 2300],
                ['name' => 'Torpa', 'slug' => 'torpa', 'type' => 'village', 'lat' => 29.88, 'lng' => 81.98, 'alt' => 2100],
                ['name' => 'Kermi', 'slug' => 'kermi', 'type' => 'village', 'lat' => 29.85, 'lng' => 82.02, 'alt' => 2670],
                ['name' => 'Yalbang', 'slug' => 'yalbang', 'type' => 'village', 'lat' => 29.83, 'lng' => 82.04, 'alt' => 2850],
                ['name' => 'Muchu', 'slug' => 'muchu', 'type' => 'village', 'lat' => 29.80, 'lng' => 82.06, 'alt' => 3040],
                ['name' => 'Yari', 'slug' => 'yari', 'type' => 'village', 'lat' => 29.79, 'lng' => 82.08, 'alt' => 3700],
                ['name' => 'Hilsa', 'slug' => 'hilsa', 'type' => 'village', 'lat' => 29.7789, 'lng' => 82.0789, 'alt' => 3720],
            ],
            'segments' => [
                ['from' => 'simikot', 'to' => 'chhipra', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 650],
                ['from' => 'chhipra', 'to' => 'torpa', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 200],
                ['from' => 'torpa', 'to' => 'kermi', 'dist' => 8.0, 'time' => 4.5, 'gain' => 570, 'loss' => 0],
                ['from' => 'kermi', 'to' => 'yalbang', 'dist' => 10.0, 'time' => 5.0, 'gain' => 180, 'loss' => 0],
                ['from' => 'yalbang', 'to' => 'muchu', 'dist' => 6.0, 'time' => 3.5, 'gain' => 190, 'loss' => 0],
                ['from' => 'muchu', 'to' => 'yari', 'dist' => 10.0, 'time' => 6.0, 'gain' => 660, 'loss' => 0],
                ['from' => 'yari', 'to' => 'hilsa', 'dist' => 6.0, 'time' => 3.0, 'gain' => 20, 'loss' => 0],
                ['from' => 'hilsa', 'to' => 'yari', 'dist' => 6.0, 'time' => 2.5, 'gain' => 0, 'loss' => 20],
                ['from' => 'yari', 'to' => 'yari', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'yari', 'to' => 'muchu', 'dist' => 10.0, 'time' => 4.5, 'gain' => 0, 'loss' => 660],
                ['from' => 'muchu', 'to' => 'yalbang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 190],
                ['from' => 'yalbang', 'to' => 'kermi', 'dist' => 10.0, 'time' => 4.0, 'gain' => 0, 'loss' => 180],
                ['from' => 'kermi', 'to' => 'torpa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 570],
                ['from' => 'torpa', 'to' => 'chhipra', 'dist' => 6.0, 'time' => 2.5, 'gain' => 0, 'loss' => 0],
                ['from' => 'chhipra', 'to' => 'simikot', 'dist' => 6.0, 'time' => 3.5, 'gain' => 650, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Humla Restricted Area Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Humla seeded (15 segs, 1 rest, Simikot↔Hilsa).');

        // ==========================================
        // 6. DHAULAGIRI CIRCUIT
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Dhaulagiri Circuit',
                'slug' => 'dhaulagiri-circuit',
                'description' => 'Epic trek around Mount Dhaulagiri (8167m), crossing the French Col and crossing high passes.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 5360,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Bagh Kharka', 'slug' => 'bagh-kharka', 'type' => 'village', 'lat' => 28.7123, 'lng' => 83.6123, 'alt' => 2100],
                ['name' => 'Dobang', 'slug' => 'dobang-dhaulagiri', 'type' => 'village', 'lat' => 28.7456, 'lng' => 83.6345, 'alt' => 2700],
                ['name' => 'Jungle Camp', 'slug' => 'jungle-camp-dhaulagiri', 'type' => 'village', 'lat' => 28.7789, 'lng' => 83.6567, 'alt' => 3200],
                ['name' => 'Italian Base Camp', 'slug' => 'italian-bc', 'type' => 'village', 'lat' => 28.8123, 'lng' => 83.6789, 'alt' => 3660],
                ['name' => 'Dhaulagiri Base Camp', 'slug' => 'dhaulagiri-bc', 'type' => 'checkpoint', 'lat' => 28.8456, 'lng' => 83.7012, 'alt' => 4750],
                ['name' => 'French Col', 'slug' => 'french-col', 'type' => 'pass', 'lat' => 28.8789, 'lng' => 83.7234, 'alt' => 5360],
                ['name' => 'Tukuche', 'slug' => 'tukuche', 'type' => 'village', 'lat' => 28.8123, 'lng' => 83.7456, 'alt' => 2600],
                ['name' => 'Bagh Kharka', 'slug' => 'bagh-kharka-return', 'type' => 'village', 'lat' => 28.7123, 'lng' => 83.6123, 'alt' => 2100],
            ],
            'segments' => [
                ['from' => 'bagh-kharka', 'to' => 'dobang-dhaulagiri', 'dist' => 8.0, 'time' => 4.0, 'gain' => 600],
                ['from' => 'dobang-dhaulagiri', 'to' => 'jungle-camp-dhaulagiri', 'dist' => 8.0, 'time' => 4.0, 'gain' => 500],
                ['from' => 'jungle-camp-dhaulagiri', 'to' => 'italian-bc', 'dist' => 8.0, 'time' => 4.0, 'gain' => 460],
                ['from' => 'italian-bc', 'to' => 'dhaulagiri-bc', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1090],
                ['from' => 'dhaulagiri-bc', 'to' => 'french-col', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'french-col', 'to' => 'tukuche', 'dist' => 12.0, 'time' => 6.0, 'loss' => 2760],
                ['from' => 'tukuche', 'to' => 'bagh-kharka-return', 'dist' => 14.0, 'time' => 6.0, 'loss' => 500],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Dhaulagiri Special Permit', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dhaulagiri Circuit seeded.');

        // ==========================================
        // 7. MAHAKALI RIVER TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Mahakali River Trek',
                'slug' => 'mahakali-river',
                'description' => 'Trek along the Mahakali River, the border between Nepal and India, with views of the Himalayan foothills.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 2000,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Darchula', 'slug' => 'darchula', 'type' => 'village', 'lat' => 29.8456, 'lng' => 80.6123, 'alt' => 700],
                ['name' => 'Sitapur', 'slug' => 'sitapur', 'type' => 'village', 'lat' => 29.7789, 'lng' => 80.6345, 'alt' => 900],
                ['name' => 'Khalanga', 'slug' => 'khalanga', 'type' => 'village', 'lat' => 29.7123, 'lng' => 80.6567, 'alt' => 1200],
                ['name' => 'Mahakali River', 'slug' => 'mahakali-river', 'type' => 'landmark', 'lat' => 29.6456, 'lng' => 80.6789, 'alt' => 1500],
                ['name' => 'Darchula', 'slug' => 'darchula-return', 'type' => 'village', 'lat' => 29.8456, 'lng' => 80.6123, 'alt' => 700],
            ],
            'segments' => [
                ['from' => 'darchula', 'to' => 'sitapur', 'dist' => 10.0, 'time' => 5.0, 'gain' => 200],
                ['from' => 'sitapur', 'to' => 'khalanga', 'dist' => 12.0, 'time' => 6.0, 'gain' => 300],
                ['from' => 'khalanga', 'to' => 'mahakali-river', 'dist' => 14.0, 'time' => 7.0, 'gain' => 300],
                ['from' => 'mahakali-river', 'to' => 'darchula-return', 'dist' => 36.0, 'time' => 14.0, 'loss' => 800],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Mahakali River seeded.');

                // ==========================================
        // 8. API HIMAL TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Api Himal Trek',
                'slug' => 'api-himal',
                'description' => 'Trek to the base camp of Mount Api (7132m) in the far west of Nepal.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 4500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Darchula', 'slug' => 'darchula-api', 'type' => 'village', 'lat' => 29.8456, 'lng' => 80.6123, 'alt' => 700],
                ['name' => 'Gokuleshwar', 'slug' => 'gokuleshwar', 'type' => 'village', 'lat' => 29.8356, 'lng' => 80.5345, 'alt' => 1200],
                ['name' => 'Bitule', 'slug' => 'bitule', 'type' => 'village', 'lat' => 29.8456, 'lng' => 80.5567, 'alt' => 1800],
                ['name' => 'Khandeshwari', 'slug' => 'khandeshwari', 'type' => 'village', 'lat' => 29.8678, 'lng' => 80.5789, 'alt' => 2400],
                ['name' => 'Chiureni', 'slug' => 'chiureni', 'type' => 'village', 'lat' => 29.8891, 'lng' => 80.6012, 'alt' => 3000],
                ['name' => 'Makarigaun', 'slug' => 'makarigaun', 'type' => 'village', 'lat' => 29.9123, 'lng' => 80.6234, 'alt' => 3400],
                ['name' => 'Seti (Api)', 'slug' => 'seti-api', 'type' => 'village', 'lat' => 29.9345, 'lng' => 80.6456, 'alt' => 3800],
                ['name' => 'Api Base Camp', 'slug' => 'api-bc', 'type' => 'checkpoint', 'lat' => 30.0123, 'lng' => 80.6000, 'alt' => 4500],
            ],
            'segments' => [
                // Forward (6)
                ['from' => 'darchula-api', 'to' => 'gokuleshwar', 'dist' => 15.0, 'time' => 5.0, 'gain' => 500, 'loss' => 0],
                ['from' => 'gokuleshwar', 'to' => 'bitule', 'dist' => 12.0, 'time' => 5.0, 'gain' => 600, 'loss' => 0],
                ['from' => 'bitule', 'to' => 'khandeshwari', 'dist' => 14.0, 'time' => 6.0, 'gain' => 600, 'loss' => 0],
                ['from' => 'khandeshwari', 'to' => 'chiureni', 'dist' => 13.0, 'time' => 6.0, 'gain' => 600, 'loss' => 0],
                ['from' => 'chiureni', 'to' => 'makarigaun', 'dist' => 10.0, 'time' => 5.0, 'gain' => 400, 'loss' => 0],
                ['from' => 'makarigaun', 'to' => 'seti-api', 'dist' => 8.0, 'time' => 4.0, 'gain' => 400, 'loss' => 0],
                // Rest at Seti
                ['from' => 'seti-api', 'to' => 'seti-api', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'seti-api', 'to' => 'api-bc', 'dist' => 12.0, 'time' => 6.0, 'gain' => 700, 'loss' => 0],
                // Rest at Base Camp
                ['from' => 'api-bc', 'to' => 'api-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // Return (6)
                ['from' => 'api-bc', 'to' => 'seti-api', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 700],
                ['from' => 'seti-api', 'to' => 'makarigaun', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 400],
                ['from' => 'makarigaun', 'to' => 'chiureni', 'dist' => 10.0, 'time' => 5.0, 'gain' => 0, 'loss' => 400],
                ['from' => 'chiureni', 'to' => 'bitule', 'dist' => 14.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1200],
                ['from' => 'bitule', 'to' => 'gokuleshwar', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 600],
                ['from' => 'gokuleshwar', 'to' => 'darchula-api', 'dist' => 15.0, 'time' => 5.0, 'gain' => 0, 'loss' => 500],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Api Nampa Conservation Area Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Api Himal seeded (15 segments, 2 rest days).');

                // ==========================================
        // 9. SAIPAL TREK — Bajhang → Saipal BC
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Saipal Trek',
                'slug' => 'saipal',
                'description' => 'Remote trek to Mount Saipal (7031m) base camp in far-western Nepal, near the Tibetan border, through traditional Bajhang villages.',
                'difficulty' => 'hard',
                'duration_days' => 13,
                'max_altitude' => 4200,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Bajhang', 'slug' => 'bajhang', 'type' => 'village', 'lat' => 29.7123, 'lng' => 81.2345, 'alt' => 900],
                ['name' => 'Dati', 'slug' => 'dati', 'type' => 'village', 'lat' => 29.7456, 'lng' => 81.2567, 'alt' => 1500],
                ['name' => 'Jhulaghat', 'slug' => 'jhulaghat', 'type' => 'village', 'lat' => 29.7789, 'lng' => 81.2789, 'alt' => 2200],
                ['name' => 'Kanda', 'slug' => 'kanda', 'type' => 'village', 'lat' => 29.72, 'lng' => 81.28, 'alt' => 3200],
                ['name' => 'Nauli', 'slug' => 'nauli', 'type' => 'village', 'lat' => 29.75, 'lng' => 81.30, 'alt' => 3800],
                ['name' => 'Saipal Base Camp', 'slug' => 'saipal-bc', 'type' => 'checkpoint', 'lat' => 29.8123, 'lng' => 81.3012, 'alt' => 4200],
            ],
                        'segments' => [
                ['from' => 'bajhang', 'to' => 'dati', 'dist' => 10.0, 'time' => 5.0, 'gain' => 600, 'loss' => 0],
                ['from' => 'dati', 'to' => 'jhulaghat', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700, 'loss' => 0],
                ['from' => 'jhulaghat', 'to' => 'kanda', 'dist' => 8.0, 'time' => 5.0, 'gain' => 1000, 'loss' => 0],
                ['from' => 'kanda', 'to' => 'kanda', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'kanda', 'to' => 'nauli', 'dist' => 8.0, 'time' => 5.0, 'gain' => 600, 'loss' => 0],
                ['from' => 'nauli', 'to' => 'saipal-bc', 'dist' => 8.0, 'time' => 5.0, 'gain' => 400, 'loss' => 0],
                ['from' => 'saipal-bc', 'to' => 'saipal-bc', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'saipal-bc', 'to' => 'nauli', 'dist' => 8.0, 'time' => 4.5, 'gain' => 0, 'loss' => 400],
                ['from' => 'nauli', 'to' => 'kanda', 'dist' => 8.0, 'time' => 4.5, 'gain' => 0, 'loss' => 600],
                ['from' => 'kanda', 'to' => 'jhulaghat', 'dist' => 8.0, 'time' => 4.5, 'gain' => 0, 'loss' => 1000],
                ['from' => 'jhulaghat', 'to' => 'dati', 'dist' => 8.0, 'time' => 3.5, 'gain' => 0, 'loss' => 700],
                ['from' => 'dati', 'to' => 'bajhang', 'dist' => 10.0, 'time' => 4.5, 'gain' => 0, 'loss' => 600],
                ['from' => 'bajhang', 'to' => 'bajhang', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Saipal Conservation Area Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Saipal seeded (13 segs, 3 rest, Bajhang↔Saipal BC).');

        // ==========================================
        // 10. PHARPING–CHOBAR TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Pharping–Chobar Trek',
                'slug' => 'pharping-chobar',
                'description' => 'Short trek near Kathmandu visiting the Pharping Monastery, Chobar Gorge, and Dakshinkali Temple.',
                'difficulty' => 'easy',
                'duration_days' => 2,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-pc', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Pharping', 'slug' => 'pharping', 'type' => 'village', 'lat' => 27.6456, 'lng' => 85.2678, 'alt' => 1300],
                ['name' => 'Chobar', 'slug' => 'chobar', 'type' => 'village', 'lat' => 27.6123, 'lng' => 85.2901, 'alt' => 1200],
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-pc-return', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            ],
            'segments' => [
                ['from' => 'kathmandu-pc', 'to' => 'pharping', 'dist' => 8.0, 'time' => 3.0, 'loss' => 100],
                ['from' => 'pharping', 'to' => 'chobar', 'dist' => 6.0, 'time' => 2.0, 'loss' => 100],
                ['from' => 'chobar', 'to' => 'kathmandu-pc-return', 'dist' => 10.0, 'time' => 3.0, 'gain' => 200],
            ],
            'costs' => [
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 15, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Pharping–Chobar seeded.');

        // ==========================================
        // 11. SUNDARIJAL–CHISAPANI–NAGARKOT
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Sundarijal–Chisapani–Nagarkot Trek',
                'slug' => 'sundarijal-nagarkot',
                'description' => 'Popular day trek near Kathmandu, offering views of the Himalayas and Kathmandu Valley.',
                'difficulty' => 'easy',
                'duration_days' => 3,
                'max_altitude' => 2195,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Sundarijal', 'slug' => 'sundarijal-sn', 'type' => 'village', 'lat' => 28.0821, 'lng' => 85.4243, 'alt' => 1350],
                ['name' => 'Chisapani', 'slug' => 'chisapani-sn', 'type' => 'village', 'lat' => 28.1356, 'lng' => 85.4283, 'alt' => 2300],
                ['name' => 'Nagarkot', 'slug' => 'nagarkot', 'type' => 'village', 'lat' => 27.7123, 'lng' => 85.5345, 'alt' => 2195],
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-sn', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            ],
            'segments' => [
                ['from' => 'sundarijal-sn', 'to' => 'chisapani-sn', 'dist' => 10.0, 'time' => 4.0, 'gain' => 950],
                ['from' => 'chisapani-sn', 'to' => 'nagarkot', 'dist' => 14.0, 'time' => 5.0, 'loss' => 105],
                ['from' => 'nagarkot', 'to' => 'kathmandu-sn', 'dist' => 20.0, 'time' => 6.0, 'loss' => 795],
            ],
            'costs' => [
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 15, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sundarijal–Chisapani–Nagarkot seeded.');

        // ==========================================
        // 12. SHIVAPURI NAGARJUN TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Shivapuri Nagarjun Trek',
                'slug' => 'shivapuri-nagarjun',
                'description' => 'Trek through the Shivapuri Nagarjun National Park, with views of the Himalayas and Kathmandu Valley.',
                'difficulty' => 'easy',
                'duration_days' => 2,
                'max_altitude' => 2732,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-sn', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Shivapuri Peak', 'slug' => 'shivapuri-peak', 'type' => 'peak', 'lat' => 27.7891, 'lng' => 85.3567, 'alt' => 2732],
                ['name' => 'Nagarjun', 'slug' => 'nagarjun', 'type' => 'village', 'lat' => 27.7567, 'lng' => 85.3891, 'alt' => 1600],
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-sn-return', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            ],
            'segments' => [
                ['from' => 'kathmandu-sn', 'to' => 'shivapuri-peak', 'dist' => 10.0, 'time' => 4.0, 'gain' => 1332],
                ['from' => 'shivapuri-peak', 'to' => 'nagarjun', 'dist' => 8.0, 'time' => 3.0, 'loss' => 1132],
                ['from' => 'nagarjun', 'to' => 'kathmandu-sn-return', 'dist' => 6.0, 'time' => 2.0, 'loss' => 200],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Shivapuri National Park Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 15, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Shivapuri Nagarjun seeded.');

        // ==========================================
        // 13. KAKANI–GURJE BHANJYANG
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Kakani–Gurje Bhanjyang Trek',
                'slug' => 'kakani-gurje',
                'description' => 'Short trek from Kakani to Gurje Bhanjyang, with views of the Ganesh Himal and Langtang ranges.',
                'difficulty' => 'easy',
                'duration_days' => 3,
                'max_altitude' => 2400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kakani', 'slug' => 'kakani', 'type' => 'village', 'lat' => 27.8123, 'lng' => 85.4567, 'alt' => 2000],
                ['name' => 'Gurje Bhanjyang', 'slug' => 'gurje-bhanjyang', 'type' => 'village', 'lat' => 27.8456, 'lng' => 85.4891, 'alt' => 2400],
                ['name' => 'Kakani', 'slug' => 'kakani-return', 'type' => 'village', 'lat' => 27.8123, 'lng' => 85.4567, 'alt' => 2000],
            ],
            'segments' => [
                ['from' => 'kakani', 'to' => 'gurje-bhanjyang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 400],
                ['from' => 'gurje-bhanjyang', 'to' => 'kakani-return', 'dist' => 6.0, 'time' => 2.0, 'loss' => 400],
            ],
            'costs' => [
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 15, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kakani–Gurje Bhanjyang seeded.');

        $this->command->info('🎉 Remote & Off-the-Beaten-Path Treks Complete! 13 destinations seeded.');
    }
}
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
                'description' => 'Trek through Bardiya National Park, Nepal\'s largest national park, home to tigers, rhinos, and elephants.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 200,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Thakurdwara', 'slug' => 'thakurdwara', 'type' => 'village', 'lat' => 28.3123, 'lng' => 81.4234, 'alt' => 150],
                ['name' => 'Bagaura Phanta', 'slug' => 'bagaura-phanta', 'type' => 'village', 'lat' => 28.3456, 'lng' => 81.4567, 'alt' => 180],
                ['name' => 'Karnali River', 'slug' => 'karnali-river', 'type' => 'landmark', 'lat' => 28.3789, 'lng' => 81.4891, 'alt' => 200],
                ['name' => 'Thakurdwara', 'slug' => 'thakurdwara-return', 'type' => 'village', 'lat' => 28.3123, 'lng' => 81.4234, 'alt' => 150],
            ],
            'segments' => [
                ['from' => 'thakurdwara', 'to' => 'bagaura-phanta', 'dist' => 8.0, 'time' => 4.0, 'gain' => 30],
                ['from' => 'bagaura-phanta', 'to' => 'karnali-river', 'dist' => 8.0, 'time' => 4.0, 'gain' => 20],
                ['from' => 'karnali-river', 'to' => 'thakurdwara-return', 'dist' => 16.0, 'time' => 6.0, 'loss' => 50],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Bardiya National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 20, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bardiya National Park seeded.');

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
        // 4. ROLWALING VALLEY (TSO ROLPA)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Rolwaling Valley (Tso Rolpa) Trek',
                'slug' => 'rolwaling',
                'description' => 'Trek to the remote Rolwaling Valley and Tso Rolpa Lake, one of the largest glacial lakes in Nepal.',
                'difficulty' => 'hard',
                'duration_days' => 13,
                'max_altitude' => 4540,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Dolakha', 'slug' => 'dolakha', 'type' => 'village', 'lat' => 27.6123, 'lng' => 86.2234, 'alt' => 1500],
                ['name' => 'Beding', 'slug' => 'beding', 'type' => 'village', 'lat' => 27.6456, 'lng' => 86.2456, 'alt' => 3690],
                ['name' => 'Na', 'slug' => 'na', 'type' => 'village', 'lat' => 27.6789, 'lng' => 86.2678, 'alt' => 4180],
                ['name' => 'Tso Rolpa', 'slug' => 'tso-rolpa', 'type' => 'landmark', 'lat' => 27.7123, 'lng' => 86.2901, 'alt' => 4540],
                ['name' => 'Dolakha', 'slug' => 'dolakha-return', 'type' => 'village', 'lat' => 27.6123, 'lng' => 86.2234, 'alt' => 1500],
            ],
            'segments' => [
                ['from' => 'dolakha', 'to' => 'beding', 'dist' => 14.0, 'time' => 7.0, 'gain' => 2190],
                ['from' => 'beding', 'to' => 'na', 'dist' => 6.0, 'time' => 3.0, 'gain' => 490],
                ['from' => 'na', 'to' => 'tso-rolpa', 'dist' => 6.0, 'time' => 3.0, 'gain' => 360],
                ['from' => 'tso-rolpa', 'to' => 'dolakha-return', 'dist' => 26.0, 'time' => 10.0, 'loss' => 3040],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Rolwaling Restricted Area Permit', 'amount' => 50, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Rolwaling Valley seeded.');

        // ==========================================
        // 5. HUMLA TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Humla Trek',
                'slug' => 'humla',
                'description' => 'Trek to the remote Humla region near the Tibetan border, visiting ancient monasteries and traditional villages.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 4800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Simikot', 'slug' => 'simikot', 'type' => 'village', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
                ['name' => 'Sipsi', 'slug' => 'sipsi', 'type' => 'village', 'lat' => 29.9123, 'lng' => 82.0345, 'alt' => 3400],
                ['name' => 'Jhari', 'slug' => 'jhari', 'type' => 'village', 'lat' => 29.8456, 'lng' => 82.0567, 'alt' => 3700],
                ['name' => 'Hilsa', 'slug' => 'hilsa', 'type' => 'village', 'lat' => 29.7789, 'lng' => 82.0789, 'alt' => 4200],
                ['name' => 'Simikot', 'slug' => 'simikot-return', 'type' => 'village', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
            ],
            'segments' => [
                ['from' => 'simikot', 'to' => 'sipsi', 'dist' => 8.0, 'time' => 4.0, 'gain' => 450],
                ['from' => 'sipsi', 'to' => 'jhari', 'dist' => 12.0, 'time' => 6.0, 'gain' => 300],
                ['from' => 'jhari', 'to' => 'hilsa', 'dist' => 10.0, 'time' => 5.0, 'gain' => 500],
                ['from' => 'hilsa', 'to' => 'simikot-return', 'dist' => 30.0, 'time' => 12.0, 'loss' => 1250],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Humla Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Humla seeded.');

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
                ['name' => 'Sitapur', 'slug' => 'sitapur-api', 'type' => 'village', 'lat' => 29.7789, 'lng' => 80.6345, 'alt' => 900],
                ['name' => 'Khalanga', 'slug' => 'khalanga-api', 'type' => 'village', 'lat' => 29.7123, 'lng' => 80.6567, 'alt' => 1200],
                ['name' => 'Api Base Camp', 'slug' => 'api-bc', 'type' => 'checkpoint', 'lat' => 29.6456, 'lng' => 80.6789, 'alt' => 4500],
                ['name' => 'Darchula', 'slug' => 'darchula-api-return', 'type' => 'village', 'lat' => 29.8456, 'lng' => 80.6123, 'alt' => 700],
            ],
            'segments' => [
                ['from' => 'darchula-api', 'to' => 'sitapur-api', 'dist' => 10.0, 'time' => 5.0, 'gain' => 200],
                ['from' => 'sitapur-api', 'to' => 'khalanga-api', 'dist' => 12.0, 'time' => 6.0, 'gain' => 300],
                ['from' => 'khalanga-api', 'to' => 'api-bc', 'dist' => 18.0, 'time' => 8.0, 'gain' => 3300],
                ['from' => 'api-bc', 'to' => 'darchula-api-return', 'dist' => 40.0, 'time' => 16.0, 'loss' => 3800],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Api Nampa Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Api Himal seeded.');

        // ==========================================
        // 9. SAIPAL TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Saipal Trek',
                'slug' => 'saipal',
                'description' => 'Trek to Mount Saipal (7031m) in the far west of Nepal, near the Tibetan border.',
                'difficulty' => 'hard',
                'duration_days' => 13,
                'max_altitude' => 4200,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Bajhang', 'slug' => 'bajhang', 'type' => 'village', 'lat' => 29.7123, 'lng' => 81.2345, 'alt' => 900],
                ['name' => 'Dati', 'slug' => 'dati', 'type' => 'village', 'lat' => 29.7456, 'lng' => 81.2567, 'alt' => 1500],
                ['name' => 'Jhulaghat', 'slug' => 'jhulaghat', 'type' => 'village', 'lat' => 29.7789, 'lng' => 81.2789, 'alt' => 2200],
                ['name' => 'Saipal Base Camp', 'slug' => 'saipal-bc', 'type' => 'checkpoint', 'lat' => 29.8123, 'lng' => 81.3012, 'alt' => 4200],
                ['name' => 'Bajhang', 'slug' => 'bajhang-return', 'type' => 'village', 'lat' => 29.7123, 'lng' => 81.2345, 'alt' => 900],
            ],
            'segments' => [
                ['from' => 'bajhang', 'to' => 'dati', 'dist' => 10.0, 'time' => 5.0, 'gain' => 600],
                ['from' => 'dati', 'to' => 'jhulaghat', 'dist' => 8.0, 'time' => 4.0, 'gain' => 700],
                ['from' => 'jhulaghat', 'to' => 'saipal-bc', 'dist' => 12.0, 'time' => 6.0, 'gain' => 2000],
                ['from' => 'saipal-bc', 'to' => 'bajhang-return', 'dist' => 30.0, 'time' => 12.0, 'loss' => 3300],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Saipal Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Saipal seeded.');

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
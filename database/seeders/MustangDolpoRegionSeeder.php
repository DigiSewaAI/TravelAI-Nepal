<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class MustangDolpoRegionSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏔️ Seeding Mustang & Dolpo Region...');

        // ==========================================
        // 1. UPPER MUSTANG (LO MANTHANG)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Upper Mustang (Lo Manthang) Trek',
                'slug' => 'upper-mustang',
                'description' => 'Trek to the ancient kingdom of Lo Manthang, exploring Tibetan Buddhist culture, monasteries, and stark Himalayan desert landscapes.',
                'difficulty' => 'moderate',
                'duration_days' => 12,
                'max_altitude' => 3810,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jomsom', 'slug' => 'jomsom-um', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Tangbe', 'slug' => 'tangbe', 'type' => 'village', 'lat' => 28.8345, 'lng' => 83.8012, 'alt' => 2950],
                ['name' => 'Chhusang', 'slug' => 'chhusang', 'type' => 'village', 'lat' => 28.8567, 'lng' => 83.8234, 'alt' => 3020],
                ['name' => 'Chele', 'slug' => 'chele', 'type' => 'village', 'lat' => 28.8789, 'lng' => 83.8456, 'alt' => 3050],
                ['name' => 'Ghemi', 'slug' => 'ghemi', 'type' => 'village', 'lat' => 28.9012, 'lng' => 83.8678, 'alt' => 3510],
                ['name' => 'Tsarang', 'slug' => 'tsarang', 'type' => 'village', 'lat' => 28.9234, 'lng' => 83.8901, 'alt' => 3620],
                ['name' => 'Lo Manthang', 'slug' => 'lo-manthang', 'type' => 'village', 'lat' => 28.9456, 'lng' => 83.9123, 'alt' => 3810],
                ['name' => 'Jomsom', 'slug' => 'jomsom-um-return', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            ],
            'segments' => [
                ['from' => 'jomsom-um', 'to' => 'kagbeni', 'dist' => 6.0, 'time' => 3.0, 'gain' => 100],
                ['from' => 'kagbeni', 'to' => 'tangbe', 'dist' => 5.0, 'time' => 2.5, 'gain' => 150],
                ['from' => 'tangbe', 'to' => 'chhusang', 'dist' => 5.0, 'time' => 2.5, 'gain' => 70],
                ['from' => 'chhusang', 'to' => 'chele', 'dist' => 6.0, 'time' => 3.0, 'gain' => 30],
                ['from' => 'chele', 'to' => 'ghemi', 'dist' => 8.0, 'time' => 4.0, 'gain' => 460],
                ['from' => 'ghemi', 'to' => 'tsarang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 110],
                ['from' => 'tsarang', 'to' => 'lo-manthang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 190],
                ['from' => 'lo-manthang', 'to' => 'jomsom-um-return', 'dist' => 42.0, 'time' => 14.0, 'loss' => 1110],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'Upper Mustang Restricted Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Upper Mustang seeded.');

        // ==========================================
        // 2. LOWER MUSTANG
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Lower Mustang Trek',
                'slug' => 'lower-mustang',
                'description' => 'Trek through the lower Mustang region with visits to Kagbeni, Muktinath, and Jomsom.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jomsom', 'slug' => 'jomsom-lm', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni-lm', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Muktinath', 'slug' => 'muktinath-lm', 'type' => 'village', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
                ['name' => 'Tatopani', 'slug' => 'tatopani-lm', 'type' => 'village', 'lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
                ['name' => 'Ghasa', 'slug' => 'ghasa', 'type' => 'village', 'lat' => 28.6123, 'lng' => 83.6456, 'alt' => 2010],
                ['name' => 'Marpha', 'slug' => 'marpha', 'type' => 'village', 'lat' => 28.7345, 'lng' => 83.7123, 'alt' => 2670],
            ],
            'segments' => [
                ['from' => 'jomsom-lm', 'to' => 'kagbeni-lm', 'dist' => 6.0, 'time' => 3.0, 'gain' => 100],
                ['from' => 'kagbeni-lm', 'to' => 'muktinath-lm', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1000],
                ['from' => 'muktinath-lm', 'to' => 'tatopani-lm', 'dist' => 16.0, 'time' => 6.0, 'loss' => 2610],
                ['from' => 'tatopani-lm', 'to' => 'ghasa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 820],
                ['from' => 'ghasa', 'to' => 'marpha', 'dist' => 14.0, 'time' => 5.0, 'gain' => 660],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Lower Mustang seeded.');

        // ==========================================
        // 3. JOMSOM MUKTINATH TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Jomsom Muktinath Trek',
                'slug' => 'jomsom-muktinath',
                'description' => 'Scenic trek from Jomsom to the sacred Muktinath Temple, crossing the Kali Gandaki gorge.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jomsom', 'slug' => 'jomsom-jm', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni-jm', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Khingar', 'slug' => 'khingar', 'type' => 'village', 'lat' => 28.8345, 'lng' => 83.8345, 'alt' => 3200],
                ['name' => 'Muktinath', 'slug' => 'muktinath-jm', 'type' => 'village', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
                ['name' => 'Jomsom', 'slug' => 'jomsom-jm-return', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            ],
            'segments' => [
                ['from' => 'jomsom-jm', 'to' => 'kagbeni-jm', 'dist' => 6.0, 'time' => 3.0, 'gain' => 100],
                ['from' => 'kagbeni-jm', 'to' => 'khingar', 'dist' => 7.0, 'time' => 3.5, 'gain' => 400],
                ['from' => 'khingar', 'to' => 'muktinath-jm', 'dist' => 6.0, 'time' => 3.0, 'gain' => 600],
                ['from' => 'muktinath-jm', 'to' => 'jomsom-jm-return', 'dist' => 19.0, 'time' => 7.0, 'loss' => 1100],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Jomsom Muktinath seeded.');

                // ==========================================
        // 4. DAMODAR KUNDA — Jomsom → Lo Manthang → Yara → Ghara → Damodar Kunda → return
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Damodar Kunda Trek',
                'slug' => 'damodar-kunda',
                'description' => 'Remote trek to the sacred Damodar Kunda lake cluster (4890m) in Upper Mustang, following the ancient salt route through Lo Manthang, Yara, and Ghara.',
                'difficulty' => 'hard',
                'duration_days' => 14,
                'max_altitude' => 4890,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jomsom', 'slug' => 'jomsom', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Chele', 'slug' => 'chele', 'type' => 'village', 'lat' => 28.8789, 'lng' => 83.8456, 'alt' => 3050],
                ['name' => 'Ghemi', 'slug' => 'ghemi', 'type' => 'village', 'lat' => 28.9012, 'lng' => 83.8678, 'alt' => 3510],
                ['name' => 'Tsarang', 'slug' => 'tsarang', 'type' => 'village', 'lat' => 28.9234, 'lng' => 83.8901, 'alt' => 3620],
                ['name' => 'Lo Manthang', 'slug' => 'lo-manthang', 'type' => 'village', 'lat' => 28.9456, 'lng' => 83.9123, 'alt' => 3810],
                ['name' => 'Yara', 'slug' => 'yara', 'type' => 'village', 'lat' => 29.0200, 'lng' => 83.8500, 'alt' => 3900],
                ['name' => 'Ghara', 'slug' => 'ghara', 'type' => 'village', 'lat' => 28.9900, 'lng' => 83.8800, 'alt' => 4000],
                ['name' => 'Damodar Kunda', 'slug' => 'damodar-kunda', 'type' => 'landmark', 'lat' => 28.8567, 'lng' => 83.8345, 'alt' => 4890],
            ],
            'segments' => [
                // Outbound
                ['from' => 'jomsom', 'to' => 'kagbeni', 'dist' => 6.0, 'time' => 3.0, 'gain' => 100, 'loss' => 0],
                ['from' => 'kagbeni', 'to' => 'chele', 'dist' => 16.0, 'time' => 7.0, 'gain' => 250, 'loss' => 0],
                ['from' => 'chele', 'to' => 'ghemi', 'dist' => 8.0, 'time' => 4.0, 'gain' => 460, 'loss' => 0],
                ['from' => 'ghemi', 'to' => 'tsarang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 110, 'loss' => 0],
                ['from' => 'tsarang', 'to' => 'lo-manthang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 190, 'loss' => 0],
                // Rest at Lo Manthang (acclimatize before high lake)
                ['from' => 'lo-manthang', 'to' => 'lo-manthang', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'lo-manthang', 'to' => 'yara', 'dist' => 14.0, 'time' => 6.0, 'gain' => 90, 'loss' => 0],
                ['from' => 'yara', 'to' => 'yara', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'yara', 'to' => 'ghara', 'dist' => 8.0, 'time' => 4.0, 'gain' => 100, 'loss' => 0],
                ['from' => 'ghara', 'to' => 'damodar-kunda', 'dist' => 10.0, 'time' => 6.0, 'gain' => 890, 'loss' => 0],
                // Return
                ['from' => 'damodar-kunda', 'to' => 'yara', 'dist' => 18.0, 'time' => 8.0, 'gain' => 0, 'loss' => 990],
                ['from' => 'yara', 'to' => 'lo-manthang', 'dist' => 14.0, 'time' => 6.0, 'gain' => 0, 'loss' => 90],
                ['from' => 'lo-manthang', 'to' => 'chele', 'dist' => 20.0, 'time' => 8.0, 'gain' => 0, 'loss' => 760],
                ['from' => 'chele', 'to' => 'kagbeni', 'dist' => 16.0, 'time' => 6.0, 'gain' => 0, 'loss' => 250],
                ['from' => 'kagbeni', 'to' => 'jomsom', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 100],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Upper Mustang Restricted Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Damodar Kunda seeded (14 segs, 1 rest, Jomsom→Damodar Kunda→Jomsom).');

        // ==========================================
        // 5. UPPER DOLPO (SHEY GOMPA) — Jumla → Jomsom
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Upper Dolpo (Shey Gompa) Trek',
                'slug' => 'upper-dolpo',
                'description' => 'Trek through the ancient salt trade route from Jumla to Jomsom, crossing Kagmara La (5115m) and Jeng La (5309m), visiting Shey Gompa and Phoksundo Lake.',
                'difficulty' => 'hard',
                'duration_days' => 18,
                'max_altitude' => 5309,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Chhetra', 'slug' => 'chhetra', 'type' => 'village', 'lat' => 29.30, 'lng' => 82.22, 'alt' => 3000],
                ['name' => 'Chaurikot', 'slug' => 'chaurikot', 'type' => 'village', 'lat' => 29.34, 'lng' => 82.28, 'alt' => 3000],
                ['name' => 'Kagmara Phedi', 'slug' => 'kagmara-phedi', 'type' => 'village', 'lat' => 29.38, 'lng' => 82.36, 'alt' => 4000],
                ['name' => 'Kagmara La', 'slug' => 'kagmara-la', 'type' => 'pass', 'lat' => 29.41, 'lng' => 82.42, 'alt' => 5115],
                ['name' => 'Pungmo', 'slug' => 'pungmo', 'type' => 'village', 'lat' => 29.43, 'lng' => 82.50, 'alt' => 3200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-lake', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Phoksundo Bhanjyang', 'slug' => 'phoksundo-bhanjyang', 'type' => 'village', 'lat' => 29.47, 'lng' => 82.86, 'alt' => 4200],
                ['name' => 'Shey Gompa', 'slug' => 'shey-gompa', 'type' => 'landmark', 'lat' => 29.55, 'lng' => 82.93, 'alt' => 4200],
                ['name' => 'Sal Dang', 'slug' => 'sal-dang', 'type' => 'village', 'lat' => 29.35, 'lng' => 82.82, 'alt' => 3800],
                ['name' => 'Nisal', 'slug' => 'nisal', 'type' => 'village', 'lat' => 29.50, 'lng' => 83.02, 'alt' => 4100],
                ['name' => 'Jeng La', 'slug' => 'jeng-la', 'type' => 'pass', 'lat' => 29.46, 'lng' => 83.08, 'alt' => 5309],
                ['name' => 'Tokyu Gaon', 'slug' => 'tokyu-gaon', 'type' => 'village', 'lat' => 29.42, 'lng' => 83.12, 'alt' => 4000],
                ['name' => 'Dho Tarap', 'slug' => 'dho-tarap', 'type' => 'village', 'lat' => 29.35, 'lng' => 83.15, 'alt' => 4000],
                ['name' => 'Chharka', 'slug' => 'chharka', 'type' => 'village', 'lat' => 29.25, 'lng' => 83.20, 'alt' => 4200],
                ['name' => 'Sangda La', 'slug' => 'sangda-la', 'type' => 'pass', 'lat' => 29.10, 'lng' => 83.30, 'alt' => 5000],
                ['name' => 'Jomsom', 'slug' => 'jomsom', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            ],
            'segments' => [
                ['from' => 'jumla', 'to' => 'chhetra', 'dist' => 14.0, 'time' => 6.0, 'gain' => 660, 'loss' => 0],
                ['from' => 'chhetra', 'to' => 'chaurikot', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'chaurikot', 'to' => 'kagmara-phedi', 'dist' => 12.0, 'time' => 6.0, 'gain' => 1000, 'loss' => 0],
                ['from' => 'kagmara-phedi', 'to' => 'kagmara-phedi', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'kagmara-phedi', 'to' => 'kagmara-la', 'dist' => 6.0, 'time' => 4.0, 'gain' => 1115, 'loss' => 0],
                ['from' => 'kagmara-la', 'to' => 'pungmo', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1915],
                ['from' => 'pungmo', 'to' => 'phoksundo-lake', 'dist' => 10.0, 'time' => 5.0, 'gain' => 411, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'phoksundo-lake', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'phoksundo-bhanjyang', 'dist' => 8.0, 'time' => 5.0, 'gain' => 589, 'loss' => 0],
                ['from' => 'phoksundo-bhanjyang', 'to' => 'shey-gompa', 'dist' => 10.0, 'time' => 6.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'shey-gompa', 'to' => 'shey-gompa', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'shey-gompa', 'to' => 'sal-dang', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 400],
                ['from' => 'sal-dang', 'to' => 'nisal', 'dist' => 10.0, 'time' => 5.0, 'gain' => 300, 'loss' => 0],
                ['from' => 'nisal', 'to' => 'jeng-la', 'dist' => 8.0, 'time' => 5.0, 'gain' => 1209, 'loss' => 0],
                ['from' => 'jeng-la', 'to' => 'tokyu-gaon', 'dist' => 8.0, 'time' => 5.0, 'gain' => 0, 'loss' => 1309],
                ['from' => 'tokyu-gaon', 'to' => 'dho-tarap', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'dho-tarap', 'to' => 'dho-tarap', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'dho-tarap', 'to' => 'chharka', 'dist' => 14.0, 'time' => 7.0, 'gain' => 200, 'loss' => 0],
                ['from' => 'chharka', 'to' => 'chharka', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'chharka', 'to' => 'sangda-la', 'dist' => 10.0, 'time' => 6.0, 'gain' => 800, 'loss' => 0],
                ['from' => 'sangda-la', 'to' => 'jomsom', 'dist' => 20.0, 'time' => 9.0, 'gain' => 0, 'loss' => 2300],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Dolpo Restricted Area Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Upper Dolpo seeded (18 segs, 2 rest, Jumla→Jomsom).');

        // ==========================================
        // 6. DOLPO CIRCUIT — Jumla → Jomsom (extended)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Dolpo Circuit',
                'slug' => 'dolpo-circuit',
                'description' => 'Extended Dolpo traverse from Jumla to Jomsom, visiting Shey Gompa, Phoksundo Lake, Dho Tarap, and crossing three high passes (Kagmara La 5115m, Jeng La 5309m, Sangda La 5000m).',
                'difficulty' => 'hard',
                'duration_days' => 20,
                'max_altitude' => 5309,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Chhetra', 'slug' => 'chhetra', 'type' => 'village', 'lat' => 29.30, 'lng' => 82.22, 'alt' => 3000],
                ['name' => 'Chaurikot', 'slug' => 'chaurikot', 'type' => 'village', 'lat' => 29.34, 'lng' => 82.28, 'alt' => 3000],
                ['name' => 'Kagmara Phedi', 'slug' => 'kagmara-phedi', 'type' => 'village', 'lat' => 29.38, 'lng' => 82.36, 'alt' => 4000],
                ['name' => 'Kagmara La', 'slug' => 'kagmara-la', 'type' => 'pass', 'lat' => 29.41, 'lng' => 82.42, 'alt' => 5115],
                ['name' => 'Pungmo', 'slug' => 'pungmo', 'type' => 'village', 'lat' => 29.43, 'lng' => 82.50, 'alt' => 3200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-lake', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Phoksundo Bhanjyang', 'slug' => 'phoksundo-bhanjyang', 'type' => 'village', 'lat' => 29.47, 'lng' => 82.86, 'alt' => 4200],
                ['name' => 'Shey Gompa', 'slug' => 'shey-gompa', 'type' => 'landmark', 'lat' => 29.55, 'lng' => 82.93, 'alt' => 4200],
                ['name' => 'Sal Dang', 'slug' => 'sal-dang', 'type' => 'village', 'lat' => 29.35, 'lng' => 82.82, 'alt' => 3800],
                ['name' => 'Nisal', 'slug' => 'nisal', 'type' => 'village', 'lat' => 29.50, 'lng' => 83.02, 'alt' => 4100],
                ['name' => 'Jeng La', 'slug' => 'jeng-la', 'type' => 'pass', 'lat' => 29.46, 'lng' => 83.08, 'alt' => 5309],
                ['name' => 'Tokyu Gaon', 'slug' => 'tokyu-gaon', 'type' => 'village', 'lat' => 29.42, 'lng' => 83.12, 'alt' => 4000],
                ['name' => 'Dho Tarap', 'slug' => 'dho-tarap', 'type' => 'village', 'lat' => 29.35, 'lng' => 83.15, 'alt' => 4000],
                ['name' => 'Chharka', 'slug' => 'chharka', 'type' => 'village', 'lat' => 29.25, 'lng' => 83.20, 'alt' => 4200],
                ['name' => 'Sangda La', 'slug' => 'sangda-la', 'type' => 'pass', 'lat' => 29.10, 'lng' => 83.30, 'alt' => 5000],
                ['name' => 'Sangda Village', 'slug' => 'sangda-village', 'type' => 'village', 'lat' => 28.95, 'lng' => 83.45, 'alt' => 3500],
                ['name' => 'Jomsom', 'slug' => 'jomsom', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            ],
            'segments' => [
                ['from' => 'jumla', 'to' => 'chhetra', 'dist' => 14.0, 'time' => 6.0, 'gain' => 660, 'loss' => 0],
                ['from' => 'chhetra', 'to' => 'chaurikot', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'chaurikot', 'to' => 'kagmara-phedi', 'dist' => 12.0, 'time' => 6.0, 'gain' => 1000, 'loss' => 0],
                ['from' => 'kagmara-phedi', 'to' => 'kagmara-phedi', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'kagmara-phedi', 'to' => 'kagmara-la', 'dist' => 6.0, 'time' => 4.0, 'gain' => 1115, 'loss' => 0],
                ['from' => 'kagmara-la', 'to' => 'pungmo', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1915],
                ['from' => 'pungmo', 'to' => 'pungmo', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'pungmo', 'to' => 'phoksundo-lake', 'dist' => 10.0, 'time' => 5.0, 'gain' => 411, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'phoksundo-lake', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'phoksundo-bhanjyang', 'dist' => 8.0, 'time' => 5.0, 'gain' => 589, 'loss' => 0],
                ['from' => 'phoksundo-bhanjyang', 'to' => 'shey-gompa', 'dist' => 10.0, 'time' => 6.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'shey-gompa', 'to' => 'shey-gompa', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'shey-gompa', 'to' => 'sal-dang', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 400],
                ['from' => 'sal-dang', 'to' => 'nisal', 'dist' => 10.0, 'time' => 5.0, 'gain' => 300, 'loss' => 0],
                ['from' => 'nisal', 'to' => 'jeng-la', 'dist' => 8.0, 'time' => 5.0, 'gain' => 1209, 'loss' => 0],
                ['from' => 'jeng-la', 'to' => 'tokyu-gaon', 'dist' => 8.0, 'time' => 5.0, 'gain' => 0, 'loss' => 1309],
                ['from' => 'tokyu-gaon', 'to' => 'dho-tarap', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'dho-tarap', 'to' => 'dho-tarap', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'dho-tarap', 'to' => 'chharka', 'dist' => 14.0, 'time' => 7.0, 'gain' => 200, 'loss' => 0],
                ['from' => 'chharka', 'to' => 'chharka', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'chharka', 'to' => 'sangda-la', 'dist' => 10.0, 'time' => 6.0, 'gain' => 800, 'loss' => 0],
                ['from' => 'sangda-la', 'to' => 'sangda-village', 'dist' => 6.0, 'time' => 3.0, 'gain' => 0, 'loss' => 1500],
                ['from' => 'sangda-village', 'to' => 'jomsom', 'dist' => 8.0, 'time' => 4.0, 'gain' => 0, 'loss' => 800],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Dolpo Restricted Area Permit', 'amount' => 500, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Dolpo Circuit seeded (20 segs, 3 rest, Jumla→Jomsom).');

        // ==========================================
        // 7. PHOKSUNDO LAKE TREK — Jumla → Juphal
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Phoksundo Lake Trek',
                'slug' => 'phoksundo-lake',
                'description' => 'Trek to the stunning turquoise Phoksundo Lake (3611m), the deepest lake in Nepal, crossing Kagmara La from Jumla and exiting via Dunai and Juphal.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 5115,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Chhetra', 'slug' => 'chhetra', 'type' => 'village', 'lat' => 29.30, 'lng' => 82.22, 'alt' => 3000],
                ['name' => 'Chaurikot', 'slug' => 'chaurikot', 'type' => 'village', 'lat' => 29.34, 'lng' => 82.28, 'alt' => 3000],
                ['name' => 'Kagmara Phedi', 'slug' => 'kagmara-phedi', 'type' => 'village', 'lat' => 29.38, 'lng' => 82.36, 'alt' => 4000],
                ['name' => 'Kagmara La', 'slug' => 'kagmara-la', 'type' => 'pass', 'lat' => 29.41, 'lng' => 82.42, 'alt' => 5115],
                ['name' => 'Pungmo', 'slug' => 'pungmo', 'type' => 'village', 'lat' => 29.43, 'lng' => 82.50, 'alt' => 3200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-lake', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Ringmo', 'slug' => 'ringmo', 'type' => 'village', 'lat' => 29.45, 'lng' => 82.85, 'alt' => 3500],
                ['name' => 'Chhepka', 'slug' => 'chhepka', 'type' => 'village', 'lat' => 29.30, 'lng' => 82.90, 'alt' => 2600],
                ['name' => 'Dunai', 'slug' => 'dunai', 'type' => 'village', 'lat' => 29.15, 'lng' => 82.95, 'alt' => 2100],
                ['name' => 'Juphal', 'slug' => 'juphal', 'type' => 'village', 'lat' => 29.10, 'lng' => 82.98, 'alt' => 2500],
            ],
            'segments' => [
                ['from' => 'jumla', 'to' => 'chhetra', 'dist' => 14.0, 'time' => 6.0, 'gain' => 660, 'loss' => 0],
                ['from' => 'chhetra', 'to' => 'chaurikot', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 0],
                ['from' => 'chaurikot', 'to' => 'kagmara-phedi', 'dist' => 12.0, 'time' => 6.0, 'gain' => 1000, 'loss' => 0],
                ['from' => 'kagmara-phedi', 'to' => 'kagmara-la', 'dist' => 6.0, 'time' => 4.0, 'gain' => 1115, 'loss' => 0],
                ['from' => 'kagmara-la', 'to' => 'pungmo', 'dist' => 12.0, 'time' => 6.0, 'gain' => 0, 'loss' => 1915],
                ['from' => 'pungmo', 'to' => 'pungmo', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'pungmo', 'to' => 'phoksundo-lake', 'dist' => 10.0, 'time' => 5.0, 'gain' => 411, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'phoksundo-lake', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                ['from' => 'phoksundo-lake', 'to' => 'ringmo', 'dist' => 4.0, 'time' => 2.0, 'gain' => 0, 'loss' => 111],
                ['from' => 'ringmo', 'to' => 'chhepka', 'dist' => 14.0, 'time' => 6.0, 'gain' => 0, 'loss' => 900],
                ['from' => 'chhepka', 'to' => 'dunai', 'dist' => 12.0, 'time' => 5.0, 'gain' => 0, 'loss' => 500],
                ['from' => 'dunai', 'to' => 'juphal', 'dist' => 10.0, 'time' => 4.0, 'gain' => 400, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'currency' => 'USD', 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'currency' => 'USD', 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Phoksundo Lake seeded (11 segs, 1 rest, Jumla→Juphal).');

        $this->command->info('🎉 Mustang & Dolpo Region Complete! 7 destinations seeded.');
    }
}
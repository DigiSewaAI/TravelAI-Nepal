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
        // 4. DAMODAR KUNDA (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Damodar Kunda Trek',
                'slug' => 'damodar-kunda',
                'description' => 'Remote trek to the sacred Damodar Kunda lake near the Tibetan border, with stunning Himalayan views.',
                'difficulty' => 'hard',
                'duration_days' => 14,
                'max_altitude' => 4890,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jomsom', 'slug' => 'jomsom-dk', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni-dk', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Chhushyang', 'slug' => 'chhushyang', 'type' => 'village', 'lat' => 28.8456, 'lng' => 83.8123, 'alt' => 2980],
                ['name' => 'Damodar Kunda', 'slug' => 'damodar-kunda', 'type' => 'landmark', 'lat' => 28.8567, 'lng' => 83.8345, 'alt' => 4890],
                ['name' => 'Lo Manthang', 'slug' => 'lo-manthang-dk', 'type' => 'village', 'lat' => 28.9456, 'lng' => 83.9123, 'alt' => 3810],
                ['name' => 'Jomsom', 'slug' => 'jomsom-dk-return', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            ],
            'segments' => [
                ['from' => 'jomsom-dk', 'to' => 'kagbeni-dk', 'dist' => 6.0, 'time' => 3.0, 'gain' => 100],
                ['from' => 'kagbeni-dk', 'to' => 'chhushyang', 'dist' => 6.0, 'time' => 3.0, 'gain' => 180],
                ['from' => 'chhushyang', 'to' => 'damodar-kunda', 'dist' => 8.0, 'time' => 5.0, 'gain' => 1910],
                ['from' => 'damodar-kunda', 'to' => 'lo-manthang-dk', 'dist' => 14.0, 'time' => 6.0, 'loss' => 1080],
                ['from' => 'lo-manthang-dk', 'to' => 'jomsom-dk-return', 'dist' => 42.0, 'time' => 14.0, 'loss' => 1110],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Upper Mustang Restricted Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Damodar Kunda seeded.');

        // ==========================================
        // 5. UPPER DOLPO (SHEY GOMPA) - (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Upper Dolpo (Shey Gompa) Trek',
                'slug' => 'upper-dolpo',
                'description' => 'Trek to the remote Shey Gompa in Upper Dolpo, following the ancient salt trade route.',
                'difficulty' => 'hard',
                'duration_days' => 18,
                'max_altitude' => 5200,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Talchi', 'slug' => 'talchi', 'type' => 'village', 'lat' => 29.3123, 'lng' => 82.3123, 'alt' => 2950],
                ['name' => 'Dolpu', 'slug' => 'dolpu', 'type' => 'village', 'lat' => 29.3456, 'lng' => 82.4567, 'alt' => 3200],
                ['name' => 'Sal Dang', 'slug' => 'sal-dang', 'type' => 'village', 'lat' => 29.3789, 'lng' => 82.5891, 'alt' => 3500],
                ['name' => 'Shey Gompa', 'slug' => 'shey-gompa', 'type' => 'landmark', 'lat' => 29.4123, 'lng' => 82.7123, 'alt' => 4200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-upper', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Jumla', 'slug' => 'jumla-upper-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'segments' => [
                ['from' => 'jumla', 'to' => 'talchi', 'dist' => 12.0, 'time' => 6.0, 'gain' => 610],
                ['from' => 'talchi', 'to' => 'dolpu', 'dist' => 10.0, 'time' => 5.0, 'gain' => 250],
                ['from' => 'dolpu', 'to' => 'sal-dang', 'dist' => 12.0, 'time' => 6.0, 'gain' => 300],
                ['from' => 'sal-dang', 'to' => 'shey-gompa', 'dist' => 10.0, 'time' => 5.0, 'gain' => 700],
                ['from' => 'shey-gompa', 'to' => 'phoksundo-upper', 'dist' => 14.0, 'time' => 6.0, 'loss' => 589],
                ['from' => 'phoksundo-upper', 'to' => 'jumla-upper-return', 'dist' => 32.0, 'time' => 14.0, 'loss' => 1271],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Dolpo Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Upper Dolpo seeded.');

        // ==========================================
        // 6. LOWER DOLPO (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Lower Dolpo Trek',
                'slug' => 'lower-dolpo',
                'description' => 'Trek through the lower Dolpo region with visits to Phoksundo Lake and local villages.',
                'difficulty' => 'moderate',
                'duration_days' => 13,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla-ld', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Talchi', 'slug' => 'talchi-ld', 'type' => 'village', 'lat' => 29.3123, 'lng' => 82.3123, 'alt' => 2950],
                ['name' => 'Dolpu', 'slug' => 'dolpu-ld', 'type' => 'village', 'lat' => 29.3456, 'lng' => 82.4567, 'alt' => 3200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-ld', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Ringmo', 'slug' => 'ringmo', 'type' => 'village', 'lat' => 29.4567, 'lng' => 82.8456, 'alt' => 3500],
                ['name' => 'Jumla', 'slug' => 'jumla-ld-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'segments' => [
                ['from' => 'jumla-ld', 'to' => 'talchi-ld', 'dist' => 12.0, 'time' => 6.0, 'gain' => 610],
                ['from' => 'talchi-ld', 'to' => 'dolpu-ld', 'dist' => 10.0, 'time' => 5.0, 'gain' => 250],
                ['from' => 'dolpu-ld', 'to' => 'phoksundo-ld', 'dist' => 14.0, 'time' => 6.0, 'gain' => 411],
                ['from' => 'phoksundo-ld', 'to' => 'ringmo', 'dist' => 4.0, 'time' => 2.0, 'loss' => 111],
                ['from' => 'ringmo', 'to' => 'jumla-ld-return', 'dist' => 28.0, 'time' => 12.0, 'loss' => 1160],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Lower Dolpo seeded.');

        // ==========================================
        // 7. DOLPO CIRCUIT (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Dolpo Circuit',
                'slug' => 'dolpo-circuit',
                'description' => 'Complete circuit of the Dolpo region, crossing several high passes and visiting Shey Gompa and Phoksundo Lake.',
                'difficulty' => 'hard',
                'duration_days' => 20,
                'max_altitude' => 5400,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla-dc', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Talchi', 'slug' => 'talchi-dc', 'type' => 'village', 'lat' => 29.3123, 'lng' => 82.3123, 'alt' => 2950],
                ['name' => 'Dolpu', 'slug' => 'dolpu-dc', 'type' => 'village', 'lat' => 29.3456, 'lng' => 82.4567, 'alt' => 3200],
                ['name' => 'Sal Dang', 'slug' => 'sal-dang-dc', 'type' => 'village', 'lat' => 29.3789, 'lng' => 82.5891, 'alt' => 3500],
                ['name' => 'Shey Gompa', 'slug' => 'shey-dc', 'type' => 'landmark', 'lat' => 29.4123, 'lng' => 82.7123, 'alt' => 4200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-dc', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Ringmo', 'slug' => 'ringmo-dc', 'type' => 'village', 'lat' => 29.4567, 'lng' => 82.8456, 'alt' => 3500],
                ['name' => 'Jumla', 'slug' => 'jumla-dc-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'segments' => [
                ['from' => 'jumla-dc', 'to' => 'talchi-dc', 'dist' => 12.0, 'time' => 6.0, 'gain' => 610],
                ['from' => 'talchi-dc', 'to' => 'dolpu-dc', 'dist' => 10.0, 'time' => 5.0, 'gain' => 250],
                ['from' => 'dolpu-dc', 'to' => 'sal-dang-dc', 'dist' => 12.0, 'time' => 6.0, 'gain' => 300],
                ['from' => 'sal-dang-dc', 'to' => 'shey-dc', 'dist' => 10.0, 'time' => 5.0, 'gain' => 700],
                ['from' => 'shey-dc', 'to' => 'phoksundo-dc', 'dist' => 14.0, 'time' => 6.0, 'loss' => 589],
                ['from' => 'phoksundo-dc', 'to' => 'ringmo-dc', 'dist' => 4.0, 'time' => 2.0, 'loss' => 111],
                ['from' => 'ringmo-dc', 'to' => 'jumla-dc-return', 'dist' => 32.0, 'time' => 14.0, 'loss' => 1160],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Dolpo Restricted Area Permit', 'amount' => 500, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dolpo Circuit seeded.');

        // ==========================================
        // 8. PHOKSUNDO LAKE (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Phoksundo Lake Trek',
                'slug' => 'phoksundo-lake',
                'description' => 'Trek to the stunning Phoksundo Lake (3611m), the deepest lake in Nepal with turquoise blue waters.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 3611,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla-pl', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Talchi', 'slug' => 'talchi-pl', 'type' => 'village', 'lat' => 29.3123, 'lng' => 82.3123, 'alt' => 2950],
                ['name' => 'Dolpu', 'slug' => 'dolpu-pl', 'type' => 'village', 'lat' => 29.3456, 'lng' => 82.4567, 'alt' => 3200],
                ['name' => 'Phoksundo Lake', 'slug' => 'phoksundo-pl', 'type' => 'landmark', 'lat' => 29.4456, 'lng' => 82.8345, 'alt' => 3611],
                ['name' => 'Ringmo', 'slug' => 'ringmo-pl', 'type' => 'village', 'lat' => 29.4567, 'lng' => 82.8456, 'alt' => 3500],
                ['name' => 'Jumla', 'slug' => 'jumla-pl-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'segments' => [
                ['from' => 'jumla-pl', 'to' => 'talchi-pl', 'dist' => 12.0, 'time' => 6.0, 'gain' => 610],
                ['from' => 'talchi-pl', 'to' => 'dolpu-pl', 'dist' => 10.0, 'time' => 5.0, 'gain' => 250],
                ['from' => 'dolpu-pl', 'to' => 'phoksundo-pl', 'dist' => 14.0, 'time' => 6.0, 'gain' => 411],
                ['from' => 'phoksundo-pl', 'to' => 'ringmo-pl', 'dist' => 4.0, 'time' => 2.0, 'loss' => 111],
                ['from' => 'ringmo-pl', 'to' => 'jumla-pl-return', 'dist' => 28.0, 'time' => 12.0, 'loss' => 1160],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Shey Phoksundo National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Phoksundo Lake seeded.');

        $this->command->info('🎉 Mustang & Dolpo Region Complete! 8 destinations seeded.');
    }
}
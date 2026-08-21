<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class EverestRegionSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏔️ Seeding Everest Region...');

        // ==========================================
        // 1. GOKYO LAKES
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Gokyo Lakes Trek',
                'slug' => 'gokyo-lakes',
                'description' => 'Trek to the sacred Gokyo Lakes with spectacular views of Everest, Cho Oyu, and the Ngozumpa Glacier.',
                'difficulty' => 'moderate',
                'duration_days' => 12,
                'max_altitude' => 5360,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-gokyo', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Phakding', 'slug' => 'phakding-gokyo', 'type' => 'village', 'lat' => 27.7408, 'lng' => 86.7125, 'alt' => 2610],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-gokyo', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Dole', 'slug' => 'dole', 'type' => 'village', 'lat' => 27.8326, 'lng' => 86.7285, 'alt' => 4080],
                ['name' => 'Machhermo', 'slug' => 'machhermo', 'type' => 'village', 'lat' => 27.8549, 'lng' => 86.7342, 'alt' => 4470],
                ['name' => 'Gokyo', 'slug' => 'gokyo', 'type' => 'village', 'lat' => 27.9585, 'lng' => 86.7428, 'alt' => 4750],
                ['name' => 'Gokyo Ri', 'slug' => 'gokyo-ri', 'type' => 'peak', 'lat' => 27.9612, 'lng' => 86.7483, 'alt' => 5360],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-gokyo-return', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Lukla', 'slug' => 'lukla-gokyo-return', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            ],
            'segments' => [
                ['from' => 'lukla-gokyo', 'to' => 'phakding-gokyo', 'dist' => 8.0, 'time' => 3.0, 'loss' => 250],
                ['from' => 'phakding-gokyo', 'to' => 'namche-gokyo', 'dist' => 10.5, 'time' => 5.0, 'gain' => 830],
                ['from' => 'namche-gokyo', 'to' => 'dole', 'dist' => 8.0, 'time' => 4.0, 'gain' => 640],
                ['from' => 'dole', 'to' => 'machhermo', 'dist' => 5.0, 'time' => 2.5, 'gain' => 390],
                ['from' => 'machhermo', 'to' => 'gokyo', 'dist' => 7.0, 'time' => 3.5, 'gain' => 280],
                ['from' => 'gokyo', 'to' => 'gokyo-ri', 'dist' => 3.0, 'time' => 2.0, 'gain' => 610],
                ['from' => 'gokyo', 'to' => 'namche-gokyo-return', 'dist' => 20.0, 'time' => 8.0, 'loss' => 1310],
                ['from' => 'namche-gokyo-return', 'to' => 'lukla-gokyo-return', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false, 'metadata' => ['verified' => false, 'source' => 'estimate']],
            ],
        ]);

        $this->command->info('✅ Gokyo Lakes seeded.');

        // ==========================================
        // 2. THREE PASSES TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Three Passes Trek',
                'slug' => 'three-passes',
                'description' => 'Challenging high-altitude trek crossing Kongma La, Cho La, and Renjo La passes with Everest views.',
                'difficulty' => 'hard',
                'duration_days' => 18,
                'max_altitude' => 5535,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-3p', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-3p', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Tengboche', 'slug' => 'tengboche-3p', 'type' => 'village', 'lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
                ['name' => 'Dingboche', 'slug' => 'dingboche-3p', 'type' => 'village', 'lat' => 27.8927, 'lng' => 86.8242, 'alt' => 4410],
                ['name' => 'Kongma La', 'slug' => 'kongma-la', 'type' => 'pass', 'lat' => 27.8981, 'lng' => 86.8456, 'alt' => 5535],
                ['name' => 'Lobuche', 'slug' => 'lobuche-3p', 'type' => 'village', 'lat' => 27.9358, 'lng' => 86.8087, 'alt' => 4940],
                ['name' => 'Gorak Shep', 'slug' => 'gorak-3p', 'type' => 'village', 'lat' => 27.9812, 'lng' => 86.8274, 'alt' => 5140],
                ['name' => 'Everest Base Camp', 'slug' => 'ebc-3p', 'type' => 'peak', 'lat' => 28.0057, 'lng' => 86.8294, 'alt' => 5364],
                ['name' => 'Cho La', 'slug' => 'cho-la', 'type' => 'pass', 'lat' => 27.9037, 'lng' => 86.7223, 'alt' => 5420],
                ['name' => 'Gokyo', 'slug' => 'gokyo-3p', 'type' => 'village', 'lat' => 27.9585, 'lng' => 86.7428, 'alt' => 4750],
                ['name' => 'Renjo La', 'slug' => 'renjo-la', 'type' => 'pass', 'lat' => 27.9125, 'lng' => 86.6902, 'alt' => 5360],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-3p-return', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Lukla', 'slug' => 'lukla-3p-return', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            ],
            'segments' => [
                ['from' => 'lukla-3p', 'to' => 'namche-3p', 'dist' => 18.5, 'time' => 8.0, 'gain' => 580],
                ['from' => 'namche-3p', 'to' => 'tengboche-3p', 'dist' => 9.0, 'time' => 5.0, 'gain' => 420],
                ['from' => 'tengboche-3p', 'to' => 'dingboche-3p', 'dist' => 10.0, 'time' => 5.0, 'gain' => 550],
                ['from' => 'dingboche-3p', 'to' => 'kongma-la', 'dist' => 6.0, 'time' => 4.0, 'gain' => 1125],
                ['from' => 'kongma-la', 'to' => 'lobuche-3p', 'dist' => 5.0, 'time' => 3.0, 'loss' => 595],
                ['from' => 'lobuche-3p', 'to' => 'gorak-3p', 'dist' => 5.5, 'time' => 3.5, 'gain' => 200],
                ['from' => 'gorak-3p', 'to' => 'ebc-3p', 'dist' => 3.5, 'time' => 2.5, 'gain' => 224],
                ['from' => 'gorak-3p', 'to' => 'cho-la', 'dist' => 12.0, 'time' => 6.0, 'gain' => 280],
                ['from' => 'cho-la', 'to' => 'gokyo-3p', 'dist' => 6.0, 'time' => 3.5, 'loss' => 670],
                ['from' => 'gokyo-3p', 'to' => 'renjo-la', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'renjo-la', 'to' => 'namche-3p-return', 'dist' => 12.0, 'time' => 6.0, 'loss' => 1920],
                ['from' => 'namche-3p-return', 'to' => 'lukla-3p-return', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Gokyo Special Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 40, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Three Passes Trek seeded.');

        // ==========================================
        // 3. EVEREST VIEW TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Everest View Trek',
                'slug' => 'everest-view',
                'description' => 'Short trek for stunning views of Everest from Namche Bazaar and Tengboche.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 3860,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-ev', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Phakding', 'slug' => 'phakding-ev', 'type' => 'village', 'lat' => 27.7408, 'lng' => 86.7125, 'alt' => 2610],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-ev', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Tengboche', 'slug' => 'tengboche-ev', 'type' => 'village', 'lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
            ],
            'segments' => [
                ['from' => 'lukla-ev', 'to' => 'phakding-ev', 'dist' => 8.0, 'time' => 3.0, 'loss' => 250],
                ['from' => 'phakding-ev', 'to' => 'namche-ev', 'dist' => 10.5, 'time' => 5.0, 'gain' => 830],
                ['from' => 'namche-ev', 'to' => 'tengboche-ev', 'dist' => 9.0, 'time' => 5.0, 'gain' => 420],
                ['from' => 'tengboche-ev', 'to' => 'namche-ev', 'dist' => 9.0, 'time' => 4.0, 'loss' => 420],
                ['from' => 'namche-ev', 'to' => 'lukla-ev', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Everest View Trek seeded.');

        // ==========================================
        // 4. CHOLA PASS TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Chola Pass Trek',
                'slug' => 'chola-pass',
                'description' => 'Trek crossing the challenging Chola Pass connecting the Khumbu and Gokyo valleys.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 5420,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-chola', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-chola', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Tengboche', 'slug' => 'tengboche-chola', 'type' => 'village', 'lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
                ['name' => 'Dingboche', 'slug' => 'dingboche-chola', 'type' => 'village', 'lat' => 27.8927, 'lng' => 86.8242, 'alt' => 4410],
                ['name' => 'Chola Pass', 'slug' => 'chola-pass', 'type' => 'pass', 'lat' => 27.9037, 'lng' => 86.7223, 'alt' => 5420],
                ['name' => 'Gokyo', 'slug' => 'gokyo-chola', 'type' => 'village', 'lat' => 27.9585, 'lng' => 86.7428, 'alt' => 4750],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-chola-return', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Lukla', 'slug' => 'lukla-chola-return', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            ],
            'segments' => [
                ['from' => 'lukla-chola', 'to' => 'namche-chola', 'dist' => 18.5, 'time' => 8.0, 'gain' => 580],
                ['from' => 'namche-chola', 'to' => 'tengboche-chola', 'dist' => 9.0, 'time' => 5.0, 'gain' => 420],
                ['from' => 'tengboche-chola', 'to' => 'dingboche-chola', 'dist' => 10.0, 'time' => 5.0, 'gain' => 550],
                ['from' => 'dingboche-chola', 'to' => 'chola-pass', 'dist' => 12.0, 'time' => 6.0, 'gain' => 1010],
                ['from' => 'chola-pass', 'to' => 'gokyo-chola', 'dist' => 6.0, 'time' => 3.5, 'loss' => 670],
                ['from' => 'gokyo-chola', 'to' => 'namche-chola-return', 'dist' => 20.0, 'time' => 8.0, 'loss' => 1310],
                ['from' => 'namche-chola-return', 'to' => 'lukla-chola-return', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Chola Pass Trek seeded.');

        // ==========================================
        // 5. RENJO LA PASS TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Renjo La Pass Trek',
                'slug' => 'renjo-la',
                'description' => 'Trek crossing the Renjo La pass with stunning views of Everest, Cho Oyu, and the Gokyo Lakes.',
                'difficulty' => 'moderate',
                'duration_days' => 13,
                'max_altitude' => 5360,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-renjo', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-renjo', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Dole', 'slug' => 'dole-renjo', 'type' => 'village', 'lat' => 27.8326, 'lng' => 86.7285, 'alt' => 4080],
                ['name' => 'Machhermo', 'slug' => 'machhermo-renjo', 'type' => 'village', 'lat' => 27.8549, 'lng' => 86.7342, 'alt' => 4470],
                ['name' => 'Gokyo', 'slug' => 'gokyo-renjo', 'type' => 'village', 'lat' => 27.9585, 'lng' => 86.7428, 'alt' => 4750],
                ['name' => 'Renjo La', 'slug' => 'renjo-la', 'type' => 'pass', 'lat' => 27.9125, 'lng' => 86.6902, 'alt' => 5360],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-renjo-return', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Lukla', 'slug' => 'lukla-renjo-return', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
            ],
            'segments' => [
                ['from' => 'lukla-renjo', 'to' => 'namche-renjo', 'dist' => 18.5, 'time' => 8.0, 'gain' => 580],
                ['from' => 'namche-renjo', 'to' => 'dole-renjo', 'dist' => 8.0, 'time' => 4.0, 'gain' => 640],
                ['from' => 'dole-renjo', 'to' => 'machhermo-renjo', 'dist' => 5.0, 'time' => 2.5, 'gain' => 390],
                ['from' => 'machhermo-renjo', 'to' => 'gokyo-renjo', 'dist' => 7.0, 'time' => 3.5, 'gain' => 280],
                ['from' => 'gokyo-renjo', 'to' => 'renjo-la', 'dist' => 8.0, 'time' => 4.5, 'gain' => 610],
                ['from' => 'renjo-la', 'to' => 'namche-renjo-return', 'dist' => 12.0, 'time' => 6.0, 'loss' => 1920],
                ['from' => 'namche-renjo-return', 'to' => 'lukla-renjo-return', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Renjo La Pass Trek seeded.');

        // ==========================================
        // 6. SHERPA CULTURAL TREK
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Sherpa Cultural Trek',
                'slug' => 'sherpa-cultural',
                'description' => 'Cultural trek through Sherpa villages, monasteries, and traditional mountain life.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 3440,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Lukla', 'slug' => 'lukla-sc', 'type' => 'village', 'lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2860],
                ['name' => 'Phakding', 'slug' => 'phakding-sc', 'type' => 'village', 'lat' => 27.7408, 'lng' => 86.7125, 'alt' => 2610],
                ['name' => 'Namche Bazaar', 'slug' => 'namche-sc', 'type' => 'village', 'lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
                ['name' => 'Khumjung', 'slug' => 'khumjung', 'type' => 'village', 'lat' => 27.8133, 'lng' => 86.7189, 'alt' => 3790],
                ['name' => 'Kunde', 'slug' => 'kunde', 'type' => 'village', 'lat' => 27.8138, 'lng' => 86.7166, 'alt' => 3840],
            ],
            'segments' => [
                ['from' => 'lukla-sc', 'to' => 'phakding-sc', 'dist' => 8.0, 'time' => 3.0, 'loss' => 250],
                ['from' => 'phakding-sc', 'to' => 'namche-sc', 'dist' => 10.5, 'time' => 5.0, 'gain' => 830],
                ['from' => 'namche-sc', 'to' => 'khumjung', 'dist' => 4.0, 'time' => 2.0, 'gain' => 350],
                ['from' => 'khumjung', 'to' => 'kunde', 'dist' => 1.0, 'time' => 0.5, 'gain' => 50],
                ['from' => 'kunde', 'to' => 'namche-sc', 'dist' => 5.0, 'time' => 2.0, 'loss' => 400],
                ['from' => 'namche-sc', 'to' => 'lukla-sc', 'dist' => 18.5, 'time' => 7.0, 'loss' => 580],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Sagarmatha National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Khumbu Pasang Lhamu Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sherpa Cultural Trek seeded.');

        $this->command->info('🎉 Everest Region Complete! 6 destinations seeded.');
    }
}
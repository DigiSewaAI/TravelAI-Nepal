<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class NationalParksSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🌿 Seeding National Parks & Wildlife Reserves...');

        // ==========================================
        // 1. CHITWAN NATIONAL PARK SAFARI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Chitwan National Park Safari',
                'slug' => 'chitwan-safari',
                'description' => 'Jungle safari in Nepal\'s first national park, home to Bengal tigers, one-horned rhinos, and elephants.',
                'duration_days' => 3,
                'max_altitude' => 150,
                'season' => 'October–June',
            ],
                        'waypoints' => [
                ['name' => 'Sauraha', 'slug' => 'sauraha', 'type' => 'village', 'lat' => 27.5789, 'lng' => 84.4567, 'alt' => 150],
                ['name' => 'Chitwan National Park Entrance', 'slug' => 'chitwan-entrance', 'type' => 'landmark', 'lat' => 27.5891, 'lng' => 84.4789, 'alt' => 150],
                ['name' => 'Elephant Breeding Center', 'slug' => 'elephant-center', 'type' => 'landmark', 'lat' => 27.6123, 'lng' => 84.5123, 'alt' => 150],
                ['name' => 'Rapti River', 'slug' => 'rapti-river', 'type' => 'landmark', 'lat' => 27.5456, 'lng' => 84.4234, 'alt' => 150],
            ],
                        'segments' => [
                // Day 1: Arrive at Sauraha (settle-in)
                ['from' => 'sauraha', 'to' => 'sauraha', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
                // Day 2: Full-day safari loop
                ['from' => 'sauraha', 'to' => 'chitwan-entrance', 'dist' => 5.0, 'time' => 0.3, 'loss' => 50],
                ['from' => 'chitwan-entrance', 'to' => 'elephant-center', 'dist' => 3.0, 'time' => 0.3, 'loss' => 0],
                ['from' => 'elephant-center', 'to' => 'rapti-river', 'dist' => 2.0, 'time' => 0.2, 'loss' => 0],
                ['from' => 'rapti-river', 'to' => 'sauraha', 'dist' => 8.0, 'time' => 0.5, 'gain' => 50],
                // Day 3: Depart (prep before travel)
                ['from' => 'sauraha', 'to' => 'sauraha', 'dist' => 0, 'time' => 0, 'gain' => 0, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Chitwan National Park Permit', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'tour', 'name' => 'Jungle Safari (Jeep)', 'amount' => 40, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Canoe Ride', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Chitwan National Park seeded.');

        // ==========================================
        // 2. KHAPTAD NATIONAL PARK (lake → landmark)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Khaptad National Park Tour',
                'slug' => 'khaptad-tour',
                'description' => 'Tour of Khaptad National Park, a pristine Himalayan forest with rich biodiversity and the sacred Khaptad Lake.',
                'duration_days' => 3,
                'max_altitude' => 3300,
                'season' => 'Spring/Autumn',
            ],
                        'waypoints' => [
                ['name' => 'Silgadhi', 'slug' => 'silgadhi', 'type' => 'village', 'lat' => 29.1234, 'lng' => 81.2345, 'alt' => 1500],
                ['name' => 'Khaptad Lake', 'slug' => 'khaptad-lake', 'type' => 'landmark', 'lat' => 29.1567, 'lng' => 81.2678, 'alt' => 3300],
                ['name' => 'Khaptad National Park Entrance', 'slug' => 'khaptad-entrance', 'type' => 'landmark', 'lat' => 29.1789, 'lng' => 81.2891, 'alt' => 3200],
                ['name' => 'Silgadhi', 'slug' => 'silgadhi-return', 'type' => 'village', 'lat' => 29.1234, 'lng' => 81.2345, 'alt' => 1500],
            ],
            'segments' => [
                ['from' => 'silgadhi', 'to' => 'khaptad-lake', 'dist' => 60.0, 'time' => 5.0, 'gain' => 1800],
                ['from' => 'khaptad-lake', 'to' => 'khaptad-entrance', 'dist' => 5.0, 'time' => 1.5, 'loss' => 100],
                ['from' => 'khaptad-entrance', 'to' => 'silgadhi-return', 'dist' => 55.0, 'time' => 4.0, 'loss' => 1700],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Khaptad National Park Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 60, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Khaptad National Park seeded.');

        // ==========================================
        // 3. KOSHI TAPPU WILDLIFE RESERVE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Koshi Tappu Wildlife Reserve Tour',
                'slug' => 'koshi-tappu',
                'description' => 'Tour of Koshi Tappu Wildlife Reserve, a wetland paradise for birds and home to the endangered wild buffalo.',
                'duration_days' => 2,
                'max_altitude' => 80,
                'season' => 'October–June',
            ],
                        'waypoints' => [
                ['name' => 'Koshi Barrage', 'slug' => 'koshi-barrage-tappu', 'type' => 'landmark', 'lat' => 26.4789, 'lng' => 87.3123, 'alt' => 80],
                ['name' => 'Koshi Tappu Entrance', 'slug' => 'koshi-tappu-entrance', 'type' => 'landmark', 'lat' => 26.5123, 'lng' => 87.3456, 'alt' => 80],
                ['name' => 'Bird Watching Tower', 'slug' => 'bird-watching-tower', 'type' => 'landmark', 'lat' => 26.5456, 'lng' => 87.3789, 'alt' => 80],
                ['name' => 'Koshi Barrage', 'slug' => 'koshi-barrage-return', 'type' => 'landmark', 'lat' => 26.4789, 'lng' => 87.3123, 'alt' => 80],
            ],
            'segments' => [
                ['from' => 'koshi-barrage-tappu', 'to' => 'koshi-tappu-entrance', 'dist' => 6.0, 'time' => 1.5, 'loss' => 0],
                ['from' => 'koshi-tappu-entrance', 'to' => 'bird-watching-tower', 'dist' => 5.0, 'time' => 1.5, 'loss' => 0],
                ['from' => 'bird-watching-tower', 'to' => 'koshi-barrage-return', 'dist' => 9.0, 'time' => 2.0, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Koshi Tappu Reserve Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Boat Ride', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Koshi Tappu Wildlife Reserve seeded.');

        // ==========================================
        // 4. SHUKLAPHANTA NATIONAL PARK (lake → landmark)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Shuklaphanta National Park Tour',
                'slug' => 'shuklaphanta',
                'description' => 'Tour of Shuklaphanta National Park, home to the largest herd of swamp deer in Nepal.',
                'duration_days' => 2,
                'max_altitude' => 200,
                'season' => 'October–June',
            ],
                        'waypoints' => [
                ['name' => 'Kanchanpur', 'slug' => 'kanchanpur', 'type' => 'village', 'lat' => 28.8234, 'lng' => 80.4567, 'alt' => 200],
                ['name' => 'Shuklaphanta Entrance', 'slug' => 'shuklaphanta-entrance', 'type' => 'landmark', 'lat' => 28.8567, 'lng' => 80.4891, 'alt' => 200],
                ['name' => 'Sikta Lake', 'slug' => 'sikta-lake', 'type' => 'landmark', 'lat' => 28.8891, 'lng' => 80.5123, 'alt' => 200],
                ['name' => 'Kanchanpur', 'slug' => 'kanchanpur-return', 'type' => 'village', 'lat' => 28.8234, 'lng' => 80.4567, 'alt' => 200],
            ],
            'segments' => [
                ['from' => 'kanchanpur', 'to' => 'shuklaphanta-entrance', 'dist' => 8.0, 'time' => 1.5, 'loss' => 0],
                ['from' => 'shuklaphanta-entrance', 'to' => 'sikta-lake', 'dist' => 5.0, 'time' => 1.5, 'loss' => 0],
                ['from' => 'sikta-lake', 'to' => 'kanchanpur-return', 'dist' => 12.0, 'time' => 2.0, 'loss' => 0],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Shuklaphanta National Park Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Shuklaphanta National Park seeded.');

        // ==========================================
        // 5. DHORPATAN HUNTING RESERVE (lake → landmark)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dhorpatan Hunting Reserve Tour',
                'slug' => 'dhorpatan',
                'description' => 'Tour of Dhorpatan Hunting Reserve, the only hunting reserve in Nepal, with pristine Himalayan landscapes.',
                'duration_days' => 3,
                'max_altitude' => 3600,
                'season' => 'Spring/Autumn',
            ],
                        'waypoints' => [
                ['name' => 'Baglung', 'slug' => 'baglung', 'type' => 'village', 'lat' => 28.3123, 'lng' => 83.6123, 'alt' => 1000],
                ['name' => 'Dhorpatan Entrance', 'slug' => 'dhorpatan-entrance', 'type' => 'landmark', 'lat' => 28.3456, 'lng' => 83.6456, 'alt' => 3000],
                ['name' => 'Dhorpatan Lake', 'slug' => 'dhorpatan-lake', 'type' => 'landmark', 'lat' => 28.3789, 'lng' => 83.6789, 'alt' => 3600],
                ['name' => 'Baglung', 'slug' => 'baglung-return', 'type' => 'village', 'lat' => 28.3123, 'lng' => 83.6123, 'alt' => 1000],
            ],
            'segments' => [
                ['from' => 'baglung', 'to' => 'dhorpatan-entrance', 'dist' => 70.0, 'time' => 4.0, 'gain' => 2000],
                ['from' => 'dhorpatan-entrance', 'to' => 'dhorpatan-lake', 'dist' => 10.0, 'time' => 3.0, 'gain' => 600],
                ['from' => 'dhorpatan-lake', 'to' => 'baglung-return', 'dist' => 80.0, 'time' => 5.0, 'loss' => 2600],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Dhorpatan Reserve Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 80, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dhorpatan Hunting Reserve seeded.');

        // ==========================================
        // 6. BANKE NATIONAL PARK (lake → landmark, city → village)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Banke National Park Tour',
                'slug' => 'banke-tour',
                'description' => 'Tour of Banke National Park, a pristine forest in western Nepal with tigers, leopards, and elephants.',
                'duration_days' => 2,
                'max_altitude' => 300,
                'season' => 'October–June',
            ],
                        'waypoints' => [
                ['name' => 'Nepalgunj', 'slug' => 'nepalgunj', 'type' => 'village', 'lat' => 28.0456, 'lng' => 81.6123, 'alt' => 150],
                ['name' => 'Banke National Park Entrance', 'slug' => 'banke-entrance', 'type' => 'landmark', 'lat' => 28.0789, 'lng' => 81.6456, 'alt' => 300],
                ['name' => 'Kataiya Lake', 'slug' => 'kataiya-lake', 'type' => 'landmark', 'lat' => 28.1123, 'lng' => 81.6789, 'alt' => 300],
                ['name' => 'Nepalgunj', 'slug' => 'nepalgunj-return', 'type' => 'village', 'lat' => 28.0456, 'lng' => 81.6123, 'alt' => 150],
            ],
            'segments' => [
                ['from' => 'nepalgunj', 'to' => 'banke-entrance', 'dist' => 15.0, 'time' => 1.5, 'gain' => 150],
                ['from' => 'banke-entrance', 'to' => 'kataiya-lake', 'dist' => 5.0, 'time' => 1.5, 'loss' => 0],
                ['from' => 'kataiya-lake', 'to' => 'nepalgunj-return', 'dist' => 18.0, 'time' => 2.0, 'loss' => 150],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Banke National Park Permit', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Banke National Park seeded.');

        $this->command->info('🎉 National Parks & Wildlife Reserves Complete! 6 destinations seeded.');
    }
}
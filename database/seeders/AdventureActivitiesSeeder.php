<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class AdventureActivitiesSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏄‍♂️ Seeding Adventure Activities...');

        // ==========================================
        // 1. TRISHULI RIVER RAFTING
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Trishuli River Rafting',
                'slug' => 'trishuli-rafting',
                'description' => 'White-water rafting on the Trishuli River with class II-III rapids, perfect for beginners and families.',
                'duration_days' => 1,
                'max_altitude' => 500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-trishuli', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Trishuli River', 'slug' => 'trishuli-river', 'type' => 'landmark', 'lat' => 27.8123, 'lng' => 84.9345, 'alt' => 500],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Rafting Package (Full Day)', 'amount' => 60, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Lunch & Equipment', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Trishuli River Rafting seeded.');

        // ==========================================
        // 2. BHOTE KOSHI RAFTING
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bhote Koshi River Rafting',
                'slug' => 'bhote-koshi-rafting',
                'description' => 'Exciting white-water rafting on the Bhote Koshi River with class III-IV rapids, near the Tibetan border.',
                'duration_days' => 1,
                'max_altitude' => 600,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-bhote', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Bhote Koshi River', 'slug' => 'bhote-koshi-river', 'type' => 'landmark', 'lat' => 27.9123, 'lng' => 85.9345, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Rafting Package (Full Day)', 'amount' => 70, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Lunch & Equipment', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bhote Koshi Rafting seeded.');

        // ==========================================
        // 3. KALI GANDAKI RAFTING
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kali Gandaki River Rafting',
                'slug' => 'kali-gandaki-rafting',
                'description' => 'Rafting on the Kali Gandaki River with class II-III rapids, through the deepest gorge in the world.',
                'duration_days' => 2,
                'max_altitude' => 800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-kali', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Kali Gandaki River', 'slug' => 'kali-gandaki-river', 'type' => 'landmark', 'lat' => 28.3123, 'lng' => 83.7123, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Rafting Package (2 Days)', 'amount' => 120, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Accommodation & Meals', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kali Gandaki Rafting seeded.');

        // ==========================================
        // 4. SETI RIVER RAFTING
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Seti River Rafting',
                'slug' => 'seti-river-rafting',
                'description' => 'Gentle rafting on the Seti River with class I-II rapids, ideal for families and beginners.',
                'duration_days' => 1,
                'max_altitude' => 600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-seti', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Seti River', 'slug' => 'seti-river', 'type' => 'landmark', 'lat' => 28.2345, 'lng' => 84.0123, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Rafting Package (Half Day)', 'amount' => 40, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Equipment', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Seti River Rafting seeded.');

        // ==========================================
        // 5. PARAGLIDING (POKHARA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Paragliding in Pokhara',
                'slug' => 'pokhara-paragliding',
                'description' => 'Experience the thrill of paragliding over Pokhara with stunning views of Fewa Lake and the Annapurna range.',
                'duration_days' => 1,
                'max_altitude' => 1600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-para', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Sarangkot', 'slug' => 'sarangkot-para', 'type' => 'viewpoint', 'lat' => 28.2446, 'lng' => 83.9453, 'alt' => 1600],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Paragliding Flight (20-30 min)', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Photo/Video Package', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Paragliding seeded.');

        // ==========================================
        // 6. BUNGEE JUMPING (KUSMA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bungee Jumping at Kusma',
                'slug' => 'kusma-bungee',
                'description' => 'Bungee jumping from the Kusma Bridge (228m), one of the highest bungee jumps in the world.',
                'duration_days' => 1,
                'max_altitude' => 800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-kusma', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Kusma Bridge', 'slug' => 'kusma-bridge', 'type' => 'landmark', 'lat' => 28.2123, 'lng' => 83.7123, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Bungee Jump (Single)', 'amount' => 85, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Photo/Video Package', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kusma Bungee seeded.');

        // ==========================================
        // 7. BUNGEE JUMPING (BHOTE KOSHI)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bungee Jumping at Bhote Koshi',
                'slug' => 'bhote-koshi-bungee',
                'description' => 'Bungee jumping from the Bhote Koshi suspension bridge (160m) with canyon views.',
                'duration_days' => 1,
                'max_altitude' => 600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-bungee-bhote', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Bhote Koshi Bridge', 'slug' => 'bhote-koshi-bridge', 'type' => 'landmark', 'lat' => 27.9123, 'lng' => 85.9345, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Bungee Jump (Single)', 'amount' => 75, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Photo/Video Package', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bhote Koshi Bungee seeded.');

        // ==========================================
        // 8. ZIP-LINING (POKHARA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Zip-lining in Pokhara',
                'slug' => 'pokhara-zipline',
                'description' => 'Zip-line ride over the Pokhara valley with views of Fewa Lake and the Annapurna range.',
                'duration_days' => 1,
                'max_altitude' => 1200,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-zip', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Zip-line Start Point', 'slug' => 'zipline-start', 'type' => 'landmark', 'lat' => 28.2567, 'lng' => 83.9567, 'alt' => 1200],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Zip-line Ride', 'amount' => 50, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Photo Package', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Zip-lining seeded.');

        // ==========================================
        // 9. MOUNTAIN BIKING (KATHMANDU)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Mountain Biking in Kathmandu',
                'slug' => 'kathmandu-mountain-biking',
                'description' => 'Mountain biking through the Kathmandu Valley hills with views of the Himalayas.',
                'duration_days' => 1,
                'max_altitude' => 1500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-mtb', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Shivapuri National Park', 'slug' => 'shivapuri-mtb', 'type' => 'landmark', 'lat' => 27.7891, 'lng' => 85.3567, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Bike Rental (Full Day)', 'amount' => 25, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Mountain Biking seeded.');

        // ==========================================
        // 10. ROCK CLIMBING (NAGARJUN)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Rock Climbing at Nagarjun',
                'slug' => 'nagarjun-rock-climbing',
                'description' => 'Rock climbing at Nagarjun forest, a popular climbing spot near Kathmandu.',
                'duration_days' => 1,
                'max_altitude' => 1600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-nagarjun', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Nagarjun Forest', 'slug' => 'nagarjun-forest', 'type' => 'landmark', 'lat' => 27.7567, 'lng' => 85.3891, 'alt' => 1600],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Climbing Package (Half Day)', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Equipment Rental', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Rock Climbing seeded.');

        // ==========================================
        // 11. CANYONING (SUNDARIJAL)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Canyoning at Sundarijal',
                'slug' => 'sundarijal-canyoning',
                'description' => 'Canyoning adventure in the Sundarijal water falls and gorges near Kathmandu.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-canyoning', 'type' => 'city', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Sundarijal', 'slug' => 'sundarijal-canyoning', 'type' => 'landmark', 'lat' => 28.0821, 'lng' => 85.4243, 'alt' => 1350],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Canyoning Package (Half Day)', 'amount' => 40, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Equipment Rental', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Canyoning seeded.');

        // ==========================================
        // 12. SKYDIVING (POKHARA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Skydiving in Pokhara',
                'slug' => 'pokhara-skydiving',
                'description' => 'Tandem skydiving over Pokhara with breathtaking views of the Annapurna range and Fewa Lake.',
                'duration_days' => 1,
                'max_altitude' => 4500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-skydive', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Skydiving Drop Zone', 'slug' => 'skydive-zone', 'type' => 'landmark', 'lat' => 28.1789, 'lng' => 83.9789, 'alt' => 827],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Tandem Skydive', 'amount' => 250, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Photo/Video Package', 'amount' => 50, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Skydiving seeded.');

        // ==========================================
        // 13. HOT AIR BALLOONING (POKHARA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Hot Air Ballooning in Pokhara',
                'slug' => 'pokhara-ballooning',
                'description' => 'Hot air balloon ride over Pokhara with sunrise views of the Annapurna and Dhaulagiri ranges.',
                'duration_days' => 1,
                'max_altitude' => 1500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-balloon', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Balloon Launch Site', 'slug' => 'balloon-launch', 'type' => 'landmark', 'lat' => 28.2234, 'lng' => 83.9567, 'alt' => 827],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Balloon Ride (1 Hour)', 'amount' => 180, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Breakfast Included', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Hot Air Ballooning seeded.');

        // ==========================================
        // 14. KAYAKING (FEWA LAKE)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kayaking in Fewa Lake',
                'slug' => 'fewa-lake-kayaking',
                'description' => 'Kayaking on the pristine Fewa Lake in Pokhara with views of the Annapurna range.',
                'duration_days' => 1,
                'max_altitude' => 800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-kayak', 'type' => 'city', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Fewa Lake', 'slug' => 'fewa-lake-kayak', 'type' => 'lake', 'lat' => 28.2231, 'lng' => 83.9490, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'activity', 'name' => 'Kayak Rental (Half Day)', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'activity', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kayaking seeded.');

        $this->command->info('🎉 Adventure Activities Complete! 14 destinations seeded.');
    }
}
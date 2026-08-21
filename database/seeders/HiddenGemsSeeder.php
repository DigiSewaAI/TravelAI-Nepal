<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class HiddenGemsSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('💎 Seeding Hidden Gems...');

        // ==========================================
        // 1. BANDIPUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bandipur Village Tour',
                'slug' => 'bandipur',
                'description' => 'Tour of Bandipur, a traditional Newari village with preserved architecture and mountain views.',
                'duration_days' => 2,
                'max_altitude' => 1030,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bandipur', 'slug' => 'bandipur', 'type' => 'village', 'lat' => 27.9123, 'lng' => 84.4123, 'alt' => 1030],
                ['name' => 'Bandipur Bazaar', 'slug' => 'bandipur-bazaar', 'type' => 'landmark', 'lat' => 27.9123, 'lng' => 84.4123, 'alt' => 1030],
                ['name' => 'Khagendra Lake', 'slug' => 'khagendra-lake', 'type' => 'lake', 'lat' => 27.9234, 'lng' => 84.4234, 'alt' => 1000],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Kathmandu–Bandipur)', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bandipur seeded.');

        // ==========================================
        // 2. GORKHA
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Gorkha Heritage Tour',
                'slug' => 'gorkha-heritage',
                'description' => 'Tour of Gorkha, the birthplace of King Prithvi Narayan Shah, with historical palaces and temples.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Gorkha', 'slug' => 'gorkha-heritage', 'type' => 'village', 'lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1300],
                ['name' => 'Gorkha Durbar', 'slug' => 'gorkha-durbar-heritage', 'type' => 'landmark', 'lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Gorkha seeded.');

        // ==========================================
        // 3. TANSEN
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Tansen Hill Town Tour',
                'slug' => 'tansen',
                'description' => 'Tour of Tansen, a historic hill town with Newari architecture and views of the Himalayas.',
                'duration_days' => 2,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Tansen', 'slug' => 'tansen', 'type' => 'village', 'lat' => 27.8456, 'lng' => 83.5123, 'alt' => 1350],
                ['name' => 'Srinagar Hill', 'slug' => 'srinagar-hill-tansen', 'type' => 'viewpoint', 'lat' => 27.8567, 'lng' => 83.5234, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Tansen seeded.');

        // ==========================================
        // 4. DHULIKHEL
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dhulikhel Scenic Tour',
                'slug' => 'dhulikhel-scenic',
                'description' => 'Tour of Dhulikhel with panoramic views of the Himalayan range and traditional Newari culture.',
                'duration_days' => 1,
                'max_altitude' => 1550,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Dhulikhel', 'slug' => 'dhulikhel-scenic', 'type' => 'village', 'lat' => 27.6223, 'lng' => 85.5456, 'alt' => 1550],
                ['name' => 'Dhulikhel Viewpoint', 'slug' => 'dhulikhel-view', 'type' => 'viewpoint', 'lat' => 27.6323, 'lng' => 85.5456, 'alt' => 1700],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dhulikhel seeded.');

        // ==========================================
        // 5. PANAUTI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Panauti Heritage Tour',
                'slug' => 'panauti-heritage',
                'description' => 'Tour of Panauti, a historical town with ancient temples and traditional Newari architecture.',
                'duration_days' => 1,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Panauti', 'slug' => 'panauti-heritage', 'type' => 'village', 'lat' => 27.6123, 'lng' => 85.5345, 'alt' => 1350],
                ['name' => 'Panauti Durbar Square', 'slug' => 'panauti-ds', 'type' => 'landmark', 'lat' => 27.6123, 'lng' => 85.5345, 'alt' => 1350],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Panauti seeded.');

        // ==========================================
        // 6. NAMOBUDDHA
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Namobuddha Monastery Tour',
                'slug' => 'namobuddha-monastery',
                'description' => 'Tour of Namobuddha Monastery, a sacred Buddhist site with peaceful atmosphere and mountain views.',
                'duration_days' => 1,
                'max_altitude' => 1700,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Namobuddha', 'slug' => 'namobuddha-monastery', 'type' => 'landmark', 'lat' => 27.5891, 'lng' => 85.5567, 'alt' => 1700],
                ['name' => 'Namobuddha Stupa', 'slug' => 'namobuddha-stupa', 'type' => 'landmark', 'lat' => 27.5891, 'lng' => 85.5567, 'alt' => 1700],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Namobuddha seeded.');

        // ==========================================
        // 7. KIRTIPUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kirtipur Ancient Town Tour',
                'slug' => 'kirtipur-ancient',
                'description' => 'Tour of Kirtipur, an ancient Newari town with historical temples and traditional culture.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kirtipur', 'slug' => 'kirtipur-ancient', 'type' => 'village', 'lat' => 27.6756, 'lng' => 85.2789, 'alt' => 1400],
                ['name' => 'Chilancho Stupa', 'slug' => 'chilancho-stupa', 'type' => 'landmark', 'lat' => 27.6789, 'lng' => 85.2812, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kirtipur seeded.');

        // ==========================================
        // 8. SANKHU
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Sankhu Historical Town Tour',
                'slug' => 'sankhu-historical',
                'description' => 'Tour of Sankhu, an ancient Newari town with historical temples and cultural heritage.',
                'duration_days' => 1,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Sankhu', 'slug' => 'sankhu-historical', 'type' => 'village', 'lat' => 27.7345, 'lng' => 85.4567, 'alt' => 1350],
                ['name' => 'Bajrayogini Temple', 'slug' => 'bajrayogini-sankhu', 'type' => 'landmark', 'lat' => 27.7423, 'lng' => 85.4634, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sankhu seeded.');

        // ==========================================
        // 9. KHOKANA
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Khokana Traditional Village Tour',
                'slug' => 'khokana-traditional',
                'description' => 'Tour of Khokana, a traditional Newari village known for mustard oil production and ancient architecture.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Khokana', 'slug' => 'khokana-traditional', 'type' => 'village', 'lat' => 27.6456, 'lng' => 85.2989, 'alt' => 1300],
                ['name' => 'Khokana Durbar Square', 'slug' => 'khokana-ds', 'type' => 'landmark', 'lat' => 27.6456, 'lng' => 85.2989, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Khokana seeded.');

        // ==========================================
        // 10. BUNGAMATI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bungamati Woodcarving Village Tour',
                'slug' => 'bungamati-woodcarving',
                'description' => 'Tour of Bungamati, a village famous for traditional Newari woodcarving and architecture.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bungamati', 'slug' => 'bungamati-woodcarving', 'type' => 'village', 'lat' => 27.6345, 'lng' => 85.3123, 'alt' => 1300],
                ['name' => 'Bungamati Temple', 'slug' => 'bungamati-temple', 'type' => 'landmark', 'lat' => 27.6345, 'lng' => 85.3123, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bungamati seeded.');

        // ==========================================
        // 11. CHOBAR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Chobar Gorge & Temple Tour',
                'slug' => 'chobar-gorge',
                'description' => 'Tour of Chobar Gorge with its ancient temple and scenic views of the Kathmandu Valley.',
                'duration_days' => 1,
                'max_altitude' => 1200,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Chobar', 'slug' => 'chobar-gorge', 'type' => 'village', 'lat' => 27.6123, 'lng' => 85.2901, 'alt' => 1200],
                ['name' => 'Chobar Gorge', 'slug' => 'chobar-gorge', 'type' => 'landmark', 'lat' => 27.6123, 'lng' => 85.2901, 'alt' => 1200],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Chobar seeded.');

        // ==========================================
        // 12. GODAVARI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Godavari Botanical Garden Tour',
                'slug' => 'godavari-botanical',
                'description' => 'Tour of Godavari Botanical Garden and the historic Royal Palace in Lalitpur.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Godavari', 'slug' => 'godavari-botanical', 'type' => 'village', 'lat' => 27.6123, 'lng' => 85.3345, 'alt' => 1400],
                ['name' => 'Botanical Garden', 'slug' => 'botanical-garden', 'type' => 'landmark', 'lat' => 27.6123, 'lng' => 85.3345, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Godavari seeded.');

        // ==========================================
        // 13. PHARPING
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Pharping Monastery & Cave Tour',
                'slug' => 'pharping-monastery',
                'description' => 'Tour of Pharping, a sacred Buddhist site with monasteries and meditation caves.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pharping', 'slug' => 'pharping-monastery', 'type' => 'village', 'lat' => 27.6456, 'lng' => 85.2678, 'alt' => 1300],
                ['name' => 'Pharping Monastery', 'slug' => 'pharping-monastery', 'type' => 'landmark', 'lat' => 27.6456, 'lng' => 85.2678, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Pharping seeded.');

        // ==========================================
        // 14. KAKANI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kakani Hill Station Tour',
                'slug' => 'kakani-hill',
                'description' => 'Tour of Kakani, a scenic hill station with strawberry farms and mountain views.',
                'duration_days' => 1,
                'max_altitude' => 2000,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kakani', 'slug' => 'kakani-hill', 'type' => 'village', 'lat' => 27.8123, 'lng' => 85.4567, 'alt' => 2000],
                ['name' => 'Kakani Viewpoint', 'slug' => 'kakani-view', 'type' => 'viewpoint', 'lat' => 27.8123, 'lng' => 85.4567, 'alt' => 2000],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Kakani seeded.');

        // ==========================================
        // 15. NUWAKOT DURBAR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Nuwakot Durbar Tour',
                'slug' => 'nuwakot-durbar',
                'description' => 'Tour of Nuwakot Durbar, a historical palace with views of the Himalayas.',
                'duration_days' => 1,
                'max_altitude' => 1500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Nuwakot', 'slug' => 'nuwakot-durbar', 'type' => 'village', 'lat' => 27.9123, 'lng' => 85.6123, 'alt' => 1500],
                ['name' => 'Nuwakot Durbar', 'slug' => 'nuwakot-durbar', 'type' => 'landmark', 'lat' => 27.9123, 'lng' => 85.6123, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Nuwakot seeded.');

        // ==========================================
        // 16. SINDHULI
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Sindhuli Fort Tour',
                'slug' => 'sindhuli-fort',
                'description' => 'Tour of Sindhuli Fort, a historic fort with panoramic views of the Sunkoshi River.',
                'duration_days' => 1,
                'max_altitude' => 800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Sindhuli', 'slug' => 'sindhuli-fort', 'type' => 'village', 'lat' => 27.4567, 'lng' => 85.9123, 'alt' => 800],
                ['name' => 'Sindhuli Fort', 'slug' => 'sindhuli-fort', 'type' => 'landmark', 'lat' => 27.4567, 'lng' => 85.9123, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sindhuli seeded.');

        // ==========================================
        // 17. BHEDETAR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bhedetar Hill Station Tour',
                'slug' => 'bhedetar-hill',
                'description' => 'Tour of Bhedetar, a scenic hill station with panoramic views of the eastern Himalayas.',
                'duration_days' => 1,
                'max_altitude' => 1800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bhedetar', 'slug' => 'bhedetar-hill', 'type' => 'village', 'lat' => 26.8567, 'lng' => 87.3789, 'alt' => 1800],
                ['name' => 'Bhedetar Viewpoint', 'slug' => 'bhedetar-view', 'type' => 'viewpoint', 'lat' => 26.8567, 'lng' => 87.3789, 'alt' => 1800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Bhedetar seeded.');

        // ==========================================
        // 18. HILE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Hile Tea Garden Tour',
                'slug' => 'hile-tea',
                'description' => 'Tour of Hile, a scenic town in eastern Nepal known for tea gardens and mountain views.',
                'duration_days' => 1,
                'max_altitude' => 1500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Hile', 'slug' => 'hile-tea', 'type' => 'village', 'lat' => 27.2345, 'lng' => 87.3456, 'alt' => 1500],
                ['name' => 'Hile Tea Gardens', 'slug' => 'hile-tea-gardens', 'type' => 'landmark', 'lat' => 27.2345, 'lng' => 87.3456, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Hile seeded.');

        // ==========================================
        // 19. DHARAN
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dharan City Tour',
                'slug' => 'dharan-city',
                'description' => 'Tour of Dharan, a city in eastern Nepal with cultural sites and scenic views.',
                'duration_days' => 1,
                'max_altitude' => 350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Dharan', 'slug' => 'dharan-city', 'type' => 'city', 'lat' => 26.7891, 'lng' => 87.3123, 'alt' => 350],
                ['name' => 'Bishnupaduka Temple', 'slug' => 'bishnupaduka', 'type' => 'landmark', 'lat' => 26.7891, 'lng' => 87.3123, 'alt' => 350],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dharan seeded.');

        // ==========================================
        // 20. BARUN VALLEY
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Barun Valley Wilderness Tour',
                'slug' => 'barun-valley',
                'description' => 'Tour of Barun Valley, a pristine wilderness area with rich biodiversity and mountain views.',
                'duration_days' => 3,
                'max_altitude' => 3500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Barun Valley', 'slug' => 'barun-valley', 'type' => 'village', 'lat' => 27.5789, 'lng' => 87.4012, 'alt' => 3500],
                ['name' => 'Barun River', 'slug' => 'barun-river', 'type' => 'landmark', 'lat' => 27.5789, 'lng' => 87.4012, 'alt' => 3500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 150, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Barun Valley seeded.');

        // ==========================================
        // 21. SIMIKOT
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Simikot Remote Town Tour',
                'slug' => 'simikot-remote',
                'description' => 'Tour of Simikot, a remote town in Humla near the Tibetan border.',
                'duration_days' => 2,
                'max_altitude' => 2950,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Simikot', 'slug' => 'simikot-remote', 'type' => 'village', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
                ['name' => 'Simikot Gompa', 'slug' => 'simikot-gompa', 'type' => 'landmark', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 80, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Simikot seeded.');

        // ==========================================
        // 22. SINJA VALLEY
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Sinja Valley Historical Tour',
                'slug' => 'sinja-valley',
                'description' => 'Tour of Sinja Valley, the ancient capital of the Khas Kingdom with historical ruins.',
                'duration_days' => 2,
                'max_altitude' => 2500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Sinja Valley', 'slug' => 'sinja-valley', 'type' => 'village', 'lat' => 28.8234, 'lng' => 81.8456, 'alt' => 2500],
                ['name' => 'Sinja Ruins', 'slug' => 'sinja-ruins', 'type' => 'landmark', 'lat' => 28.8234, 'lng' => 81.8456, 'alt' => 2500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sinja Valley seeded.');

        // ==========================================
        // 23. SHEY GOMPA (DOLPA)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Shey Gompa (Dolpa) Tour',
                'slug' => 'shey-gompa-dolpa',
                'description' => 'Tour of Shey Gompa, a sacred Buddhist monastery in the remote Dolpa region.',
                'duration_days' => 3,
                'max_altitude' => 4200,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Shey Gompa', 'slug' => 'shey-gompa-dolpa', 'type' => 'landmark', 'lat' => 29.4123, 'lng' => 82.7123, 'alt' => 4200],
                ['name' => 'Shey Gompa', 'slug' => 'shey-gompa-dolpa', 'type' => 'landmark', 'lat' => 29.4123, 'lng' => 82.7123, 'alt' => 4200],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 150, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Shey Gompa seeded.');

        $this->command->info('🎉 Hidden Gems Complete! 23 destinations seeded.');
    }
}
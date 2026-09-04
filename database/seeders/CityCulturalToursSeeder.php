<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class CityCulturalToursSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏛️ Seeding City & Cultural Tours...');

        // ==========================================
// 1. KATHMANDU HERITAGE TOUR (1 DAY, 4 UNESCO SITES)
// ==========================================
$this->helper->seedTour([
    'route' => [
        'name' => 'Kathmandu Heritage Tour',
        'slug' => 'kathmandu-heritage',
        'description' => 'UNESCO World Heritage tour covering Pashupatinath Temple, Boudhanath Stupa, Swayambhunath Stupa, and Kathmandu Durbar Square.',
        'duration_days' => 1,
        'max_altitude' => 1450,
        'season' => 'All Year',
    ],
    'waypoints' => [
        ['name' => 'Kathmandu', 'slug' => 'kathmandu-heritage-start', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
        ['name' => 'Pashupatinath Temple', 'slug' => 'pashupatinath-heritage', 'type' => 'landmark', 'lat' => 27.7108, 'lng' => 85.3482, 'alt' => 1350],
        ['name' => 'Boudhanath Stupa', 'slug' => 'boudhanath-heritage', 'type' => 'landmark', 'lat' => 27.7215, 'lng' => 85.3629, 'alt' => 1350],
        ['name' => 'Swayambhunath Stupa', 'slug' => 'swayambhunath-heritage', 'type' => 'landmark', 'lat' => 27.7148, 'lng' => 85.2901, 'alt' => 1450],
        ['name' => 'Kathmandu Durbar Square', 'slug' => 'kathmandu-durbar', 'type' => 'landmark', 'lat' => 27.7044, 'lng' => 85.3078, 'alt' => 1400],
        ['name' => 'Kathmandu', 'slug' => 'kathmandu-heritage-end', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
    ],
    'segments' => [
        ['from' => 'kathmandu-heritage-start', 'to' => 'pashupatinath-heritage', 'dist' => 2.0, 'time' => 1.0],
        ['from' => 'pashupatinath-heritage', 'to' => 'boudhanath-heritage', 'dist' => 1.5, 'time' => 0.5],
        ['from' => 'boudhanath-heritage', 'to' => 'swayambhunath-heritage', 'dist' => 5.0, 'time' => 2.0],
        ['from' => 'swayambhunath-heritage', 'to' => 'kathmandu-durbar', 'dist' => 3.0, 'time' => 1.0],
        ['from' => 'kathmandu-durbar', 'to' => 'kathmandu-heritage-end', 'dist' => 1.0, 'time' => 0.5],
    ],
    'costs' => [
        ['type' => 'tour', 'name' => 'UNESCO Entrance Fees (4 sites)', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => false],
        ['type' => 'tour', 'name' => 'Private Vehicle (Full Day)', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
        ['type' => 'tour', 'name' => 'Guide Service (Full Day)', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
    ],
]);
$this->command->info('✅ Kathmandu Heritage Tour seeded.');

        // ==========================================
// 2. KATHMANDU CITY TOUR (2 DAYS, 7 ATTRACTIONS)
// ==========================================
$this->helper->seedTour([
    'route' => [
        'name' => 'Kathmandu City Tour',
        'slug' => 'kathmandu-city-tour',
        'description' => 'Comprehensive Kathmandu city experience covering UNESCO sites, local markets, Thamel, Asan Bazaar, and Garden of Dreams.',
        'duration_days' => 2,
        'max_altitude' => 1450,
        'season' => 'All Year',
    ],
    'waypoints' => [
        ['name' => 'Kathmandu', 'slug' => 'kathmandu-city-start', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
        ['name' => 'Swayambhunath Stupa', 'slug' => 'swayambhunath-city', 'type' => 'landmark', 'lat' => 27.7148, 'lng' => 85.2901, 'alt' => 1450],
        ['name' => 'Boudhanath Stupa', 'slug' => 'boudhanath-city', 'type' => 'landmark', 'lat' => 27.7215, 'lng' => 85.3629, 'alt' => 1350],
        ['name' => 'Pashupatinath Temple', 'slug' => 'pashupatinath-city', 'type' => 'landmark', 'lat' => 27.7108, 'lng' => 85.3482, 'alt' => 1350],
        ['name' => 'Kathmandu Durbar Square', 'slug' => 'kathmandu-durbar-city', 'type' => 'landmark', 'lat' => 27.7044, 'lng' => 85.3078, 'alt' => 1400],
        ['name' => 'Thamel', 'slug' => 'thamel-city', 'type' => 'neighborhood', 'lat' => 27.7141, 'lng' => 85.3124, 'alt' => 1400],
        ['name' => 'Asan Bazaar', 'slug' => 'asan-city', 'type' => 'neighborhood', 'lat' => 27.7072, 'lng' => 85.3082, 'alt' => 1400],
        ['name' => 'Garden of Dreams', 'slug' => 'garden-city', 'type' => 'park', 'lat' => 27.7168, 'lng' => 85.3182, 'alt' => 1400],
        ['name' => 'Kathmandu', 'slug' => 'kathmandu-city-end', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
    ],
    'segments' => [
        ['from' => 'kathmandu-city-start', 'to' => 'swayambhunath-city', 'dist' => 3.0, 'time' => 1.0],
        ['from' => 'swayambhunath-city', 'to' => 'boudhanath-city', 'dist' => 6.0, 'time' => 2.0],
        ['from' => 'boudhanath-city', 'to' => 'pashupatinath-city', 'dist' => 2.0, 'time' => 0.5],
        ['from' => 'pashupatinath-city', 'to' => 'kathmandu-durbar-city', 'dist' => 4.0, 'time' => 1.5],
        ['from' => 'kathmandu-durbar-city', 'to' => 'thamel-city', 'dist' => 0.5, 'time' => 0.2],
        ['from' => 'thamel-city', 'to' => 'asan-city', 'dist' => 0.5, 'time' => 0.2],
        ['from' => 'asan-city', 'to' => 'garden-city', 'dist' => 1.0, 'time' => 0.3],
        ['from' => 'garden-city', 'to' => 'kathmandu-city-end', 'dist' => 0.5, 'time' => 0.2],
    ],
    'costs' => [
        ['type' => 'tour', 'name' => 'UNESCO Entrance Fees (4 sites)', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => false],
        ['type' => 'tour', 'name' => 'Private Vehicle (2 days)', 'amount' => 80, 'unit' => 'per_group', 'mandatory' => false],
        ['type' => 'tour', 'name' => 'Guide Service (2 days)', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
    ],
]);
$this->command->info('✅ Kathmandu City Tour seeded.');


        // ==========================================
        // 4. BHAKTAPUR DURBAR SQUARE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bhaktapur Durbar Square Tour',
                'slug' => 'bhaktapur-tour',
                'description' => 'Full-day tour of Bhaktapur Durbar Square, a UNESCO World Heritage Site with ancient Newari architecture.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bhaktapur', 'slug' => 'bhaktapur', 'type' => 'village', 'lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1400],
                ['name' => 'Bhaktapur Durbar Square', 'slug' => 'bhaktapur-ds-tour', 'type' => 'landmark', 'lat' => 27.6719, 'lng' => 85.4284, 'alt' => 1400],
                ['name' => 'Nyatapola Temple', 'slug' => 'nyatapola', 'type' => 'landmark', 'lat' => 27.6725, 'lng' => 85.4303, 'alt' => 1400],
                ['name' => 'Pottery Square', 'slug' => 'pottery-square', 'type' => 'landmark', 'lat' => 27.6713, 'lng' => 85.4314, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Bhaktapur Entrance Fee', 'amount' => 15, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Bhaktapur Durbar Square Tour seeded.');

        // ==========================================
        // 5. PATAN DURBAR SQUARE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Patan Durbar Square Tour',
                'slug' => 'patan-tour',
                'description' => 'Tour of Patan Durbar Square, known for its fine Newari art and architecture.',
                'duration_days' => 1,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Patan', 'slug' => 'patan', 'type' => 'village', 'lat' => 27.6736, 'lng' => 85.3251, 'alt' => 1350],
                ['name' => 'Patan Durbar Square', 'slug' => 'patan-ds-tour', 'type' => 'landmark', 'lat' => 27.6732, 'lng' => 85.3249, 'alt' => 1350],
                ['name' => 'Golden Temple', 'slug' => 'golden-temple', 'type' => 'landmark', 'lat' => 27.6741, 'lng' => 85.3263, 'alt' => 1350],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Patan Entrance Fee', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Patan Durbar Square Tour seeded.');

        // ==========================================
        // 6. KIRTIPUR VILLAGE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kirtipur Village Tour',
                'slug' => 'kirtipur-tour',
                'description' => 'Tour of the ancient Newari town of Kirtipur with its temples and traditional houses.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-kirtipur', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Kirtipur', 'slug' => 'kirtipur', 'type' => 'village', 'lat' => 27.6756, 'lng' => 85.2789, 'alt' => 1400],
                ['name' => 'Chilancho Stupa', 'slug' => 'chilancho', 'type' => 'landmark', 'lat' => 27.6789, 'lng' => 85.2812, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Kirtipur Village Tour seeded.');

        // ==========================================
        // 7. SANKHU–BAJRAYOGINI TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Sankhu–Bajrayogini Tour',
                'slug' => 'sankhu-bajrayogini',
                'description' => 'Tour of the ancient town of Sankhu and the Bajrayogini Temple, a sacred Shakti site.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-sankhu', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Sankhu', 'slug' => 'sankhu', 'type' => 'village', 'lat' => 27.7345, 'lng' => 85.4567, 'alt' => 1350],
                ['name' => 'Bajrayogini Temple', 'slug' => 'bajrayogini', 'type' => 'landmark', 'lat' => 27.7423, 'lng' => 85.4634, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Sankhu–Bajrayogini Tour seeded.');

        // ==========================================
        // 8. NAGARKOT SUNRISE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Nagarkot Sunrise Tour',
                'slug' => 'nagarkot-sunrise',
                'description' => 'Early morning tour to Nagarkot for sunrise views over the Himalayan range, including Everest on clear days.',
                'duration_days' => 1,
                'max_altitude' => 2195,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-nagarkot', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Nagarkot Viewpoint', 'slug' => 'nagarkot-view', 'type' => 'landmark', 'lat' => 27.7123, 'lng' => 85.5345, 'alt' => 2195],
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-nagarkot-return', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            ],
            'segments' => [
                ['from' => 'kathmandu-nagarkot', 'to' => 'nagarkot-view', 'dist' => 25.0, 'time' => 6.0],
                ['from' => 'nagarkot-view', 'to' => 'kathmandu-nagarkot-return', 'dist' => 25.0, 'time' => 6.0],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Round Trip)', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Breakfast at Nagarkot', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Nagarkot Sunrise Tour seeded.');

        // ==========================================
        // 9. SARANGKOT SUNRISE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Sarangkot Sunrise Tour',
                'slug' => 'sarangkot-sunrise',
                'description' => 'Early morning drive to Sarangkot for breathtaking sunrise views over Annapurna and Dhaulagiri.',
                'duration_days' => 1,
                'max_altitude' => 1600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-sarangkot', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Sarangkot', 'slug' => 'sarangkot-tour', 'type' => 'landmark', 'lat' => 28.2446, 'lng' => 83.9453, 'alt' => 1600],
                ['name' => 'Pokhara', 'slug' => 'pokhara-sarangkot-return', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
            ],
            'segments' => [
                ['from' => 'pokhara-sarangkot', 'to' => 'sarangkot-tour', 'dist' => 8.0, 'time' => 2.0],
                ['from' => 'sarangkot-tour', 'to' => 'pokhara-sarangkot-return', 'dist' => 8.0, 'time' => 2.0],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Round Trip)', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Sarangkot Sunrise Tour seeded.');

        // ==========================================
        // 10. DHULIKHEL–NAMOBUDDHA TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dhulikhel–Namobuddha Tour',
                'slug' => 'dhulikhel-namobuddha',
                'description' => 'Scenic tour to Dhulikhel for mountain views and Namobuddha Monastery.',
                'duration_days' => 1,
                'max_altitude' => 1700,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-dhulikhel', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Dhulikhel', 'slug' => 'dhulikhel', 'type' => 'village', 'lat' => 27.6223, 'lng' => 85.5456, 'alt' => 1550],
                ['name' => 'Namobuddha Monastery', 'slug' => 'namobuddha', 'type' => 'landmark', 'lat' => 27.5891, 'lng' => 85.5567, 'alt' => 1700],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Dhulikhel–Namobuddha Tour seeded.');

        // ==========================================
        // 11. PANUTI–KHOKANA–BUNGAMATI TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Panauti–Khokana–Bungamati Tour',
                'slug' => 'panauti-khokana-bungamati',
                'description' => 'Tour of three traditional Newari towns: Panauti, Khokana, and Bungamati.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-pkb', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Panauti', 'slug' => 'panauti', 'type' => 'village', 'lat' => 27.6123, 'lng' => 85.5345, 'alt' => 1350],
                ['name' => 'Khokana', 'slug' => 'khokana', 'type' => 'village', 'lat' => 27.6456, 'lng' => 85.2989, 'alt' => 1300],
                ['name' => 'Bungamati', 'slug' => 'bungamati', 'type' => 'village', 'lat' => 27.6345, 'lng' => 85.3123, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Panauti–Khokana–Bungamati Tour seeded.');

        // ==========================================
        // 12. LUMBINI BUDDHIST CIRCUIT
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Lumbini Buddhist Circuit',
                'slug' => 'lumbini-circuit',
                'description' => 'Tour of Lumbini, the birthplace of Lord Buddha, visiting monasteries from various countries.',
                'duration_days' => 2,
                'max_altitude' => 150,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Lumbini', 'slug' => 'lumbini', 'type' => 'village', 'lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
                ['name' => 'Mayadevi Temple', 'slug' => 'mayadevi', 'type' => 'landmark', 'lat' => 27.4698, 'lng' => 83.2762, 'alt' => 150],
                ['name' => 'Ashoka Pillar', 'slug' => 'ashoka-pillar', 'type' => 'landmark', 'lat' => 27.4702, 'lng' => 83.2774, 'alt' => 150],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Lumbini Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Lumbini Buddhist Circuit seeded.');

        // ==========================================
        // 13. KAPILAVASTU TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kapilavastu Tour',
                'slug' => 'kapilavastu-tour',
                'description' => 'Tour of Kapilavastu, the ancient capital of the Shakya kingdom where Buddha spent his early years.',
                'duration_days' => 1,
                'max_altitude' => 150,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Lumbini', 'slug' => 'lumbini-kapilavastu', 'type' => 'village', 'lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
                ['name' => 'Kapilavastu', 'slug' => 'kapilavastu', 'type' => 'village', 'lat' => 27.5345, 'lng' => 83.0234, 'alt' => 150],
                ['name' => 'Tilaurakot', 'slug' => 'tilaurakot', 'type' => 'landmark', 'lat' => 27.5456, 'lng' => 83.0123, 'alt' => 150],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Kapilavastu Tour seeded.');

        // ==========================================
        // 14. JANAKPUR (JANAKI TEMPLE) TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Janakpur (Janaki Temple) Tour',
                'slug' => 'janakpur-tour',
                'description' => 'Tour of Janakpur, the birthplace of Sita, with a visit to the magnificent Janaki Temple.',
                'duration_days' => 2,
                'max_altitude' => 80,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Janakpur', 'slug' => 'janakpur', 'type' => 'village', 'lat' => 26.7234, 'lng' => 85.9234, 'alt' => 80],
                ['name' => 'Janaki Temple', 'slug' => 'janaki-temple', 'type' => 'landmark', 'lat' => 26.7234, 'lng' => 85.9234, 'alt' => 80],
                ['name' => 'Ram Sita Vivaha Mandap', 'slug' => 'vivaha-mandap', 'type' => 'landmark', 'lat' => 26.7234, 'lng' => 85.9234, 'alt' => 80],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Janaki Temple Donation', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Janakpur Tour seeded.');

        // ==========================================
        // 15. MUKTINATH TEMPLE TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Muktinath Temple Tour',
                'slug' => 'muktinath-temple-tour',
                'description' => 'Tour of the sacred Muktinath Temple, a pilgrimage site for both Hindus and Buddhists.',
                'duration_days' => 2,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-muktinath', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Jomsom', 'slug' => 'jomsom-muktinath-tour', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Muktinath Temple', 'slug' => 'muktinath-temple', 'type' => 'landmark', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Pokhara–Jomsom)', 'amount' => 150, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Muktinath Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Muktinath Temple Tour seeded.');

        // ==========================================
        // 16. JOMSOM–KAGBENI–MUKTINATH TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Jomsom–Kagbeni–Muktinath Tour',
                'slug' => 'jomsom-kagbeni-muktinath',
                'description' => 'Cultural tour through the Mustang region, visiting Kagbeni and Muktinath.',
                'duration_days' => 3,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-jkm', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Jomsom', 'slug' => 'jomsom-jkm', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Kagbeni', 'slug' => 'kagbeni-jkm', 'type' => 'village', 'lat' => 28.8145, 'lng' => 83.7812, 'alt' => 2800],
                ['name' => 'Muktinath Temple', 'slug' => 'muktinath-jkm', 'type' => 'landmark', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Pokhara–Jomsom)', 'amount' => 200, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Muktinath Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Jomsom–Kagbeni–Muktinath Tour seeded.');

        // ==========================================
        // 17. MARPHA–TUKUCHE–KOBANG TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Marpha–Tukuche–Kobang Tour',
                'slug' => 'marpha-tukuche-kobang',
                'description' => 'Tour of the apple-growing villages of Marpha, Tukuche, and Kobang in Mustang.',
                'duration_days' => 2,
                'max_altitude' => 2800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-mtk', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Marpha', 'slug' => 'marpha-mtk', 'type' => 'village', 'lat' => 28.7345, 'lng' => 83.7123, 'alt' => 2670],
                ['name' => 'Tukuche', 'slug' => 'tukuche-mtk', 'type' => 'village', 'lat' => 28.7567, 'lng' => 83.6891, 'alt' => 2590],
                ['name' => 'Kobang', 'slug' => 'kobang', 'type' => 'village', 'lat' => 28.7789, 'lng' => 83.6678, 'alt' => 2800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Pokhara–Marpha)', 'amount' => 180, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Marpha–Tukuche–Kobang Tour seeded.');

        // ==========================================
        // 18. DHARAN–DHANKUTA–BHEDETAR TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dharan–Dhankuta–Bhedetar Tour',
                'slug' => 'dharan-dhankuta-bhedetar',
                'description' => 'Scenic tour of eastern Nepal, visiting Dharan, Dhankuta, and the hill station Bhedetar.',
                'duration_days' => 2,
                'max_altitude' => 1800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Biratnagar', 'slug' => 'biratnagar-ddb', 'type' => 'village', 'lat' => 26.4567, 'lng' => 87.2789, 'alt' => 80],
                ['name' => 'Dharan', 'slug' => 'dharan', 'type' => 'village', 'lat' => 26.7891, 'lng' => 87.3123, 'alt' => 350],
                ['name' => 'Dhankuta', 'slug' => 'dhankuta', 'type' => 'village', 'lat' => 26.8234, 'lng' => 87.3456, 'alt' => 1200],
                ['name' => 'Bhedetar', 'slug' => 'bhedetar', 'type' => 'village', 'lat' => 26.8567, 'lng' => 87.3789, 'alt' => 1800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 100, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Dharan–Dhankuta–Bhedetar Tour seeded.');

        // ==========================================
        // 19. BIRATNAGAR–KOSHI RIVER TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Biratnagar–Koshi River Tour',
                'slug' => 'biratnagar-koshi',
                'description' => 'Tour of Biratnagar and the Koshi River, Nepal\'s largest river system.',
                'duration_days' => 1,
                'max_altitude' => 80,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Biratnagar', 'slug' => 'biratnagar-koshi', 'type' => 'village', 'lat' => 26.4567, 'lng' => 87.2789, 'alt' => 80],
                ['name' => 'Koshi Barrage', 'slug' => 'koshi-barrage', 'type' => 'landmark', 'lat' => 26.4789, 'lng' => 87.3123, 'alt' => 80],
                ['name' => 'Koshi Tappu Wildlife Reserve', 'slug' => 'koshi-tappu', 'type' => 'landmark', 'lat' => 26.5123, 'lng' => 87.3456, 'alt' => 80],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Biratnagar–Koshi River Tour seeded.');

        // ==========================================
        // 20. BUTWAL–SIDDHARTHANAGAR TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Butwal–Siddharthanagar Tour',
                'slug' => 'butwal-siddharthanagar',
                'description' => 'Tour of Butwal and Siddharthanagar (Bhairahawa), the gateway to Lumbini.',
                'duration_days' => 1,
                'max_altitude' => 150,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Butwal', 'slug' => 'butwal', 'type' => 'village', 'lat' => 27.7123, 'lng' => 83.4567, 'alt' => 150],
                ['name' => 'Siddharthanagar', 'slug' => 'siddharthanagar', 'type' => 'village', 'lat' => 27.5345, 'lng' => 83.4567, 'alt' => 150],
                ['name' => 'Bhairahawa', 'slug' => 'bhairahawa', 'type' => 'village', 'lat' => 27.5234, 'lng' => 83.4567, 'alt' => 150],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Butwal–Siddharthanagar Tour seeded.');

        // ==========================================
        // 21. SURKHET–BIRENDRANAGAR TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Surkhet–Birendranagar Tour',
                'slug' => 'surkhet-birendranagar',
                'description' => 'Tour of Surkhet Valley and Birendranagar, the capital of Karnali Province.',
                'duration_days' => 1,
                'max_altitude' => 600,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Surkhet', 'slug' => 'surkhet', 'type' => 'village', 'lat' => 28.6123, 'lng' => 81.6123, 'alt' => 600],
                ['name' => 'Birendranagar', 'slug' => 'birendranagar', 'type' => 'village', 'lat' => 28.6456, 'lng' => 81.6456, 'alt' => 600],
                ['name' => 'Kakrebihar', 'slug' => 'kakrebihar', 'type' => 'landmark', 'lat' => 28.6789, 'lng' => 81.6789, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Surkhet–Birendranagar Tour seeded.');

        // ==========================================
        // 22. KALIKOT–SINJA VALLEY TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Kalikot–Sinja Valley Tour',
                'slug' => 'kalikot-sinja',
                'description' => 'Tour of Kalikot and Sinja Valley, the ancient capital of the Khas Kingdom.',
                'duration_days' => 2,
                'max_altitude' => 2500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Surkhet', 'slug' => 'surkhet-ks', 'type' => 'village', 'lat' => 28.6123, 'lng' => 81.6123, 'alt' => 600],
                ['name' => 'Kalikot', 'slug' => 'kalikot', 'type' => 'village', 'lat' => 28.7891, 'lng' => 81.8123, 'alt' => 1800],
                ['name' => 'Sinja Valley', 'slug' => 'sinja-valley', 'type' => 'village', 'lat' => 28.8234, 'lng' => 81.8456, 'alt' => 2500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 80, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Kalikot–Sinja Valley Tour seeded.');

        // ==========================================
        // 23. JUMLA–SINJA VALLEY TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Jumla–Sinja Valley Tour',
                'slug' => 'jumla-sinja',
                'description' => 'Tour of Jumla and Sinja Valley, the historical heart of the Khas Kingdom.',
                'duration_days' => 3,
                'max_altitude' => 2500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Jumla', 'slug' => 'jumla-sinja', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
                ['name' => 'Sinja Valley', 'slug' => 'sinja-jumla', 'type' => 'village', 'lat' => 28.8234, 'lng' => 81.8456, 'alt' => 2500],
                ['name' => 'Jumla', 'slug' => 'jumla-sinja-return', 'type' => 'village', 'lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 60, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Jumla–Sinja Valley Tour seeded.');

        // ==========================================
        // 24. SIMIKOT–HUMLA TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Simikot–Humla Tour',
                'slug' => 'simikot-humla',
                'description' => 'Tour of Simikot and the remote Humla region, near the Tibetan border.',
                'duration_days' => 3,
                'max_altitude' => 3000,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Simikot', 'slug' => 'simikot-humla', 'type' => 'village', 'lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2950],
                ['name' => 'Sipsi', 'slug' => 'sipsi-humla', 'type' => 'village', 'lat' => 29.9123, 'lng' => 82.0345, 'alt' => 3400],
                ['name' => 'Jhari', 'slug' => 'jhari-humla', 'type' => 'village', 'lat' => 29.8456, 'lng' => 82.0567, 'alt' => 3700],
                ['name' => 'Hilsa', 'slug' => 'hilsa-humla', 'type' => 'village', 'lat' => 29.7789, 'lng' => 82.0789, 'alt' => 4200],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 100, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Simikot–Humla Tour seeded.');

        // ==========================================
        // 25. BAJHANG–BAJURA TOUR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Bajhang–Bajura Tour',
                'slug' => 'bajhang-bajura',
                'description' => 'Tour of Bajhang and Bajura districts in the far west of Nepal.',
                'duration_days' => 3,
                'max_altitude' => 1500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Bajhang', 'slug' => 'bajhang-tour', 'type' => 'village', 'lat' => 29.7123, 'lng' => 81.2345, 'alt' => 900],
                ['name' => 'Bajura', 'slug' => 'bajura', 'type' => 'village', 'lat' => 29.6456, 'lng' => 81.4567, 'alt' => 1500],
                ['name' => 'Bajhang', 'slug' => 'bajhang-tour-return', 'type' => 'village', 'lat' => 29.7123, 'lng' => 81.2345, 'alt' => 900],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 120, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);
        $this->command->info('✅ Bajhang–Bajura Tour seeded.');

        $this->command->info('🎉 City & Cultural Tours Complete! 25 destinations seeded.');
    }
}
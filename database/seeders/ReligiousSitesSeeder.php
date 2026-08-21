<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class ReligiousSitesSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🕉️ Seeding Religious & Pilgrimage Sites...');

        // ==========================================
        // 1. PASHUPATINATH TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Pashupatinath Temple Tour',
                'slug' => 'pashupatinath-tour',
                'description' => 'Tour of the sacred Pashupatinath Temple, one of the most important Hindu temples dedicated to Lord Shiva.',
                'duration_days' => 1,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-pashu', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Pashupatinath Temple', 'slug' => 'pashupatinath-temple', 'type' => 'landmark', 'lat' => 27.7108, 'lng' => 85.3482, 'alt' => 1350],
                ['name' => 'Aryaghat', 'slug' => 'aryaghat', 'type' => 'landmark', 'lat' => 27.7123, 'lng' => 85.3491, 'alt' => 1350],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Temple Entrance Fee', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Pashupatinath Temple seeded.');

        // ==========================================
        // 2. BOUDHANATH STUPA
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Boudhanath Stupa Tour',
                'slug' => 'boudhanath-tour',
                'description' => 'Tour of Boudhanath Stupa, one of the largest stupas in the world and a sacred Buddhist pilgrimage site.',
                'duration_days' => 1,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-boudha', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Boudhanath Stupa', 'slug' => 'boudhanath-stupa', 'type' => 'landmark', 'lat' => 27.7215, 'lng' => 85.3629, 'alt' => 1350],
                ['name' => 'Monasteries', 'slug' => 'boudha-monasteries', 'type' => 'landmark', 'lat' => 27.7234, 'lng' => 85.3634, 'alt' => 1350],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Stupa Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Boudhanath Stupa seeded.');

        // ==========================================
        // 3. SWAYAMBHUNATH STUPA
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Swayambhunath Stupa Tour',
                'slug' => 'swayambhunath-tour',
                'description' => 'Tour of Swayambhunath Stupa, the ancient monkey temple with panoramic views of Kathmandu Valley.',
                'duration_days' => 1,
                'max_altitude' => 1450,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-swayambhu', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Swayambhunath Stupa', 'slug' => 'swayambhunath-stupa', 'type' => 'landmark', 'lat' => 27.7148, 'lng' => 85.2901, 'alt' => 1450],
                ['name' => 'Harati Temple', 'slug' => 'harati-temple', 'type' => 'landmark', 'lat' => 27.7156, 'lng' => 85.2891, 'alt' => 1450],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Stupa Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Swayambhunath Stupa seeded.');

        // ==========================================
        // 4. MUKTINATH TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Muktinath Temple Pilgrimage',
                'slug' => 'muktinath-pilgrimage',
                'description' => 'Pilgrimage to Muktinath Temple, a sacred site for both Hindus and Buddhists at 3800m altitude.',
                'duration_days' => 3,
                'max_altitude' => 3800,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-muktinath-pil', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Jomsom', 'slug' => 'jomsom-muktinath-pil', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Muktinath Temple', 'slug' => 'muktinath-pil-temple', 'type' => 'landmark', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Temple Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle (Pokhara–Jomsom)', 'amount' => 150, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Muktinath Temple seeded.');

        // ==========================================
        // 5. JANAKI TEMPLE (JANAKPUR)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Janaki Temple Pilgrimage',
                'slug' => 'janaki-temple-pilgrimage',
                'description' => 'Pilgrimage to Janaki Temple in Janakpur, the birthplace of Goddess Sita and a sacred Hindu site.',
                'duration_days' => 2,
                'max_altitude' => 80,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Janakpur', 'slug' => 'janakpur-pil', 'type' => 'village', 'lat' => 26.7234, 'lng' => 85.9234, 'alt' => 80],
                ['name' => 'Janaki Temple', 'slug' => 'janaki-temple-pil', 'type' => 'landmark', 'lat' => 26.7234, 'lng' => 85.9234, 'alt' => 80],
                ['name' => 'Ram Sita Vivaha Mandap', 'slug' => 'vivaha-mandap-pil', 'type' => 'landmark', 'lat' => 26.7245, 'lng' => 85.9245, 'alt' => 80],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Temple Donation', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Janaki Temple seeded.');

        // ==========================================
        // 6. LUMBINI (MAYADEVI TEMPLE)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Lumbini Mayadevi Temple Pilgrimage',
                'slug' => 'lumbini-mayadevi',
                'description' => 'Pilgrimage to Mayadevi Temple in Lumbini, the birthplace of Lord Buddha, a UNESCO World Heritage Site.',
                'duration_days' => 2,
                'max_altitude' => 150,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Lumbini', 'slug' => 'lumbini-mayadevi', 'type' => 'village', 'lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
                ['name' => 'Mayadevi Temple', 'slug' => 'mayadevi-temple', 'type' => 'landmark', 'lat' => 27.4698, 'lng' => 83.2762, 'alt' => 150],
                ['name' => 'Ashoka Pillar', 'slug' => 'ashoka-pillar-maya', 'type' => 'landmark', 'lat' => 27.4702, 'lng' => 83.2774, 'alt' => 150],
                ['name' => 'Monasteries', 'slug' => 'lumbini-monasteries', 'type' => 'landmark', 'lat' => 27.4723, 'lng' => 83.2812, 'alt' => 150],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Lumbini Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Lumbini Mayadevi Temple seeded.');

        // ==========================================
        // 7. MANAKAMANA TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Manakamana Temple Pilgrimage',
                'slug' => 'manakamana-temple',
                'description' => 'Pilgrimage to Manakamana Temple, the wish-fulfilling goddess temple accessible by cable car.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-manakamana', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Manakamana Temple', 'slug' => 'manakamana-temple', 'type' => 'landmark', 'lat' => 27.8234, 'lng' => 84.5678, 'alt' => 1300],
                ['name' => 'Cable Car Station', 'slug' => 'manakamana-cable', 'type' => 'landmark', 'lat' => 27.8123, 'lng' => 84.5567, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Cable Car Ticket (Round Trip)', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 40, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Manakamana Temple seeded.');

        // ==========================================
        // 8. GORKHA DURBAR
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Gorkha Durbar Tour',
                'slug' => 'gorkha-durbar',
                'description' => 'Tour of Gorkha Durbar, the historic palace of King Prithvi Narayan Shah, the unifier of Nepal.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-gorkha', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Gorkha Durbar', 'slug' => 'gorkha-durbar', 'type' => 'landmark', 'lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1300],
                ['name' => 'Gorkha Kalika Temple', 'slug' => 'gorkha-kalika', 'type' => 'landmark', 'lat' => 28.0156, 'lng' => 84.6156, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Durbar Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Gorkha Durbar seeded.');

        // ==========================================
        // 9. PALPA (TANSEN, RANI MAHAL) - viewpoint → landmark
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Palpa (Tansen, Rani Mahal) Tour',
                'slug' => 'palpa-tansen-rani',
                'description' => 'Tour of Tansen and Rani Mahal, the historical palace built by General Khadga Shumsher on the banks of the Kali Gandaki.',
                'duration_days' => 2,
                'max_altitude' => 1350,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Tansen', 'slug' => 'tansen', 'type' => 'village', 'lat' => 27.8456, 'lng' => 83.5123, 'alt' => 1350],
                ['name' => 'Rani Mahal', 'slug' => 'rani-mahal', 'type' => 'landmark', 'lat' => 27.8123, 'lng' => 83.5345, 'alt' => 800],
                ['name' => 'Srinagar Hill', 'slug' => 'srinagar-hill', 'type' => 'landmark', 'lat' => 27.8567, 'lng' => 83.5234, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 60, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Palpa (Tansen, Rani Mahal) seeded.');

        // ==========================================
        // 10. RANIGHAT (RANI MAHAL)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Ranighat (Rani Mahal) Tour',
                'slug' => 'ranighat-rani-mahal',
                'description' => 'Tour of Ranighat (Rani Mahal), the historic palace on the banks of the Kali Gandaki River.',
                'duration_days' => 1,
                'max_altitude' => 800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Tansen', 'slug' => 'tansen-ranighat', 'type' => 'village', 'lat' => 27.8456, 'lng' => 83.5123, 'alt' => 1350],
                ['name' => 'Ranighat', 'slug' => 'ranighat', 'type' => 'landmark', 'lat' => 27.8123, 'lng' => 83.5345, 'alt' => 800],
                ['name' => 'Kali Gandaki River', 'slug' => 'kali-gandaki-ranighat', 'type' => 'landmark', 'lat' => 27.8145, 'lng' => 83.5367, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Ranighat seeded.');

        // ==========================================
        // 11. DAKSHINKALI TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Dakshinkali Temple Tour',
                'slug' => 'dakshinkali-temple',
                'description' => 'Tour of Dakshinkali Temple, a sacred Hindu temple dedicated to Goddess Kali, located near Kathmandu.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-dakshinkali', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Dakshinkali Temple', 'slug' => 'dakshinkali-temple', 'type' => 'landmark', 'lat' => 27.6456, 'lng' => 85.2789, 'alt' => 1400],
                ['name' => 'Dakshinkali River', 'slug' => 'dakshinkali-river', 'type' => 'landmark', 'lat' => 27.6456, 'lng' => 85.2789, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour' , 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Dakshinkali Temple seeded.');

        // ==========================================
        // 12. CHANDRAGIRI TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Chandragiri Temple Tour',
                'slug' => 'chandragiri-temple',
                'description' => 'Tour of Chandragiri Temple, a sacred Hindu temple with cable car access and panoramic Himalayan views.',
                'duration_days' => 1,
                'max_altitude' => 2551,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Kathmandu', 'slug' => 'kathmandu-chandragiri', 'type' => 'village', 'lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
                ['name' => 'Chandragiri Temple', 'slug' => 'chandragiri-temple', 'type' => 'landmark', 'lat' => 27.6456, 'lng' => 85.2456, 'alt' => 2551],
                ['name' => 'Cable Car Station', 'slug' => 'chandragiri-cable', 'type' => 'landmark', 'lat' => 27.6567, 'lng' => 85.2345, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Cable Car Ticket (Round Trip)', 'amount' => 10, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Chandragiri Temple seeded.');

        // ==========================================
        // 13. GUPTESHWOR MAHADEV CAVE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Gupteshwor Mahadev Cave Tour',
                'slug' => 'gupteshwor-cave',
                'description' => 'Tour of Gupteshwor Mahadev Cave, a sacred Hindu cave near Pokhara with stalactites and stalagmites.',
                'duration_days' => 1,
                'max_altitude' => 950,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-gupteshwor', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Gupteshwor Cave', 'slug' => 'gupteshwor-cave', 'type' => 'landmark', 'lat' => 28.2318, 'lng' => 83.9341, 'alt' => 950],
                ['name' => 'Davis Falls', 'slug' => 'davis-falls', 'type' => 'landmark', 'lat' => 28.2323, 'lng' => 83.9323, 'alt' => 950],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Cave Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 5, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Gupteshwor Mahadev Cave seeded.');

        // ==========================================
        // 14. BARAHI TEMPLE - lake → landmark
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Barahi Temple Tour',
                'slug' => 'barahi-temple',
                'description' => 'Tour of Barahi Temple, a sacred Hindu temple located on an island in Fewa Lake, Pokhara.',
                'duration_days' => 1,
                'max_altitude' => 800,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-barahi', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Barahi Temple', 'slug' => 'barahi-temple', 'type' => 'landmark', 'lat' => 28.2181, 'lng' => 83.9482, 'alt' => 800],
                ['name' => 'Fewa Lake', 'slug' => 'fewa-lake-barahi', 'type' => 'landmark', 'lat' => 28.2231, 'lng' => 83.9490, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Boat Ride (Round Trip)', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 5, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Barahi Temple seeded.');

        // ==========================================
        // 15. GORAKHNATH TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Gorakhnath Temple Tour',
                'slug' => 'gorakhnath-temple',
                'description' => 'Tour of Gorakhnath Temple, a sacred Hindu temple dedicated to Guru Gorakhnath in Gorkha.',
                'duration_days' => 1,
                'max_altitude' => 1300,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Gorkha', 'slug' => 'gorkha-gorakhnath', 'type' => 'village', 'lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1300],
                ['name' => 'Gorakhnath Temple', 'slug' => 'gorakhnath-temple', 'type' => 'landmark', 'lat' => 28.0156, 'lng' => 84.6156, 'alt' => 1300],
                ['name' => 'Gorkha Bazaar', 'slug' => 'gorkha-bazaar', 'type' => 'village', 'lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1300],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 5, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Gorakhnath Temple seeded.');

        // ==========================================
        // 16. DOLESHWAR MAHADEV
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Doleshwar Mahadev Tour',
                'slug' => 'doleshwar-mahadev',
                'description' => 'Tour of Doleshwar Mahadev Temple, a sacred Shiva temple in Bhaktapur.',
                'duration_days' => 1,
                'max_altitude' => 1400,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bhaktapur', 'slug' => 'bhaktapur-doleshwar', 'type' => 'village', 'lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1400],
                ['name' => 'Doleshwar Mahadev Temple', 'slug' => 'doleshwar-mahadev', 'type' => 'landmark', 'lat' => 27.6678, 'lng' => 85.4345, 'alt' => 1400],
                ['name' => 'Bhaktapur Durbar Square', 'slug' => 'bhaktapur-ds-doleshwar', 'type' => 'landmark', 'lat' => 27.6719, 'lng' => 85.4284, 'alt' => 1400],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 5, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Doleshwar Mahadev seeded.');

        // ==========================================
        // 17. CHANGUNARAYAN TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Changunarayan Temple Tour',
                'slug' => 'changunarayan-temple',
                'description' => 'Tour of Changunarayan Temple, the oldest Hindu temple in Nepal, a UNESCO World Heritage Site.',
                'duration_days' => 1,
                'max_altitude' => 1500,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Bhaktapur', 'slug' => 'bhaktapur-changu', 'type' => 'village', 'lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1400],
                ['name' => 'Changunarayan Temple', 'slug' => 'changunarayan-temple', 'type' => 'landmark', 'lat' => 27.7123, 'lng' => 85.4234, 'alt' => 1500],
                ['name' => 'Changunarayan Village', 'slug' => 'changunarayan-village', 'type' => 'village', 'lat' => 27.7145, 'lng' => 85.4245, 'alt' => 1500],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Temple Entrance Fee', 'amount' => 5, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 20, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 10, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Changunarayan Temple seeded.');

        // ==========================================
        // 18. BAGLUNG KALIKA TEMPLE
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Baglung Kalika Temple Tour',
                'slug' => 'baglung-kalika',
                'description' => 'Tour of Baglung Kalika Temple, a sacred Hindu temple dedicated to Goddess Kalika.',
                'duration_days' => 1,
                'max_altitude' => 1000,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Baglung', 'slug' => 'baglung-kalika', 'type' => 'village', 'lat' => 28.3123, 'lng' => 83.6123, 'alt' => 1000],
                ['name' => 'Baglung Kalika Temple', 'slug' => 'baglung-kalika-temple', 'type' => 'landmark', 'lat' => 28.3156, 'lng' => 83.6156, 'alt' => 1000],
                ['name' => 'Baglung Bazaar', 'slug' => 'baglung-bazaar', 'type' => 'village', 'lat' => 28.3123, 'lng' => 83.6123, 'alt' => 1000],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 5, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Baglung Kalika Temple seeded.');

        $this->command->info('🎉 Religious & Pilgrimage Sites Complete! 18 destinations seeded.');
    }
}
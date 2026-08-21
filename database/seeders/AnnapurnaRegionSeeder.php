<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class AnnapurnaRegionSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🌄 Seeding Annapurna Region...');

        // ==========================================
        // 1. GHOREPANI POON HILL
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Ghorepani Poon Hill Trek',
                'slug' => 'poon-hill',
                'description' => 'Short and scenic trek to Poon Hill for sunrise views over Annapurna and Dhaulagiri.',
                'difficulty' => 'easy',
                'duration_days' => 5,
                'max_altitude' => 3210,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Nayapul', 'slug' => 'nayapul-poon', 'type' => 'village', 'lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
                ['name' => 'Birethanti', 'slug' => 'birethanti-poon', 'type' => 'village', 'lat' => 28.4245, 'lng' => 83.7564, 'alt' => 1025],
                ['name' => 'Tikhedhunga', 'slug' => 'tikhedhunga-poon', 'type' => 'village', 'lat' => 28.4387, 'lng' => 83.7105, 'alt' => 1540],
                ['name' => 'Ulleri', 'slug' => 'ulleri-poon', 'type' => 'village', 'lat' => 28.4412, 'lng' => 83.7221, 'alt' => 1960],
                ['name' => 'Ghorepani', 'slug' => 'ghorepani', 'type' => 'village', 'lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
                ['name' => 'Poon Hill', 'slug' => 'poon-hill', 'type' => 'peak', 'lat' => 28.4964, 'lng' => 83.7188, 'alt' => 3210],
                ['name' => 'Tadapani', 'slug' => 'tadapani-poon', 'type' => 'village', 'lat' => 28.5107, 'lng' => 83.7435, 'alt' => 2630],
                ['name' => 'Ghandruk', 'slug' => 'ghandruk', 'type' => 'village', 'lat' => 28.4681, 'lng' => 83.8027, 'alt' => 1940],
            ],
            'segments' => [
                ['from' => 'nayapul-poon', 'to' => 'birethanti-poon', 'dist' => 10.5, 'time' => 4.5],
                ['from' => 'birethanti-poon', 'to' => 'tikhedhunga-poon', 'dist' => 8.2, 'time' => 3.5, 'gain' => 515],
                ['from' => 'tikhedhunga-poon', 'to' => 'ulleri-poon', 'dist' => 5.8, 'time' => 2.5, 'gain' => 420],
                ['from' => 'ulleri-poon', 'to' => 'ghorepani', 'dist' => 9.1, 'time' => 4.0, 'gain' => 900],
                ['from' => 'ghorepani', 'to' => 'poon-hill', 'dist' => 2.0, 'time' => 1.0, 'gain' => 350],
                ['from' => 'ghorepani', 'to' => 'tadapani-poon', 'dist' => 7.4, 'time' => 3.5, 'loss' => 230],
                ['from' => 'tadapani-poon', 'to' => 'ghandruk', 'dist' => 5.6, 'time' => 2.5, 'loss' => 690],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false, 'metadata' => ['verified' => false, 'source' => 'estimate']],
            ],
        ]);

        $this->command->info('✅ Ghorepani Poon Hill seeded.');

        // ==========================================
        // 2. ANNAPURNA CIRCUIT
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Annapurna Circuit Trek',
                'slug' => 'annapurna-circuit',
                'description' => 'Classic circuit around the Annapurna massif, crossing Thorong La pass (5416m).',
                'difficulty' => 'moderate',
                'duration_days' => 16,
                'max_altitude' => 5416,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Besisahar', 'slug' => 'besisahar', 'type' => 'village', 'lat' => 28.2398, 'lng' => 84.3824, 'alt' => 760],
                ['name' => 'Bahundanda', 'slug' => 'bahundanda', 'type' => 'village', 'lat' => 28.3312, 'lng' => 84.3601, 'alt' => 1310],
                ['name' => 'Chamche', 'slug' => 'chamche', 'type' => 'village', 'lat' => 28.4751, 'lng' => 84.3317, 'alt' => 1380],
                ['name' => 'Dharapani', 'slug' => 'dharapani', 'type' => 'village', 'lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
                ['name' => 'Chame', 'slug' => 'chame', 'type' => 'village', 'lat' => 28.5581, 'lng' => 84.3587, 'alt' => 2670],
                ['name' => 'Pisang', 'slug' => 'pisang', 'type' => 'village', 'lat' => 28.6194, 'lng' => 84.2027, 'alt' => 3200],
                ['name' => 'Manang', 'slug' => 'manang', 'type' => 'village', 'lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
                ['name' => 'Yak Kharka', 'slug' => 'yak-kharka', 'type' => 'village', 'lat' => 28.7123, 'lng' => 84.0877, 'alt' => 4010],
                ['name' => 'Thorong Phedi', 'slug' => 'thorong-phedi', 'type' => 'village', 'lat' => 28.7525, 'lng' => 84.0649, 'alt' => 4420],
                ['name' => 'Thorong La', 'slug' => 'thorong-la', 'type' => 'pass', 'lat' => 28.7992, 'lng' => 84.0081, 'alt' => 5416],
                ['name' => 'Muktinath', 'slug' => 'muktinath', 'type' => 'village', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
                ['name' => 'Jomsom', 'slug' => 'jomsom', 'type' => 'village', 'lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
                ['name' => 'Tatopani', 'slug' => 'tatopani', 'type' => 'village', 'lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
                ['name' => 'Ghorepani', 'slug' => 'ghorepani', 'type' => 'village', 'lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
                ['name' => 'Nayapul', 'slug' => 'nayapul', 'type' => 'village', 'lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
            ],
            'segments' => [
                ['from' => 'besisahar', 'to' => 'bahundanda', 'dist' => 12.0, 'time' => 5.0, 'gain' => 550],
                ['from' => 'bahundanda', 'to' => 'chamche', 'dist' => 8.5, 'time' => 4.0, 'gain' => 70],
                ['from' => 'chamche', 'to' => 'dharapani', 'dist' => 10.0, 'time' => 4.5, 'gain' => 480],
                ['from' => 'dharapani', 'to' => 'chame', 'dist' => 14.0, 'time' => 6.0, 'gain' => 810],
                ['from' => 'chame', 'to' => 'pisang', 'dist' => 9.0, 'time' => 4.0, 'gain' => 530],
                ['from' => 'pisang', 'to' => 'manang', 'dist' => 12.0, 'time' => 5.0, 'gain' => 340],
                ['from' => 'manang', 'to' => 'yak-kharka', 'dist' => 7.5, 'time' => 3.5, 'gain' => 470],
                ['from' => 'yak-kharka', 'to' => 'thorong-phedi', 'dist' => 5.5, 'time' => 3.0, 'gain' => 410],
                ['from' => 'thorong-phedi', 'to' => 'thorong-la', 'dist' => 5.0, 'time' => 3.0, 'gain' => 996],
                ['from' => 'thorong-la', 'to' => 'muktinath', 'dist' => 8.0, 'time' => 4.0, 'loss' => 1616],
                ['from' => 'muktinath', 'to' => 'jomsom', 'dist' => 12.0, 'time' => 5.0, 'loss' => 1100],
                ['from' => 'jomsom', 'to' => 'tatopani', 'dist' => 16.0, 'time' => 6.0, 'loss' => 1510],
                ['from' => 'tatopani', 'to' => 'ghorepani', 'dist' => 10.0, 'time' => 4.5, 'gain' => 1670],
                ['from' => 'ghorepani', 'to' => 'nayapul', 'dist' => 18.0, 'time' => 7.0, 'loss' => 1790],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'Manang Special Permit', 'amount' => 50, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false, 'metadata' => ['verified' => false, 'source' => 'estimate']],
            ],
        ]);

        $this->command->info('✅ Annapurna Circuit seeded.');

        // ==========================================
        // 3. MARDI HIMAL
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Mardi Himal Trek',
                'slug' => 'mardi-himal',
                'description' => 'Short, scenic trek with close-up views of Machhapuchhare and Annapurna South.',
                'difficulty' => 'moderate',
                'duration_days' => 6,
                'max_altitude' => 4500,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Kande', 'slug' => 'kande', 'type' => 'village', 'lat' => 28.3602, 'lng' => 83.8468, 'alt' => 1770],
                ['name' => 'Pothana', 'slug' => 'pothana', 'type' => 'village', 'lat' => 28.3714, 'lng' => 83.8309, 'alt' => 1900],
                ['name' => 'Forest Camp', 'slug' => 'forest-camp', 'type' => 'village', 'lat' => 28.3915, 'lng' => 83.7912, 'alt' => 2500],
                ['name' => 'Low Camp', 'slug' => 'low-camp', 'type' => 'village', 'lat' => 28.3997, 'lng' => 83.7745, 'alt' => 3150],
                ['name' => 'High Camp', 'slug' => 'high-camp', 'type' => 'village', 'lat' => 28.4089, 'lng' => 83.7593, 'alt' => 3580],
                ['name' => 'Mardi Himal Base Camp', 'slug' => 'mardi-bc', 'type' => 'checkpoint', 'lat' => 28.4201, 'lng' => 83.7482, 'alt' => 4500],
                ['name' => 'Siding Village', 'slug' => 'siding', 'type' => 'village', 'lat' => 28.3442, 'lng' => 83.7109, 'alt' => 1700],
            ],
            'segments' => [
                ['from' => 'kande', 'to' => 'pothana', 'dist' => 4.0, 'time' => 2.0, 'gain' => 130],
                ['from' => 'pothana', 'to' => 'forest-camp', 'dist' => 6.0, 'time' => 3.0, 'gain' => 600],
                ['from' => 'forest-camp', 'to' => 'low-camp', 'dist' => 5.0, 'time' => 2.5, 'gain' => 650],
                ['from' => 'low-camp', 'to' => 'high-camp', 'dist' => 4.0, 'time' => 2.0, 'gain' => 430],
                ['from' => 'high-camp', 'to' => 'mardi-bc', 'dist' => 3.5, 'time' => 2.5, 'gain' => 920],
                ['from' => 'high-camp', 'to' => 'siding', 'dist' => 10.0, 'time' => 4.5, 'loss' => 1880],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Mardi Himal seeded.');

        // ==========================================
        // 4. NAR PHU VALLEY
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Nar Phu Valley Trek',
                'slug' => 'nar-phu',
                'description' => 'Remote valley trek with Tibetan culture and stunning mountain views.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 5320,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Koto', 'slug' => 'koto', 'type' => 'village', 'lat' => 28.5531, 'lng' => 84.2877, 'alt' => 2680],
                ['name' => 'Nar Phedi', 'slug' => 'nar-phedi', 'type' => 'village', 'lat' => 28.5683, 'lng' => 84.2534, 'alt' => 2900],
                ['name' => 'Nar Village', 'slug' => 'nar', 'type' => 'village', 'lat' => 28.6127, 'lng' => 84.2108, 'alt' => 4110],
                ['name' => 'Phu Village', 'slug' => 'phu', 'type' => 'village', 'lat' => 28.6512, 'lng' => 84.1589, 'alt' => 4080],
                ['name' => 'Kang La Pass', 'slug' => 'kang-la', 'type' => 'pass', 'lat' => 28.6897, 'lng' => 84.1123, 'alt' => 5320],
                ['name' => 'Ngawal', 'slug' => 'ngawal', 'type' => 'village', 'lat' => 28.7048, 'lng' => 84.0582, 'alt' => 3650],
            ],
            'segments' => [
                ['from' => 'koto', 'to' => 'nar-phedi', 'dist' => 8.0, 'time' => 4.0, 'gain' => 220],
                ['from' => 'nar-phedi', 'to' => 'nar', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1210],
                ['from' => 'nar', 'to' => 'phu', 'dist' => 6.0, 'time' => 3.0, 'loss' => 30],
                ['from' => 'phu', 'to' => 'kang-la', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1240],
                ['from' => 'kang-la', 'to' => 'ngawal', 'dist' => 6.0, 'time' => 3.5, 'loss' => 1670],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Nar Phu Special Permit', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Nar Phu Valley seeded.');

        // ==========================================
        // 5. TILICHO LAKE (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Tilicho Lake Trek',
                'slug' => 'tilicho-lake',
                'description' => 'Trek to the world\'s highest lake at 4919m, with views of Annapurna and Manaslu.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 4919,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Manang', 'slug' => 'manang', 'type' => 'village', 'lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
                ['name' => 'Khangsar', 'slug' => 'khangsar', 'type' => 'village', 'lat' => 28.6963, 'lng' => 84.0731, 'alt' => 3730],
                ['name' => 'Tilicho Base Camp', 'slug' => 'tilicho-bc', 'type' => 'village', 'lat' => 28.7234, 'lng' => 84.0274, 'alt' => 4150],
                ['name' => 'Tilicho Lake', 'slug' => 'tilicho-lake', 'type' => 'landmark', 'lat' => 28.7346, 'lng' => 84.0098, 'alt' => 4919],
                ['name' => 'Muktinath', 'slug' => 'muktinath', 'type' => 'village', 'lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            ],
            'segments' => [
                ['from' => 'manang', 'to' => 'khangsar', 'dist' => 6.0, 'time' => 3.0, 'gain' => 190],
                ['from' => 'khangsar', 'to' => 'tilicho-bc', 'dist' => 8.0, 'time' => 4.5, 'gain' => 420],
                ['from' => 'tilicho-bc', 'to' => 'tilicho-lake', 'dist' => 4.0, 'time' => 2.5, 'gain' => 769],
                ['from' => 'tilicho-lake', 'to' => 'muktinath', 'dist' => 14.0, 'time' => 6.0, 'loss' => 1119],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Tilicho Lake seeded.');

        // ==========================================
        // 6. KHOPRA RIDGE / KHAYER LAKE (viewpoint → landmark, lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Khopra Ridge / Khayer Lake Trek',
                'slug' => 'khopra-ridge',
                'description' => 'Off-beat trek with panoramic views of Dhaulagiri, Annapurna, and Machhapuchhare.',
                'difficulty' => 'moderate',
                'duration_days' => 8,
                'max_altitude' => 4660,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Nayapul', 'slug' => 'nayapul-khopra', 'type' => 'village', 'lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
                ['name' => 'Ghandruk', 'slug' => 'ghandruk-khopra', 'type' => 'village', 'lat' => 28.4681, 'lng' => 83.8027, 'alt' => 1940],
                ['name' => 'Tadapani', 'slug' => 'tadapani-khopra', 'type' => 'village', 'lat' => 28.5107, 'lng' => 83.7435, 'alt' => 2630],
                ['name' => 'Chhomrong', 'slug' => 'chhomrong-khopra', 'type' => 'village', 'lat' => 28.5332, 'lng' => 83.7589, 'alt' => 2170],
                ['name' => 'Khopra Ridge', 'slug' => 'khopra-ridge', 'type' => 'landmark', 'lat' => 28.4934, 'lng' => 83.6271, 'alt' => 3660],
                ['name' => 'Khayer Lake', 'slug' => 'khayer-lake', 'type' => 'landmark', 'lat' => 28.5123, 'lng' => 83.5987, 'alt' => 4660],
            ],
            'segments' => [
                ['from' => 'nayapul-khopra', 'to' => 'ghandruk-khopra', 'dist' => 12.0, 'time' => 5.0, 'gain' => 870],
                ['from' => 'ghandruk-khopra', 'to' => 'tadapani-khopra', 'dist' => 6.0, 'time' => 3.0, 'gain' => 690],
                ['from' => 'tadapani-khopra', 'to' => 'chhomrong-khopra', 'dist' => 8.0, 'time' => 3.5, 'loss' => 460],
                ['from' => 'chhomrong-khopra', 'to' => 'khopra-ridge', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1490],
                ['from' => 'khopra-ridge', 'to' => 'khayer-lake', 'dist' => 6.0, 'time' => 3.5, 'gain' => 1000],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Khopra Ridge seeded.');

        // ==========================================
        // 7. MOHARE DANDA (viewpoint → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Mohare Danda Trek',
                'slug' => 'mohare-danda',
                'description' => 'Community-based eco-trek with sunrise views over Dhaulagiri and Annapurna.',
                'difficulty' => 'easy',
                'duration_days' => 6,
                'max_altitude' => 3300,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Kalikasthan', 'slug' => 'kalikasthan', 'type' => 'village', 'lat' => 28.3489, 'lng' => 83.8154, 'alt' => 1370],
                ['name' => 'Banthanti', 'slug' => 'banthanti-mohare', 'type' => 'village', 'lat' => 28.3823, 'lng' => 83.7856, 'alt' => 2180],
                ['name' => 'Mohare Danda', 'slug' => 'mohare-danda', 'type' => 'landmark', 'lat' => 28.3987, 'lng' => 83.7468, 'alt' => 3300],
                ['name' => 'Danda Kharka', 'slug' => 'danda-kharka', 'type' => 'village', 'lat' => 28.4205, 'lng' => 83.7312, 'alt' => 2800],
            ],
            'segments' => [
                ['from' => 'kalikasthan', 'to' => 'banthanti-mohare', 'dist' => 6.0, 'time' => 3.0, 'gain' => 810],
                ['from' => 'banthanti-mohare', 'to' => 'mohare-danda', 'dist' => 4.0, 'time' => 2.5, 'gain' => 1120],
                ['from' => 'mohare-danda', 'to' => 'danda-kharka', 'dist' => 5.0, 'time' => 2.0, 'loss' => 500],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 20, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Mohare Danda seeded.');

        // ==========================================
        // 8. SIKLES TREK (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Sikles Trek',
                'slug' => 'sikles',
                'description' => 'Cultural trek through Gurung villages with views of Annapurna and Machhapuchhare.',
                'difficulty' => 'easy',
                'duration_days' => 6,
                'max_altitude' => 2200,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-sikles', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Sikles', 'slug' => 'sikles', 'type' => 'village', 'lat' => 28.2918, 'lng' => 84.0357, 'alt' => 1980],
                ['name' => 'Kapuche Lake', 'slug' => 'kapuche', 'type' => 'landmark', 'lat' => 28.3154, 'lng' => 84.0503, 'alt' => 2200],
            ],
            'segments' => [
                ['from' => 'pokhara-sikles', 'to' => 'sikles', 'dist' => 15.0, 'time' => 6.0, 'gain' => 1153],
                ['from' => 'sikles', 'to' => 'kapuche', 'dist' => 5.0, 'time' => 2.5, 'gain' => 220],
            ],
            'costs' => [
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 20, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sikles Trek seeded.');

        // ==========================================
        // 9. PANCHASE TREK (viewpoint → landmark, lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Panchase Trek',
                'slug' => 'panchase',
                'description' => 'Short trek with views of Annapurna, Dhaulagiri, and Machhapuchhare from Panchase Hill.',
                'difficulty' => 'easy',
                'duration_days' => 5,
                'max_altitude' => 2490,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-panchase', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Bhamarkot', 'slug' => 'bhamarkot', 'type' => 'village', 'lat' => 28.2458, 'lng' => 83.9712, 'alt' => 1290],
                ['name' => 'Panchase Bhanjyang', 'slug' => 'panchase-bhanjyang', 'type' => 'landmark', 'lat' => 28.2703, 'lng' => 83.9508, 'alt' => 2490],
                ['name' => 'Panchase Lake', 'slug' => 'panchase-lake', 'type' => 'landmark', 'lat' => 28.2815, 'lng' => 83.9456, 'alt' => 2100],
            ],
            'segments' => [
                ['from' => 'pokhara-panchase', 'to' => 'bhamarkot', 'dist' => 8.0, 'time' => 4.0, 'gain' => 463],
                ['from' => 'bhamarkot', 'to' => 'panchase-bhanjyang', 'dist' => 5.0, 'time' => 2.5, 'gain' => 1200],
                ['from' => 'panchase-bhanjyang', 'to' => 'panchase-lake', 'dist' => 3.0, 'time' => 1.5, 'loss' => 390],
            ],
            'costs' => [
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 20, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Panchase Trek seeded.');

        // ==========================================
        // 10. POKHARA CITY TOUR (city → village)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Pokhara City Tour',
                'slug' => 'pokhara-city-tour',
                'description' => 'Explore Pokhara\'s top attractions: Fewa Lake, World Peace Pagoda, Gupteshwor Cave, and Barahi Temple.',
                'duration_days' => 2,
                'max_altitude' => 1100,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-city', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Fewa Lake', 'slug' => 'fewa-lake', 'type' => 'landmark', 'lat' => 28.2231, 'lng' => 83.9490, 'alt' => 800],
                ['name' => 'World Peace Pagoda', 'slug' => 'peace-pagoda', 'type' => 'landmark', 'lat' => 28.2269, 'lng' => 83.9284, 'alt' => 1100],
                ['name' => 'Gupteshwor Cave', 'slug' => 'gupteshwor', 'type' => 'landmark', 'lat' => 28.2318, 'lng' => 83.9341, 'alt' => 950],
                ['name' => 'Barahi Temple', 'slug' => 'barahi', 'type' => 'landmark', 'lat' => 28.2181, 'lng' => 83.9482, 'alt' => 800],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle', 'amount' => 50, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Pokhara City Tour seeded.');

        // ==========================================
        // 11. SARANGKOT SUNRISE TOUR (viewpoint → landmark)
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
                ['name' => 'Sarangkot', 'slug' => 'sarangkot', 'type' => 'landmark', 'lat' => 28.2446, 'lng' => 83.9453, 'alt' => 1600],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Round Trip)', 'amount' => 30, 'unit' => 'per_group', 'mandatory' => false],
                ['type' => 'tour', 'name' => 'Guide Service', 'amount' => 15, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Sarangkot Sunrise Tour seeded.');

        // ==========================================
        // 12. BEGNAS–RUPA LAKE TOUR (lake → landmark)
        // ==========================================
        $this->helper->seedTour([
            'route' => [
                'name' => 'Begnas–Rupa Lake Tour',
                'slug' => 'begnas-rupa-lake',
                'description' => 'Scenic tour of the pristine Begnas and Rupa Lakes near Pokhara.',
                'duration_days' => 1,
                'max_altitude' => 650,
                'season' => 'All Year',
            ],
            'waypoints' => [
                ['name' => 'Pokhara', 'slug' => 'pokhara-begnas', 'type' => 'village', 'lat' => 28.2096, 'lng' => 83.9857, 'alt' => 827],
                ['name' => 'Begnas Lake', 'slug' => 'begnas-lake', 'type' => 'landmark', 'lat' => 28.1734, 'lng' => 84.0741, 'alt' => 650],
                ['name' => 'Rupa Lake', 'slug' => 'rupa-lake', 'type' => 'landmark', 'lat' => 28.1578, 'lng' => 84.0987, 'alt' => 600],
            ],
            'costs' => [
                ['type' => 'tour', 'name' => 'Private Vehicle (Round Trip)', 'amount' => 25, 'unit' => 'per_group', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Begnas–Rupa Lake Tour seeded.');

        $this->command->info('🎉 Annapurna Region Complete! 12 destinations seeded.');
    }
}
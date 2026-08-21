<?php

namespace Database\Seeders;

use App\Services\RouteDataHelper;
use Illuminate\Database\Seeder;

class LangtangHelambuManasluRegionSeeder extends Seeder
{
    protected RouteDataHelper $helper;

    public function __construct(RouteDataHelper $helper)
    {
        $this->helper = $helper;
    }

    public function run(): void
    {
        $this->command->info('🏔️ Seeding Langtang, Helambu & Manaslu Region...');

        // ==========================================
        // 1. TAMANG HERITAGE TRAIL
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Tamang Heritage Trail',
                'slug' => 'tamang-heritage',
                'description' => 'Cultural trek through Tamang villages, monasteries, and traditional mountain life near Langtang.',
                'difficulty' => 'easy',
                'duration_days' => 7,
                'max_altitude' => 3165,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Syabrubesi', 'slug' => 'syabru-tamang', 'type' => 'village', 'lat' => 28.1579, 'lng' => 85.3378, 'alt' => 1503],
                ['name' => 'Goljung', 'slug' => 'goljung', 'type' => 'village', 'lat' => 28.1842, 'lng' => 85.3785, 'alt' => 1830],
                ['name' => 'Thuman', 'slug' => 'thuman', 'type' => 'village', 'lat' => 28.2045, 'lng' => 85.4003, 'alt' => 2280],
                ['name' => 'Briddim', 'slug' => 'briddim', 'type' => 'village', 'lat' => 28.2267, 'lng' => 85.4241, 'alt' => 2229],
                ['name' => 'Nagthali', 'slug' => 'nagthali', 'type' => 'village', 'lat' => 28.2478, 'lng' => 85.4512, 'alt' => 3165],
                ['name' => 'Thangjet', 'slug' => 'thangjet', 'type' => 'village', 'lat' => 28.2612, 'lng' => 85.4789, 'alt' => 2700],
                ['name' => 'Syabrubesi', 'slug' => 'syabru-tamang-return', 'type' => 'village', 'lat' => 28.1579, 'lng' => 85.3378, 'alt' => 1503],
            ],
            'segments' => [
                ['from' => 'syabru-tamang', 'to' => 'goljung', 'dist' => 6.0, 'time' => 3.0, 'gain' => 327],
                ['from' => 'goljung', 'to' => 'thuman', 'dist' => 5.0, 'time' => 2.5, 'gain' => 450],
                ['from' => 'thuman', 'to' => 'briddim', 'dist' => 4.0, 'time' => 2.0, 'loss' => 51],
                ['from' => 'briddim', 'to' => 'nagthali', 'dist' => 5.0, 'time' => 3.0, 'gain' => 936],
                ['from' => 'nagthali', 'to' => 'thangjet', 'dist' => 4.0, 'time' => 2.0, 'loss' => 465],
                ['from' => 'thangjet', 'to' => 'syabru-tamang-return', 'dist' => 10.0, 'time' => 4.5, 'loss' => 1197],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Langtang National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true, 'metadata' => ['verified' => true, 'source' => 'NTB']],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Tamang Heritage Trail seeded.');

        // ==========================================
        // 2. GOSAIKUNDA TREK (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Gosaikunda Trek',
                'slug' => 'gosaikunda',
                'description' => 'Trek to the sacred Gosaikunda Lake at 4380m, an important pilgrimage site for Hindus and Buddhists.',
                'difficulty' => 'moderate',
                'duration_days' => 9,
                'max_altitude' => 4380,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Dhunche', 'slug' => 'dhunche', 'type' => 'village', 'lat' => 28.0937, 'lng' => 85.3068, 'alt' => 1950],
                ['name' => 'Sing Gompa', 'slug' => 'sing-gompa', 'type' => 'village', 'lat' => 28.1284, 'lng' => 85.3412, 'alt' => 3250],
                ['name' => 'Chandanbari', 'slug' => 'chandanbari', 'type' => 'village', 'lat' => 28.1453, 'lng' => 85.3589, 'alt' => 3330],
                ['name' => 'Gosaikunda', 'slug' => 'gosaikunda', 'type' => 'landmark', 'lat' => 28.1784, 'lng' => 85.3931, 'alt' => 4380],
                ['name' => 'Ghopte', 'slug' => 'ghopte', 'type' => 'village', 'lat' => 28.1589, 'lng' => 85.4127, 'alt' => 3440],
                ['name' => 'Chisapani', 'slug' => 'chisapani-gosaikunda', 'type' => 'village', 'lat' => 28.1356, 'lng' => 85.4283, 'alt' => 2300],
                ['name' => 'Sundarijal', 'slug' => 'sundarijal', 'type' => 'village', 'lat' => 28.0821, 'lng' => 85.4243, 'alt' => 1350],
            ],
            'segments' => [
                ['from' => 'dhunche', 'to' => 'sing-gompa', 'dist' => 8.0, 'time' => 4.0, 'gain' => 1300],
                ['from' => 'sing-gompa', 'to' => 'chandanbari', 'dist' => 3.0, 'time' => 1.5, 'gain' => 80],
                ['from' => 'chandanbari', 'to' => 'gosaikunda', 'dist' => 6.0, 'time' => 3.5, 'gain' => 1050],
                ['from' => 'gosaikunda', 'to' => 'ghopte', 'dist' => 5.0, 'time' => 3.0, 'loss' => 940],
                ['from' => 'ghopte', 'to' => 'chisapani-gosaikunda', 'dist' => 7.0, 'time' => 3.5, 'loss' => 1140],
                ['from' => 'chisapani-gosaikunda', 'to' => 'sundarijal', 'dist' => 8.0, 'time' => 4.0, 'loss' => 950],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Langtang National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Gosaikunda Trek seeded.');

        // ==========================================
        // 3. HELAMBU CIRCUIT
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Helambu Circuit',
                'slug' => 'helambu-circuit',
                'description' => 'Beautiful trek through the Helambu region with views of Langtang and Gauri Shankar.',
                'difficulty' => 'moderate',
                'duration_days' => 9,
                'max_altitude' => 3710,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Sundarijal', 'slug' => 'sundarijal-helambu', 'type' => 'village', 'lat' => 28.0821, 'lng' => 85.4243, 'alt' => 1350],
                ['name' => 'Chisapani', 'slug' => 'chisapani-helambu', 'type' => 'village', 'lat' => 28.1356, 'lng' => 85.4283, 'alt' => 2300],
                ['name' => 'Gul Bhanjyang', 'slug' => 'gul-bhanjyang', 'type' => 'village', 'lat' => 28.1589, 'lng' => 85.4678, 'alt' => 2700],
                ['name' => 'Tharepati', 'slug' => 'tharepati', 'type' => 'village', 'lat' => 28.1845, 'lng' => 85.5123, 'alt' => 3490],
                ['name' => 'Melamchi Gaon', 'slug' => 'melamchi-gaon', 'type' => 'village', 'lat' => 28.1532, 'lng' => 85.5389, 'alt' => 3100],
                ['name' => 'Tarkeghyang', 'slug' => 'tarkeghyang', 'type' => 'village', 'lat' => 28.1234, 'lng' => 85.5567, 'alt' => 2740],
                ['name' => 'Sermathang', 'slug' => 'sermathang', 'type' => 'village', 'lat' => 28.0987, 'lng' => 85.5891, 'alt' => 2610],
                ['name' => 'Melamchi Bazaar', 'slug' => 'melamchi-bazaar', 'type' => 'village', 'lat' => 28.0456, 'lng' => 85.4987, 'alt' => 1270],
            ],
            'segments' => [
                ['from' => 'sundarijal-helambu', 'to' => 'chisapani-helambu', 'dist' => 10.0, 'time' => 5.0, 'gain' => 950],
                ['from' => 'chisapani-helambu', 'to' => 'gul-bhanjyang', 'dist' => 5.0, 'time' => 3.0, 'gain' => 400],
                ['from' => 'gul-bhanjyang', 'to' => 'tharepati', 'dist' => 6.0, 'time' => 3.5, 'gain' => 790],
                ['from' => 'tharepati', 'to' => 'melamchi-gaon', 'dist' => 5.0, 'time' => 2.5, 'loss' => 390],
                ['from' => 'melamchi-gaon', 'to' => 'tarkeghyang', 'dist' => 4.0, 'time' => 2.0, 'loss' => 360],
                ['from' => 'tarkeghyang', 'to' => 'sermathang', 'dist' => 5.0, 'time' => 2.5, 'loss' => 130],
                ['from' => 'sermathang', 'to' => 'melamchi-bazaar', 'dist' => 12.0, 'time' => 5.0, 'loss' => 1340],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Helambu Special Permit', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 25, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Helambu Circuit seeded.');

        // ==========================================
        // 4. LAURIBINA PASS (lake → landmark)
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Lauribina Pass Trek',
                'slug' => 'lauribina-pass',
                'description' => 'Trek to the sacred Gosaikunda Lake crossing the Lauribina Pass (4610m) for panoramic Himalayan views.',
                'difficulty' => 'moderate',
                'duration_days' => 8,
                'max_altitude' => 4610,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Dhunche', 'slug' => 'dhunche-lauribina', 'type' => 'village', 'lat' => 28.0937, 'lng' => 85.3068, 'alt' => 1950],
                ['name' => 'Sing Gompa', 'slug' => 'sing-gompa-lauribina', 'type' => 'village', 'lat' => 28.1284, 'lng' => 85.3412, 'alt' => 3250],
                ['name' => 'Gosaikunda', 'slug' => 'gosaikunda-lauribina', 'type' => 'landmark', 'lat' => 28.1784, 'lng' => 85.3931, 'alt' => 4380],
                ['name' => 'Lauribina Pass', 'slug' => 'lauribina-pass', 'type' => 'pass', 'lat' => 28.1823, 'lng' => 85.4027, 'alt' => 4610],
                ['name' => 'Ghopte', 'slug' => 'ghopte-lauribina', 'type' => 'village', 'lat' => 28.1589, 'lng' => 85.4127, 'alt' => 3440],
                ['name' => 'Sundarijal', 'slug' => 'sundarijal-lauribina', 'type' => 'village', 'lat' => 28.0821, 'lng' => 85.4243, 'alt' => 1350],
            ],
            'segments' => [
                ['from' => 'dhunche-lauribina', 'to' => 'sing-gompa-lauribina', 'dist' => 8.0, 'time' => 4.0, 'gain' => 1300],
                ['from' => 'sing-gompa-lauribina', 'to' => 'gosaikunda-lauribina', 'dist' => 8.0, 'time' => 4.5, 'gain' => 1130],
                ['from' => 'gosaikunda-lauribina', 'to' => 'lauribina-pass', 'dist' => 2.0, 'time' => 1.5, 'gain' => 230],
                ['from' => 'lauribina-pass', 'to' => 'ghopte-lauribina', 'dist' => 5.0, 'time' => 2.5, 'loss' => 1170],
                ['from' => 'ghopte-lauribina', 'to' => 'sundarijal-lauribina', 'dist' => 15.0, 'time' => 6.0, 'loss' => 2090],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Langtang National Park Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Lauribina Pass Trek seeded.');

        // ==========================================
        // 5. MANASLU CIRCUIT
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Manaslu Circuit Trek',
                'slug' => 'manaslu-circuit',
                'description' => 'Epic trek around Mount Manaslu (8163m) crossing the Larkya La Pass (5160m) with diverse cultural landscapes.',
                'difficulty' => 'hard',
                'duration_days' => 15,
                'max_altitude' => 5160,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Soti Khola', 'slug' => 'soti-khola', 'type' => 'village', 'lat' => 28.3456, 'lng' => 84.7223, 'alt' => 700],
                ['name' => 'Machhakhola', 'slug' => 'machhakhola', 'type' => 'village', 'lat' => 28.3812, 'lng' => 84.7345, 'alt' => 900],
                ['name' => 'Dobhan', 'slug' => 'dobhan', 'type' => 'village', 'lat' => 28.4123, 'lng' => 84.7567, 'alt' => 1070],
                ['name' => 'Bhulbhule', 'slug' => 'bhulbhule', 'type' => 'village', 'lat' => 28.4456, 'lng' => 84.7823, 'alt' => 1270],
                ['name' => 'Laprak', 'slug' => 'laprak', 'type' => 'village', 'lat' => 28.4789, 'lng' => 84.8123, 'alt' => 1800],
                ['name' => 'Ghap', 'slug' => 'ghap', 'type' => 'village', 'lat' => 28.5123, 'lng' => 84.8456, 'alt' => 2140],
                ['name' => 'Dharapani', 'slug' => 'dharapani-manaslu', 'type' => 'village', 'lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
                ['name' => 'Sama Gaon', 'slug' => 'sama-gaon', 'type' => 'village', 'lat' => 28.5345, 'lng' => 84.9823, 'alt' => 3520],
                ['name' => 'Samdo', 'slug' => 'samdo', 'type' => 'village', 'lat' => 28.5567, 'lng' => 85.0023, 'alt' => 3860],
                ['name' => 'Dharmashala', 'slug' => 'dharmashala', 'type' => 'village', 'lat' => 28.5891, 'lng' => 85.0123, 'alt' => 4460],
                ['name' => 'Larkya La', 'slug' => 'larkya-la', 'type' => 'pass', 'lat' => 28.6123, 'lng' => 85.0234, 'alt' => 5160],
                ['name' => 'Bimthang', 'slug' => 'bimthang', 'type' => 'village', 'lat' => 28.6123, 'lng' => 85.0489, 'alt' => 3720],
                ['name' => 'Dharapani', 'slug' => 'dharapani-manaslu-return', 'type' => 'village', 'lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
            ],
            'segments' => [
                ['from' => 'soti-khola', 'to' => 'machhakhola', 'dist' => 8.0, 'time' => 4.0, 'gain' => 200],
                ['from' => 'machhakhola', 'to' => 'dobhan', 'dist' => 6.0, 'time' => 3.0, 'gain' => 170],
                ['from' => 'dobhan', 'to' => 'bhulbhule', 'dist' => 7.0, 'time' => 3.5, 'gain' => 200],
                ['from' => 'bhulbhule', 'to' => 'laprak', 'dist' => 9.0, 'time' => 4.5, 'gain' => 530],
                ['from' => 'laprak', 'to' => 'ghap', 'dist' => 7.0, 'time' => 3.5, 'gain' => 340],
                ['from' => 'ghap', 'to' => 'dharapani-manaslu', 'dist' => 10.0, 'time' => 5.0, 'gain' => 680],
                ['from' => 'dharapani-manaslu', 'to' => 'sama-gaon', 'dist' => 12.0, 'time' => 6.0, 'gain' => 1660],
                ['from' => 'sama-gaon', 'to' => 'samdo', 'dist' => 5.0, 'time' => 3.0, 'gain' => 340],
                ['from' => 'samdo', 'to' => 'dharmashala', 'dist' => 6.0, 'time' => 3.5, 'gain' => 600],
                ['from' => 'dharmashala', 'to' => 'larkya-la', 'dist' => 4.0, 'time' => 3.0, 'gain' => 700],
                ['from' => 'larkya-la', 'to' => 'bimthang', 'dist' => 8.0, 'time' => 4.0, 'loss' => 1440],
                ['from' => 'bimthang', 'to' => 'dharapani-manaslu-return', 'dist' => 18.0, 'time' => 7.0, 'loss' => 1860],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Manaslu Restricted Area Permit', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Manaslu Conservation Area Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Manaslu Circuit seeded.');

        // ==========================================
        // 6. TSUM VALLEY
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Tsum Valley Trek',
                'slug' => 'tsum-valley',
                'description' => 'Remote valley trek with Tibetan Buddhist culture, ancient monasteries, and stunning mountain views.',
                'difficulty' => 'moderate',
                'duration_days' => 11,
                'max_altitude' => 3700,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Soti Khola', 'slug' => 'soti-tsum', 'type' => 'village', 'lat' => 28.3456, 'lng' => 84.7223, 'alt' => 700],
                ['name' => 'Machhakhola', 'slug' => 'machha-tsum', 'type' => 'village', 'lat' => 28.3812, 'lng' => 84.7345, 'alt' => 900],
                ['name' => 'Laprak', 'slug' => 'laprak-tsum', 'type' => 'village', 'lat' => 28.4789, 'lng' => 84.8123, 'alt' => 1800],
                ['name' => 'Gumbadanda', 'slug' => 'gumbadanda', 'type' => 'village', 'lat' => 28.5123, 'lng' => 84.8456, 'alt' => 2140],
                ['name' => 'Lokpa', 'slug' => 'lokpa', 'type' => 'village', 'lat' => 28.5234, 'lng' => 84.8934, 'alt' => 2180],
                ['name' => 'Chumling', 'slug' => 'chumling', 'type' => 'village', 'lat' => 28.5567, 'lng' => 84.9345, 'alt' => 2386],
                ['name' => 'Chhokang Paro', 'slug' => 'chhokang-paro', 'type' => 'village', 'lat' => 28.5891, 'lng' => 84.9567, 'alt' => 3010],
                ['name' => 'Nile', 'slug' => 'nile', 'type' => 'village', 'lat' => 28.6123, 'lng' => 84.9789, 'alt' => 3361],
                ['name' => 'Mu Gompa', 'slug' => 'mu-gompa', 'type' => 'landmark', 'lat' => 28.6345, 'lng' => 85.0123, 'alt' => 3700],
                ['name' => 'Dharapani', 'slug' => 'dharapani-tsum-return', 'type' => 'village', 'lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
            ],
            'segments' => [
                ['from' => 'soti-tsum', 'to' => 'machha-tsum', 'dist' => 8.0, 'time' => 4.0, 'gain' => 200],
                ['from' => 'machha-tsum', 'to' => 'laprak-tsum', 'dist' => 12.0, 'time' => 6.0, 'gain' => 900],
                ['from' => 'laprak-tsum', 'to' => 'gumbadanda', 'dist' => 8.0, 'time' => 4.0, 'gain' => 340],
                ['from' => 'gumbadanda', 'to' => 'lokpa', 'dist' => 4.0, 'time' => 2.0, 'gain' => 40],
                ['from' => 'lokpa', 'to' => 'chumling', 'dist' => 6.0, 'time' => 3.0, 'gain' => 206],
                ['from' => 'chumling', 'to' => 'chhokang-paro', 'dist' => 8.0, 'time' => 4.0, 'gain' => 624],
                ['from' => 'chhokang-paro', 'to' => 'nile', 'dist' => 5.0, 'time' => 2.5, 'gain' => 351],
                ['from' => 'nile', 'to' => 'mu-gompa', 'dist' => 6.0, 'time' => 3.0, 'gain' => 339],
                ['from' => 'mu-gompa', 'to' => 'dharapani-tsum-return', 'dist' => 28.0, 'time' => 10.0, 'loss' => 1840],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Manaslu Restricted Area Permit', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'Tsum Valley Special Permit', 'amount' => 50, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 30, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Tsum Valley seeded.');

        // ==========================================
        // 7. RUPINA LA PASS
        // ==========================================
        $this->helper->seedRoute([
            'route' => [
                'name' => 'Rupina La Pass Trek',
                'slug' => 'rupina-la',
                'description' => 'Challenging trek crossing the Rupina La pass connecting Manaslu and Annapurna regions.',
                'difficulty' => 'hard',
                'duration_days' => 13,
                'max_altitude' => 4640,
                'season' => 'Spring/Autumn',
            ],
            'waypoints' => [
                ['name' => 'Soti Khola', 'slug' => 'soti-rupina', 'type' => 'village', 'lat' => 28.3456, 'lng' => 84.7223, 'alt' => 700],
                ['name' => 'Dharapani', 'slug' => 'dharapani-rupina', 'type' => 'village', 'lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
                ['name' => 'Sama Gaon', 'slug' => 'sama-rupina', 'type' => 'village', 'lat' => 28.5345, 'lng' => 84.9823, 'alt' => 3520],
                ['name' => 'Samdo', 'slug' => 'samdo-rupina', 'type' => 'village', 'lat' => 28.5567, 'lng' => 85.0023, 'alt' => 3860],
                ['name' => 'Rupina La', 'slug' => 'rupina-la', 'type' => 'pass', 'lat' => 28.5789, 'lng' => 85.0345, 'alt' => 4640],
                ['name' => 'Nar Phu', 'slug' => 'nar-phu-rupina', 'type' => 'village', 'lat' => 28.6127, 'lng' => 84.2108, 'alt' => 4110],
                ['name' => 'Manang', 'slug' => 'manang-rupina', 'type' => 'village', 'lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
            ],
            'segments' => [
                ['from' => 'soti-rupina', 'to' => 'dharapani-rupina', 'dist' => 22.0, 'time' => 10.0, 'gain' => 1160],
                ['from' => 'dharapani-rupina', 'to' => 'sama-rupina', 'dist' => 10.0, 'time' => 5.0, 'gain' => 1660],
                ['from' => 'sama-rupina', 'to' => 'samdo-rupina', 'dist' => 5.0, 'time' => 3.0, 'gain' => 340],
                ['from' => 'samdo-rupina', 'to' => 'rupina-la', 'dist' => 7.0, 'time' => 4.0, 'gain' => 780],
                ['from' => 'rupina-la', 'to' => 'nar-phu-rupina', 'dist' => 12.0, 'time' => 6.0, 'loss' => 530],
                ['from' => 'nar-phu-rupina', 'to' => 'manang-rupina', 'dist' => 10.0, 'time' => 5.0, 'loss' => 570],
            ],
            'costs' => [
                ['type' => 'permit', 'name' => 'Manaslu Restricted Area Permit', 'amount' => 100, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'ACAP Permit', 'amount' => 30, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'permit', 'name' => 'TIMS Card', 'amount' => 20, 'unit' => 'per_person', 'mandatory' => true],
                ['type' => 'food_estimate', 'name' => 'Daily Food Budget', 'amount' => 35, 'unit' => 'per_day', 'mandatory' => false],
            ],
        ]);

        $this->command->info('✅ Rupina La Pass seeded.');

        $this->command->info('🎉 Langtang, Helambu & Manaslu Region Complete! 7 destinations seeded.');
    }
}
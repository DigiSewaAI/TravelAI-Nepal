<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WaypointLocationSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            // ============================================================
            // ANNAPURNA CIRCUIT TREK
            // ============================================================
            'Besisahar' => 'Besisahar',
            'Bahundanda' => 'Besisahar',
            'Chamche' => 'Chamche',
            'Dharapani' => 'Dharapani',
            'Chame' => 'Chame',
            'Pisang' => 'Pisang',
            'Manang' => 'Manang',
            'Yak Kharka' => 'Yak Kharka',
            'Thorong Phedi' => 'Thorong Phedi',
            'Thorong La' => 'Thorong La',
            'Muktinath' => 'Muktinath',
            'Jomsom' => 'Jomsom',
            'Tatopani' => 'Tatopani',
            'Ghorepani' => 'Ghorepani',
            'Nayapul' => 'Nayapul',
            'Birethanti' => 'Nayapul',

            // Annapurna Base Camp route
            'Tikhedhunga' => 'Tikhedhunga',
            'Ulleri' => 'Ulleri',
            'Ghandruk' => 'Ghandruk',
            'Tadapani' => 'Tadapani',
            'Chhomrong' => 'Chhomrong',
            'Sinuwa' => 'Sinuwa',
            'Bamboo' => 'Bamboo',
            'Dovan' => 'Dovan',
            'Himalaya' => 'Himalaya',
            'Deurali' => 'Deurali',
            'Machhapuchhre Base Camp' => 'Machhapuchhre Base Camp',
            'Annapurna Base Camp' => 'Annapurna Base Camp',

            // Mardi Himal route
            'Pothana' => 'Pothana',
            'Forest Camp' => 'Forest Camp',
            'Low Camp' => 'Low Camp',
            'High Camp' => 'High Camp',
            'Mardi Himal Base Camp' => 'Mardi Himal Base Camp',
            'Siding Village' => 'Siding Village',

            // ============================================================
            // EVEREST REGION
            // ============================================================
            'Lukla' => 'Lukla',
            'Phakding' => 'Phakding',
            'Namche Bazaar' => 'Namche',
            'Tengboche' => 'Tengboche',
            'Dingboche' => 'Dingboche',
            'Lobuche' => 'Lobuche',
            'Gorak Shep' => 'Gorak Shep',
            'Everest Base Camp' => 'Everest Base Camp',
            'Gokyo' => 'Gokyo',

            // ============================================================
            // LANGTANG REGION
            // ============================================================
            'Syabrubesi' => 'Syabrubesi',
            'Lama Hotel' => 'Lama Hotel',
            'Langtang Village' => 'Langtang',
            'Kyangjin Gompa' => 'Kyangjin Gompa',
            'Dhunche' => 'Dhunche',
            'Gosaikunda' => 'Gosaikunda',
            'Chandanbari' => 'Gosaikunda',
            'Sing Gompa' => 'Gosaikunda',
            'Ghopte' => 'Gosaikunda',
            'Chisapani' => 'Gosaikunda',
            'Sundarijal' => 'Gosaikunda',

            // ============================================================
            // MUSTANG / DOLPO
            // ============================================================
            'Kagbeni' => 'Kagbeni',
            'Marpha' => 'Marpha',
            'Lo Manthang' => 'Lo Manthang',
            'Phoksundo Lake' => 'Phoksundo Lake',
            'Shey Gompa' => 'Shey Gompa',

            // ============================================================
            // KANCHENJUNGA REGION
            // ============================================================
            'Suketar' => 'Suketar',
            'Kabeli' => 'Suketar',
            'Chirwa' => 'Suketar',
            'Sakathum' => 'Suketar',
            'Amjilosa' => 'Suketar',
            'Gyabla' => 'Suketar',
            'Ghunsa' => 'Ghunsa',
            'Kambachen' => 'Kanchenjunga Base Camp',
            'Lhonak' => 'Kanchenjunga Base Camp',
            'Kanchenjunga North Base Camp' => 'Kanchenjunga Base Camp',
            'Mamanke' => 'Suketar',
            'Yamphudin' => 'Suketar',
            'Torotong' => 'Suketar',
            'Lamite' => 'Suketar',
            'Cheram' => 'Suketar',
            'Ramche' => 'Suketar',
            'Kanchenjunga South Base Camp' => 'Kanchenjunga Base Camp',

            // ============================================================
            // MAKALU REGION
            // ============================================================
            'Tumlingtar' => 'Tumlingtar',
            'Chichila' => 'Tumlingtar',
            'Num' => 'Tumlingtar',
            'Sedua' => 'Tumlingtar',
            'Tashigaon' => 'Tumlingtar',
            'Kharkadanda' => 'Tumlingtar',
            'Mumbuk' => 'Tumlingtar',
            'Yangri Kharka' => 'Tumlingtar',
            'Makalu Base Camp' => 'Makalu Base Camp',

            // ============================================================
            // MANASLU REGION
            // ============================================================
            'Arughat' => 'Arughat',
            'Soti Khola' => 'Soti Khola',
            'Machha Khola' => 'Machha Khola',
            'Jagat' => 'Jagat',
            'Deng' => 'Deng',
            'Namrung' => 'Namrung',
            'Lho' => 'Lho',
            'Samagaon' => 'Samagaon',
            'Samdo' => 'Samdo',
            'Dharamsala' => 'Dharamsala',
            'Bimthang' => 'Bimthang',
            'Tilije' => 'Tilije',
            'Tal' => 'Tal',
            'Larkya La' => 'Larkya La',

            // ============================================================
            // API HIMAL REGION
            // ============================================================
            'Darchula' => 'Darchula',
            'Sitapur' => 'Sitapur',
            'Khalanga' => 'Khalanga',
            'Api Base Camp' => 'Api Base Camp',
            'Gokuleshwar' => 'Gokuleshwar',
            'Bitule' => 'Bitule',
            'Khandeshwari' => 'Khandeshwari',
            'Chiureni' => 'Chiureni',
            'Makarigaun' => 'Makarigaun',
            'Seti (Api)' => 'Seti (Api)',

            // ============================================================
            // CITIES / TOURS (Existing)
            // ============================================================
            'Kathmandu' => 'Kathmandu',
            'Pokhara' => 'Pokhara',
            'Lumbini' => 'Lumbini',
            'Janakpur' => 'Janakpur',
            'Chitwan' => 'Chitwan',
            'Gorkha' => 'Gorkha',
            'Bandipur' => 'Bandipur',
            'Tansen' => 'Tansen',
            'Dhulikhel' => 'Dhulikhel',
            'Panauti' => 'Panauti',
            'Kirtipur' => 'Kirtipur',
            'Sankhu' => 'Sankhu',
            'Khokana' => 'Khokana',
            'Bungamati' => 'Bungamati',
            'Nagarkot' => 'Nagarkot',
            'Bhaktapur' => 'Bhaktapur',
            'Patan' => 'Patan',

            // ============================================================
            // CITY TOURS - Start/End waypoints
            // ============================================================
            'Tansen Hill Town Tour Start' => 'Tansen',
            'Tansen Hill Town Tour End' => 'Tansen',
            'Palpa (Tansen, Rani Mahal) Tour Start' => 'Tansen',
            'Palpa (Tansen, Rani Mahal) Tour End' => 'Tansen',
            'Lumbini Buddhist Circuit Start' => 'Lumbini',
            'Lumbini Buddhist Circuit End' => 'Lumbini',
            'Lumbini Mayadevi Temple Pilgrimage Start' => 'Lumbini',
            'Lumbini Mayadevi Temple Pilgrimage End' => 'Lumbini',
            'Chitwan National Park Safari Start' => 'Chitwan',
            'Chitwan National Park Safari End' => 'Chitwan',

            // ============================================================
            // ADVENTURE ACTIVITIES
            // ============================================================
            'Trishuli River Rafting Start' => 'Trishuli River',
            'Trishuli River Rafting End' => 'Trishuli River',
            'Bhote Koshi River Rafting Start' => 'Bhote Koshi River',
            'Bhote Koshi River Rafting End' => 'Bhote Koshi River',
            'Kali Gandaki River Rafting Start' => 'Kali Gandaki River',
            'Kali Gandaki River Rafting End' => 'Kali Gandaki River',
            'Seti River Rafting Start' => 'Seti River',
            'Seti River Rafting End' => 'Seti River',
            'Sarangkot Paragliding Start' => 'Sarangkot',
            'Sarangkot Paragliding End' => 'Sarangkot',
            'Kusma Bridge Bungee Start' => 'Kusma Bridge',
            'Kusma Bridge Bungee End' => 'Kusma Bridge',
            'Trishuli River' => 'Trishuli River',
            'Bhote Koshi River' => 'Bhote Koshi River',
            'Kali Gandaki River' => 'Kali Gandaki River',
            'Seti River' => 'Seti River',
            'Sarangkot' => 'Sarangkot',
            'Kusma Bridge' => 'Kusma Bridge',
            'Paragliding in Pokhara Start' => 'Sarangkot',
            'Paragliding in Pokhara End' => 'Sarangkot',

            // ============================================================
            // RELIGIOUS SITES - Pathibhara
            // ============================================================
            'Pathibhara Temple' => 'Pathibhara',
            'Pathibhara Devi Temple' => 'Pathibhara',
            'Pathibhara' => 'Pathibhara',
            'Taplejung' => 'Taplejung',

            // ============================================================
            // KATHMANDU HERITAGE / CITY TOURS waypoints
            // ============================================================
            'Swayambhunath Stupa' => 'Kathmandu',
            'Boudhanath Stupa' => 'Kathmandu',
            'Pashupatinath Temple' => 'Kathmandu',
            'Kathmandu Durbar Square' => 'Kathmandu',
            'Thamel' => 'Kathmandu',
            'Asan Bazaar' => 'Kathmandu',
            'Garden of Dreams' => 'Kathmandu',
            'Patan Durbar Square' => 'Patan',
            'Bhaktapur Durbar Square' => 'Bhaktapur',

            // ============================================================
            // OTHER
            // ============================================================
            'Sindhuli' => 'Sindhuli',
            'Dhorpatan' => 'Dhorpatan',
            'Rara Lake' => 'Rara Lake',
            'Simikot' => 'Simikot',

            // ============================================================
            // DOLPO REGION (Batch 2)
            // ============================================================
            'Chhetra' => 'Chhetra',
            'Chaurikot' => 'Chaurikot',
            'Kagmara Phedi' => 'Kagmara Phedi',
            'Kagmara La' => 'Kagmara La',
            'Pungmo' => 'Pungmo',
            'Phoksundo Bhanjyang' => 'Phoksundo Bhanjyang',
            'Ringmo' => 'Ringmo',
            'Nisal' => 'Nisal',
            'Jeng La' => 'Jeng La',
            'Tokyu Gaon' => 'Tokyu Gaon',
            'Dho Tarap' => 'Dho Tarap',
            'Chharka' => 'Chharka',
            'Sangda La' => 'Sangda La',
            'Sangda Village' => 'Sangda Village',
            'Chhepka' => 'Chhepka',
            'Dunai' => 'Dunai',
            'Juphal' => 'Juphal',

            // Batch 3 — Makalu-Barun + Damodar Kunda
            'Langmale Kharka' => 'Langmale Kharka',
            'Barun Valley' => 'Barun Pokhari',
            'Yara' => 'Yara',
            'Ghara' => 'Ghara',
            'Bajhang' => 'Bajhang',
            'Bajura' => 'Bajura',
            'Jumla' => 'Jumla',
            'Dolakha' => 'Dolakha',
            'Rolwaling' => 'Rolwaling',
            'Bardiya' => 'Bardiya',
            'Kanchanpur' => 'Kanchanpur',

            // Batch 4 — Humla
            'Chhipra' => 'Chhipra',
            'Torpa' => 'Torpa',
            'Kermi' => 'Kermi',
            'Yalbang' => 'Yalbang',
            'Muchu' => 'Muchu',
            'Yari' => 'Yari',
            // Batch 4 — Rolwaling
            'Singati' => 'Singati',
            'Jagat' => 'Jagat',
            'Simigaon' => 'Simigaon',
            'Dongang' => 'Dongang',
                        // Batch 4 — Saipal
            'Kanda' => 'Kanda',
            'Nauli' => 'Nauli',

            // Phase 4Q9 — Activity waypoints location mapping
            'Nagarjun Forest' => 'Kathmandu',
            'Shivapuri National Park' => 'Kathmandu',
            'Fewa Lake' => 'Pokhara',
            'Bhote Koshi Bridge' => 'Bhote Koshi',
            'Zip-line Start Point' => 'Pokhara',
            'Sarangkot Zipline Start' => 'Pokhara',
            'Skydiving Drop Zone' => 'Pokhara',
            'Pame Drop Zone' => 'Pokhara',
            'Balloon Launch Site' => 'Pokhara',
            'Pokhara Balloon Launch' => 'Pokhara',
            'Hemja Landing' => 'Pokhara',
            'Sarangkot Launch Point' => 'Sarangkot',
            'Pokhara Landing Zone' => 'Pokhara',
            'Pokhara Landing' => 'Pokhara',
            'Pokhara Skydiving Landing' => 'Pokhara',
            'Balloon Max Altitude Point' => 'Pokhara',
            'Charaudi (Put-in)' => 'Trishuli River',
            'Fishling (Take-out)' => 'Trishuli River',
        ];

        $totalUpdated = 0;
        $totalNotFound = 0;
        $totalFallback = 0;

        $this->command->info('📍 Populating waypoint location_id...');

        // ============================================================
        // STEP 1: Map all waypoints to locations using $map
        // ============================================================
        foreach (Waypoint::all() as $waypoint) {
            $waypointName = trim($waypoint->name);
            $waypointSlug = trim($waypoint->slug ?? '');
            $locationName = null;

            // Exact match (name OR slug)
            foreach ($map as $key => $value) {
                if (
                    strtolower($waypointName) === strtolower($key) ||
                    strtolower($waypointSlug) === strtolower($key)
                ) {
                    $locationName = $value;
                    break;
                }
            }

            // Partial match (name only)
            if (!$locationName) {
                foreach ($map as $key => $value) {
                    if (stripos($waypointName, $key) !== false) {
                        $locationName = $value;
                        break;
                    }
                }
            }

            if ($locationName) {
                $location = Location::where('city', 'LIKE', "%{$locationName}%")->first();

                if ($location) {
                    $waypoint->location_id = $location->id;
                    $waypoint->save();
                    $totalUpdated++;
                } else {
                    Log::warning("❌ Location not found for: {$locationName} (Waypoint: {$waypoint->name})");
                    $totalNotFound++;
                }
            } else {
                Log::info("ℹ️ No mapping for waypoint: {$waypoint->name}");
                $totalNotFound++;
            }
        }

        // ============================================================
        // ✅ STEP 2: Set is_overnight_stop = false ONLY for specific waypoints
        // (Do NOT touch other waypoints – preserve manual fixes)
        // ============================================================
        $explicitNonOvernight = [
            // ─── High-altitude passes ───
            'Thorong La', 'Thorong La Pass',
            'Kang La', 'Larkya La', 'Cho La', 'Renjo La',
            'Kongma La', 'Mirgin La', 'Rupina La', 'French Col',
            'Lauribina Pass',

            // Dolpo passes
            'Damodar Kunda',
            'Kagmara La', 'Jeng La', 'Sangda La',

            // ─── Base camps without lodges ───
            'Everest Base Camp',
            'Kanchenjunga North Base Camp', 'Kanchenjunga South Base Camp',
            'Makalu Base Camp', 'Dhaulagiri Base Camp',
            'Mardi Himal Base Camp', 'Tilicho Base Camp',

                        // ─── Lakes (non-habitable) ───
            'Gosaikunda', 'Panch Pokhari',
            'Phoksundo Lake', 'Rara Lake',
            'Kataiya Lake', 'Sikta Lake', 'Tilicho Lake',
            'Damodar Kunda', 'Tso Rolpa', 'Kapuche Lake',

            // ─── Landmarks / monuments ───
            'Barahi Temple', 'Rani Mahal', 'Ranighat',
            'Pathibhara Temple', 'Pathibhara Devi Temple',
            'Swayambhunath Stupa', 'Boudhanath Stupa', 'Pashupatinath Temple',
            'Kathmandu Durbar Square', 'Patan Durbar Square', 'Bhaktapur Durbar Square',
            'Muktinath Temple', 'Janaki Temple', 'Mayadevi Temple',
            'Ashoka Pillar', 'Ram Sita Vivaha Mandap',
            'Chilancho Stupa', 'Bajrayogini Temple', 'Nyatapola Temple',
            'Pottery Square', 'Golden Temple', 'Doleshwar Mahadev',
            'Poon Hill',

            // ─── Viewpoints (no lodges) ───
            'Dhulikhel Viewpoint', 'Kakani Viewpoint',

            // ─── Rivers / bridges ───
            'Kali Gandaki River', 'Trishuli River',
            'Bhote Koshi River', 'Seti River', 'Kusma Bridge',

            // ─── Entrance gates / non-habitable landmarks ───
            'World Peace Pagoda', 'Gupteshwor Cave',
            'Kakrebihar', 'Aryaghat',
            'Gorkha Durbar', 'Gorkha Kalika Temple',
            'Chitwan National Park Entrance', 'Dhorpatan Entrance',
            'Panauti Durbar Square', 'Khokana Durbar Square',
            'Bungamati Temple', 'Sindhuli Fort', 'Dakshinkali Temple',
            'Chandragiri Temple', 'Changunarayan Temple',
            'Gorakhnath Temple', 'Baglung Kalika Temple',
        ];
        foreach ($explicitNonOvernight as $wpName) {
            $updated = Waypoint::where('name', $wpName)->update(['is_overnight_stop' => false]);
            if ($updated > 0) {
                $this->command->info("🚫 is_overnight_stop=false → {$wpName}");
            }
        }

        // ============================================================
        // 🆕 STEP 2.5 (Phase 4N): Type-based is_overnight_stop enforcement
        // ═══════════════════════════════════════════════════════════════
        // Phase 4A migration पछि नयाँ waypoints default false मा आए।
        // यो rule ले सधैं correct state राख्छ र STEP 2 को explicit
        // non-overnight लाई override गर्छ (exceptions को लागि)।
        // ============================================================
        $this->command->info('🔧 Phase 4N: Enforcing type-based overnight rules...');

        // Rule 1: सबै village/city → overnight = true
        $villageCount = Waypoint::whereIn('type', ['village', 'city'])
            ->update(['is_overnight_stop' => true]);
        $this->command->info("   ✅ Villages/cities overnight: {$villageCount}");

        // Rule 2: Non-village types → default false
        $nonVillageCount = Waypoint::whereIn('type', ['pass', 'peak', 'lake', 'landmark', 'viewpoint', 'checkpoint'])
            ->update(['is_overnight_stop' => false]);
        $this->command->info("   ✅ Non-village non-overnight: {$nonVillageCount}");

        // Rule 3: Exceptions — non-village waypoints WITH lodging
        // (यी STEP 2 ले false बनाएको भए पनि यहाँ true हुन्छन्)
                $explicitOvernightExceptions = [
    // Phase 4N original:
    'Annapurna Base Camp',
    'Sarangkot',
    'Bhedetar Viewpoint',
    'Phoksundo Lake',
    'Shey Gompa',
    'Mu Gompa',
    'Namobuddha',
    'Sundarijal',
    'Bandipur Bazaar',
    'Chitwan National Park',
    'Lumbini Garden',

    // Phase 4N.1c — checkpoint-type base camps:
    'Api Base Camp',
    'Makalu Base Camp',
    'Kyangjin Gompa',
    'Machhapuchhre Base Camp',
    'Kanchenjunga North Base Camp',
    'Kanchenjunga South Base Camp',

    // Phase 4N.5b1 — Bardiya safari exception:
    'Karnali River',

    // Phase 4N.5b1_fix — Makalu-Barun exception:
    'Barun Valley',

    // Phase 4N.5b2 — Saipal exception:
    'Saipal Base Camp',

        // Phase 4Q1g-2A — NationalParks lodges (Batch 4 fix):
    'Khaptad Lake',                    // lodge at lake
    'Khaptad National Park Entrance',  // checkpost + lodging
    'Dhorpatan Entrance',              // checkpost + basic lodge
    'Dhorpatan Lake',                  // basic lodge at lake

        // Phase 4Q1g-2B — Panchase treks (Batch 5 fix):
    'Panchase Bhanjyang',              // basic lodge at viewpoint
    'Panchase Lake',                   // basic lodge at lake

        // Phase 4Q1g-3 — Remote treks with lodges at landmark type:
    'Gosaikunda',                      // pilgrimage lodge at lake
    'Mohare Danda',                    // community lodge at viewpoint
    'Panch Pokhari',                   // pilgrimage lodge at five lakes
    'Rara Lake',                       // national park lodge

        // Phase 4Q1g-3 fix — trek structure has rest day here:
    'Kapuche Lake',                    // basic teahouse at lake
    'Tilicho Lake',                    // trek structure has rest day here

            // Phase 4Q1g-4 — Dhaulagiri BC exception:
    'Dhaulagiri Base Camp',            // real BC has tents/lodging

    // Phase 4Q8 — Kali Gandaki rafting camp:
    'Kali Gandaki River',              // overnight rafting camp (real 2-day trip)
];
        foreach ($explicitOvernightExceptions as $name) {
            Waypoint::where('name', $name)->update(['is_overnight_stop' => true]);
        }
        $this->command->info("   ✅ Exceptions applied: " . count($explicitOvernightExceptions));

        // ============================================================
        // 🔥 STEP 3: EXPLICIT FORCE SET for Annapurna Circuit waypoints
        // (Override both location_id and is_overnight_stop)
        // ============================================================
        $annapurnaWaypoints = [
            'Besisahar'    => ['location' => 'Besisahar',    'overnight' => true],
            'Bahundanda'   => ['location' => 'Besisahar',    'overnight' => true],
            'Chamche'      => ['location' => 'Chamche',      'overnight' => true],
            'Dharapani'    => ['location' => 'Dharapani',    'overnight' => true],
            'Chame'        => ['location' => 'Chame',        'overnight' => true],
            'Pisang'       => ['location' => 'Pisang',       'overnight' => true],
            'Manang'       => ['location' => 'Manang',       'overnight' => true],
            'Yak Kharka'   => ['location' => 'Yak Kharka',   'overnight' => true],
            'Thorong Phedi'=> ['location' => 'Thorong Phedi','overnight' => true],
            'Thorong La'   => ['location' => 'Thorong La',   'overnight' => false],
            'Muktinath'    => ['location' => 'Muktinath',    'overnight' => true],
            'Jomsom'       => ['location' => 'Jomsom',       'overnight' => true],
            'Tatopani'     => ['location' => 'Tatopani',     'overnight' => true],
            'Ghorepani'    => ['location' => 'Ghorepani',    'overnight' => true],
            'Nayapul'      => ['location' => 'Nayapul',      'overnight' => true],
        ];

        foreach ($annapurnaWaypoints as $name => $data) {
            $waypoint = Waypoint::where('name', $name)->first();
            if ($waypoint) {
                $location = Location::where('city', $data['location'])->first();
                if ($location) {
                    $waypoint->location_id = $location->id;
                    $waypoint->is_overnight_stop = $data['overnight'];
                    $waypoint->save();
                } else {
                    Log::warning("⚠️ Explicit set skipped: Location '{$data['location']}' not found for waypoint '{$name}'");
                }
            } else {
                Log::warning("⚠️ Explicit set skipped: Waypoint '{$name}' not found");
            }
        }

        // ============================================================
        // STEP 4: Verify counts
        // ============================================================
        $totalOvernight = Waypoint::where('is_overnight_stop', true)->count();
        $totalNonOvernight = Waypoint::where('is_overnight_stop', false)->count();

        $this->command->newLine();
        $this->command->info("✅ Waypoint Location Seeder Completed!");
        $this->command->info("   📌 Location mappings updated: {$totalUpdated}");
        $this->command->info("   ❌ Not Found / Skipped: {$totalNotFound}");
        $this->command->info("   🏨 Overnight stops: {$totalOvernight}");
        $this->command->info("   🚫 Non-overnight stops: {$totalNonOvernight}");
    }
}
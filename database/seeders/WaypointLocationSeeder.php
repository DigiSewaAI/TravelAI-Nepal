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

            // ============================================================
            // LANGTANG REGION
            // ============================================================
            'Syabrubesi' => 'Syabrubesi',
            'Lama Hotel' => 'Lama Hotel',
            'Langtang Village' => 'Langtang',
            'Kyangjin Gompa' => 'Kyangjin Gompa',

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
            'Barun Valley' => 'Barun Valley',

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
            // CITY TOURS - Start/End waypoints (Existing)
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

            // ============================================================
            // RELIGIOUS SITES - Pathibhara
            // ============================================================
            'Pathibhara Temple' => 'Pathibhara',
            'Pathibhara Devi Temple' => 'Pathibhara',
            'Pathibhara' => 'Pathibhara',
            'Suketar' => 'Suketar',
            'Taplejung' => 'Taplejung',

            // ============================================================
            // ✅ NEW: Kathmandu Heritage / City Tours waypoints
            // ============================================================
            // ============================================================
// ✅ FIX: Start/End slugs for Heritage & City Tours
// ============================================================
'kathmandu-heritage-start' => 'Kathmandu',
'kathmandu-heritage-end'   => 'Kathmandu',
'kathmandu-city-tour-start'=> 'Kathmandu',
'kathmandu-city-tour-end'  => 'Kathmandu',

// ✅ Durbar Square slugs (Heritage & City)
'kathmandu-durbar'        => 'Kathmandu',
'kathmandu-durbar-city'   => 'Kathmandu',
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
// ✅ FIX: City Tour start/end slugs
// ============================================================
'kathmandu-city-start' => 'Kathmandu',
'kathmandu-city-end'   => 'Kathmandu',

            // ============================================================
            // OTHER
            // ============================================================
            'Sindhuli' => 'Sindhuli',
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
            $locationName = null;

            // Exact match
            foreach ($map as $key => $value) {
                if (strtolower($waypointName) === strtolower($key)) {
                    $locationName = $value;
                    break;
                }
            }

            // Partial match
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
                    $this->command->info("✅ {$waypoint->name} → {$location->city}");
                } else {
                    // Fallback for some Annapurna waypoints if location not found
                    $fallbackLocation = Location::where('city', 'LIKE', "%Manang%")->first();
                    if ($fallbackLocation && in_array($waypointName, ['Yak Kharka', 'Thorong Phedi', 'Thorong La'])) {
                        $waypoint->location_id = $fallbackLocation->id;
                        $waypoint->save();
                        $totalFallback++;
                        Log::warning("⚠️ Fallback: {$waypoint->name} → Manang (location '{$locationName}' not found)");
                        $this->command->warn("⚠️ Fallback: {$waypoint->name} → Manang (please run LocationSeeder first)");
                    } else {
                        Log::warning("❌ Location not found for: {$locationName} (Waypoint: {$waypoint->name})");
                        $totalNotFound++;
                    }
                }
            } else {
                Log::info("ℹ️ No mapping for waypoint: {$waypoint->name}");
                $totalNotFound++;
            }
        }

        // ============================================================
        // STEP 2: Set is_overnight_stop for ALL waypoints
        // (Thorong La and EBC are explicitly false)
        // ============================================================
        $nonOvernightNames = ['Thorong La', 'Everest Base Camp'];

        foreach (Waypoint::all() as $waypoint) {
            $isOvernight = !in_array($waypoint->name, $nonOvernightNames);
            $waypoint->is_overnight_stop = $isOvernight;
            $waypoint->save();

            if (!$isOvernight) {
                $this->command->info("🚫 Set is_overnight_stop=false for: {$waypoint->name}");
            }
        }

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
                    $this->command->info("🔒 FORCE SET: {$name} → location_id={$location->id}, overnight=" . ($data['overnight'] ? 'true' : 'false'));
                } else {
                    Log::warning("⚠️ Explicit set skipped: Location '{$data['location']}' not found for waypoint '{$name}'");
                    $this->command->warn("⚠️ Explicit set skipped: Location '{$data['location']}' not found for waypoint '{$name}'");
                }
            } else {
                Log::warning("⚠️ Explicit set skipped: Waypoint '{$name}' not found");
                $this->command->warn("⚠️ Explicit set skipped: Waypoint '{$name}' not found");
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
        $this->command->info("   ⚠️  Fallback (Manang): {$totalFallback}");
        $this->command->info("   ❌ Not Found / Skipped: {$totalNotFound}");
        $this->command->info("   🏨 Overnight stops: {$totalOvernight}");
        $this->command->info("   🚫 Non-overnight stops: {$totalNonOvernight}");
        $this->command->info("   📌 Non-overnight waypoints: " . implode(', ', $nonOvernightNames));
    }
}
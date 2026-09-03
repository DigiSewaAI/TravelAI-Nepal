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
            // MANASLU REGION (ADDED)
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
            // CITIES / TOURS
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
// ADVENTURE ACTIVITIES - Waypoint to Location Mapping (Exact)
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
// Also map the generic location names (if any waypoint is just "Trishuli River")
'Trishuli River' => 'Trishuli River',
'Bhote Koshi River' => 'Bhote Koshi River',
'Kali Gandaki River' => 'Kali Gandaki River',
'Seti River' => 'Seti River',
'Sarangkot' => 'Sarangkot',
'Kusma Bridge' => 'Kusma Bridge',
        ];

        $totalUpdated = 0;
        $totalNotFound = 0;
        $totalFallback = 0;

        $this->command->info('📍 Populating waypoint location_id...');

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

        $this->command->newLine();
        $this->command->info("✅ Waypoint Location Seeder Completed:");
        $this->command->info("   📌 Updated: {$totalUpdated}");
        $this->command->info("   ⚠️  Fallback (Manang): {$totalFallback}");
        $this->command->info("   ❌ Not Found / Skipped: {$totalNotFound}");
    }
}
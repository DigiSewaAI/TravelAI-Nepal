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
        // ✅ VERIFIED MAPPING: Annapurna Circuit waypoints → actual locations.city
        // पहिले locations table मा यी cities छन् भनेर verify गरिएको छ
        $map = [
            // Annapurna Circuit (सबै waypoints को सही mapping)
            'Besisahar' => 'Besisahar',
            'Bahundanda' => 'Besisahar',      // Bahundanda को location Besisahar नै हो
            'Chamche' => 'Chamche',
            'Dharapani' => 'Dharapani',
            'Chame' => 'Chame',
            'Pisang' => 'Pisang',
            'Manang' => 'Manang',
            'Yak Kharka' => 'Manang',          // Yak Kharka Manang मा पर्छ
            'Thorong Phedi' => 'Manang',       // Thorong Phedi Manang मा
            'Thorong La' => 'Manang',          // Thorong La Manang मा
            'Muktinath' => 'Muktinath',
            'Jomsom' => 'Jomsom',
            'Tatopani' => 'Tatopani',
            'Ghorepani' => 'Ghorepani',
            'Nayapul' => 'Nayapul',
            'Birethanti' => 'Nayapul',         // Birethanti Nayapul नजिक
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
            // Everest Region
            'Lukla' => 'Lukla',
            'Phakding' => 'Phakding',
            'Namche Bazaar' => 'Namche',
            'Tengboche' => 'Tengboche',
            'Dingboche' => 'Dingboche',
            'Lobuche' => 'Lobuche',
            'Gorak Shep' => 'Gorak Shep',
            'Everest Base Camp' => 'Everest Base Camp',
            // Langtang
            'Syabrubesi' => 'Syabrubesi',
            'Lama Hotel' => 'Lama Hotel',
            'Langtang Village' => 'Langtang',
            'Kyangjin Gompa' => 'Kyangjin Gompa',
            // Mustang/Dolpo
            'Kagbeni' => 'Kagbeni',
            'Marpha' => 'Marpha',
            'Lo Manthang' => 'Lo Manthang',
            'Phoksundo Lake' => 'Phoksundo Lake',
            'Shey Gompa' => 'Shey Gompa',
            // Cities/Tours
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
            // Adventure Activities (काठमाडौं/पोखरा based)
            'Trishuli River' => 'Kathmandu',
            'Bhote Koshi River' => 'Kathmandu',
            'Kali Gandaki River' => 'Pokhara',
            'Seti River' => 'Pokhara',
            'Sarangkot' => 'Pokhara',
            'Kusma Bridge' => 'Pokhara',
        ];

        $totalUpdated = 0;
        $totalNotFound = 0;

        $this->command->info('📍 Populating waypoint location_id...');

        foreach (Waypoint::all() as $waypoint) {
            $locationName = null;

            // 1. Exact match (case-insensitive, trimmed)
            $waypointName = trim($waypoint->name);
            foreach ($map as $key => $value) {
                if (strtolower($waypointName) === strtolower($key)) {
                    $locationName = $value;
                    break;
                }
            }

            // 2. Partial match (यदि exact match नभए)
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
                    // $this->command->info("✅ Updated: {$waypoint->name} → {$location->city}");
                } else {
                    Log::warning("Location not found for city: {$locationName} (Waypoint: {$waypoint->name})");
                    $totalNotFound++;
                }
            } else {
                // No mapping found – log but don't set location_id
                Log::info("No mapping for waypoint: {$waypoint->name}");
                $totalNotFound++;
            }
        }

        $this->command->info("✅ Waypoint Location Seeder Completed:");
        $this->command->info("   - Updated: {$totalUpdated}");
        $this->command->info("   - Not Found / Skipped: {$totalNotFound}");
    }
}
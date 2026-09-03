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
        // ✅ Updated MAPPING: Annapurna Circuit waypoints → actual locations.city
        // Note: Yak Kharka, Thorong Phedi, Thorong La now have their own locations
        // (Make sure LocationSeeder has these cities before running this seeder)
        $map = [
            // ============================================================
            // ANNAPURNA CIRCUIT TREK (with correct location mapping)
            // ============================================================
            'Besisahar' => 'Besisahar',
            'Bahundanda' => 'Besisahar',      // Bahundanda → Besisahar area
            'Chamche' => 'Chamche',
            'Dharapani' => 'Dharapani',
            'Chame' => 'Chame',
            'Pisang' => 'Pisang',
            'Manang' => 'Manang',

            // ✅ NEW: Separate locations (not Manang)
            'Yak Kharka' => 'Yak Kharka',           // New location
            'Thorong Phedi' => 'Thorong Phedi',     // New location
            'Thorong La' => 'Thorong La',           // New location

            'Muktinath' => 'Muktinath',
            'Jomsom' => 'Jomsom',
            'Tatopani' => 'Tatopani',
            'Ghorepani' => 'Ghorepani',
            'Nayapul' => 'Nayapul',
            'Birethanti' => 'Nayapul',              // Birethanti → Nayapul area

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
            // ADVENTURE ACTIVITIES (Kathmandu / Pokhara based)
            // ============================================================
            'Trishuli River' => 'Kathmandu',
            'Bhote Koshi River' => 'Kathmandu',
            'Kali Gandaki River' => 'Pokhara',
            'Seti River' => 'Pokhara',
            'Sarangkot' => 'Pokhara',
            'Kusma Bridge' => 'Pokhara',
        ];

        $totalUpdated = 0;
        $totalNotFound = 0;
        $totalFallback = 0;

        $this->command->info('📍 Populating waypoint location_id...');

        foreach (Waypoint::all() as $waypoint) {
            $waypointName = trim($waypoint->name);
            $locationName = null;

            // 1. Exact match (case-insensitive)
            foreach ($map as $key => $value) {
                if (strtolower($waypointName) === strtolower($key)) {
                    $locationName = $value;
                    break;
                }
            }

            // 2. Partial match (if exact match not found)
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
                    // Fallback: try to find any location with similar name (for new locations)
                    $fallbackLocation = Location::where('city', 'LIKE', "%Manang%")->first();
                    if ($fallbackLocation && in_array($waypointName, ['Yak Kharka', 'Thorong Phedi', 'Thorong La'])) {
                        // Temporary fallback – but ideally LocationSeeder should have these cities
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

        if ($totalFallback > 0) {
            $this->command->warn("🔴 Please run LocationSeeder first to create: Yak Kharka, Thorong Phedi, Thorong La");
            $this->command->warn("   Then re-run this seeder for exact mapping.");
        }
    }
}
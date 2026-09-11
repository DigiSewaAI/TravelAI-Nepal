<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * PHASE 4B — Reordered DatabaseSeeder (dependency-safe)
     */
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════
        // PHASE 4B — Reordered DatabaseSeeder (dependency-safe)
        // ═══════════════════════════════════════════════════════

        // ─── 1. Base / reference data ───
        $this->call([
            ProviderTypeSeeder::class,
            ServiceCategorySeeder::class,
            PlanSeeder::class,

            // ─── 2. Locations (must exist before waypoint sync) ───
            LocationSeeder::class,
        ]);

        // ─── 3. Route seeders (create routes + waypoints + segments + costs) ───
        $this->call([
            AbcRouteSeeder::class,
            EbcRouteSeeder::class,
            LangtangRouteSeeder::class,
            AnnapurnaRegionSeeder::class,
            EverestRegionSeeder::class,
            LangtangHelambuManasluRegionSeeder::class,
            MustangDolpoRegionSeeder::class,
            KanchenjungaMakaluRegionSeeder::class,
            RemoteTreksSeeder::class,
            CityCulturalToursSeeder::class,
            NationalParksSeeder::class,
            ReligiousSitesSeeder::class,
            AdventureActivitiesSeeder::class,
            HiddenGemsSeeder::class,
        ]);

        // ─── 4. Waypoint ↔ Location sync ───
        //         (needs waypoints + locations to exist)
        $this->call([
            WaypointLocationSeeder::class,
            SyncMissingLocationsSeeder::class,
        ]);

        // ─── 5. Route category assignment ───
        //         (needs routes + service_categories)
        $this->call([
            AssignRouteCategoriesSeeder::class,
        ]);

        // ─── 6. Provider seeders per region ───
        //         (need locations + service_categories + users)
        $this->call([
            AnnapurnaProviderSeeder::class,
            EverestProviderSeeder::class,
            LangtangProviderSeeder::class,
            ManasluProviderSeeder::class,
            KanchenjungaMakaluProviderSeeder::class,
            MustangDolpoProviderSeeder::class,
            NationalParksProviderSeeder::class,
            ReligiousSitesProviderSeeder::class,
            HiddenGemsProviderSeeder::class,
            CityCulturalProviderSeeder::class,
            AdventureActivitiesProviderSeeder::class,
            RemoteTreksProviderSeeder::class,
        ]);

        // ─── 7. Base services + location assignment ───
        $this->call([
            ServiceSeeder::class,
            ServiceLocationSeeder::class,
        ]);

        // ─── 8. Tourism providers (with services, reviews) ───
        $this->call([
            TourismProvidersSeeder::class,
        ]);

        // ─── 9. Final assignments (need all above) ───
        $this->call([
            AssignProviderTypesSeeder::class,
        ]);

        // ─── 10. Testing data (dev only) ───
        // $this->call([TestingDataSeeder::class]); // ← uncomment if needed
    }
}
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

        // ─── 6-9. DEV-ONLY (test providers + services + tourism + assignments) ───
        // Production = fresh DB + curated demo (Hybrid strategy)
        if (!app()->environment('production')) {
            $this->call([
                // §6 — Provider Seeders (12)
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

                // §7 — Service Seeders
                ServiceSeeder::class,
                ServiceLocationSeeder::class,

                // §8 — Tourism Providers
                TourismProvidersSeeder::class,

                // §9 — Assign Provider Types (depends on §6)
                AssignProviderTypesSeeder::class,
            ]);
        }

        // ─── Always: production admin (idempotent, env-driven) ───
        $this->call(ProductionAdminSeeder::class);
                // ─── Always: production admin (idempotent, env-driven) ───
        $this->call(ProductionAdminSeeder::class);

        // ─── Real entities (migrated from local — idempotent) ───
        $this->call(RealEntitiesSeeder::class);

        // ─── Demo providers (1 per remaining category — idempotent) ───
        $this->call(DemoProvidersSeeder::class);

        // ─── Demo products (shop + rental + wholesale — idempotent) ───
        $this->call(DemoProductsSeeder::class);

        // ─── 10. Testing data (dev only) ───
        // $this->call([TestingDataSeeder::class]); // ← uncomment if needed
    }
}
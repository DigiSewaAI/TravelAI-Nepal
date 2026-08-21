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
     */
    public function run(): void
    {
        // Create a test user (for development)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Call all seeders in correct order
        $this->call([
            ProviderTypeSeeder::class,      // Phase 1: Provider types
            ServiceCategorySeeder::class,   // Phase 1: Service categories
            PlanSeeder::class,              // Phase 8: Subscription plans
            
            // ✅ Phase 3-4: Base route seeders (ABC, EBC, Langtang)
            AbcRouteSeeder::class,          // Annapurna Base Camp
            EbcRouteSeeder::class,          // Everest Base Camp
            LangtangRouteSeeder::class,     // Langtang Valley
            
            // ✅ Phase 4: Annapurna Region (12 destinations)
            AnnapurnaRegionSeeder::class,
            
            // ✅ Everest Region (6 destinations)
            EverestRegionSeeder::class,
            
            // ✅ Langtang, Helambu & Manaslu Region (7 destinations)
            LangtangHelambuManasluRegionSeeder::class,
            
            // ✅ Mustang & Dolpo Region (8 destinations)
            MustangDolpoRegionSeeder::class,
            
            // ✅ Kanchenjunga & Makalu Region
            KanchenjungaMakaluRegionSeeder::class,
            
            // ✅ Remote Treks (13 destinations)
            RemoteTreksSeeder::class,
            
            // ✅ NEW: City & Cultural Tours
            CityCulturalToursSeeder::class,
            
            // ✅ Demo providers with services, bookings & reviews
            TourismProvidersSeeder::class,
        ]);
    }
}
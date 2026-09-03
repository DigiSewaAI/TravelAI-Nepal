<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class LangtangProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Syabrubesi' => 25,
        'Lama Hotel' => 25,
        'Langtang' => 30,
        'Kyangjin Gompa' => 35,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'langtang-providers@travelai.com'],
            [
                'name' => 'Langtang Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info("✅ User ready: {$user->name} (ID: {$user->id})");

        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        if (!$hotelCat || !$guideCat || !$transportCat) {
            $this->command->error('❌ Service categories not found. Please run ServiceCategorySeeder first.');
            return;
        }

        $locationData = [
            'Syabrubesi' => ['lat' => 28.1579, 'lng' => 85.3378, 'alt' => 1500],
            'Lama Hotel' => ['lat' => 28.1678, 'lng' => 85.3782, 'alt' => 2470],
            'Langtang' => ['lat' => 28.2219, 'lng' => 85.5147, 'alt' => 3430],
            'Kyangjin Gompa' => ['lat' => 28.2567, 'lng' => 85.5234, 'alt' => 3870],
        ];

        $totalProviders = 0;
        $totalServices = 0;

        foreach ($locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("⚠️ Location not found: {$city}, skipping.");
                continue;
            }

            $midPrice = $this->priceMap[$city] ?? 30;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 Updating providers for {$city} (mid-price: \${$midPrice})...");

            // ============================================================
            // 1. BUDGET + BACKPACKER PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "langtang-{$city}-budget-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Budget accommodation at {$city} (Langtang Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'budget']
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'backpacker']
            );

            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-budget-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Basic lodge at {$city} (Langtang Region)",
                    'price' => $budgetPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-local-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Local Guide",
                    'description' => "Local guide at {$city} (Langtang Region)",
                    'price' => round($budgetPrice * 0.8, 0),
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices += 2;

            // ============================================================
            // 2. MID-RANGE PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "langtang-{$city}-mid-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Mid-range accommodation at {$city} (Langtang Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'mid_range']
            );

            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-mid-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Comfortable lodge at {$city} (Langtang Region)",
                    'price' => $midPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-certified-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Certified Guide",
                    'description' => "Certified guide at {$city} (Langtang Region)",
                    'price' => round($midPrice * 0.9, 0),
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices += 2;

            // ============================================================
            // 3. LUXURY PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "langtang-{$city}-luxury-resort"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Luxury accommodation at {$city} (Langtang Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'luxury']
            );

            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-luxury-resort"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Premium resort at {$city} (Langtang Region)",
                    'price' => $luxuryPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-expert-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Expert Guide",
                    'description' => "Expert guide at {$city} (Langtang Region)",
                    'price' => round($luxuryPrice * 0.6, 0),
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices += 2;

            // ============================================================
            // 4. TRANSPORT PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "langtang-{$city}-transport"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Transport",
                    'description' => "Local transport at {$city} (Langtang Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'mid_range']
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'budget']
            );

            Service::updateOrCreate(
                ['slug' => "langtang-{$city}-jeep"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $transportCat->id,
                    'name' => "{$city} Jeep Rental",
                    'description' => "Jeep rental at {$city} (Langtang Region)",
                    'price' => round($midPrice * 1.2, 0),
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ LangtangProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
        $this->command->info("   📌 Locations seeded: " . count($locationData));
        $this->command->info("   ✅ Location-based pricing applied!");
    }
}
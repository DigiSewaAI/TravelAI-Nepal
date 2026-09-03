<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class EverestProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Lukla' => 30,
        'Phakding' => 25,
        'Namche' => 45,
        'Tengboche' => 35,
        'Dingboche' => 35,
        'Lobuche' => 40,
        'Gorak Shep' => 50,
        'Everest Base Camp' => 55,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'everest-providers@travelai.com'],
            [
                'name' => 'Everest Provider System',
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
            'Lukla' => ['lat' => 27.6869, 'lng' => 86.7314, 'alt' => 2840],
            'Phakding' => ['lat' => 27.7432, 'lng' => 86.7123, 'alt' => 2610],
            'Namche' => ['lat' => 27.8042, 'lng' => 86.7106, 'alt' => 3440],
            'Tengboche' => ['lat' => 27.8361, 'lng' => 86.7643, 'alt' => 3860],
            'Dingboche' => ['lat' => 27.8927, 'lng' => 86.8242, 'alt' => 4410],
            'Lobuche' => ['lat' => 27.9358, 'lng' => 86.8087, 'alt' => 4940],
            'Gorak Shep' => ['lat' => 27.9812, 'lng' => 86.8274, 'alt' => 5140],
            'Everest Base Camp' => ['lat' => 28.0057, 'lng' => 86.8294, 'alt' => 5364],
        ];

        $totalProviders = 0;
        $totalServices = 0;

        foreach ($locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("⚠️ Location not found: {$city}, skipping.");
                continue;
            }

            $midPrice = $this->priceMap[$city] ?? 40;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 Updating providers for {$city} (mid-price: \${$midPrice})...");

            // ============================================================
            // 1. BUDGET + BACKPACKER PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "everest-{$city}-budget-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Budget accommodation at {$city} (Everest Region)",
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
                ['slug' => "everest-{$city}-budget-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Basic lodge at {$city} (Everest Region)",
                    'price' => $budgetPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "everest-{$city}-local-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Local Guide",
                    'description' => "Local guide at {$city} (Everest Region)",
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
                ['slug' => "everest-{$city}-mid-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Mid-range accommodation at {$city} (Everest Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'mid_range']
            );

            Service::updateOrCreate(
                ['slug' => "everest-{$city}-mid-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Comfortable lodge at {$city} (Everest Region)",
                    'price' => $midPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "everest-{$city}-certified-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Certified Guide",
                    'description' => "Certified guide at {$city} (Everest Region)",
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
                ['slug' => "everest-{$city}-luxury-resort"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Luxury accommodation at {$city} (Everest Region)",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'luxury']
            );

            Service::updateOrCreate(
                ['slug' => "everest-{$city}-luxury-resort"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Premium resort at {$city} (Everest Region)",
                    'price' => $luxuryPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "everest-{$city}-expert-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Expert Guide",
                    'description' => "Expert guide at {$city} (Everest Region)",
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
                ['slug' => "everest-{$city}-transport"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Transport",
                    'description' => "Local transport at {$city} (Everest Region)",
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
                ['slug' => "everest-{$city}-jeep"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $transportCat->id,
                    'name' => "{$city} Jeep Rental",
                    'description' => "Jeep rental at {$city} (Everest Region)",
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
        $this->command->info("✅ EverestProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
        $this->command->info("   📌 Locations seeded: " . count($locationData));
        $this->command->info("   ✅ Location-based pricing applied!");
    }
}
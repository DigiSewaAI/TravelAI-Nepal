<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnapurnaProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Besisahar' => 25,
        'Bahundanda' => 22,
        'Chamche' => 22,
        'Dharapani' => 28,
        'Chame' => 30,
        'Pisang' => 35,
        'Manang' => 40,
        'Yak Kharka' => 30,
        'Thorong Phedi' => 25,
        'Thorong La' => 50,
        'Muktinath' => 30,
        'Jomsom' => 28,
        'Tatopani' => 25,
        'Ghorepani' => 30,
        'Nayapul' => 20,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'annapurna-providers@travelai.com'],
            [
                'name' => 'Annapurna Provider System',
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
            'Besisahar' => ['lat' => 28.2398, 'lng' => 84.3824, 'alt' => 760],
            'Bahundanda' => ['lat' => 28.3312, 'lng' => 84.3601, 'alt' => 1310],
            'Chamche' => ['lat' => 28.4751, 'lng' => 84.3317, 'alt' => 1380],
            'Dharapani' => ['lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
            'Chame' => ['lat' => 28.5581, 'lng' => 84.3587, 'alt' => 2670],
            'Pisang' => ['lat' => 28.6194, 'lng' => 84.2027, 'alt' => 3200],
            'Manang' => ['lat' => 28.6664, 'lng' => 84.1248, 'alt' => 3540],
            'Yak Kharka' => ['lat' => 28.7123, 'lng' => 84.0877, 'alt' => 4010],
            'Thorong Phedi' => ['lat' => 28.7525, 'lng' => 84.0649, 'alt' => 4420],
            'Thorong La' => ['lat' => 28.7992, 'lng' => 84.0081, 'alt' => 5416],
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Jomsom' => ['lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            'Tatopani' => ['lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
            'Ghorepani' => ['lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
            'Nayapul' => ['lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
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
                ['slug' => "{$city}-budget-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Budget accommodation at {$city}",
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
                ['slug' => "{$city}-budget-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Basic lodge at {$city}",
                    'price' => $budgetPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "{$city}-local-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Local Guide",
                    'description' => "Local guide at {$city}",
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
                ['slug' => "{$city}-mid-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Mid-range accommodation at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'mid_range']
            );

            Service::updateOrCreate(
                ['slug' => "{$city}-mid-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Comfortable lodge at {$city}",
                    'price' => $midPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "{$city}-certified-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Certified Guide",
                    'description' => "Certified guide at {$city}",
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
                ['slug' => "{$city}-luxury-resort"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Luxury accommodation at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(
                ['provider_id' => $provider->id, 'style_slug' => 'luxury']
            );

            Service::updateOrCreate(
                ['slug' => "{$city}-luxury-resort"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Premium resort at {$city}",
                    'price' => $luxuryPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            Service::updateOrCreate(
                ['slug' => "{$city}-expert-guide"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $guideCat->id,
                    'name' => "{$city} Expert Guide",
                    'description' => "Expert guide at {$city}",
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
                ['slug' => "{$city}-transport"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Transport",
                    'description' => "Local transport at {$city}",
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
                ['slug' => "{$city}-jeep"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $transportCat->id,
                    'name' => "{$city} Jeep Rental",
                    'description' => "Jeep rental at {$city}",
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
        $this->command->info("✅ AnnapurnaProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
        $this->command->info("   ✅ Location-based pricing updated!");
    }
}
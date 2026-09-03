<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReligiousSitesProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Lumbini' => 25,
        'Janakpur' => 20,
        'Muktinath' => 30,
        'Manakamana' => 22,
        'Gorkha' => 20,
        'Patan' => 25,
        'Bhaktapur' => 25,
        'Kathmandu' => 35,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'religious-providers@travelai.com'],
            [
                'name' => 'Religious Sites Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info("✅ User ready: {$user->name} (ID: {$user->id})");

        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        if (!$hotelCat || !$guideCat || !$transportCat) {
            $this->command->error('❌ Service categories not found.');
            return;
        }

        $locationData = [
            'Lumbini' => ['lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
            'Janakpur' => ['lat' => 26.7234, 'lng' => 85.9234, 'alt' => 74],
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Manakamana' => ['lat' => 27.8234, 'lng' => 84.5678, 'alt' => 1300],
            'Gorkha' => ['lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1135],
            'Patan' => ['lat' => 27.6736, 'lng' => 85.3251, 'alt' => 1400],
            'Bhaktapur' => ['lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1401],
            'Kathmandu' => ['lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
        ];

        $totalProviders = 0;
        $totalServices = 0;

        foreach ($locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("⚠️ Location not found: {$city}, skipping.");
                continue;
            }

            $midPrice = $this->priceMap[$city] ?? 25;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 Updating providers for {$city} (mid-price: \${$midPrice})...");

            // Budget
            $provider = Provider::updateOrCreate(
                ['slug' => "rel-{$city}-budget-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget stay near {$city} religious site", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "rel-{$city}-budget-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget stay near {$city}", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "rel-{$city}-local-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Religious Guide", 'description' => "Local guide for religious sites at {$city}", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "rel-{$city}-mid-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable stay near {$city} religious site", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "rel-{$city}-mid-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable stay near {$city}", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "rel-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide for religious sites at {$city}", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "rel-{$city}-luxury-resort"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Resort", 'description' => "Luxury stay near {$city} religious site", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "rel-{$city}-luxury-resort"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Resort", 'description' => "Premium stay near {$city}", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "rel-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide for religious sites at {$city}", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "rel-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Transport", 'description' => "Local transport near {$city} religious site", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "rel-{$city}-car-rental"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Car Rental", 'description' => "Car rental near {$city}", 'price' => round($midPrice * 1.2, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ ReligiousSitesProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
    }
}
<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class NationalParksProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Chitwan' => 30,
        'Bardiya' => 25,
        'Sauraha' => 30,
        'Kanchanpur' => 20,
        'Dhorpatan' => 20,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'national-parks-providers@travelai.com'],
            [
                'name' => 'National Parks Provider System',
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
            'Chitwan' => ['lat' => 27.5789, 'lng' => 84.4567, 'alt' => 415],
            'Bardiya' => ['lat' => 28.3123, 'lng' => 81.4234, 'alt' => 200],
            'Sauraha' => ['lat' => 27.5789, 'lng' => 84.4567, 'alt' => 415],
            'Kanchanpur' => ['lat' => 28.8234, 'lng' => 80.4567, 'alt' => 150],
            'Dhorpatan' => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
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
                ['slug' => "np-{$city}-budget-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget stay near {$city} National Park", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "np-{$city}-budget-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget stay near {$city} National Park", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "np-{$city}-safari-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Safari Guide", 'description' => "Guide for jungle safari at {$city} National Park", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "np-{$city}-mid-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable stay near {$city} National Park", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "np-{$city}-mid-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable stay near {$city} National Park", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "np-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide for {$city} National Park", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "np-{$city}-luxury-resort"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Resort", 'description' => "Luxury stay near {$city} National Park", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "np-{$city}-luxury-resort"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Resort", 'description' => "Premium stay near {$city} National Park", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "np-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide for {$city} National Park", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport (Jeep Safari)
            $provider = Provider::updateOrCreate(
                ['slug' => "np-{$city}-jeep-safari"],
                ['user_id' => $user->id, 'name' => "{$city} Jeep Safari", 'description' => "Jeep safari at {$city} National Park", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "np-{$city}-jeep-safari"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Jeep Safari", 'description' => "Jeep safari at {$city} National Park", 'price' => round($midPrice * 1.5, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ NationalParksProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
    }
}
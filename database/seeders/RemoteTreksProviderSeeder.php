<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class RemoteTreksProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Simikot' => 25,
        'Rara Lake' => 25,
        'Jumla' => 20,
        'Bajhang' => 20,
        'Bajura' => 20,
        'Dhorpatan' => 20,
        'Rolwaling' => 25,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'remote-providers@travelai.com'],
            [
                'name' => 'Remote Treks Provider System',
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
            'Simikot' => ['lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2910],
            'Rara Lake' => ['lat' => 29.3789, 'lng' => 82.3891, 'alt' => 2990],
            'Jumla' => ['lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            'Bajhang' => ['lat' => 29.7123, 'lng' => 81.2345, 'alt' => 1720],
            'Bajura' => ['lat' => 29.6456, 'lng' => 81.4567, 'alt' => 1800],
            'Dhorpatan' => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
            'Rolwaling' => ['lat' => 27.6456, 'lng' => 86.2456, 'alt' => 2500],
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
                ['slug' => "remote-{$city}-budget-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget accommodation at {$city} (Remote)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "remote-{$city}-budget-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Lodge", 'description' => "Basic lodge at {$city}", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "remote-{$city}-local-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Local Guide", 'description' => "Local guide at {$city}", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "remote-{$city}-mid-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Mid-range accommodation at {$city} (Remote)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "remote-{$city}-mid-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable lodge at {$city}", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "remote-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide at {$city}", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "remote-{$city}-luxury-resort"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Resort", 'description' => "Luxury accommodation at {$city} (Remote)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "remote-{$city}-luxury-resort"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Resort", 'description' => "Premium resort at {$city}", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "remote-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide at {$city}", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "remote-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Transport", 'description' => "Local transport at {$city} (Remote)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "remote-{$city}-jeep"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Jeep Rental", 'description' => "Jeep rental at {$city}", 'price' => round($midPrice * 1.2, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ RemoteTreksProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
    }
}
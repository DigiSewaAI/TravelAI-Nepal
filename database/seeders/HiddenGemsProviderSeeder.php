<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class HiddenGemsProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Tansen' => 20,
        'Gorkha' => 20,
        'Dolakha' => 18,
        'Sindhuli' => 18,
        'Manakamana' => 22,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'hidden-gems-providers@travelai.com'],
            [
                'name' => 'Hidden Gems Provider System',
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
            'Tansen' => ['lat' => 27.8456, 'lng' => 83.5123, 'alt' => 1350],
            'Gorkha' => ['lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1135],
            'Dolakha' => ['lat' => 27.6123, 'lng' => 86.2234, 'alt' => 1900],
            'Sindhuli' => ['lat' => 27.2789, 'lng' => 85.9567, 'alt' => 600],
            'Manakamana' => ['lat' => 27.8234, 'lng' => 84.5678, 'alt' => 1300],
        ];

        $totalProviders = 0;
        $totalServices = 0;

        foreach ($locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("⚠️ Location not found: {$city}, skipping.");
                continue;
            }

            $midPrice = $this->priceMap[$city] ?? 20;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 Updating providers for {$city} (mid-price: \${$midPrice})...");

            // Budget + Backpacker
            $provider = Provider::updateOrCreate(
                ['slug' => "hg-{$city}-budget-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget accommodation at {$city} (Hidden Gem)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "hg-{$city}-budget-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Lodge", 'description' => "Basic lodge at {$city}", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "hg-{$city}-local-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Local Guide", 'description' => "Local guide at {$city}", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "hg-{$city}-mid-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Mid-range accommodation at {$city} (Hidden Gem)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "hg-{$city}-mid-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable lodge at {$city}", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "hg-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide at {$city}", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "hg-{$city}-luxury-resort"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Resort", 'description' => "Luxury accommodation at {$city} (Hidden Gem)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "hg-{$city}-luxury-resort"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Resort", 'description' => "Premium resort at {$city}", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "hg-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide at {$city}", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "hg-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Transport", 'description' => "Local transport at {$city} (Hidden Gem)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "hg-{$city}-jeep"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Jeep Rental", 'description' => "Jeep rental at {$city}", 'price' => round($midPrice * 1.2, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ HiddenGemsProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
        $this->command->info("   📌 Locations seeded: " . count($locationData));
    }
}
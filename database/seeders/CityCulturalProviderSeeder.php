<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class CityCulturalProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Kathmandu' => 35,
        'Pokhara' => 30,
        'Bhaktapur' => 25,
        'Patan' => 25,
        'Lumbini' => 25,
        'Janakpur' => 20,
        'Chitwan' => 30,
        'Gorkha' => 20,
        'Bandipur' => 20,
        'Dhulikhel' => 20,
        'Nagarkot' => 25,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'city-providers@travelai.com'],
            [
                'name' => 'City Cultural Provider System',
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
            'Kathmandu' => ['lat' => 27.7172, 'lng' => 85.3240, 'alt' => 1400],
            'Pokhara' => ['lat' => 28.2096, 'lng' => 83.9857, 'alt' => 822],
            'Bhaktapur' => ['lat' => 27.6722, 'lng' => 85.4295, 'alt' => 1401],
            'Patan' => ['lat' => 27.6736, 'lng' => 85.3251, 'alt' => 1400],
            'Lumbini' => ['lat' => 27.4689, 'lng' => 83.2767, 'alt' => 150],
            'Janakpur' => ['lat' => 26.7234, 'lng' => 85.9234, 'alt' => 74],
            'Chitwan' => ['lat' => 27.5789, 'lng' => 84.4567, 'alt' => 415],
            'Gorkha' => ['lat' => 28.0123, 'lng' => 84.6123, 'alt' => 1135],
            'Bandipur' => ['lat' => 27.9123, 'lng' => 84.4123, 'alt' => 1030],
            'Dhulikhel' => ['lat' => 27.6223, 'lng' => 85.5456, 'alt' => 1550],
            'Nagarkot' => ['lat' => 27.7145, 'lng' => 85.5234, 'alt' => 2175],
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
                ['slug' => "city-{$city}-budget-hotel"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Hotel", 'description' => "Budget hotel at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "city-{$city}-budget-hotel"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Hotel", 'description' => "Budget hotel at {$city}", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "city-{$city}-local-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Local Guide", 'description' => "Local guide at {$city}", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "city-{$city}-mid-hotel"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Hotel", 'description' => "Mid-range hotel at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "city-{$city}-mid-hotel"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Hotel", 'description' => "Comfortable hotel at {$city}", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "city-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide at {$city}", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "city-{$city}-luxury-hotel"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Hotel", 'description' => "Luxury hotel at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "city-{$city}-luxury-hotel"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Hotel", 'description' => "Premium hotel at {$city}", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "city-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide at {$city}", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "city-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Transport", 'description' => "Local transport at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "city-{$city}-car-rental"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Car Rental", 'description' => "Car rental at {$city}", 'price' => round($midPrice * 1.2, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-hotel \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ CityCulturalProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
    }
}
<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManasluProviderSeeder extends Seeder
{
    protected array $priceMap = [
        'Arughat' => 20,
        'Soti Khola' => 20,
        'Machha Khola' => 22,
        'Jagat' => 25,
        'Deng' => 25,
        'Namrung' => 28,
        'Lho' => 30,
        'Samagaon' => 35,
        'Samdo' => 35,
        'Dharamsala' => 30,
        'Bimthang' => 25,
        'Tilije' => 22,
        'Tal' => 20,
        'Dharapani' => 25,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'manaslu-providers@travelai.com'],
            [
                'name' => 'Manaslu Provider System',
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
            'Arughat' => ['lat' => 28.0456, 'lng' => 84.8123, 'alt' => 600],
            'Soti Khola' => ['lat' => 28.0789, 'lng' => 84.8345, 'alt' => 700],
            'Machha Khola' => ['lat' => 28.1123, 'lng' => 84.8567, 'alt' => 900],
            'Jagat' => ['lat' => 28.1456, 'lng' => 84.8789, 'alt' => 1350],
            'Deng' => ['lat' => 28.1789, 'lng' => 84.9012, 'alt' => 1800],
            'Namrung' => ['lat' => 28.2123, 'lng' => 84.9234, 'alt' => 2630],
            'Lho' => ['lat' => 28.2456, 'lng' => 84.9456, 'alt' => 3180],
            'Samagaon' => ['lat' => 28.2789, 'lng' => 84.9678, 'alt' => 3530],
            'Samdo' => ['lat' => 28.3123, 'lng' => 84.9901, 'alt' => 3870],
            'Dharamsala' => ['lat' => 28.3456, 'lng' => 85.0123, 'alt' => 4460],
            'Bimthang' => ['lat' => 28.3789, 'lng' => 84.6345, 'alt' => 3720],
            'Tilije' => ['lat' => 28.4123, 'lng' => 84.6567, 'alt' => 2300],
            'Tal' => ['lat' => 28.4456, 'lng' => 84.6789, 'alt' => 1700],
            'Dharapani' => ['lat' => 28.5289, 'lng' => 84.3545, 'alt' => 1860],
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

            // Budget + Backpacker
            $provider = Provider::updateOrCreate(
                ['slug' => "manaslu-{$city}-budget-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Budget Lodge", 'description' => "Budget accommodation at {$city} (Manaslu Region)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-budget-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Budget Lodge", 'description' => "Basic lodge at {$city} (Manaslu Region)", 'price' => $budgetPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-local-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Local Guide", 'description' => "Local guide at {$city} (Manaslu Region)", 'price' => round($budgetPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Mid-Range
            $provider = Provider::updateOrCreate(
                ['slug' => "manaslu-{$city}-mid-lodge"],
                ['user_id' => $user->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Mid-range accommodation at {$city} (Manaslu Region)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-mid-lodge"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Mid-Range Lodge", 'description' => "Comfortable lodge at {$city} (Manaslu Region)", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-certified-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Certified Guide", 'description' => "Certified guide at {$city} (Manaslu Region)", 'price' => round($midPrice * 0.9, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Luxury
            $provider = Provider::updateOrCreate(
                ['slug' => "manaslu-{$city}-luxury-resort"],
                ['user_id' => $user->id, 'name' => "{$city} Luxury Resort", 'description' => "Luxury accommodation at {$city} (Manaslu Region)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-luxury-resort"],
                ['provider_id' => $provider->id, 'service_category_id' => $hotelCat->id, 'name' => "{$city} Luxury Resort", 'description' => "Premium resort at {$city} (Manaslu Region)", 'price' => $luxuryPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-expert-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Expert Guide", 'description' => "Expert guide at {$city} (Manaslu Region)", 'price' => round($luxuryPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices += 2;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "manaslu-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Transport", 'description' => "Local transport at {$city} (Manaslu Region)", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::updateOrCreate(
                ['slug' => "manaslu-{$city}-jeep"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Jeep Rental", 'description' => "Jeep rental at {$city} (Manaslu Region)", 'price' => round($midPrice * 1.2, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: mid-lodge \${$midPrice}, budget \${$budgetPrice}, luxury \${$luxuryPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ ManasluProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
        $this->command->info("   📌 Locations seeded: " . count($locationData));
        $this->command->info("   ✅ Location-based pricing applied!");
    }
}
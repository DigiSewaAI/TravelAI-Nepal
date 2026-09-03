<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdventureActivitiesProviderSeeder extends Seeder
{
    protected array $priceMap = [
        // Rafting (Kathmandu based)
        'Trishuli River' => 35,
        'Bhote Koshi River' => 50,
        'Kali Gandaki River' => 45,
        'Seti River' => 30,
        // Paragliding & Adventure (Pokhara based)
        'Sarangkot' => 60,
        'Kusma Bridge' => 55,
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'adventure-providers@travelai.com'],
            [
                'name' => 'Adventure Activities Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info("✅ User ready: {$user->name} (ID: {$user->id})");

        $activityCat = ServiceCategory::where('slug', 'activity')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        if (!$activityCat || !$guideCat || !$transportCat) {
            $this->command->error('❌ Service categories not found.');
            return;
        }

        $locationData = [
            'Trishuli River' => ['lat' => 27.9123, 'lng' => 84.8123, 'alt' => 300],
            'Bhote Koshi River' => ['lat' => 27.8456, 'lng' => 85.9234, 'alt' => 500],
            'Kali Gandaki River' => ['lat' => 28.7345, 'lng' => 83.7123, 'alt' => 800],
            'Seti River' => ['lat' => 28.2096, 'lng' => 83.9857, 'alt' => 822],
            'Sarangkot' => ['lat' => 28.2456, 'lng' => 83.9456, 'alt' => 1592],
            'Kusma Bridge' => ['lat' => 28.2096, 'lng' => 83.9857, 'alt' => 822],
        ];

        $totalProviders = 0;
        $totalServices = 0;

        foreach ($locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("⚠️ Location not found: {$city}, skipping.");
                continue;
            }

            $midPrice = $this->priceMap[$city] ?? 35;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 Updating providers for {$city} (mid-price: \${$midPrice})...");

            // Activity Provider (Rafting/Paragliding/etc.)
            $provider = Provider::updateOrCreate(
                ['slug' => "adventure-{$city}-activity"],
                ['user_id' => $user->id, 'name' => "{$city} Adventure", 'description' => "Adventure activities at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "adventure-{$city}-activity"],
                ['provider_id' => $provider->id, 'service_category_id' => $activityCat->id, 'name' => "{$city} Adventure Activity", 'description' => "Adventure activity at {$city}", 'price' => $midPrice, 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            // Guide (for adventure activities)
            Service::updateOrCreate(
                ['slug' => "adventure-{$city}-guide"],
                ['provider_id' => $provider->id, 'service_category_id' => $guideCat->id, 'name' => "{$city} Adventure Guide", 'description' => "Expert guide for adventure activities at {$city}", 'price' => round($midPrice * 0.8, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalServices++;

            // Transport
            $provider = Provider::updateOrCreate(
                ['slug' => "adventure-{$city}-transport"],
                ['user_id' => $user->id, 'name' => "{$city} Adventure Transport", 'description' => "Transport for adventure activities at {$city}", 'verification_status' => 'verified', 'is_active' => true]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "adventure-{$city}-transport"],
                ['provider_id' => $provider->id, 'service_category_id' => $transportCat->id, 'name' => "{$city} Adventure Transport", 'description' => "Transport to adventure sites at {$city}", 'price' => round($midPrice * 0.6, 0), 'currency' => 'USD', 'status' => 'active', 'location_id' => $location->id]
            );
            $totalProviders++;
            $totalServices++;

            $this->command->info("   ✅ {$city}: activity \${$midPrice}");
        }

        $this->command->newLine();
        $this->command->info("✅ AdventureActivitiesProviderSeeder completed!");
        $this->command->info("   📌 Total Providers: {$totalProviders}");
        $this->command->info("   📌 Total Services: {$totalServices}");
    }
}
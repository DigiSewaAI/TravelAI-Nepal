<?php
// database/seeders/AnnapurnaProviderSeeder.php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use App\Models\Waypoint;
use Illuminate\Database\Seeder;

class AnnapurnaProviderSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or create a provider owner user
        $user = User::firstOrCreate(
            ['email' => 'annapurna-providers@travelai.com'],
            [
                'name' => 'Annapurna Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info("✅ User ready: {$user->name} (ID: {$user->id})");

        // 2. Get service categories
        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();
        $activityCat = ServiceCategory::where('slug', 'activity')->first();

        // 3. Define providers per location (overnight stops)
        // We'll use waypoints from Annapurna Circuit (route_id 5)
        $waypoints = Waypoint::whereHas('toSegments', function($q) {
            $q->where('route_id', 5);
        })->get()->keyBy('id');

        // For each waypoint, create providers and services
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
            'Muktinath' => ['lat' => 28.8177, 'lng' => 83.8849, 'alt' => 3800],
            'Jomsom' => ['lat' => 28.7850, 'lng' => 83.7312, 'alt' => 2700],
            'Tatopani' => ['lat' => 28.6533, 'lng' => 83.6365, 'alt' => 1190],
            'Ghorepani' => ['lat' => 28.4821, 'lng' => 83.7256, 'alt' => 2860],
            'Nayapul' => ['lat' => 28.3986, 'lng' => 83.7123, 'alt' => 1070],
        ];

        $styles = ['budget', 'mid_range', 'luxury', 'backpacker'];

        foreach ($locationData as $city => $coords) {
            // Get location model
            $location = Location::where('city', $city)->first();
            if (!$location) {
                $this->command->warn("Location not found: {$city}, skipping.");
                continue;
            }

            $this->command->info("📍 Seeding providers for {$city}...");

            // Create 3 providers per location: budget, mid, luxury
            foreach ($styles as $index => $style) {
                // Only create specific styles: we can make 3 providers: budget+backpacker, mid, luxury
                if ($style === 'budget' || $style === 'backpacker') {
                    // One provider for budget+backpacker combo
                    if ($style === 'budget') {
                        $providerName = "{$city} Budget Lodge";
                        $providerSlug = "{$city}-budget-lodge";
                        $provider = Provider::create([
                            'user_id' => $user->id,
                            'name' => $providerName,
                            'slug' => $providerSlug,
                            'description' => "Budget accommodation at {$city}",
                            'verification_status' => 'verified',
                            'is_active' => true,
                        ]);
                        // Assign styles: budget and backpacker
                        ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'budget']);
                        ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

                        // Create service: hotel (budget price)
                        Service::create([
                            'provider_id' => $provider->id,
                            'service_category_id' => $hotelCat->id,
                            'name' => "{$city} Budget Lodge",
                            'slug' => "{$city}-budget-lodge",
                            'description' => "Basic lodge at {$city}",
                            'price' => 15,
                            'currency' => 'USD',
                            'status' => 'active',
                            'location_id' => $location->id,
                        ]);
                        // Also create a guide service (budget)
                        Service::create([
                            'provider_id' => $provider->id,
                            'service_category_id' => $guideCat->id,
                            'name' => "{$city} Local Guide",
                            'slug' => "{$city}-local-guide",
                            'description' => "Local guide at {$city}",
                            'price' => 20,
                            'currency' => 'USD',
                            'status' => 'active',
                            'location_id' => $location->id,
                        ]);
                    }
                } elseif ($style === 'mid_range') {
                    $providerName = "{$city} Mid-Range Lodge";
                    $providerSlug = "{$city}-mid-lodge";
                    $provider = Provider::create([
                        'user_id' => $user->id,
                        'name' => $providerName,
                        'slug' => $providerSlug,
                        'description' => "Mid-range accommodation at {$city}",
                        'verification_status' => 'verified',
                        'is_active' => true,
                    ]);
                    ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

                    Service::create([
                        'provider_id' => $provider->id,
                        'service_category_id' => $hotelCat->id,
                        'name' => "{$city} Mid-Range Lodge",
                        'slug' => "{$city}-mid-lodge",
                        'description' => "Comfortable lodge at {$city}",
                        'price' => 40,
                        'currency' => 'USD',
                        'status' => 'active',
                        'location_id' => $location->id,
                    ]);
                    // Also a guide (mid)
                    Service::create([
                        'provider_id' => $provider->id,
                        'service_category_id' => $guideCat->id,
                        'name' => "{$city} Certified Guide",
                        'slug' => "{$city}-certified-guide",
                        'description' => "Certified guide at {$city}",
                        'price' => 35,
                        'currency' => 'USD',
                        'status' => 'active',
                        'location_id' => $location->id,
                    ]);
                } elseif ($style === 'luxury') {
                    $providerName = "{$city} Luxury Resort";
                    $providerSlug = "{$city}-luxury-resort";
                    $provider = Provider::create([
                        'user_id' => $user->id,
                        'name' => $providerName,
                        'slug' => $providerSlug,
                        'description' => "Luxury accommodation at {$city}",
                        'verification_status' => 'verified',
                        'is_active' => true,
                    ]);
                    ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

                    Service::create([
                        'provider_id' => $provider->id,
                        'service_category_id' => $hotelCat->id,
                        'name' => "{$city} Luxury Resort",
                        'slug' => "{$city}-luxury-resort",
                        'description' => "Premium resort at {$city}",
                        'price' => 100,
                        'currency' => 'USD',
                        'status' => 'active',
                        'location_id' => $location->id,
                    ]);
                    // Luxury guide
                    Service::create([
                        'provider_id' => $provider->id,
                        'service_category_id' => $guideCat->id,
                        'name' => "{$city} Expert Guide",
                        'slug' => "{$city}-expert-guide",
                        'description' => "Expert guide at {$city}",
                        'price' => 60,
                        'currency' => 'USD',
                        'status' => 'active',
                        'location_id' => $location->id,
                    ]);
                }
            }

            // Also add a transport provider for this location (mid-range style)
            $transportProvider = Provider::create([
                'user_id' => $user->id,
                'name' => "{$city} Transport",
                'slug' => "{$city}-transport",
                'description' => "Local transport at {$city}",
                'verification_status' => 'verified',
                'is_active' => true,
            ]);
            ProviderStyle::create(['provider_id' => $transportProvider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::create(['provider_id' => $transportProvider->id, 'style_slug' => 'budget']);

            Service::create([
                'provider_id' => $transportProvider->id,
                'service_category_id' => $transportCat->id,
                'name' => "{$city} Jeep Rental",
                'slug' => "{$city}-jeep",
                'description' => "Jeep rental at {$city}",
                'price' => 50,
                'currency' => 'USD',
                'status' => 'active',
                'location_id' => $location->id,
            ]);
        }

        $this->command->info("✅ AnnapurnaProviderSeeder completed.");
    }
}
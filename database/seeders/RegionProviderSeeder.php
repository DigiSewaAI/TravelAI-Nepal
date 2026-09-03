// database/seeders/RegionProviderSeeder.php (Template)
<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegionProviderSeeder extends Seeder
{
    protected array $locationData = [
        // Replace with region's overnight stops
        'Location1' => ['lat' => 0, 'lng' => 0, 'alt' => 0],
        'Location2' => ['lat' => 0, 'lng' => 0, 'alt' => 0],
        // ...
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'region-providers@travelai.com'],
            [
                'name' => 'Region Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );

        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        foreach ($this->locationData as $city => $coords) {
            $location = Location::where('city', $city)->first();
            if (!$location) continue;

            // 1. Budget + Backpacker Provider
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => "{$city} Budget Lodge",
                'slug' => "{$city}-budget-lodge",
                'description' => "Budget accommodation at {$city}",
                'verification_status' => 'verified',
                'is_active' => true,
            ]);
            ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

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

            // 2. Mid-Range Provider
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => "{$city} Mid-Range Lodge",
                'slug' => "{$city}-mid-lodge",
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

            // 3. Luxury Provider
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => "{$city} Luxury Resort",
                'slug' => "{$city}-luxury-resort",
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

            // 4. Transport Provider
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => "{$city} Transport",
                'slug' => "{$city}-transport",
                'description' => "Local transport at {$city}",
                'verification_status' => 'verified',
                'is_active' => true,
            ]);
            ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);
            ProviderStyle::create(['provider_id' => $provider->id, 'style_slug' => 'budget']);

            Service::create([
                'provider_id' => $provider->id,
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

        $this->command->info("✅ RegionProviderSeeder completed.");
    }
}
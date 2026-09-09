<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;

class RemoteTreksProviderSeeder extends BaseProviderSeeder
{
    protected function getProviderEmail(): string
    {
        return 'remote-providers@travelai.com';
    }

    protected function getProviderName(): string
    {
        return 'Remote Treks Provider System';
    }

    protected function getLocationData(): array
    {
        return [
            'Simikot'    => ['lat' => 29.9789, 'lng' => 82.0123, 'alt' => 2910],
            'Rara Lake'  => ['lat' => 29.3789, 'lng' => 82.3891, 'alt' => 2990],
            'Jumla'      => ['lat' => 29.2750, 'lng' => 82.1589, 'alt' => 2340],
            'Bajhang'    => ['lat' => 29.7123, 'lng' => 81.2345, 'alt' => 1720],
            'Bajura'     => ['lat' => 29.6456, 'lng' => 81.4567, 'alt' => 1800],
            'Dhorpatan'  => ['lat' => 28.4500, 'lng' => 83.0500, 'alt' => 2850],
            'Rolwaling'  => ['lat' => 27.6456, 'lng' => 86.2456, 'alt' => 2500],
            'Darchula'   => ['lat' => 29.8456, 'lng' => 80.5345, 'alt' => 700],
            'Sitapur'    => ['lat' => 29.8789, 'lng' => 80.5567, 'alt' => 900],
            'Khalanga'   => ['lat' => 29.9123, 'lng' => 80.5789, 'alt' => 1200],
            'Api Base Camp' => ['lat' => 30.0123, 'lng' => 80.6000, 'alt' => 4500],
        ];
    }

    protected function getPriceMap(): array
    {
        return [
            'Simikot'       => 25,
            'Rara Lake'     => 25,
            'Jumla'         => 20,
            'Bajhang'       => 20,
            'Bajura'        => 20,
            'Dhorpatan'     => 20,
            'Rolwaling'     => 25,
            'Darchula'      => 20,
            'Sitapur'       => 22,
            'Khalanga'      => 25,
            'Api Base Camp' => 30,
        ];
    }

    public function run(): void
    {
        // Parent seeder (creates locations, services from parent)
        parent::run();

        // Get a valid user (required for provider)
        $user = User::first();
        if (!$user) {
            return;
        }

        // Generate slug from provider name
        $slug = Str::slug($this->getProviderName());

        // Create or retrieve the provider with all required fields
        $provider = Provider::firstOrCreate(
            ['slug' => $slug],
            [
                'name'    => $this->getProviderName(),
                'user_id' => $user->id,
            ]
        );

        if (!$provider) {
            return;
        }

        // Activity category
        $activityCat = ServiceCategory::firstOrCreate(
            ['slug' => 'activity'],
            ['name' => 'Activity']
        );

        // Pokhara location
        $pokhara = Location::where('city', 'Pokhara')->first();
        if (!$pokhara) {
            return;
        }

        // Paragliding service
        Service::updateOrCreate(
            [
                'provider_id' => $provider->id,
                'slug'        => 'paragliding-pokhara',
            ],
            [
                'service_category_id' => $activityCat->id,
                'name'                => 'Paragliding Adventure',
                'description'         => 'Paragliding over Phewa Lake with views of Annapurna',
                'price'               => 80,
                'currency'            => 'USD',
                'status'              => 'active',
                'location_id'         => $pokhara->id,
            ]
        );
    }
}
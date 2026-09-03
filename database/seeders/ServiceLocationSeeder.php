<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Provider;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ServiceLocationSeeder extends Seeder
{
    /**
     * Explicit mapping: provider name → [ service name → location city ]
     * Only services with verified fixed operating locations.
     * Do NOT guess or use approximate locations.
     */
    protected array $mapping = [
        // Hotels
        'Kathmandu Grand Hotel' => [
            'Standard Room' => 'Kathmandu',
            'Executive Suite' => 'Kathmandu',
        ],
        'Hotel Himalayan View' => [
            'Deluxe Room with Mountain View' => 'Pokhara',
            'Suite Room' => 'Pokhara',
        ],
        'Pokhara Lakeside Resort' => [
            'Lake View Room' => 'Pokhara',
            'Villa with Private Pool' => 'Pokhara',
        ],
        // Activities (fixed location)
        'Adventure Nepal Activities' => [
            'Paragliding in Pokhara' => 'Pokhara',
            // Trishuli River Rafting is route-based → NOT mapped
        ],
        // Tours (city-specific)
        'Nepal Heritage Tours' => [
            'Kathmandu Valley Heritage Tour' => 'Kathmandu',
            // Lumbini & Pokhara Tour is multi-city → NOT mapped
        ],
        // Other fixed-location services can be added here.
    ];

    public function run(): void
    {
        $this->command->info('📍 Assigning locations to services (explicit mapping only)...');

        $updated = 0;
        $skipped = 0;
        $notFoundProvider = 0;
        $notFoundService = 0;
        $notFoundLocation = 0;

        foreach ($this->mapping as $providerName => $services) {
            $provider = Provider::where('name', $providerName)->first();

            if (!$provider) {
                $this->command->warn("Provider not found: {$providerName}");
                $notFoundProvider++;
                continue;
            }

            foreach ($services as $serviceName => $city) {
                $service = Service::where('provider_id', $provider->id)
                    ->where('name', $serviceName)
                    ->first();

                if (!$service) {
                    $this->command->warn("Service not found: {$serviceName} (provider: {$providerName})");
                    $notFoundService++;
                    continue;
                }

                $location = Location::where('city', $city)->first();

                if (!$location) {
                    $this->command->warn("Location not found: {$city} (for service: {$serviceName})");
                    $notFoundLocation++;
                    continue;
                }

                // Only update if currently NULL (avoid overwriting)
                if (is_null($service->location_id)) {
                    $service->location_id = $location->id;
                    $service->save();
                    $updated++;
                    $this->command->line("  ✅ {$serviceName} → {$city}");
                } else {
                    $this->command->line("  ⏩ {$serviceName} already has location_id: {$service->location_id}");
                    $skipped++;
                }
            }
        }

        $this->command->info("✅ ServiceLocationSeeder completed.");
        $this->command->info("   Updated: {$updated}");
        $this->command->info("   Skipped (already set): {$skipped}");
        $this->command->info("   Providers not found: {$notFoundProvider}");
        $this->command->info("   Services not found: {$notFoundService}");
        $this->command->info("   Locations not found: {$notFoundLocation}");
        $this->command->info("   ───");
        $this->command->info("   ⚠️ Route-based treks, guides, transport, and multi-location services remain NULL.");
        $this->command->info("   They will be excluded from location-based filtering.");
    }
}
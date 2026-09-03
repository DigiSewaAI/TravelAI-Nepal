<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderStyle;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

abstract class BaseProviderSeeder extends Seeder
{
    abstract protected function getLocationData(): array;
    abstract protected function getPriceMap(): array;
    abstract protected function getProviderEmail(): string;
    abstract protected function getProviderName(): string;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => $this->getProviderEmail()],
            [
                'name' => $this->getProviderName(),
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );
        $this->command->info("✅ User ready: {$user->name}");

        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        if (!$hotelCat || !$guideCat || !$transportCat) {
            $this->command->error('❌ Service categories not found. Please run ServiceCategorySeeder first.');
            return;
        }

        $locationData = $this->getLocationData();
        $priceMap = $this->getPriceMap();

        $totalProviders = 0;
        $totalServices = 0;
        $skipped = 0;

        foreach ($locationData as $city => $coords) {
            // ✅ PERMANENT FIX: ONLY habitable locations
            $location = Location::where('city', $city)
                ->where('is_habitable', true)
                ->first();

            if (!$location) {
                $this->command->warn("⚠️ Skipping non-habitable: {$city}");
                $skipped++;
                continue;
            }

            $midPrice = $priceMap[$city] ?? 40;
            $budgetPrice = round($midPrice * 0.55, 0);
            $luxuryPrice = round($midPrice * 2.5, 0);

            $this->command->info("📍 {$city} (mid: \${$midPrice})");

            // ============================================================
            // BUDGET PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "{$city}-budget-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Budget accommodation at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'budget']);
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'backpacker']);

            Service::updateOrCreate(
                ['slug' => "{$city}-budget-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Budget Lodge",
                    'description' => "Basic lodge at {$city}",
                    'price' => $budgetPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices++;

            // ============================================================
            // MID-RANGE PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "{$city}-mid-lodge"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Mid-range accommodation at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "{$city}-mid-lodge"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Mid-Range Lodge",
                    'description' => "Comfortable lodge at {$city}",
                    'price' => $midPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices++;

            // ============================================================
            // LUXURY PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "{$city}-luxury-resort"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Luxury accommodation at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'luxury']);

            Service::updateOrCreate(
                ['slug' => "{$city}-luxury-resort"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$city} Luxury Resort",
                    'description' => "Premium resort at {$city}",
                    'price' => $luxuryPrice,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices++;

            // ============================================================
            // TRANSPORT PROVIDER
            // ============================================================
            $provider = Provider::updateOrCreate(
                ['slug' => "{$city}-transport"],
                [
                    'user_id' => $user->id,
                    'name' => "{$city} Transport",
                    'description' => "Local transport at {$city}",
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]
            );
            ProviderStyle::firstOrCreate(['provider_id' => $provider->id, 'style_slug' => 'mid_range']);

            Service::updateOrCreate(
                ['slug' => "{$city}-jeep"],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $transportCat->id,
                    'name' => "{$city} Jeep Rental",
                    'description' => "Jeep rental at {$city}",
                    'price' => round($midPrice * 1.2, 0),
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]
            );
            $totalProviders++;
            $totalServices++;
        }

        $this->command->newLine();
        $this->command->info("✅ " . class_basename($this) . " completed!");
        $this->command->info("   📌 Providers: {$totalProviders}, Services: {$totalServices}");
        $this->command->info("   ⚠️  Skipped (non-habitable): {$skipped}");
    }
}
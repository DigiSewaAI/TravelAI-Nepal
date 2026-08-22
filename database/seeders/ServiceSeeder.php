<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get categories
        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        $transportCat = ServiceCategory::where('slug', 'transport')->first();

        // Get a provider (or create one)
        $provider = Provider::first();
        if (!$provider) {
            $provider = Provider::create([
                'name' => 'TravelAI Partner',
                'slug' => 'travelai-partner',
                'description' => 'Official TravelAI partner',
                'verification_status' => 'verified',
                'is_active' => true,
            ]);
        }

        // Hotels (Budget, Mid-Range, Luxury)
        $services = [
            // Budget Hotels
            ['name' => 'Budget Teahouse', 'category' => $hotelCat->id, 'price' => 10, 'currency' => 'USD'],
            ['name' => 'Simple Lodge', 'category' => $hotelCat->id, 'price' => 15, 'currency' => 'USD'],
            // Mid-Range Hotels
            ['name' => 'Comfort Lodge', 'category' => $hotelCat->id, 'price' => 30, 'currency' => 'USD'],
            ['name' => 'Standard Hotel', 'category' => $hotelCat->id, 'price' => 45, 'currency' => 'USD'],
            // Luxury Hotels
            ['name' => 'Premium Resort', 'category' => $hotelCat->id, 'price' => 80, 'currency' => 'USD'],
            ['name' => 'Luxury Hotel', 'category' => $hotelCat->id, 'price' => 120, 'currency' => 'USD'],

            // Guides
            ['name' => 'Local Guide (Basic)', 'category' => $guideCat->id, 'price' => 15, 'currency' => 'USD'],
            ['name' => 'Licensed Guide', 'category' => $guideCat->id, 'price' => 25, 'currency' => 'USD'],
            ['name' => 'Senior Guide', 'category' => $guideCat->id, 'price' => 40, 'currency' => 'USD'],

            // Transport
            ['name' => 'Local Bus', 'category' => $transportCat->id, 'price' => 5, 'currency' => 'USD'],
            ['name' => 'Private Jeep', 'category' => $transportCat->id, 'price' => 50, 'currency' => 'USD'],
            ['name' => 'Luxury Vehicle', 'category' => $transportCat->id, 'price' => 100, 'currency' => 'USD'],
        ];

        foreach ($services as $svc) {
            Service::updateOrCreate(
                ['name' => $svc['name']],
                [
                    'provider_id' => $provider->id,
                    'service_category_id' => $svc['category'],
                    'slug' => \Illuminate\Support\Str::slug($svc['name']) . '-' . uniqid(),
                    'price' => $svc['price'],
                    'currency' => $svc['currency'],
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('✅ Services seeded successfully!');
    }
}
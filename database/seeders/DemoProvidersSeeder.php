<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoProvidersSeeder extends Seeder
{
    public function run(): void
    {
        // PATH-3B B6 FIX: Use updated codes + emails (matches ProductionFixSeeder renames)
        // Idempotent: safe to re-run after ProductionFixSeeder has renamed providers
        // Prevents "demo.hotel@..." → "book@..." rename conflict on re-run
        $demos = [
            ['user_email' => 'book@himalayanviewhotel.com',    'user_name' => 'Bikash Thapa',    'prov_name' => 'Himalayan View Hotel',      'prov_code' => 'HVH', 'prov_slug' => 'himalayan-view-hotel',      'prov_desc' => 'Comfortable hotel in Kathmandu with mountain views.', 'cat_id' => 3, 'services' => [['name' => 'Standard Room', 'price' => 45, 'desc' => 'Cozy room with breakfast.'], ['name' => 'Deluxe Mountain View', 'price' => 85, 'desc' => 'Spacious room with Himalayan views.']]],
            ['user_email' => 'info@adventurenepalsports.com',  'user_name' => 'Pemba Sherpa',    'prov_name' => 'Adventure Nepal Sports',    'prov_code' => 'ANS', 'prov_slug' => 'adventure-nepal-sports',    'prov_desc' => 'Paragliding, rafting, and bungee adventures across Nepal.', 'cat_id' => 6, 'services' => [['name' => 'Pokhara Paragliding', 'price' => 120, 'desc' => '30-min tandem paragliding over Phewa Lake.'], ['name' => 'Trishuli River Rafting', 'price' => 60, 'desc' => 'Full-day white-water rafting.']]],
            ['user_email' => 'hello@nepalcultural.com',        'user_name' => 'Sunita Gurung',   'prov_name' => 'Nepal Cultural Experiences', 'prov_code' => 'NCE', 'prov_slug' => 'nepal-cultural-experiences', 'prov_desc' => 'Authentic Nepali cooking classes, homestays, and cultural immersions.', 'cat_id' => 7, 'services' => [['name' => 'Nepali Cooking Class', 'price' => 35, 'desc' => 'Learn to cook dal bhat and momo.'], ['name' => 'Village Homestay Experience', 'price' => 55, 'desc' => '2-day stay with a local family.']]],
            ['user_email' => 'book@pokharagrandlakeview.com',  'user_name' => 'Rajesh Adhikari', 'prov_name' => 'Pokhara Grand Lakeview',    'prov_code' => 'PGL', 'prov_slug' => 'pokhara-grand-lakeview',    'prov_desc' => 'Luxury lakeside resort with spa, pool, and mountain views.', 'cat_id' => 8, 'services' => [['name' => 'Garden View Suite', 'price' => 150, 'desc' => 'Suite with private garden and lake view.'], ['name' => 'Presidential Villa', 'price' => 320, 'desc' => 'Luxury villa with private pool.']]],
            ['user_email' => 'stay@gurungvillagehomestay.com', 'user_name' => 'Kamala Gurung',   'prov_name' => 'Gurung Village Homestay',   'prov_code' => 'GVH', 'prov_slug' => 'gurung-village-homestay',   'prov_desc' => 'Authentic Gurung village homestay in the Annapurna region.', 'cat_id' => 10, 'services' => [['name' => 'Traditional Gurung Homestay', 'price' => 30, 'desc' => 'Night with a Gurung family, meals included.'], ['name' => 'Village Trek Package', 'price' => 75, 'desc' => '3-day guided village trek with homestay.']]],
        ];

        foreach ($demos as $d) {
            $user = User::updateOrCreate(
                ['email' => $d['user_email']],
                [
                    'name'     => $d['user_name'],
                    'password' => Hash::make('Himalayan@1980'),
                    'role'     => 'provider_owner',
                ]
            );

            $prov = Provider::updateOrCreate(
                ['code' => $d['prov_code']],
                [
                    'user_id'             => $user->id,
                    'name'                => $d['prov_name'],
                    'slug'                => $d['prov_slug'],
                    'description'         => $d['prov_desc'],
                    'contact_email'       => $d['user_email'],
                    'verification_status' => 'verified',
                    'is_active'           => true,
                ]
            );

            foreach ($d['services'] as $i => $svc) {
                $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $svc['name']))
                        . '-' . $d['prov_code'] . '-' . ($i + 1);

                Service::updateOrCreate(
                    ['provider_id' => $prov->id, 'slug' => $slug],
                    [
                        'service_category_id' => $d['cat_id'],
                        'name'                => $svc['name'],
                        'description'         => $svc['desc'],
                        'price'               => $svc['price'],
                        'currency'            => 'USD',
                        'status'              => 'active',
                        'itinerary_status'    => 'draft',
                    ]
                );
            }
        }

        $this->command->info('✅ DemoProvidersSeeder: 5 providers, 10 services');
    }
}
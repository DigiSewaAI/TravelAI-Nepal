<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Ideal for startups and small businesses',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => ['Basic Dashboard', '3 Listings', '5 AI Requests/mo', '10 Bookings/mo'],
                'limits' => ['max_listings' => 3, 'max_staff' => 1, 'max_ai_requests' => 5, 'max_bookings' => 10],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing agencies and hotels',
                'price_monthly' => 4499,        // NPR
                'price_yearly' => 44999,        // NPR
                'features' => ['Advanced Dashboard', '20 Listings', '50 AI Requests/mo', '100 Bookings/mo', 'Custom Logo'],
                'limits' => ['max_listings' => 20, 'max_staff' => 5, 'max_ai_requests' => 50, 'max_bookings' => 100],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For large operators and chains',
                'price_monthly' => 11999,       // NPR
                'price_yearly' => 119999,       // NPR
                'features' => ['Full Analytics', '100 Listings', '500 AI Requests/mo', '1000 Bookings/mo', 'White-label'],
                'limits' => ['max_listings' => 100, 'max_staff' => 20, 'max_ai_requests' => 500, 'max_bookings' => 1000],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large enterprises',
                'price_monthly' => null,
                'price_yearly' => null,
                'features' => ['Unlimited Listings', 'Unlimited Staff', 'Unlimited AI', 'Unlimited Bookings', 'Priority Support'],
                'limits' => ['max_listings' => -1, 'max_staff' => -1, 'max_ai_requests' => -1, 'max_bookings' => -1],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
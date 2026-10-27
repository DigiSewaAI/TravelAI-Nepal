<?php

namespace Database\Seeders;

use App\Models\IndividualBooking;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class IndividualSeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))->get();
        if ($providers->isEmpty()) { $this->command->warn('No individual providers found.'); return; }

        foreach ($providers as $provider) {
            // Update Provider Stats
            $provider->update([
                'profile_views' => rand(500, 2500),
                'completed_jobs' => rand(50, 300),
                'repeat_clients_pct' => rand(40, 80),
                'experience_years' => rand(3, 15),
            ]);

            // Create Bookings
            $bookings = [
                ['guide', 'EBC Trek — 12 days', 'Emily Watson', 2, 1, '12 days', 85000, 'confirmed'],
                ['photographer', 'Pre-wedding Shoot', 'Arjun & Sneha', 2, 3, '2 days', 42000, 'confirmed'],
                ['porter', 'Annapurna Base Camp', 'Rohan Kapoor', 3, 5, '8 days', 24000, 'confirmed'],
                ['guide', 'Kathmandu Heritage Walk', 'Maria Garcia', 4, 8, '1 day', 6500, 'pending'],
                ['photographer', 'Product Shoot — Thamel', 'Daniel Lee', 1, 10, '3 hours', 15000, 'pending'],
                ['porter', 'Manaslu Circuit', 'Kiran Adhikari', 2, 14, '14 days', 38000, 'confirmed'],
                ['guide', 'Langtang Valley Trek', 'Priya Nair', 2, 20, '9 days', 72000, 'pending'],
            ];

            foreach ($bookings as $b) {
                IndividualBooking::create([
                    'provider_id' => $provider->id,
                    'service_type' => $b[0],
                    'title' => $b[1],
                    'client_name' => $b[2],
                    'pax' => $b[3],
                    'start_date' => now()->addDays($b[4]),
                    'duration' => $b[5],
                    'amount' => $b[6],
                    'status' => $b[7],
                ]);
            }
        }

        $this->command->info("Individual Seeder: " . $providers->count() . " providers seeded with stats & bookings.");
    }
}
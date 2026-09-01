<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Models\QrScan;
use App\Models\Waypoint;
use App\Models\UserMedia;
use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestingDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Seeding Testing Data for John Adreson...');

        // 1. Get user: John Adreson (ID: 3)
        $user = User::where('email', 'shresthaxok@gmail.com')->first();
        if (!$user) {
            $this->command->error('❌ User John Adreson not found. Run UserSeeder first.');
            return;
        }
        $this->command->info("✅ User found: {$user->name} (ID: {$user->id})");

        // 2. Get or create a service (trek)
        $service = Service::where('name', 'like', '%Annapurna Base Camp%')->first();
        if (!$service) {
            $service = Service::create([
                'provider_id' => \App\Models\Provider::first()->id ?? 1,
                'service_category_id' => \App\Models\ServiceCategory::where('slug', 'trek')->first()->id ?? 1,
                'name' => 'Annapurna Base Camp Trek (Test)',
                'slug' => 'annapurna-base-camp-test-' . Str::random(6),
                'description' => 'Classic ABC trek for testing purposes.',
                'price' => 750,
                'currency' => 'USD',
                'status' => 'active',
            ]);
            $this->command->info("✅ Created test service: {$service->name}");
        } else {
            $this->command->info("✅ Using existing service: {$service->name}");
        }

        // 3. Create a booking for John
        $booking = Booking::where('traveler_id', $user->id)
            ->where('service_id', $service->id)
            ->first();

        if (!$booking) {
            $booking = Booking::create([
                'traveler_id' => $user->id,
                'service_id' => $service->id,
                'status' => 'completed',
                'start_date' => now()->subDays(15),
                'booking_date' => now()->subDays(20),
                'qr_code' => Str::random(40),
            ]);
            $this->command->info("✅ Created booking #{$booking->id} for {$user->name}");
        } else {
            $this->command->info("⏩ Booking already exists: #{$booking->id}");
        }

        // 4. Get waypoints from ABC route (or any route)
        $route = Route::where('slug', 'annapurna-base-camp')->first();
        if (!$route) {
            $this->command->warn('⚠️ Annapurna Base Camp route not found; using any waypoint.');
            $waypoints = Waypoint::limit(5)->get();
        } else {
            $waypoints = $route->segments->flatMap(function ($segment) {
                return [$segment->fromWaypoint, $segment->toWaypoint];
            })->unique('id')->filter();
            $this->command->info("✅ Found " . $waypoints->count() . " waypoints from ABC route.");
        }

        if ($waypoints->isEmpty()) {
            $this->command->error('❌ No waypoints found. Please seed routes first.');
            return;
        }

        // 5. Create QR scans for some waypoints
        $qrScansCreated = 0;
        foreach ($waypoints->take(5) as $index => $wp) {
            $scan = QrScan::where('booking_id', $booking->id)
                ->where('waypoint_id', $wp->id)
                ->first();

            if (!$scan) {
                QrScan::create([
                    'booking_id' => $booking->id,
                    'waypoint_id' => $wp->id,
                    'scanned_at' => now()->subDays(15 - $index),
                    'latitude' => $wp->latitude,
                    'longitude' => $wp->longitude,
                    'checkpoint_name' => $wp->name,
                    'duplicate_of' => null,
                ]);
                $qrScansCreated++;
            }
        }
        $this->command->info("✅ Created {$qrScansCreated} QR scans for booking.");

        // 6. Create user media (memories) – FIXED source column
        $mediaCreated = 0;
        $mediaTypes = ['image', 'image', 'image'];
        foreach ($waypoints->take(3) as $index => $wp) {
            $exists = UserMedia::where('user_id', $user->id)
                ->where('waypoint_id', $wp->id)
                ->where('booking_id', $booking->id)
                ->exists();

            if (!$exists) {
                UserMedia::create([
                    'user_id' => $user->id,
                    'waypoint_id' => $wp->id,
                    'booking_id' => $booking->id,
                    'qr_scan_id' => null,
                    'media_type' => $mediaTypes[$index % count($mediaTypes)],
                    'file_name' => 'test-' . Str::random(6) . '.jpg',
                    'optimized_path' => 'uploads/test/' . Str::random(10) . '.jpg',
                    'thumbnail_path' => 'uploads/test/thumb/' . Str::random(10) . '.jpg',
                    'metadata' => json_encode(['caption' => 'Test memory at ' . $wp->name]),
                    'captured_at' => now()->subDays(15 - $index),
                    'is_primary' => $index === 0,
                    'source' => 'user', // ✅ Fixed: use 'user' instead of 'user_upload'
                ]);
                $mediaCreated++;
            }
        }
        $this->command->info("✅ Created {$mediaCreated} user media entries.");

        // 7. Summary
        $this->command->info('🎉 Testing data seeding complete!');
        $this->command->info("📊 Booking: #{$booking->id}");
        $this->command->info("📊 QR Scans: {$qrScansCreated}");
        $this->command->info("📊 Media: {$mediaCreated}");
        $this->command->info('🔑 Login: shresthaxok@gmail.com / Himalayan@1980');
        $this->command->info('📸 Check Traveler Dashboard → My Journey Replay & My Travel Memories');
    }
}
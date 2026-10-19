<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Booking;
use App\Models\QrScan;
use App\Models\User;
use App\Models\Waypoint;
use App\Services\Passport\AchievementService;
use Illuminate\Database\Seeder;

class DemoPassportSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: only for demo user (R10 — no fake data for real users)
        $john = User::where('email', 'shresthaxok@gmail.com')->first();
        if (!$john) {
            $this->command->warn('⚠️ Demo user (shresthaxok@gmail.com) not found — skipping');
            return;
        }

        // Idempotency guard: skip if already seeded
        $existingScans = QrScan::whereHas('booking', fn($q) => $q->where('traveler_id', $john->id))->count();
        if ($existingScans >= 10) {
            $this->command->info("✅ Demo passport already seeded ({$existingScans} scans) — skipping");
            return;
        }

        $this->command->info("Seeding demo passport for: {$john->name}");

        // ============================================================
        // 1. Add QrScans across John's existing bookings
        // ============================================================
        $bookings = Booking::where('traveler_id', $john->id)->get()->keyBy('id');
        if ($bookings->isEmpty()) {
            $this->command->warn('⚠️ No bookings found for John');
            return;
        }

        $bookingIds = $bookings->keys()->toArray();
        $this->command->info("Found bookings: " . implode(', ', $bookingIds));

        // Route map: booking index → waypoint IDs
        // Booking 1 (ABC) → waypoints 2-14 (complete route)
        // Booking 2 (EBC) → waypoints 16-22 (in-progress)
        // Booking 3 (Pokhara) → waypoint 2 (transfer start)

        $scanPlan = [];
        $bookingArray = $bookings->values();

        if (isset($bookingArray[0])) {
            // ABC Trek: full route (skip waypoints 1 and 5 which John already has)
            $abcWaypoints = [2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14];
            foreach ($abcWaypoints as $idx => $wpId) {
                $scanPlan[] = [
                    'booking_id' => $bookingArray[0]->id,
                    'waypoint_id' => $wpId,
                    'days_ago' => 60 - ($idx * 3),
                ];
            }
        }

        if (isset($bookingArray[1])) {
            // EBC Trek: partial (in progress)
            $ebcWaypoints = [16, 17, 18, 19, 20, 21, 22];
            foreach ($ebcWaypoints as $idx => $wpId) {
                $scanPlan[] = [
                    'booking_id' => $bookingArray[1]->id,
                    'waypoint_id' => $wpId,
                    'days_ago' => 30 - ($idx * 2),
                ];
            }
        }

        if (isset($bookingArray[2])) {
            // Pokhara transfer: 1 checkpoint
            $scanPlan[] = [
                'booking_id' => $bookingArray[2]->id,
                'waypoint_id' => 2,
                'days_ago' => 20,
            ];
        }

        // ============================================================
        // 2. Create QrScans (idempotent per waypoint+booking)
        // ============================================================
        $created = 0;
        foreach ($scanPlan as $plan) {
            $waypoint = Waypoint::find($plan['waypoint_id']);
            if (!$waypoint) {
                continue;
            }

            $exists = QrScan::where('booking_id', $plan['booking_id'])
                ->where('waypoint_id', $plan['waypoint_id'])
                ->exists();
            if ($exists) {
                continue;
            }

            QrScan::create([
                'booking_id' => $plan['booking_id'],
                'waypoint_id' => $plan['waypoint_id'],
                'checkpoint_name' => $waypoint->name,
                'scanned_at' => now()->subDays($plan['days_ago']),
                'latitude' => $waypoint->latitude,
                'longitude' => $waypoint->longitude,
                'verification_status' => 'verified',
            ]);
            $created++;
        }

        $this->command->info("✅ Created {$created} QrScans");

        // ============================================================
        // 3. Auto-unlock achievements via service
        // ============================================================
        $achievementService = app(AchievementService::class);
        $unlocked = $achievementService->evaluateAndUnlock($john);
        $this->command->info("✅ Unlocked " . count($unlocked) . " achievements");

        // ============================================================
        // 4. Summary
        // ============================================================
        $scanCount = QrScan::whereHas('booking', fn($q) => $q->where('traveler_id', $john->id))->count();
        $achCount = \DB::table('user_achievements')->where('user_id', $john->id)->count();
        $xp = app(\App\Services\Passport\DigitalTrekPassportService::class)->getTotalXP($john);
        $level = app(\App\Services\Passport\DigitalTrekPassportService::class)->calculateLevel($john);

        $this->command->info("─────────────────────────────────────");
        $this->command->info("📊 Final demo state for {$john->name}:");
        $this->command->info("   Total check-ins: {$scanCount}");
        $this->command->info("   Achievements: {$achCount}");
        $this->command->info("   XP: {$xp}");
        $this->command->info("   Level: {$level}");
        $this->command->info("─────────────────────────────────────");
    }
}
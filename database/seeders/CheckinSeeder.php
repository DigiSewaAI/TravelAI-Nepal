<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\QrScan;
use App\Models\Waypoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent — skip if data exists
        if (DB::table('qr_scans')->count() > 0) {
            $this->command->info('✅ QR scans already exist — skipping.');
            return;
        }

        $bookings = Booking::latest()->limit(10)->get();
        if ($bookings->isEmpty()) {
            $this->command->warn('⚠️ No bookings found — cannot seed check-ins.');
            return;
        }

        $checkpointNames = [
            'Nayapul Trailhead',
            'Ghorepani Checkpoint',
            'Annapurna Base Camp',
            'Namche Bazaar Checkpoint',
            'Everest Base Camp',
            'Tengboche Monastery',
            'Lukla Entry Point',
            'Poon Hill Viewpoint',
            'Chhomrong Village',
            'Machhapuchhre Base Camp',
        ];

        $created = 0;
        foreach ($bookings as $i => $booking) {
            if ($i >= 8) break;

            $waypoint = Waypoint::inRandomOrder()->first();
            $checkpointName = $checkpointNames[$i % count($checkpointNames)];

            QrScan::create([
                'booking_id'          => $booking->id,
                'checkpoint_name'     => $checkpointName,
                'scanned_at'          => now()->subDays(rand(1, 30)),
                'latitude'            => $waypoint->latitude ?? 28.3949,
                'longitude'           => $waypoint->longitude ?? 84.1240,
                'waypoint_id'         => $waypoint->id ?? null,
                'verification_status' => 'verified',
                'duplicate_of'        => null,
                'verified_by'         => 1,
                'verified_at'         => now(),
            ]);
            $created++;
        }

        $this->command->info("✅ CheckinSeeder: {$created} demo check-ins created");
    }
}
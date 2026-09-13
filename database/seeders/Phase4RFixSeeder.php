<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteSegment;
use Illuminate\Database\Seeder;

class Phase4RFixSeeder extends Seeder
{
    public function run(): void
    {
        // Phase 4R-fix-9: Tier 2 structural fixes
        // - bajhang-bajura: duration 3 -> 2 (only 2 segments exist)
        // - kakani-gurje: duration 3 -> 2 (only 2 segments exist)
        // - khopra-ridge: +1 return segment + duration 8 -> 6

        // Fix 1: bajhang-bajura
        $r1 = Route::where('slug', 'bajhang-bajura')->first();
        if ($r1 && $r1->duration_days !== 2) {
            $r1->duration_days = 2;
            $r1->save();
            $this->command->info("✅ bajhang-bajura -> 2d");
        }

        // Fix 2: kakani-gurje
        $r2 = Route::where('slug', 'kakani-gurje')->first();
        if ($r2 && $r2->duration_days !== 2) {
            $r2->duration_days = 2;
            $r2->save();
            $this->command->info("✅ kakani-gurje -> 2d");
        }

        // Fix 3: khopra-ridge
        $r3 = Route::where('slug', 'khopra-ridge')->first();
        if ($r3) {
            $exists = $r3->segments()
                ->where('from_waypoint_id', 72)
                ->where('to_waypoint_id', 71)
                ->exists();

            if (!$exists) {
                RouteSegment::create([
                    'route_id' => $r3->id,
                    'from_waypoint_id' => 72,  // Khayer Lake
                    'to_waypoint_id' => 71,    // Khopra Ridge
                    'sequence' => 6,
                    'distance_km' => 6.0,
                    'estimated_time_hours' => 3.5,
                    'elevation_gain_m' => 0,
                    'elevation_loss_m' => 300,
                ]);
                $this->command->info("✅ khopra-ridge: return segment added");
            }

            if ($r3->duration_days !== 6) {
                $r3->duration_days = 6;
                $r3->save();
                $this->command->info("✅ khopra-ridge -> 6d");
            }
        }

        $this->command->info('✅ Phase 4R-fix-9 complete.');
    }
}
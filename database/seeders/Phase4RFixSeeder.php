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

        // Phase 4R-fix-10: Tier 3 data fixes
        // 3a: simikot-humla max_altitude 3000 -> 4200
        $r4 = Route::where('slug', 'simikot-humla')->first();
        if ($r4 && $r4->max_altitude !== 4200) {
            $r4->max_altitude = 4200;
            $r4->save();
            $this->command->info("✅ simikot-humla max_altitude -> 4200");
        }

        // 3b: generic waypoint slugs
        $wp = \App\Models\Waypoint::where('slug', 'kathmandu-heritage-start')->first();
        if ($wp) {
            $wp->slug = 'kathmandu-heritage-tour-start';
            $wp->save();
            $this->command->info("✅ kathmandu-heritage-start slug fixed");
        }
        $wp1 = \App\Models\Waypoint::where('slug', 'kathmandu-city-start')->first();
        if ($wp1) {
            $wp1->slug = 'kathmandu-city-tour-departure';
            $wp1->save();
            $this->command->info("✅ kathmandu-city-start slug fixed");
        }
        $wp2 = \App\Models\Waypoint::where('slug', 'kathmandu-city-end')->first();
        if ($wp2) {
            $wp2->slug = 'kathmandu-city-tour-arrival';
            $wp2->save();
            $this->command->info("✅ kathmandu-city-end slug fixed");
        }

        // Phase 4R-fix-12: kathmandu-heritage-end slug fix
        $wpH = \App\Models\Waypoint::where('slug', 'kathmandu-heritage-end')->first();
        if ($wpH) {
            $wpH->slug = 'kathmandu-heritage-tour-end';
            $wpH->save();
            $this->command->info("✅ kathmandu-heritage-end slug fixed");
        }

        // Phase 4R-fix-13: T5a — 6 tour return segments
        $seeds = [
            ['dharan-dhankuta-bhedetar', 410, 407, 4, 60.0,  2.0],
            ['janakpur-tour',            395, 393, 3, 0.5,   0.2],
            ['kalikot-sinja',            422, 420, 3, 160.0, 8.0],
            ['marpha-tukuche-kobang',    406, 403, 4, 185.0, 8.0],
            ['muktinath-temple-tour',    398, 396, 3, 210.0, 9.0],
        ];
        foreach ($seeds as [$slug, $from, $to, $seq, $dist, $time]) {
            $r = Route::where('slug', $slug)->first();
            if (!$r) continue;
            if (!$r->segments()->where('from_waypoint_id', $from)->where('to_waypoint_id', $to)->exists()) {
                RouteSegment::create([
                    'route_id' => $r->id,
                    'from_waypoint_id' => $from,
                    'to_waypoint_id' => $to,
                    'sequence' => $seq,
                    'distance_km' => $dist,
                    'estimated_time_hours' => $time,
                    'elevation_gain_m' => 0,
                    'elevation_loss_m' => 0,
                ]);
                $this->command->info("✅ {$slug} +return segment");
            }
        }

        // T5a-4: koshi-tappu duplicate waypoint fix (444 → 441)
        $kt = Route::where('slug', 'koshi-tappu')->first();
        if ($kt) {
            $seg = $kt->segments()->where('sequence', 3)->first();
            if ($seg && $seg->to_waypoint_id === 444) {
                $seg->to_waypoint_id = 441;
                $seg->save();
                $this->command->info("✅ koshi-tappu seq3: 444 → 441");
            }
        }
        // Phase 4R-fix-16: T5b — nagarkot + three-passes fixes
        // nagarkot-sunrise: unrealistic 6hr → 1hr (vehicle)
                $nag = Route::where('slug', 'nagarkot-sunrise')->first();
        if ($nag) {
            foreach ($nag->segments()->get() as $seg) {
                if ($seg->estimated_time_hours != 1.0) {
                    $seg->estimated_time_hours = 1.0;
                    $seg->save();
                }
            }
            $this->command->info("✅ nagarkot-sunrise: times → 1.0hr");
        }

        // three-passes: Gokyo Ri day-hike split
        $tp = Route::where('slug', 'three-passes')->first();
        if ($tp) {
            $gokyoRi = \App\Models\Waypoint::where('slug', 'gokyo-ri')->first();
            $seg18 = $tp->segments()->where('sequence', 18)->first();
            if ($gokyoRi && $seg18 && $seg18->to_waypoint_id === 112 && $seg18->from_waypoint_id === 112) {
                $seg18->from_waypoint_id = $gokyoRi->id;
                $seg18->to_waypoint_id = 112;
                $seg18->distance_km = 3.0;
                $seg18->estimated_time_hours = 2.5;
                $seg18->elevation_loss_m = 610;
                $seg18->save();
                $this->command->info("✅ three-passes seq18: Gokyo Ri → Gokyo");
            }
        }
        $this->command->info('✅ Phase 4R-fix-9/10/12/13 complete.');
    }
}
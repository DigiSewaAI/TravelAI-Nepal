<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $kala = DB::table('waypoints')->where('slug', 'kala-patthar')->first();
        if (!$kala) {
            $kalaId = DB::table('waypoints')->insertGetId(['name' => 'Kala Patthar', 'slug' => 'kala-patthar', 'type' => 'viewpoint', 'latitude' => 27.9974, 'longitude' => 86.8286, 'altitude' => 5545, 'is_overnight_stop' => 0, 'description' => 'Famous viewpoint offering panoramic views of Everest.', 'created_at' => now(), 'updated_at' => now()]);
        } else { $kalaId = $kala->id; }
        $gorak = DB::table('waypoints')->where('slug', 'gorak-shep')->first();
        if ($gorak) {
            $seg = DB::table('route_segments')->where('from_waypoint_id', $gorak->id)->first();
            if (!$seg) {
                DB::table('route_segments')->insert(['route_id' => 1, 'from_waypoint_id' => $gorak->id, 'to_waypoint_id' => $kalaId, 'distance_km' => 3.5, 'estimated_time_hours' => 2.0, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
    public function down(): void {}
};

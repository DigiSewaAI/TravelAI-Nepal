<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$routeId    = 5;
$jomsomId   = 49;
$marphaId   = 212;
$tatopaniId = 50;

echo "=== FIX 1: Add Marpha segment to Route 5 (CORRECTED) ===\n\n";

$before = DB::table('route_segments')
    ->where('route_id', $routeId)
    ->whereNull('deleted_at')
    ->count();
echo "Before: Route 5 has {$before} active segments\n\n";

DB::beginTransaction();

try {
    // ===== DESCENDING ORDER SHIFT =====
    // Move 16 → 17 first (17 is empty)
    $s1 = DB::table('route_segments')
        ->where('route_id', $routeId)
        ->whereNull('deleted_at')
        ->where('sequence', 16)
        ->update(['sequence' => 17]);
    echo "Step 1: seq 16 → 17 [{$s1} rows]\n";

    // Move 15 → 16 (16 now empty)
    $s2 = DB::table('route_segments')
        ->where('route_id', $routeId)
        ->whereNull('deleted_at')
        ->where('sequence', 15)
        ->update(['sequence' => 16]);
    echo "Step 2: seq 15 → 16 [{$s2} rows]\n";

    // Move 14 → 15 (15 now empty)
    $s3 = DB::table('route_segments')
        ->where('route_id', $routeId)
        ->whereNull('deleted_at')
        ->where('sequence', 14)
        ->update(['sequence' => 15]);
    echo "Step 3: seq 14 → 15 [{$s3} rows]\n\n";

    // ===== Now seq 14 is empty =====

    // Update seq 15 (was Jomsom → Tatopani) → Marpha → Tatopani
    $updated = DB::table('route_segments')
        ->where('route_id', $routeId)
        ->whereNull('deleted_at')
        ->where('sequence', 15)
        ->update([
            'from_waypoint_id'      => $marphaId,
            'to_waypoint_id'        => $tatopaniId,
            'distance_km'           => 11.0,
            'estimated_time_hours'  => 4.0,
            'elevation_gain_m'      => 0,
            'elevation_loss_m'      => 1480,
            'updated_at'            => now(),
        ]);
    echo "Step 4: Updated seq 15 (Marpha → Tatopani) [{$updated} rows]\n";

    // Insert new seq 14 (Jomsom → Marpha) — 14 is now empty
    DB::table('route_segments')->insert([
        'route_id'              => $routeId,
        'from_waypoint_id'      => $jomsomId,
        'to_waypoint_id'        => $marphaId,
        'sequence'              => 14,
        'distance_km'           => 5.0,
        'estimated_time_hours'  => 2.0,
        'elevation_gain_m'      => 0,
        'elevation_loss_m'      => 50,
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);
    echo "Step 5: Inserted new seq 14 (Jomsom → Marpha)\n";

    DB::commit();
    echo "\n=== FIX 1 committed ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n=== ERROR (rolled back): " . $e->getMessage() . " ===\n";
    exit(1);
}

// Verify
$after = DB::table('route_segments')
    ->where('route_id', $routeId)
    ->whereNull('deleted_at')
    ->count();
echo "\nAfter: Route 5 has {$after} active segments (expected 17)\n\n";

echo "=== Route 5 segments (with Marpha) ===\n";
$segments = DB::table('route_segments as rs')
    ->join('waypoints as wf', 'rs.from_waypoint_id', '=', 'wf.id')
    ->join('waypoints as wt', 'rs.to_waypoint_id', '=', 'wt.id')
    ->where('rs.route_id', $routeId)
    ->whereNull('rs.deleted_at')
    ->orderBy('rs.sequence')
    ->select('rs.sequence', 'wf.name as from_name', 'wt.name as to_name', 'rs.distance_km', 'rs.estimated_time_hours')
    ->get();

foreach ($segments as $s) {
    echo sprintf("seq %2d: %-18s → %-18s (%s km, %s hrs)\n",
        $s->sequence, $s->from_name, $s->to_name, $s->distance_km, $s->estimated_time_hours);
}

echo "\n=== DONE ===\n";
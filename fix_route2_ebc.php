<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 4K-DATA-FIX-02 Part B: Route 2 EBC Segments ===\n\n";

$kalaPattharId = DB::table('waypoints')->where('slug', 'kala-patthar')->value('id');
if (!$kalaPattharId) {
    echo "Kala Patthar not found. Run fix_kala_patthar.php first.\n";
    exit(1);
}
echo "OK Kala Patthar ID: {$kalaPattharId}\n";

$namcheId = 21;
$gorakShepId = 25;

$beforeCount = DB::table('route_segments')
    ->where('route_id', 2)->whereNull('deleted_at')->count();
echo "Before: {$beforeCount} Route 2 segments (expected 15)\n\n";

DB::beginTransaction();
try {
    // Phase 1: shift seq [3-15] -> [4-16]
    $shift1 = DB::table('route_segments')
        ->where('route_id', 2)->whereNull('deleted_at')
        ->whereBetween('sequence', [3, 15])
        ->orderBy('sequence', 'desc')
        ->get(['id', 'sequence']);
    foreach ($shift1 as $seg) {
        DB::table('route_segments')->where('id', $seg->id)
            ->update(['sequence' => $seg->sequence + 1, 'updated_at' => now()]);
    }
    echo "Phase 1: shifted " . count($shift1) . " segments [3-15] -> [4-16]\n";

    // Phase 2: insert Namche -> Namche @ seq=3
    $idA = DB::table('route_segments')->insertGetId([
        'route_id'             => 2,
        'from_waypoint_id'     => $namcheId,
        'to_waypoint_id'       => $namcheId,
        'sequence'             => 3,
        'distance_km'          => 0.00,
        'estimated_time_hours' => 0.0,
        'elevation_gain_m'     => 0,
        'elevation_loss_m'     => 0,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    echo "Phase 2: inserted Namche-Namche @ seq=3, id={$idA}\n";

    // Phase 3: shift seq [11-16] -> [13-18]
    $shift2 = DB::table('route_segments')
        ->where('route_id', 2)->whereNull('deleted_at')
        ->whereBetween('sequence', [11, 16])
        ->orderBy('sequence', 'desc')
        ->get(['id', 'sequence']);
    foreach ($shift2 as $seg) {
        DB::table('route_segments')->where('id', $seg->id)
            ->update(['sequence' => $seg->sequence + 2, 'updated_at' => now()]);
    }
    echo "Phase 3: shifted " . count($shift2) . " segments [11-16] -> [13-18]\n";

    // Phase 4: insert Gorak Shep -> Kala Patthar @ seq=11
    $idB = DB::table('route_segments')->insertGetId([
        'route_id'             => 2,
        'from_waypoint_id'     => $gorakShepId,
        'to_waypoint_id'       => $kalaPattharId,
        'sequence'             => 11,
        'distance_km'          => 3.50,
        'estimated_time_hours' => 2.5,
        'elevation_gain_m'     => 405,
        'elevation_loss_m'     => 0,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    echo "Phase 4: inserted GSh-KP @ seq=11, id={$idB}\n";

    // Phase 5: insert Kala Patthar -> Gorak Shep @ seq=12
    $idC = DB::table('route_segments')->insertGetId([
        'route_id'             => 2,
        'from_waypoint_id'     => $kalaPattharId,
        'to_waypoint_id'       => $gorakShepId,
        'sequence'             => 12,
        'distance_km'          => 3.50,
        'estimated_time_hours' => 2.0,
        'elevation_gain_m'     => 0,
        'elevation_loss_m'     => 405,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    echo "Phase 5: inserted KP-GSh @ seq=12, id={$idC}\n";

    DB::commit();
    echo "\nOK Transaction committed\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "\nFAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== FINAL VERIFY ===\n";
$segments = DB::table('route_segments as rs')
    ->join('waypoints as wf', 'rs.from_waypoint_id', '=', 'wf.id')
    ->join('waypoints as wt', 'rs.to_waypoint_id', '=', 'wt.id')
    ->where('rs.route_id', 2)->whereNull('rs.deleted_at')
    ->orderBy('rs.sequence')
    ->get(['rs.id', 'rs.sequence', 'wf.name as from_name', 'wt.name as to_name',
           'rs.distance_km', 'rs.estimated_time_hours',
           'rs.elevation_gain_m', 'rs.elevation_loss_m']);

echo "Route 2 total: " . count($segments) . " segments (expected 18)\n\n";
foreach ($segments as $s) {
    echo "seq {$s->sequence}: {$s->from_name} -> {$s->to_name} "
       . "({$s->distance_km}km, {$s->estimated_time_hours}h, "
       . "+{$s->elevation_gain_m}/-{$s->elevation_loss_m})\n";
}
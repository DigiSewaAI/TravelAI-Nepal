<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function haversineKm($lat1, $lng1, $lat2, $lng2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
    return 2 * $R * asin(sqrt($a));
}

echo "=== FIX 2b: Assign location_id to 86 orphans ===\n\n";

$orphans = DB::table('waypoints')
    ->whereNull('location_id')
    ->whereNull('deleted_at')
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

$locations = DB::table('locations')
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->get();

echo "Orphans to process: " . count($orphans) . "\n";
echo "Locations available: " . count($locations) . "\n\n";

$updates = [];
foreach ($orphans as $o) {
    $nearest = null;
    $minDist = PHP_FLOAT_MAX;

    foreach ($locations as $loc) {
        $d = haversineKm(
            (float) $o->latitude,
            (float) $o->longitude,
            (float) $loc->latitude,
            (float) $loc->longitude
        );
        if ($d < $minDist) {
            $minDist = $d;
            $nearest = $loc;
        }
    }

    if ($minDist <= 30 && $nearest) {
        $updates[] = [
            'wp_id'    => $o->id,
            'loc_id'   => $nearest->id,
            'wp_name'  => $o->name,
            'loc_city' => $nearest->city,
            'dist_km'  => round($minDist, 2),
        ];
    }
}

echo "Mapped: " . count($updates) . " (expect 86)\n\n";

if (count($updates) !== 86) {
    echo "Count mismatch! Expected 86, got " . count($updates) . "\n";
    echo "Aborting for safety.\n";
    exit(1);
}

DB::beginTransaction();

try {
    $count = 0;
    foreach ($updates as $u) {
        DB::table('waypoints')
            ->where('id', $u['wp_id'])
            ->update([
                'location_id' => $u['loc_id'],
                'updated_at'  => now(),
            ]);
        $count++;
    }

    DB::commit();
    echo "Updated {$count} waypoints (transaction committed)\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR (rolled back): " . $e->getMessage() . "\n";
    exit(1);
}

$remaining = DB::table('waypoints')
    ->whereNull('location_id')
    ->whereNull('deleted_at')
    ->count();

echo "\n=== VERIFY ===\n";
echo "Remaining orphans: {$remaining} (expected 0)\n";

echo "\n=== DONE ===\n";
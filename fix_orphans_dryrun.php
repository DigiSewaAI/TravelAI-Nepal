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

echo "=== FIX 2a: Orphan Waypoints — DRY RUN (no DB change) ===\n\n";

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

echo "Orphans: " . count($orphans) . "\n";
echo "Locations: " . count($locations) . "\n\n";

$mapped  = [];
$skipped = [];

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

    if ($minDist <= 30) {
        $mapped[] = [
            'wp_id'     => $o->id,
            'wp_name'   => $o->name,
            'wp_type'   => $o->type,
            'loc_id'    => $nearest->id,
            'loc_city'  => $nearest->city,
            'dist_km'   => round($minDist, 2),
        ];
    } else {
        $skipped[] = [
            'wp_id'         => $o->id,
            'wp_name'       => $o->name,
            'nearest_city'  => $nearest->city,
            'dist_km'       => round($minDist, 2),
        ];
    }
}

echo "MAPPED:  " . count($mapped)  . " / " . count($orphans) . "\n";
echo "SKIPPED: " . count($skipped) . " / " . count($orphans) . "\n\n";

echo "=== Mapped (first 20) ===\n";
foreach (array_slice($mapped, 0, 20) as $m) {
    echo sprintf("wp %4d (%-22s) → loc %3d (%-15s) [%.1f km]\n",
        $m['wp_id'], $m['wp_name'], $m['loc_id'], $m['loc_city'], $m['dist_km']);
}

echo "\n=== Skipped (>30 km, first 20) ===\n";
foreach (array_slice($skipped, 0, 20) as $s) {
    echo sprintf("wp %4d (%-22s) — nearest: %-15s [%.1f km]\n",
        $s['wp_id'], $s['wp_name'], $s['nearest_city'], $s['dist_km']);
}

echo "\n=== DONE (DRY RUN — no changes made) ===\n";
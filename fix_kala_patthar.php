<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 4K-DATA-FIX-02 Part A: Kala Patthar ===\n\n";

$exists = DB::table('waypoints')->where('slug', 'kala-patthar')->exists();
if ($exists) {
    echo "Kala Patthar already exists. Aborting.\n";
    exit(1);
}

$beforeCount = DB::table('waypoints')->count();
echo "Before: {$beforeCount} waypoints\n";

DB::beginTransaction();
try {
    $id = DB::table('waypoints')->insertGetId([
        'name'                 => 'Kala Patthar',
        'slug'                 => 'kala-patthar',
        'type'                 => 'viewpoint',
        'latitude'             => 27.99583,
        'longitude'            => 86.82861,
        'altitude'             => 5545,
        'is_overnight_stop'    => 0,
        'location_id'          => 34,
        'description'          => 'Famous viewpoint above Gorak Shep offering the best panoramic views of Everest and Khumbu glacier. Highest point of the classic EBC trek at 5,545m.',
        'metadata'             => null,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    DB::commit();
    echo "OK Inserted Kala Patthar - ID: {$id}\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

$afterCount = DB::table('waypoints')->count();
echo "\n=== VERIFY ===\n";
echo "After:  {$afterCount} waypoints (expected " . ($beforeCount + 1) . ")\n";

$inserted = DB::table('waypoints')->where('slug', 'kala-patthar')->first();
echo "\nInserted row:\n";
echo json_encode($inserted, JSON_PRETTY_PRINT) . "\n";
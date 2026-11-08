<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════\n";
echo "  SIMULATE — Category Dropdown per Provider Type\n";
echo "═══════════════════════════════════════════════════════\n\n";

// For each provider type, simulate what categories a provider sees
$types = DB::table('provider_types')->orderBy('id')->get();

foreach ($types as $type) {
    // Product providers redirect to products, skip
    if (in_array($type->slug, ['shop-owner', 'rental-provider', 'wholesale-provider'])) {
        echo "  {$type->slug}: (Product provider — redirects to product create)\n\n";
        continue;
    }

    // Simulate ServiceController::create() logic
    $mappedCategories = DB::table('provider_type_service_category')
        ->join('service_categories', 'provider_type_service_category.service_category_id', '=', 'service_categories.id')
        ->where('provider_type_service_category.provider_type_id', $type->id)
        ->whereNotIn('service_categories.slug', ['shop', 'rental', 'wholesale'])
        ->select('service_categories.slug', 'service_categories.name')
        ->get();

    if ($mappedCategories->isEmpty()) {
        // FALLBACK — ALL categories (BUG!)
        $fallback = DB::table('service_categories')
            ->whereNotIn('slug', ['shop', 'rental', 'wholesale'])
            ->get();
        echo "  ❌ {$type->slug}: FALLBACK → सबै " . $fallback->count() . " categories\n";
        foreach ($fallback as $cat) {
            echo "      - {$cat->name}\n";
        }
    } else {
        echo "  ✅ {$type->slug}: " . $mappedCategories->count() . " categories\n";
        foreach ($mappedCategories as $cat) {
            echo "      - {$cat->name}\n";
        }
    }
    echo "\n";
}
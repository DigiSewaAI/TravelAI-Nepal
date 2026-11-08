<?php
/**
 * ULTRA-MINIMAL FIX — केवल L142 (Base Price duplicate) delete
 * 
 * L141: Price/Night (Rs) → name="base_price" ← रहन्छ
 * L142: Base Price /Night → name="base_price" ← हटाउने (duplicate)
 * L143: Min Price → name="min_price" ← छाड्ने (auto-calc fallback)
 * L144: Max Price → name="max_price" ← छाड्ने (auto-calc fallback)
 * 
 * कोई structural change नै, कोई modal change नै, कोई wrapper change नै।
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  ULTRA-MINIMAL FIX — BASE_PRICE DUPLICATE\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/provider/hotel/rooms.blade.php';
copy($file, $file . '.bak_oneline');

$lines = file($file);
$newLines = [];
$removed = false;

foreach ($lines as $i => $line) {
    // L142 को unique marker: 'base_price_night_rs' + 'name="base_price"'
    if (!$removed 
        && strpos($line, 'base_price_night_rs') !== false 
        && strpos($line, 'name="base_price"') !== false) {
        
        echo "  ✅ DELETED L" . ($i+1) . "\n";
        echo "     " . trim(substr($line, 0, 150)) . "...\n";
        $removed = true;
        continue;
    }
    $newLines[] = $line;
}

if (!$removed) {
    echo "  ⚠️ L142 not found — verify file state\n";
} else {
    file_put_contents($file, implode('', $newLines));
    echo "\n  ✅ File saved\n";
}

echo "\n─── Verify ───\n";
$final = file_get_contents($file);
$count = substr_count($final, 'name="base_price"');
echo "  name=\"base_price\" count: {$count} " . ($count === 1 ? '✅ (1 only)' : '❌') . "\n";
echo "  name=\"min_price\" count: " . substr_count($final, 'name="min_price"') . " (untouched)\n";
echo "  name=\"max_price\" count: " . substr_count($final, 'name="max_price"') . " (untouched)\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ DONE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
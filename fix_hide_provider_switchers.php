<?php
// fix_hide_provider_switchers.php — Hide switcher in 8 provider layouts
require 'vendor/autoload.php';

echo "═══ HIDE PROVIDER LANGUAGE SWITCHERS ═══\n\n";

// ═══ Pattern A: provider.blade.php (full dropdown) ═══
echo "─── Pattern A: provider.blade.php ───\n";
$fA = 'resources/views/layouts/provider.blade.php';
copy($fA, $fA . '.bak_hide');
$cA = file_get_contents($fA);

$oldA = 'id="languageDropdown" aria-haspopup="true">';
$newA = 'id="languageDropdown" aria-haspopup="true" style="display:none;">';

$cntA = substr_count($cA, $oldA);
echo "  Anchor: $cntA occurrence(s)\n";

if ($cntA === 1) {
    $cA = str_replace($oldA, $newA, $cA);
    file_put_contents($fA, $cA);
    echo "  ✅ Hidden (button display:none)\n";
} elseif ($cntA === 0) {
    echo "  ⏭  Anchor not found — check manual\n";
    unlink($fA . '.bak_hide');
} else {
    echo "  ❌ Ambiguous ($cntA) — skip\n";
    unlink($fA . '.bak_hide');
}

// ═══ Pattern B: 7 verticals (select dropdown) ═══
echo "\n─── Pattern B: 7 verticals ───\n";
$verticals = ['hotel', 'restaurant', 'transport', 'inventory', 'art-craft', 'individual', 'money-changer'];

$oldB = '<select class="lang-select" onchange="window.location.href=\'/lang/\' + this.value">';
$newB = '<select class="lang-select" onchange="window.location.href=\'/lang/\' + this.value" style="display:none;">';

foreach ($verticals as $v) {
    $f = "resources/views/layouts/provider-{$v}.blade.php";
    if (!file_exists($f)) { echo "  ❌ missing: $f\n"; continue; }
    $c = file_get_contents($f);
    $cnt = substr_count($c, $oldB);

    if ($cnt === 0) {
        echo "  ⏭  {$v}: anchor not found\n";
        continue;
    }
    if ($cnt > 1) {
        echo "  ❌ {$v}: ambiguous ($cnt) — skip\n";
        continue;
    }

    copy($f, $f . '.bak_hide');
    $c = str_replace($oldB, $newB, $c);
    file_put_contents($f, $c);
    echo "  ✅ {$v}: hidden\n";
}

// ═══ VERIFY ═══
echo "\n═══ VERIFY ═══\n";
$allLayouts = ['provider', 'provider-hotel', 'provider-restaurant', 'provider-transport',
    'provider-inventory', 'provider-art-craft', 'provider-individual', 'provider-money-changer'];

foreach ($allLayouts as $l) {
    $f = "resources/views/layouts/{$l}.blade.php";
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $hasHidden = (
        strpos($c, 'id="languageDropdown" aria-haspopup="true" style="display:none;">') !== false ||
        strpos($c, 'class="lang-select" onchange="window.location.href=\'/lang/\' + this.value" style="display:none;">') !== false
    );
    echo "  " . str_pad($l, 30) . ($hasHidden ? "✅ hidden" : "🔴 not hidden") . "\n";
}

// ═══ Middleware verify ═══
echo "\n─── Middleware verify ───\n";
$mw = file_get_contents('app/Http/Middleware/Localization.php');
$hasForce = strpos($mw, "\$request->is('provider')") !== false;
echo "  Force English for provider routes: " . ($hasForce ? "✅" : "🔴 missing") . "\n";

echo "\n✅ DONE\n";
echo "\nCleanup: for %f in (resources\\views\\layouts\\provider.blade.php.bak_hide resources\\views\\layouts\\provider-hotel.blade.php.bak_hide resources\\views\\layouts\\provider-restaurant.blade.php.bak_hide resources\\views\\layouts\\provider-transport.blade.php.bak_hide resources\\views\\layouts\\provider-inventory.blade.php.bak_hide resources\\views\\layouts\\provider-art-craft.blade.php.bak_hide resources\\views\\layouts\\provider-individual.blade.php.bak_hide resources\\views\\layouts\\provider-money-changer.blade.php.bak_hide) do @del %f 2>nul\n";
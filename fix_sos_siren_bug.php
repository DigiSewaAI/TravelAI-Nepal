<?php
/**
 * FIX — SOS Siren Bug (Status Update & Page Load)
 * Target: public/js/sos-siren-global.js
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — SOS SIREN BUG\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'public/js/sos-siren-global.js';
if (!file_exists($file)) die("❌ File not found: {$file}\n");

copy($file, $file . '.bak_siren_fix');
$c = file_get_contents($file);

// Fix 1: Remove playSiren() from enableAudio()
$pattern1 = '/return audioCtx\.resume\(\)\.then\(function\(\) \{\s*enabled = true;\s*localStorage\.setItem\(\'sos_audio_enabled\', \'true\'\);\s*playSiren\(\);\s*\}\);/s';
$replacement1 = "return audioCtx.resume().then(function() {\n                enabled = true;\n                localStorage.setItem('sos_audio_enabled', 'true');\n                // Removed playSiren() here to prevent siren on Status Update click\n            });";

if (preg_match($pattern1, $c)) {
    $c = preg_replace($pattern1, $replacement1, $c);
    echo "  ✅ Removed playSiren() from enableAudio()\n";
} else {
    echo "  ⚠️ Pattern 1 not matched (please check manually)\n";
}

// Fix 2: Acknowledge existing alerts on first load (Prevents siren on dashboard open)
$pattern2 = 'if (lastLatestId === null) lastLatestId = latest;';
$replacement2 = <<<'JS'
// FIX: Auto-acknowledge existing alerts on first load
                if (lastLatestId === null) {
                    lastLatestId = latest;
                    if (ackedLatestId === 0 && latest > 0) {
                        ackedLatestId = latest;
                        localStorage.setItem(ackKey, String(latest));
                    }
                }
JS;

if (str_contains($c, $pattern2)) {
    $c = str_replace($pattern2, $replacement2, $c);
    echo "  ✅ Added auto-acknowledge on first load\n";
} else {
    echo "  ⚠️ Pattern 2 not matched (please check manually)\n";
}

// Fix 3: Change sessionStorage to localStorage for ackKey (so it works across tabs)
$c = str_replace("sessionStorage.getItem(ackKey)", "localStorage.getItem(ackKey)", $c);
$c = str_replace("sessionStorage.setItem(ackKey", "localStorage.setItem(ackKey", $c);
echo "  ✅ Changed sessionStorage to localStorage\n";

file_put_contents($file, $c);

// Verify Syntax
$check = trim(shell_exec('node --check ' . escapeshellarg($file) . ' 2>&1'));
echo "  Syntax Check: " . (str_contains($check, 'Error') ? "❌ {$check}" : "✅ OK") . "\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "\n🔄 Rollback:\n";
echo "  copy public/js/sos-siren-global.js.bak_siren_fix public/js/sos-siren-global.js\n";
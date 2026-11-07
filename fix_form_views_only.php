<?php
/**
 * FIX — Form Views ONLY (create.blade.php & edit.blade.php)
 * Target: Fix box-sizing in .svcf-form CSS
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — FORM VIEWS ONLY (BOX-SIZING)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$targetFiles = [
    'resources/views/provider/services/create.blade.php',
    'resources/views/provider/services/edit.blade.php'
];

foreach ($targetFiles as $f) {
    if (!file_exists($f)) {
        echo "  ❌ Not found: {$f}\n";
        continue;
    }

    copy($f, $f . '.bak_form_only');
    $c = file_get_contents($f);

    // 1. Add box-sizing to the main select/input rule
    $pattern = '/(\.svcf-form \.svcf-card textarea\{)(\s*)(width:100% !important;)/';
    $replacement = "$1\n    box-sizing: border-box !important;\n    min-width: 0 !important;\n    max-width: 100% !important;$2$3";
    
    if (preg_match($pattern, $c)) {
        $c = preg_replace($pattern, $replacement, $c);
        echo "  ✅ Patched CSS in: " . basename($f) . "\n";
    } else {
        // Fallback: direct string replace if regex fails
        $old = "width:100% !important;\n    padding:11px 14px !important;";
        $new = "width:100% !important;\n    box-sizing: border-box !important;\n    max-width:100% !important;\n    padding:11px 14px !important;";
        if (str_contains($c, $old)) {
            $c = str_replace($old, $new, $c);
            echo "  ✅ Patched (fallback) in: " . basename($f) . "\n";
        } else {
            echo "  ⚠️ Pattern not found in: " . basename($f) . "\n";
        }
    }

    // 2. Add a small mobile-specific fix just for this form (no other files touched)
    $mobileCSS = <<<'CSS'

<style id="form-box-sizing-final">
@media (max-width: 767px) {
    .svcf-form .svcf-card select,
    .svcf-form .svcf-card input,
    .svcf-form .svcf-card textarea {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .svcf-form .svcf-card { padding: 14px !important; }
}
</style>
CSS;

    if (!str_contains($c, 'form-box-sizing-final')) {
        $c = str_replace('</head>', $mobileCSS . "\n</head>", $c);
        // If no </head> (partial view), append at end
        if (!str_contains($c, '</head>')) {
            $c .= "\n" . $mobileCSS;
        }
        echo "     Injected mobile CSS fix.\n";
    }

    file_put_contents($f, $c);
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
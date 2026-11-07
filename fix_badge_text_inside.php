<?php
/**
 * FIX — Badge Text Inside (Confirmed badge)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — BADGE TEXT INSIDE\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
copy($file, $file . '.bak_badge_inside');
$c = file_get_contents($file);

// Remove old simple fix
$c = preg_replace('/<!-- SIMPLE CARD BUTTON TEXT FIX -->.*?<\/style>/s', '', $c);

$cssBlock = <<<'HTML'

<!-- BADGE TEXT INSIDE FIX -->
<style id="badge-text-inside-fix">
@media (max-width: 767px) {
    /* 1. The row with Confirmed + View */
    .tv2-mytrips div[style*="justify-content: flex-end"] {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
        padding-right: 8px !important;
    }

    /* 2. The Confirmed badge - force inline-flex so content stays inside */
    .tv2-mytrips div[style*="justify-content: flex-end"] > span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        gap: 4px !important;
        line-height: 1.2 !important;
    }

    /* 3. The checkmark inside badge */
    .tv2-mytrips div[style*="justify-content: flex-end"] > span > span {
        display: inline !important;
        white-space: nowrap !important;
        line-height: 1 !important;
    }

    /* 4. The View button */
    .tv2-mytrips div[style*="justify-content: flex-end"] > a {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
    }
}
</style>
HTML;

if (!str_contains($c, 'badge-text-inside-fix')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ Badge Text Inside Fix injected.\n";
} else {
    echo "  ⏭ Already fixed.\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
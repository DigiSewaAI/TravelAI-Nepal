<?php
/**
 * FIX — Landscape Dropdowns (Pixel, Pro Max, SE, iPhone)
 * Target: resources/views/layouts/traveler-v2.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — LANDSCAPE DROPDOWNS (FINAL)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
if (!file_exists($file)) die("❌ traveler-v2.blade.php not found\n");

copy($file, $file . '.bak_landscape_final_v2');
$c = file_get_contents($file);

// Purge previous landscape fixes to avoid conflicts
$c = preg_replace('/<!-- TV2 LANDSCAPE FINAL FIX -->.*?<\/style>/s', '', $c);
$c = preg_replace('/<!-- LANDSCAPE DROPDOWN & SCROLL FIX -->.*?<\/style>/s', '', $c);
$c = preg_replace('/<!-- LANDSCAPE MOBILE FIX -->.*?<\/style>/s', '', $c);

$cssBlock = <<<'HTML'

<!-- ULTIMATE LANDSCAPE DROPDOWN FIX -->
<style id="ultimate-landscape-dropdown-fix">
@media (orientation: landscape) and (max-height: 500px) {
    /* 1. Force Dropdowns to be Fixed to the Viewport */
    .tv2-dropdown-menu {
        position: fixed !important;
        top: 55px !important;       /* Just below the header */
        right: 15px !important;     /* Keep on screen */
        left: auto !important;
        width: 90vw !important;
        max-width: 320px !important;
        max-height: calc(100vh - 70px) !important; /* Fit within screen */
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        z-index: 999999 !important;
        transform: none !important; /* Remove any centering transforms */
        box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
    }

    /* 2. Ensure Header doesn't block clicks */
    .tv2-header {
        position: relative !important;
        z-index: 1000000 !important;
    }

    /* 3. Adjust Header Buttons to fit in one line */
    .tv2-header-actions {
        gap: 4px !important;
    }
    .tv2-header-btn {
        padding: 6px 8px !important;
    }
}
</style>
HTML;

if (str_contains($c, 'ultimate-landscape-dropdown-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ Ultimate Landscape Fix injected\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
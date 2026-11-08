<?php
/**
 * FIX — Restaurant Layout: card-head buttons mobile wrap + overflow guard
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — RESTAURANT LAYOUT MOBILE\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/provider-restaurant.blade.php';
if (!file_exists($file)) die("❌ Restaurant layout not found!\n");

copy($file, $file . '.bak_mobile_btn');
$c = file_get_contents($file);

if (str_contains($c, 'restaurant-mobile-buttons-fix')) {
    die("⏭ Already fixed.\n");
}

$cssBlock = <<<'HTML'

<!-- RESTAURANT MOBILE BUTTONS + OVERFLOW FIX -->
<style id="restaurant-mobile-buttons-fix">
@media (max-width: 767px) {
    /* 1. Prevent horizontal scroll globally */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    .main, main, .content-wrapper {
        max-width: 100vw !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }

    /* 2. Card head — stack on mobile */
    .card-head {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    /* 3. Title area — full width */
    .card-head > div:first-child {
        width: 100% !important;
        min-width: 0 !important;
    }

    /* 4. Button group — wrap to second row */
    .card-head > div:last-child {
        width: 100% !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        justify-content: flex-start !important;
    }

    /* 5. Buttons — equal width, no overflow */
    .card-head > div:last-child > a,
    .card-head > div:last-child > button {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        white-space: nowrap !important;
        font-size: 12px !important;
        padding: 8px 12px !important;
    }

    /* 6. Back button — full width on next row */
    .card-head > div:last-child > a[href*="dashboard"] {
        flex-basis: 100% !important;
    }

    /* 7. Card container — clip content */
    .card {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 8. Table wrappers — allow internal scroll only */
    .tbl-wrap {
        max-width: 100% !important;
        overflow-x: auto !important;
    }
}
</style>
HTML;

$c = str_replace('</head>', $cssBlock . "\n</head>", $c);
file_put_contents($file, $c);
echo "  ✅ Restaurant layout updated\n";

// Verify
$check = trim(shell_exec('php -l ' . escapeshellarg($file) . ' 2>&1'));
echo "  Syntax: " . (str_contains($check, 'No syntax') ? '✅ OK' : "❌ {$check}") . "\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
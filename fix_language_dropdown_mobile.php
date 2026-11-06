<?php
/**
 * FIX — Mobile Dropdowns (Language, Currency, Profile) Cut-off
 * Target: resources/views/layouts/traveler-v2.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — MOBILE DROPDOWNS (CUT-OFF)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
if (!file_exists($file)) die("❌ traveler-v2.blade.php not found\n");

copy($file, $file . '.bak_lang_dropdown');
$c = file_get_contents($file);

$cssBlock = <<<'HTML'

<!-- MOBILE DROPDOWN GLOBAL FIX -->
<style id="tv2-mobile-dropdown-fix">
@media (max-width: 767px) {
    /* Target all dropdown menus in the header */
    .tv2-dropdown-menu {
        position: fixed !important;
        top: 65px !important; /* Below the header */
        right: 15px !important; /* Safe margin from right edge */
        left: auto !important; /* Prevent left overflow */
        width: 90vw !important; 
        max-width: 320px !important; /* Ensure it fits on 360px screens */
        z-index: 999999 !important;
        box-sizing: border-box !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
        border-radius: 12px !important;
    }

    /* Fix any internal padding that might push content out */
    .tv2-dropdown-menu * {
        box-sizing: border-box !important;
    }
}
</style>
HTML;

if (str_contains($c, 'tv2-mobile-dropdown-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ public.blade.php: Mobile Dropdown Global Fix injected\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
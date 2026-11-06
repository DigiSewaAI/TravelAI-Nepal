<?php
/**
 * FIX — Trek/Tour Provider Header Scroll Overlap
 * Target: resources/views/layouts/provider.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — TREK HEADER SCROLL (Z-INDEX)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/provider.blade.php';
if (!file_exists($file)) die("❌ provider.blade.php not found\n");

copy($file, $file . '.bak_header_scroll');
$c = file_get_contents($file);

$cssBlock = <<<'HTML'

<!-- TREK HEADER SCROLL Z-INDEX FIX -->
<style id="trek-header-scroll-fix">
@media (max-width: 767px) {
    /* 1. Force Header to be Sticky and Top Layer */
    header, .topbar, .main-header {
        position: sticky !important;
        top: 0 !important;
        z-index: 99999 !important; /* Make it higher than all cards */
        background-color: #ffffff !important; /* Ensure solid background */
        box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important; /* Optional: adds subtle shadow below header */
    }
    
    /* 2. Ensure Main Content stays below the header */
    main, .main-content, .content-wrapper {
        position: relative !important;
        z-index: 1 !important; /* Lower than header */
    }
}
</style>
HTML;

if (str_contains($c, 'trek-header-scroll-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ Trek Header Scroll Fix injected\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
<?php
/**
 * FIX — iPad Mini (768px) Weather Widget Overlap (Minimal Push)
 * Target: resources/views/layouts/traveler-v2.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — IPAD MINI WEATHER (MINIMAL PUSH)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
if (!file_exists($file)) die("❌ traveler-v2.blade.php not found\n");

copy($file, $file . '.bak_ipad_minimal');
$c = file_get_contents($file);

// Purge the previous broken tablet fix
$c = preg_replace('/<!-- TABLET WEATHER OVERLAP FIX.*?<\/style>/s', '', $c);

$cssBlock = <<<'HTML'

<!-- IPAD MINI WEATHER MINIMAL PUSH FIX -->
<style id="ipad-weather-minimal-fix">
/* Target ONLY iPad Mini Portrait width (768px to 820px) */
@media (min-width: 768px) and (max-width: 820px) {
    .tv2-weather-inline {
        /* Make it slightly smaller */
        transform: scale(0.85) !important;
        /* Anchor to top-right so it scales away from the text */
        transform-origin: top right !important;
        /* Minimal push: 5px up and 5px right */
        translate: 5px -5px !important; 
        margin-top: 0 !important;
        margin-right: 0 !important;
        position: relative !important;
        z-index: 10 !important;
    }
    
    /* Ensure the greeting text leaves a little room on the right */
    .tv2-hero h1 {
        padding-right: 10px !important;
    }
}
</style>
HTML;

if (str_contains($c, 'ipad-weather-minimal-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ iPad Mini Minimal Push Fix injected\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
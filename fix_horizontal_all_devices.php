<?php
/**
 * FIX — Horizontal Scroll for ALL specific devices (up to 1024px)
 * Target: resources/views/layouts/public.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — HORIZONTAL SCROLL (ALL DEVICES)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/public.blade.php';
if (!file_exists($file)) die("❌ file not found\n");

$c = file_get_contents($file);

// Purge all previous horizontal scroll fixes to avoid conflicts
$c = preg_replace('/<!-- SAFE HORIZONTAL SCROLL FIX -->.*?<\/style>/s', '', $c);
$c = preg_replace('/<!-- HORIZONTAL SCROLL GUARD.*?<\/style>/s', '', $c);

$cssBlock = <<<'HTML'

<!-- MASTER HORIZONTAL SCROLL FIX (Up to 1024px) -->
<style id="master-horizontal-fix">
@media (max-width: 1024px) {
    /* 1. Global container guard */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
        width: 100% !important;
        position: relative !important;
    }

    /* 2. Force all direct children to respect screen width */
    body > *, 
    main > *, 
    header > *, 
    footer > *,
    section > * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 3. Prevent specific oversized elements from pushing the screen */
    [style*="width: 120%"], 
    [style*="width: 150%"], 
    [style*="min-width: 400px"],
    [style*="min-width: 500px"],
    [style*="width: 450px"] {
        max-width: 100% !important;
        width: 100% !important;
    }

    /* 4. Images & Media (Safe: NO height auto to protect logos) */
    img, video, iframe, canvas, svg {
        max-width: 100% !important;
    }

    /* 5. Text wrapping (prevents long URLs from stretching) */
    p, h1, h2, h3, h4, h5, h6, span, a, div {
        overflow-wrap: break-word !important;
    }
}
</style>
HTML;

if (str_contains($c, 'master-horizontal-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ public.blade.php: Master Horizontal Fix injected\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
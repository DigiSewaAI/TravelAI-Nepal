<?php
/**
 * FIX — ArtCraft Artist Card (Portrait Mobile Only)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — ARTCRAFT ARTIST CARD (PORTRAIT MOBILE)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/provider-art-craft.blade.php';
if (!file_exists($file)) die("❌ ArtCraft layout not found!\n");

copy($file, $file . '.bak_artist_card');
$c = file_get_contents($file);

// Remove previous (irrelevant) fix if it exists
$c = preg_replace('/<!-- ARTCRAFT WIDGET COMPACT FIX.*?<\/style>/s', '', $c);

$cssBlock = <<<'HTML'

<!-- ARTCRAFT ARTIST CARD MOBILE FIX -->
<style id="artcraft-artist-card-fix">
@media (max-width: 767px) and (orientation: portrait) {
    /* Target the actual artist-card element */
    .artist-card {
        padding: 10px !important;
        margin-top: 10px !important;
        max-height: 200px !important;
        overflow-y: auto !important;
        border-radius: 12px !important;
    }

    /* Compact avatar */
    .artist-card .artist-avatar,
    .artist-avatar {
        width: 40px !important;
        height: 40px !important;
        font-size: 16px !important;
        margin: 0 auto 6px !important;
    }

    /* Compact text inside */
    .artist-card h1, .artist-card h2, .artist-card h3,
    .artist-card p, .artist-card span {
        font-size: 12px !important;
        line-height: 1.3 !important;
        margin: 2px 0 !important;
    }
    .artist-card .u-name, .artist-card .u-role {
        font-size: 11px !important;
    }
}
</style>
HTML;

if (str_contains($c, 'artcraft-artist-card-fix')) {
    die("⏭ Already fixed.\n");
}

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ ArtCraft Artist Card Fix injected successfully.\n";
} else {
    echo "  ⚠️ </head> not found.\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
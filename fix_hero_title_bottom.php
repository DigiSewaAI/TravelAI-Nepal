<?php
/**
 * FIX — Hero Title Break (Append to Bottom)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — HERO TITLE (APPEND TO FILE END)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/home.blade.php';
if (!file_exists($file)) die("❌ home.blade.php not found\n");

copy($file, $file . '.bak_hero_bottom');
$c = file_get_contents($file);

$cssBlock = <<<'HTML'

<!-- HERO TITLE EXACT FIX (iPad Mini & Landscape) -->
<style id="hero-title-exact-fix">
/* 1. iPad Mini Portrait (768px - 820px) */
@media (min-width: 768px) and (max-width: 820px) {
    section#home h1 {
        font-size: 34px !important; /* Reduce from 60px to fit */
    }
    section#home h1 span {
        white-space: nowrap !important; /* Force 'Nepal Journey' together */
    }
}

/* 2. Landscape Devices (Pixel, Pro Max, iPhone, SE) */
@media (orientation: landscape) and (max-height: 500px) {
    section#home h1 {
        font-size: 26px !important; /* Smaller to fit 2 lines */
    }
    section#home h1 span {
        white-space: nowrap !important; /* Force 'Nepal Journey' together */
    }
}
</style>
HTML;

if (str_contains($c, 'hero-title-exact-fix')) {
    die("⏭ Already fixed.\n");
}

// Just append to the very end of the file
$c .= "\n" . $cssBlock . "\n";
file_put_contents($file, $c);
echo "  ✅ Hero Title Fix appended to the end of home.blade.php\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
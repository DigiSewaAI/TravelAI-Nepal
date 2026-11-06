<?php
/**
 * FIX — Mobile Cart Icon & Landscape Dropdowns
 * Target: resources/views/layouts/public.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — MOBILE CART & LANDSCAPE\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/public.blade.php';
if (!file_exists($file)) die("❌ public.blade.php not found\n");

copy($file, $file . '.bak_cart_landscape');
$c = file_get_contents($file);

// 1. Add Mobile Cart Icon
$mobileCartHtml = <<<'HTML'

<!-- 🛒 Mobile Cart Icon -->
<a href="{{ route('cart.index') }}" class="relative flex-shrink-0 text-gray-700 hover:text-blue-600 transition-colors p-2">
    <i class="fas fa-shopping-cart text-lg"></i>
    @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
    @if($cartCount > 0)
        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">
            {{ $cartCount }}
        </span>
    @endif
</a>
HTML;

// Find the mobile header div and inject the cart
$mobileHeaderTag = '<div class="flex lg:hidden items-center gap-1">';
if (str_contains($c, $mobileHeaderTag) && !str_contains($c, 'Mobile Cart Icon')) {
    $c = str_replace($mobileHeaderTag, $mobileHeaderTag . "\n" . $mobileCartHtml, $c);
    echo "  ✅ Mobile Cart Icon injected\n";
} else {
    echo "  ⏭ Mobile Cart already exists or header tag not found\n";
}

// 2. Add Landscape CSS Fix (Universal)
$cssBlock = <<<'HTML'

<!-- LANDSCAPE MOBILE FIX -->
<style id="landscape-universal-fix">
@media (orientation: landscape) and (max-height: 500px) {
    /* Fix Hamburger / Navbar Collapse overflow */
    #mobileMenu, .mobile-menu, .navbar-collapse, .offcanvas, .sidebar {
        max-height: 85vh !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
    
    /* Fix Dropdowns (Profile, etc.) overflow */
    .dropdown-menu, .tv2-dropdown-menu, [class*="dropdown-content"], [class*="menu-panel"] {
        max-height: 85vh !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        z-index: 99999 !important;
    }
}
</style>
HTML;

if (str_contains($c, '</head>') && !str_contains($c, 'landscape-universal-fix')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    echo "  ✅ Landscape Universal CSS injected\n";
}

file_put_contents($file, $c);

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
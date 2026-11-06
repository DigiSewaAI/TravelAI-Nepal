<?php
/**
 * FIX — Traveler V2: Cart Icon & Landscape Dropdowns
 * Target: resources/views/layouts/traveler-v2.blade.php
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — TRAVELER V2 (CART & LANDSCAPE)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
if (!file_exists($file)) die("❌ traveler-v2.blade.php not found\n");

copy($file, $file . '.bak_tv2_final');
$c = file_get_contents($file);

// ═══════════════════════════════════════════════════════
// 1. INSERT CART ICON (Before Profile Dropdown)
// ═══════════════════════════════════════════════════════
$cartHtml = <<<'HTML'

                <!-- 🛒 Cart Icon -->
                <a href="{{ route('cart.index') }}" class="tv2-header-btn" style="position:relative; display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; text-decoration:none;">
                    <i class="fas fa-shopping-cart" style="font-size: 15px;"></i>
                    @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                    @if($cartCount > 0)
                        <span style="position:absolute; top:-2px; right:-2px; background:#ef4444; color:#fff; border-radius:50%; width:16px; height:16px; font-size:10px; font-weight:bold; display:flex; align-items:center; justify-content:center;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                {{-- Profile --}}
HTML;

if (!str_contains($c, 'fa-shopping-cart')) {
    if (str_contains($c, '{{-- Profile --}}')) {
        $c = str_replace('{{-- Profile --}}', $cartHtml, $c);
        echo "  ✅ Cart Icon injected before Profile\n";
    } else {
        echo "  ⚠️ '{{-- Profile --}}' anchor not found\n";
    }
} else {
    echo "  ⏭ Cart Icon already exists\n";
}

// ═══════════════════════════════════════════════════════
// 2. LANDSCAPE DROPDOWN & HEADER FIX
// ═══════════════════════════════════════════════════════
$cssBlock = <<<'HTML'

<!-- TV2 LANDSCAPE FINAL FIX -->
<style id="tv2-landscape-fix">
@media (orientation: landscape) and (max-height: 500px) {
    /* 1. Header - shrink to fit all items in one row */
    header, .tv2-header {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        padding: 4px 10px !important;
        min-height: 48px !important;
    }

    .tv2-header-btn {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }

    /* Hide the "Home" text, keep the icon */
    .tv2-header-btn span[style*="font-size:13px"] {
        display: none !important;
    }

    /* 2. Dropdowns - Center on screen so they NEVER get cut off */
    .tv2-dropdown-menu {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        max-height: 85vh !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        z-index: 999999 !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
        width: 90vw !important;
        max-width: 300px !important;
    }
}
</style>
HTML;

// Remove old broken landscape fix if it exists
$c = preg_replace('/<!-- LANDSCAPE DROPDOWN & SCROLL FIX -->.*?<\/style>/s', '', $c);

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    echo "  ✅ Landscape CSS injected (Centered Dropdowns)\n";
} else {
    echo "  ⚠️ </head> not found\n";
}

file_put_contents($file, $c);

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
echo "  php artisan config:clear\n";
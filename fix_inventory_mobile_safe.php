<?php
/**
 * FIX — Inventory Mobile Safe Fix (No horizontal scroll + Button spacing)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — INVENTORY MOBILE SAFE FIX\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Find the Product List view
$viewFile = null;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views/provider/inventory'));
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, 'Product List') || str_contains($content, 'class="tbl"')) {
            $viewFile = $file->getPathname();
            break;
        }
    }
}

if (!$viewFile) die("❌ View not found.\n");
echo "📄 Found View: " . str_replace('\\', '/', $viewFile) . "\n";

// 2. Find the Layout
$layoutFile = 'resources/views/layouts/provider-inventory.blade.php';
if (!file_exists($layoutFile)) die("❌ Layout not found.\n");
echo "📄 Found Layout: " . $layoutFile . "\n\n";

$cssBlock = <<<'HTML'

<!-- INVENTORY MOBILE SAFE FIX -->
<style id="inventory-mobile-safe-fix">
@media (max-width: 767px) {
    /* 1. Stop the whole page from scrolling horizontally */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    .main {
        max-width: 100vw !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
        padding: 12px !important; /* Reduce padding to give table more room */
    }

    /* 2. Table: Allow horizontal scroll ONLY inside the table wrapper */
    .tbl-wrap {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        border-radius: 8px !important;
    }
    .tbl {
        min-width: 600px !important; /* Force table to be wider than screen so it scrolls inside */
    }

    /* 3. Action Buttons: Use flexbox to wrap if needed (NO nowrap) */
    .tbl td:last-child {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
        padding: 10px !important;
    }
    .act-btn {
        padding: 6px 10px !important;
        font-size: 11px !important;
        margin: 0 !important; /* Flexbox gap handles spacing */
    }
    
    /* 4. Header Spacing */
    .topbar h2 { margin-bottom: 4px !important; }
    .topbar p { margin-top: 0 !important; font-size: 12px !important; }
}
</style>
HTML;

// 3. Inject into View
copy($viewFile, $viewFile . '.bak_inv_safe');
$c = file_get_contents($viewFile);

if (!str_contains($c, 'inventory-mobile-safe-fix')) {
    if (str_contains($c, '@endsection')) {
        $c = str_replace('@endsection', $cssBlock . "\n@endsection", $c);
    } else if (str_contains($c, '</head>')) {
        $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
    } else {
        $c .= "\n" . $cssBlock;
    }
    file_put_contents($viewFile, $c);
    echo "  ✅ Injected Safe Fix into View.\n";
}

// 4. Inject global body overflow fix into Layout (just in case)
copy($layoutFile, $layoutFile . '.bak_inv_safe');
$lc = file_get_contents($layoutFile);
$globalCss = <<<'HTML'
<style id="global-overflow-guard">
html, body { overflow-x: hidden !important; max-width: 100vw !important; }
</style>
HTML;

if (!str_contains($lc, 'global-overflow-guard')) {
    if (str_contains($lc, '</head>')) {
        $lc = str_replace('</head>', $globalCss . "\n</head>", $lc);
        file_put_contents($layoutFile, $lc);
        echo "  ✅ Injected Global Overflow Guard into Layout.\n";
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
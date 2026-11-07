<?php
/**
 * FIX — Card Bottom Padding (Walk up to find card, add padding)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — CARD BOTTOM PADDING\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/traveler-v2.blade.php';
if (!file_exists($file)) die("❌ Not found!\n");

copy($file, $file . '.bak_padding');
$c = file_get_contents($file);

// Purge all previous scripts
$c = preg_replace('/<!-- TREK CARD.*?<\/script>/s', '', $c);

$fixBlock = <<<'HTML'

<!-- CARD BOTTOM PADDING FIX -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (window.innerWidth > 767) return;
    
    const allButtons = document.querySelectorAll('a, button, span');
    
    allButtons.forEach(el => {
        const text = (el.textContent || '').trim().toLowerCase();
        
        if (text === 'confirmed' || text === 'view' || text.includes('confirmed') || text.includes('view')) {
            
            // 1. KEEP WORKING HORIZONTAL FIX (No wrap, no squeeze)
            el.style.setProperty('white-space', 'nowrap', 'important');
            el.style.setProperty('flex-shrink', '0', 'important');
            el.style.setProperty('display', 'inline-block', 'important');
            el.style.setProperty('font-size', '11px', 'important');
            el.style.setProperty('padding', '4px 6px', 'important');
            
            // 2. GAP between Confirmed and View (Working)
            if (text === 'confirmed' || text.includes('confirmed')) {
                el.style.setProperty('margin-right', '10px', 'important');
            }
            
            // 3. THE MAIN FIX: Walk up, find the CARD, add padding-bottom
            let ancestor = el.parentElement;
            let depth = 0;
            
            while (ancestor && depth < 5) {
                const style = window.getComputedStyle(ancestor);
                
                // Detect card by border-radius, box-shadow, or border
                const hasRadius = parseFloat(style.borderRadius) > 0;
                const hasShadow = style.boxShadow !== 'none';
                const hasBorder = parseFloat(style.borderWidth) > 0;
                const hasBg = style.backgroundColor !== 'rgba(0, 0, 0, 0)' && 
                             style.backgroundColor !== 'transparent';
                
                // Stop at the card
                if ((hasRadius && (hasShadow || hasBg)) || (hasShadow && hasBorder)) {
                    ancestor.style.setProperty('padding-bottom', '18px', 'important');
                    ancestor.style.setProperty('box-sizing', 'border-box', 'important');
                    console.log('[Card Fix] Found card at depth ' + depth + ': ' + (ancestor.className || ancestor.tagName));
                    break;
                }
                
                ancestor = ancestor.parentElement;
                depth++;
            }
        }
    });
});
</script>
HTML;

$c = str_replace('</body>', $fixBlock . "\n</body>", $c);
file_put_contents($file, $c);
echo "  ✅ Card Bottom Padding Fix injected.\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
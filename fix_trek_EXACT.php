<?php
/**
 * Trek Provider EXACT FIX (Based on true HTML)
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — TREK PROVIDER EXACT (HTML Based)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/provider.blade.php';
copy($file, $file . '.bak_exact_html');

$c = file_get_contents($file);

// Purge ALL previous injected styles and scripts completely
$c = preg_replace('/<!-- TREK PROVIDER.*?-->/s', '', $c);
$c = preg_replace('/<style id="trek.*?">.*?<\/style>/s', '', $c);
$c = preg_replace('/<script>\s*\/\/ TREK MOBILE.*?<\/script>/s', '', $c);
$c = preg_replace('/<script>\s*document\.addEventListener\("DOMContentLoaded".*?<\/script>/s', '', $c);

$inject = <<<'HTML'

    <!-- TREK PROVIDER EXACT FIX -->
    <style id="trek-exact-css">
    @media (max-width: 767px) {
        /* 1. Hide the duplicate logo in header (lg:hidden class) */
        header img.lg\:hidden { 
            display: none !important; 
        }

        /* 2. Hide the original Home button (href is localhost:8000) */
        header a[href="http://localhost:8000"] { 
            display: none !important; 
        }

        /* 3. Footer Fix */
        footer {
            display: flex !important; flex-direction: column !important;
            align-items: center !important; gap: 6px !important; padding: 16px !important;
        }
        footer * { text-align: center !important; }
        footer span:last-child { font-size: 11px !important; opacity: 0.8 !important; }

        /* 4. Dropdown Fix (Now that Bell will be on Right, dropdown opens Left) */
        #providerNotifPanel {
            position: absolute !important;
            right: 0 !important;
            left: auto !important;
            top: calc(100% + 8px) !important;
            width: 90vw !important;
            max-width: 340px !important;
            z-index: 999999 !important;
            box-sizing: border-box !important;
        }
    }
    </style>

    <script>
    // TREK MOBILE EXACT FIX
    (function() {
        function applyFix() {
            if (window.innerWidth > 767) return;
            
            let header = document.querySelector('header');
            if (!header) return;

            // 1. Clone Home Button inside Main Content
            let homeBtn = header.querySelector('a[href="http://localhost:8000"]');
            if (homeBtn) {
                let main = document.querySelector('main');
                if (main && !main.querySelector('.mobile-home-btn-clone')) {
                    let clone = homeBtn.cloneNode(true);
                    clone.classList.add('mobile-home-btn-clone');
                    clone.style.display = 'inline-flex';
                    clone.style.marginBottom = '16px';
                    main.insertBefore(clone, main.firstChild);
                }
            }

            // 2. THE SWAP: Find Bell and Profile by Exact IDs
            let bell = document.getElementById('providerNotifWrapper');
            let profile = document.getElementById('userDropdown')?.parentElement; // The wrapper div
            
            // The container that holds Bell, Home, Profile
            let rightContainer = bell?.parentElement; 

            if (bell && profile && rightContainer) {
                // Check if already swapped (prevent loop)
                if (rightContainer.firstElementChild !== profile) {
                    // PROFILE goes to the far LEFT
                    rightContainer.prepend(profile);
                    profile.style.marginRight = 'auto'; // Pushes everything else to the right

                    // BELL goes to the far RIGHT
                    rightContainer.append(bell);
                    bell.style.marginRight = '0';
                }
            }
        }

        // Run immediately
        applyFix();
        document.addEventListener("DOMContentLoaded", applyFix);
        
        // Loop to catch framework resets (fixes the 1-second flash)
        let interval = setInterval(applyFix, 50); 
        setTimeout(() => clearInterval(interval), 3000); 
    })();
    </script>
HTML;

if (str_contains($c, '</head>')) {
    $c = str_replace('</head>', $inject . "\n</head>", $c);
    file_put_contents($file, $c);
    echo "  ✅ provider.blade.php: EXACT HTML Fix injected\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
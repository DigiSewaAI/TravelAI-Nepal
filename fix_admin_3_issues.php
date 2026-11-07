<?php
/**
 * FIX — Admin Mobile: Profile Dropdown + Notification Center + Sidebar Width
 */
echo "═══════════════════════════════════════════════════════\n";
echo "  FIX — ADMIN 3 ISSUES\n";
echo "═══════════════════════════════════════════════════════\n\n";

$file = 'resources/views/layouts/admin.blade.php';
if (!file_exists($file)) die("❌ Admin layout not found!\n");

copy($file, $file . '.bak_admin_3fix');
$c = file_get_contents($file);

// ═══════════════════════════════════════════════════════
// 1. Replace Static Profile Span with Dropdown
// ═══════════════════════════════════════════════════════
$oldProfile = <<<'HTML'
        <span class="text-sm text-gray-600">
            <i class="fas fa-user-circle text-lg mr-1"></i>
            {{ Auth::user()->name ?? 'Admin' }}
        </span>
HTML;

$newProfile = <<<'HTML'
        <div class="relative" id="adminProfileWrapper">
            <button type="button" id="adminProfileToggle" class="flex items-center gap-2 text-sm text-gray-600 focus:outline-none">
                <i class="fas fa-user-circle text-lg"></i>
                <span class="hidden sm:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
            </button>
            <div id="adminProfilePanel" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                <div class="px-4 py-3 border-b">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
HTML;

$c = str_replace($oldProfile, $newProfile, $c);

// ═══════════════════════════════════════════════════════
// 2. Add CSS to Center Notification + Shrink Sidebar
// ═══════════════════════════════════════════════════════
$cssBlock = <<<'HTML'

<!-- ADMIN 3 ISSUES FIX -->
<style id="admin-3-issues-fix">
@media (max-width: 1023px) {
    /* 1. Shrink Sidebar Width */
    #adminSidebar {
        width: 220px !important; /* Reduced from 256px (w-64) */
    }
    #adminSidebar .sidebar-link {
        font-size: 13px !important;
        padding: 8px 10px !important;
    }

    /* 2. Notification Panel - Center on screen (No left cut-off) */
    #adminNotifPanel {
        position: fixed !important;
        top: 65px !important;
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
        width: 90vw !important;
        max-width: 340px !important;
        z-index: 99999 !important;
        box-sizing: border-box !important;
    }
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileToggle = document.getElementById('adminProfileToggle');
        const profilePanel = document.getElementById('adminProfilePanel');
        const profileWrapper = document.getElementById('adminProfileWrapper');

        if (profileToggle && profilePanel) {
            profileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                profilePanel.classList.toggle('hidden');
                // Close notification if open
                const notifPanel = document.getElementById('adminNotifPanel');
                if (notifPanel) notifPanel.classList.add('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!profileWrapper.contains(e.target)) {
                    profilePanel.classList.add('hidden');
                }
            });
        }
    });
</script>
HTML;

if (!str_contains($c, 'admin-3-issues-fix')) {
    $c = str_replace('</head>', $cssBlock . "\n</head>", $c);
}

file_put_contents($file, $c);
echo "  ✅ Admin 3 Issues Fix injected successfully.\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ FIX COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";
echo "\n📌 Next:\n";
echo "  php artisan view:clear\n";
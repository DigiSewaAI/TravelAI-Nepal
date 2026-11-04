<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Traveler Dashboard | TravelAI Nepal')</title>

    {{-- CDN assets --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* ═══ Header ═══ */
        .tv2-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            height: 68px;
        }
        .tv2-header-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 16px;
            color: #111827;
            text-decoration: none;
            white-space: nowrap;
        }
        .tv2-header-logo i {
            color: #2563eb;
            font-size: 24px;
        }
        .tv2-header-search {
            flex: 1;
            max-width: 480px;
            position: relative;
        }
        .tv2-header-search input {
            width: 100%;
            padding: 9px 16px 9px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s;
        }
        .tv2-header-search input:focus {
            border-color: #2563eb;
            background: #fff;
        }
        .tv2-header-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }
        .tv2-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        .tv2-header-btn {
            position: relative;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s;
        }
        .tv2-header-btn:hover { background: #f9fafb; border-color: #d1d5db; }
        .tv2-header-btn .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #dc2626;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
        }
        .tv2-profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        /* ═══ Dropdowns ═══ */
        .tv2-dropdown {
            position: relative;
        }
        .tv2-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-width: 200px;
            padding: 6px;
            z-index: 60;
        }
        .tv2-dropdown-menu.is-open { display: block; }
        .tv2-dropdown-item {
            display: block;
            padding: 8px 12px;
            font-size: 13px;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .tv2-dropdown-item:hover { background: #f3f4f6; }

        /* ═══ Dashboard wrapper ═══ */
        .tv2-dashboard-wrap {
            display: flex;
            min-height: calc(100vh - 68px);
        }

        /* ═══ Sidebar ═══ */
        .tv2-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 20px 14px;
            overflow-y: auto;
            height: calc(100vh - 68px);
            position: sticky;
            top: 68px;
        }
        .tv2-sidebar-section { margin-bottom: 20px; }
        .tv2-sidebar-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #9ca3af;
            padding: 0 10px 8px;
        }
        .tv2-sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            color: #374151;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .tv2-sidebar-link:hover { background: #f3f4f6; color: #111827; }
        .tv2-sidebar-link.is-active { background: #2563eb; color: #fff; font-weight: 600; }
        .tv2-sidebar-link.is-active:hover { background: #1d4ed8; }
        .tv2-sidebar-link i { width: 16px; text-align: center; font-size: 14px; }
        .tv2-sidebar-link.is-danger { color: #dc2626; }
        .tv2-sidebar-link.is-danger:hover { background: #fef2f2; }

        /* ═══ Main ═══ */
        .tv2-main {
            flex: 1;
            min-width: 0;
            padding: 24px;
        }

        /* ═══ Footer ═══ */
        .tv2-footer {
            background: #111827;
            color: #9ca3af;
            padding: 20px 24px;
            font-size: 13px;
            text-align: center;
        }

        /* ═══ Mobile ═══ */
        .tv2-hamburger {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 60;
            width: 52px;
            height: 52px;
            border-radius: 999px;
            background: #2563eb;
            color: #fff;
            border: 0;
            box-shadow: 0 8px 20px rgba(37,99,235,0.4);
            cursor: pointer;
            font-size: 20px;
        }
        .tv2-drawer-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 70;
        }
        .tv2-drawer-backdrop.is-open { display: block; }
        .tv2-drawer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: #fff;
            z-index: 80;
            padding: 20px 14px;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.25s;
        }
        .tv2-drawer.is-open { transform: translateX(0); }
        .tv2-drawer-close {
            background: none;
            border: 0;
            font-size: 22px;
            color: #6b7280;
            cursor: pointer;
            margin-bottom: 12px;
        }

        @media (max-width: 1023px) {
            .tv2-sidebar { display: none; }
            .tv2-hamburger { display: flex; align-items: center; justify-content: center; }
            .tv2-main { padding: 16px; }
            .tv2-header-search { display: none; }
            .tv2-header-btn span:not(.badge) { display: none; }
            .tv2-drawer { display: block; }
        }
    </style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<header class="tv2-header">
    <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-header-logo">
        <img src="{{ asset('images/logo.png') }}" alt="TravelAI Nepal" style="height: 36px; width: auto; object-fit: contain;">
    </a>

    <form action="{{ route('public.services.index') }}" method="GET" class="tv2-header-search">
        <i class="fas fa-search"></i>
        <input type="text" name="q" placeholder="{{ __('messages.search_destinations') ?? 'Search destinations, services...' }}" value="{{ request('q') }}">
    </form>

    <div class="tv2-header-actions">
        {{-- Notification bell --}}
        <div class="tv2-dropdown">
            <button type="button" class="tv2-header-btn" id="tv2BellBtn">
                <i class="far fa-bell"></i>
                @if(($notifCount ?? 0) > 0)
                    <span class="badge">{{ $notifCount }}</span>
                @endif
            </button>
            <div class="tv2-dropdown-menu" id="tv2BellMenu">
                <div style="padding: 10px; text-align: center; font-size: 13px; color: #6b7280;">
                    {{ __('messages.loading') ?? 'Loading...' }}
                </div>
            </div>
        </div>

        {{-- Language --}}
        <div class="tv2-dropdown">
            <button type="button" class="tv2-header-btn" id="tv2LangBtn">
                <i class="fas fa-globe"></i>
                <span>{{ strtoupper($currentLocale ?? 'en') }}</span>
            </button>
            <div class="tv2-dropdown-menu" id="tv2LangMenu">
                <a href="/lang/en" class="tv2-dropdown-item">🇬🇧 English</a>
                <a href="/lang/np" class="tv2-dropdown-item">🇳🇵 नेपाली</a>
                <a href="/lang/hi" class="tv2-dropdown-item">🇮🇳 हिन्दी</a>
                <a href="/lang/zh" class="tv2-dropdown-item">🇨🇳 中文</a>
                <a href="/lang/ru" class="tv2-dropdown-item">🇷🇺 Русский</a>
            </div>
        </div>

        {{-- Currency --}}
        <div class="tv2-dropdown">
            <button type="button" class="tv2-header-btn" id="tv2CurrencyBtn">
                <i class="fas fa-dollar-sign"></i>
                <span>{{ $currentCurrency ?? 'NPR' }}</span>
            </button>
            <div class="tv2-dropdown-menu" id="tv2CurrencyMenu">
                <span class="tv2-dropdown-item" style="cursor:default; color:#9ca3af; font-size:11px;">Currency is session-based</span>
            </div>
        </div>

        {{-- Profile --}}
        <div class="tv2-dropdown">
            <button type="button" class="tv2-header-btn" id="tv2ProfileBtn" style="padding: 4px 8px 4px 4px;">
                <div class="tv2-profile-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 1)) }}
                </div>
                <span style="display:none sm:inline">{{ auth()->user()->name ?? 'Traveler' }}</span>
            </button>
            <div class="tv2-dropdown-menu" id="tv2ProfileMenu">
                <div style="padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #6b7280;">
                    {{ auth()->user()->email ?? '' }}
                </div>
                <a href="{{ route('traveler.dashboard') }}" class="tv2-dropdown-item">
                    <i class="fas fa-gauge-high" style="width:14px"></i> {{ __('messages.nav_dashboard') ?? 'Dashboard' }}
                </a>
                <a href="{{ route('traveler.my-bookings') }}" class="tv2-dropdown-item">
                    <i class="fas fa-calendar-check" style="width:14px"></i> {{ __('messages.nav_my_bookings') ?? 'My Bookings' }}
                </a>
                <a href="{{ route('orders.index') }}" class="tv2-dropdown-item">
                    <i class="fas fa-shopping-bag" style="width:14px"></i> {{ __('messages.nav_my_orders') ?? 'My Orders' }}
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="tv2-dropdown-item" style="width:100%; text-align:left; background:none; border:0; color:#dc2626; cursor:pointer;">
                        <i class="fas fa-sign-out-alt" style="width:14px"></i> {{ __('messages.logout') ?? 'Logout' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- ═══ DASHBOARD WRAPPER ═══ --}}
<div class="tv2-dashboard-wrap">

    {{-- ═══ SIDEBAR (Desktop) ═══ --}}
    <aside class="tv2-sidebar">
        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_main') ?? 'Main' }}</div>
            <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-sidebar-link {{ request()->routeIs('traveler.dashboard-v2') ? 'is-active' : '' }}">
                <i class="fas fa-house"></i><span>{{ __('messages.nav_dashboard') ?? 'Dashboard' }}</span>
            </a>
            <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-sidebar-link">
                <i class="fas fa-suitcase-rolling"></i><span>{{ __('messages.nav_my_trips') ?? 'My Trips' }}</span>
            </a>
            <a href="{{ url('/') }}#ai-planner" class="tv2-sidebar-link">
                <i class="fas fa-robot"></i><span>{{ __('messages.nav_ai_planner') ?? 'AI Planner' }}</span>
            </a>
            <a href="#exchange-rates" class="tv2-sidebar-link">
                <i class="fas fa-money-bill-transfer"></i><span>{{ __('messages.nav_exchange_rates') ?? 'Exchange Rates' }}</span>
            </a>
            <a href="#memories" class="tv2-sidebar-link">
                <i class="fas fa-images"></i><span>{{ __('messages.nav_travel_memories') ?? 'Travel Memories' }}</span>
            </a>
            <a href="{{ route('traveler.journey-replay') }}" class="tv2-sidebar-link">
                <i class="fas fa-clock-rotate-left"></i><span>{{ __('messages.nav_journey_replay') ?? 'Journey Replay' }}</span>
            </a>
            <a href="{{ route('safety.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-shield-halved"></i><span>{{ __('messages.nav_safety_center') ?? 'Safety Center' }}</span>
            </a>
            <a href="#sos" class="tv2-sidebar-link is-danger">
                <i class="fas fa-circle-exclamation"></i><span>{{ __('messages.nav_emergency_sos') ?? 'Emergency SOS' }}</span>
            </a>
        </div>

        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_marketplace') ?? 'Marketplace' }}</div>
            <a href="{{ route('public.shop.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-bag-shopping"></i><span>{{ __('messages.nav_shop') ?? 'Shop' }}</span>
            </a>
            <a href="{{ route('public.rental.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-car"></i><span>{{ __('messages.nav_rental') ?? 'Rental' }}</span>
            </a>
            <a href="{{ route('public.wholesale.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-boxes-stacked"></i><span>{{ __('messages.nav_wholesale') ?? 'Wholesale' }}</span>
            </a>
            <a href="{{ route('pages.pricing') }}" class="tv2-sidebar-link">
                <i class="fas fa-tag"></i><span>{{ __('messages.nav_pricing') ?? 'Pricing' }}</span>
            </a>
        </div>

        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_company') ?? 'Company' }}</div>
            <a href="{{ route('pages.about') }}" class="tv2-sidebar-link">
                <i class="fas fa-circle-info"></i><span>{{ __('messages.nav_about') ?? 'About' }}</span>
            </a>
            <a href="{{ route('pages.contact') }}" class="tv2-sidebar-link">
                <i class="fas fa-envelope"></i><span>{{ __('messages.nav_contact') ?? 'Contact' }}</span>
            </a>
        </div>

        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_account') ?? 'My Account' }}</div>
            <a href="{{ route('traveler.my-bookings') }}" class="tv2-sidebar-link">
                <i class="fas fa-calendar-check"></i><span>{{ __('messages.nav_my_bookings') ?? 'My Bookings' }}</span>
            </a>
            <a href="{{ route('orders.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-shopping-bag"></i><span>{{ __('messages.nav_my_orders') ?? 'My Orders' }}</span>
            </a>
        </div>
    </aside>

    {{-- ═══ MAIN ═══ --}}
    <main class="tv2-main">
        @yield('traveler-v2-content')
    </main>
</div>

{{-- ═══ MOBILE DRAWER ═══ --}}
<div class="tv2-drawer-backdrop" id="tv2DrawerBackdrop"></div>
<aside class="tv2-drawer" id="tv2Drawer">
    <button class="tv2-drawer-close" id="tv2DrawerClose">&times;</button>

    <div class="tv2-sidebar-section">
        <div class="tv2-sidebar-label">{{ __('messages.sidebar_main') ?? 'Main' }}</div>
        <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-sidebar-link {{ request()->routeIs('traveler.dashboard-v2') ? 'is-active' : '' }}">
            <i class="fas fa-house"></i><span>{{ __('messages.nav_dashboard') ?? 'Dashboard' }}</span>
        </a>
        <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-sidebar-link">
            <i class="fas fa-suitcase-rolling"></i><span>{{ __('messages.nav_my_trips') ?? 'My Trips' }}</span>
        </a>
        <a href="{{ route('traveler.journey-replay') }}" class="tv2-sidebar-link">
            <i class="fas fa-clock-rotate-left"></i><span>{{ __('messages.nav_journey_replay') ?? 'Journey Replay' }}</span>
        </a>
        <a href="{{ route('safety.index') }}" class="tv2-sidebar-link">
            <i class="fas fa-shield-halved"></i><span>{{ __('messages.nav_safety_center') ?? 'Safety Center' }}</span>
        </a>
        <a href="#sos" class="tv2-sidebar-link is-danger">
            <i class="fas fa-circle-exclamation"></i><span>{{ __('messages.nav_emergency_sos') ?? 'Emergency SOS' }}</span>
        </a>
    </div>

    <div class="tv2-sidebar-section">
        <div class="tv2-sidebar-label">{{ __('messages.sidebar_marketplace') ?? 'Marketplace' }}</div>
        <a href="{{ route('public.shop.index') }}" class="tv2-sidebar-link"><i class="fas fa-bag-shopping"></i><span>{{ __('messages.nav_shop') ?? 'Shop' }}</span></a>
        <a href="{{ route('public.rental.index') }}" class="tv2-sidebar-link"><i class="fas fa-car"></i><span>{{ __('messages.nav_rental') ?? 'Rental' }}</span></a>
        <a href="{{ route('public.wholesale.index') }}" class="tv2-sidebar-link"><i class="fas fa-boxes-stacked"></i><span>{{ __('messages.nav_wholesale') ?? 'Wholesale' }}</span></a>
        <a href="{{ route('pages.pricing') }}" class="tv2-sidebar-link"><i class="fas fa-tag"></i><span>{{ __('messages.nav_pricing') ?? 'Pricing' }}</span></a>
    </div>

    <div class="tv2-sidebar-section">
        <div class="tv2-sidebar-label">{{ __('messages.sidebar_account') ?? 'My Account' }}</div>
        <a href="{{ route('traveler.my-bookings') }}" class="tv2-sidebar-link"><i class="fas fa-calendar-check"></i><span>{{ __('messages.nav_my_bookings') ?? 'My Bookings' }}</span></a>
        <a href="{{ route('orders.index') }}" class="tv2-sidebar-link"><i class="fas fa-shopping-bag"></i><span>{{ __('messages.nav_my_orders') ?? 'My Orders' }}</span></a>
    </div>
</aside>

{{-- ═══ HAMBURGER ═══ --}}
<button class="tv2-hamburger" id="tv2Hamburger"><i class="fas fa-bars"></i></button>

{{-- ═══ FOOTER ═══ --}}
<footer class="tv2-footer">
    © {{ date('Y') }} TravelAI Nepal · {{ __('messages.all_rights_reserved') ?? 'All rights reserved' }}
</footer>

{{-- ═══ SCRIPTS ═══ --}}
<script>
(function () {
    // Mobile drawer
    const hamburger = document.getElementById('tv2Hamburger');
    const drawer = document.getElementById('tv2Drawer');
    const backdrop = document.getElementById('tv2DrawerBackdrop');
    const closeBtn = document.getElementById('tv2DrawerClose');

    function openDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.add('is-open');
        backdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

    // Dropdowns
    function setupDropdown(btnId, menuId) {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(menuId);
        if (!btn || !menu) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close others
            document.querySelectorAll('.tv2-dropdown-menu.is-open').forEach(m => {
                if (m !== menu) m.classList.remove('is-open');
            });
            menu.classList.toggle('is-open');

            // Lazy load bell menu
            if (btnId === 'tv2BellBtn' && menu.classList.contains('is-open')) {
                loadBellMenu(menu);
            }
        });
    }

    setupDropdown('tv2BellBtn', 'tv2BellMenu');
    setupDropdown('tv2LangBtn', 'tv2LangMenu');
    setupDropdown('tv2CurrencyBtn', 'tv2CurrencyMenu');
    setupDropdown('tv2ProfileBtn', 'tv2ProfileMenu');

    document.addEventListener('click', function () {
        document.querySelectorAll('.tv2-dropdown-menu.is-open').forEach(m => m.classList.remove('is-open'));
    });

    // Bell fetch
    async function loadBellMenu(menu) {
        if (menu.dataset.loaded === 'true') return;
        menu.innerHTML = '<div style="padding:10px;text-align:center;font-size:13px;color:#6b7280;">Loading...</div>';
        try {
            const res = await fetch('{{ route('traveler.notifications.index') }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('fetch failed');
            const data = await res.json();
            const items = (data.notifications || []).slice(0, 5);
            if (items.length === 0) {
                menu.innerHTML = '<div style="padding:16px;text-align:center;font-size:13px;color:#6b7280;">No notifications</div>';
            } else {
                menu.innerHTML = items.map(n => `
                    <a href="${n.url || '#'}" class="tv2-dropdown-item" style="border-bottom:1px solid #f3f4f6;">
                        <div style="font-weight:600;font-size:12px;">${n.title || 'Alert'}</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">${n.message || ''}</div>
                    </a>
                `).join('') + `
                    <a href="{{ route('traveler.notifications.index') }}" class="tv2-dropdown-item" style="text-align:center;color:#2563eb;font-weight:600;">View all</a>
                `;
            }
            menu.dataset.loaded = 'true';
        } catch (e) {
            menu.innerHTML = '<div style="padding:10px;text-align:center;font-size:13px;color:#dc2626;">Failed to load</div>';
        }
    }
})();
</script>

@stack('scripts')
</body>
</html>
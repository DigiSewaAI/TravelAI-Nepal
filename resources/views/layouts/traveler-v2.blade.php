<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Traveler Dashboard | TravelAI Nepal')
    @yield('meta_description', '')
    @yield('meta_keywords', '')
    @yield('og_meta', '')</title>

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
    
        /* ═══════════════════════════════════════════════════════ */
        /* MOBILE RESPONSIVE FIXES — 2026-11-05 (max-width: 767px) */
        /* ═══════════════════════════════════════════════════════ */
        @media (max-width: 767px) {
            /* Hide arrows + checkmarks on mobile (prevent cramped buttons) */
            .tv2-btn-arrow,
            .tv2-check {
                display: none !important;
            }
            /* ─── 1. GLOBAL: Prevent horizontal scroll ─── */
            html, body {
                overflow-x: hidden !important;
                max-width: 100vw !important;
                width: 100% !important;
            }
            
            .tv2-dashboard-wrap {
                overflow-x: hidden !important;
                max-width: 100% !important;
            }
            
            /* ─── 2. WORD WRAP: Russian text overflow ─── */
            .tv2-dashboard-wrap, .tv2-dashboard-wrap * {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
            }
            
            /* ─── 3. FLEX ITEMS: Allow shrinking ─── */
            .tv2-dashboard-wrap [style*="display: flex"] > * {
                min-width: 0 !important;
            }
            
            /* ─── 4. TOOLS & SHORTCUTS: Force 5-col to auto-fit ─── */
            .tv2-dashboard-wrap [style*="grid-template-columns"][style*="repeat(5"],
            .tv2-dashboard-wrap [style*="grid-template-columns"][style*="repeat(6"],
            .tv2-dashboard-wrap [style*="grid"][style*="minmax(180px"],
            .tv2-dashboard-wrap [style*="grid"][style*="minmax(200px"] {
                grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)) !important;
                gap: 8px !important;
            }
            
            /* Reduce tool card padding on mobile */
            .tv2-dashboard-wrap [style*="grid-template-columns"] > button,
            .tv2-dashboard-wrap [style*="grid-template-columns"] > a {
                padding: 6px 4px !important;
                font-size: 10px !important;
            }
            
            /* ─── 5. WEATHER WIDGET: Shrink on mobile ─── */
            /* Widget: small + anchored top-right */
            .tv2-weather-inline {
                transform: scale(0.5);
                transform-origin: top right;
                margin-top: 5px !important;
                margin-right: -18px !important;
            }
            
            .tv2-weather-inline * {
                font-size: 8px !important;
                line-height: 1 !important;
            }
            
            .tv2-weather-inline img,
            .tv2-weather-inline i {
                width: 14px !important;
                height: 14px !important;
                font-size: 14px !important;
            }
            
            /* Reserve horizontal space for widget — narrow greeting text */
            .tv2-hero h1 {
                font-size: 22px !important;
                line-height: 1.15 !important;
                word-wrap: break-word !important;
            }
            
            .tv2-hero h1 + p {
                font-size: 12px !important;
                line-height: 1.4 !important;
            }
            
            /* Ensure stats + buttons stay full-width */
            .tv2-hero h1 ~ div {
                max-width: 100% !important;
            }
            
            /* ─── 6. HERO ACTIONS: Full width on mobile ─── */
            .tv2-dashboard-wrap a[href*="#ai-planner"],
            .tv2-dashboard-wrap a[href*="services"] {
                flex: 1 1 100% !important;
                max-width: 100% !important;
                padding: 10px 14px !important;
                font-size: 13px !important;
                justify-content: center !important;
            }
            
            /* Hero stats badges */
            .tv2-dashboard-wrap [style*="display: flex"][style*="gap: 12px"] {
                flex-wrap: wrap !important;
            }
            
            /* ─── 7. CARD IMAGES: Reduce height ─── */
            .tv2-dashboard-wrap [style*="height: 260px"] {
                height: 200px !important;
            }
            
            /* ─── 8. SOS BUTTON: Full width ─── */
            .tv2-dashboard-wrap button[onclick*="SOS"],
            .tv2-dashboard-wrap button[onclick*="sos"],
            .tv2-dashboard-wrap a[href*="sos"] {
                width: 100% !important;
            }
            
            /* ─── 9. GRID LAYOUTS: Stack on mobile ─── */
            .tv2-dashboard-wrap [style*="grid-template-columns: repeat(auto-fill"] {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
            }
            
            .tv2-dashboard-wrap [style*="grid-template-columns: repeat(auto-fit"] {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
            }
        }
        
        /* ─── EXTRA SMALL (< 480px) ─── */
        @media (max-width: 480px) {
            .tv2-dashboard-wrap [style*="grid"][style*="minmax(180px"] {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
            }
            
            .tv2-weather-inline {
                transform: scale(0.42);
                margin-top: 4px !important;
                margin-right: -20px !important;
            }
        }
    
        /* ═══ ROUND 1: Greeting line layout ═══ */
        .tv2-greeting-line {
            display: inline;
        }
        .tv2-name-line {
            display: inline;
        }

        @media (max-width: 767px) {
            .tv2-hero h1 {
                word-break: keep-all !important;
                overflow-wrap: normal !important;
            }
            .tv2-greeting-line {
                display: block !important;
            }
            .tv2-name-line {
                display: block !important;
                margin-top: 4px;
            }
        }
    
        /* ROUND2_HIGH_SPEC — override 70px rule for claim cards */
        @media (max-width: 767px) {
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
            }
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] > div {
                padding: 12px 6px !important;
                min-width: 0 !important;
                overflow: hidden !important;
                word-break: normal !important;
            }
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] > div > div:first-child {
                font-size: 22px !important;
                margin-bottom: 4px !important;
            }
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] > div > div:nth-child(2) {
                font-size: 12px !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
            }
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] button,
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] a {
                font-size: 10px !important;
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                line-height: 1.2 !important;
                max-width: 100% !important;
                display: inline-block !important;
            }
        }
    
        /* ROUND2_V5 — Fix long category names (MoneyChanger) */
        @media (max-width: 767px) {
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] > div > div:nth-child(2) {
                font-size: 10px !important;
                letter-spacing: -0.4px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                word-break: normal !important;
                overflow-wrap: normal !important;
                max-width: 100% !important;
                display: block !important;
                margin: 0 auto !important;
            }
            /* "Contact Provider" text for unclaimable — same fix */
            .tv2-dashboard-wrap div[style*="minmax(200px, 1fr)"] > div > div:nth-child(3) {
                font-size: 9px !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }
        }
    
        /* ROUND3_PASSPORT — one-line title on mobile */
        @media (max-width: 767px) {
            /* Narrower book + tighter gap = wider title column */
            .tv2-passport-inner {
                grid-template-columns: 1fr 70px !important;
                gap: 10px !important;
                padding: 16px 12px !important;
            }
            .tv2-passport-title {
                gap: 4px !important;
                margin-bottom: 6px !important;
            }
            .tv2-passport-title > span {
                font-size: 16px !important;
                line-height: 1 !important;
            }
            .tv2-passport-title h3 {
                font-size: 13px !important;
                line-height: 1.1 !important;
                white-space: nowrap !important;
                word-break: normal !important;
                overflow-wrap: normal !important;
                letter-spacing: -0.2px !important;
            }
            /* Shrink book — keeps aspect + all internals visible */
            .tv2-passport-book {
                width: 65px !important;
                height: 88px !important;
            }
            .tv2-passport-label,
            .tv2-passport-country,
            .tv2-passport-subtitle {
                font-size: 6px !important;
                letter-spacing: 0.3px !important;
            }
            .tv2-passport-emblem {
                font-size: 14px !important;
            }
            .tv2-passport-cover {
                border-radius: 4px !important;
            }
        }
    
        /* ROUND5_REVIEWS_EXCHANGE — compact stack on mobile */
        @media (max-width: 767px) {
            /* Parent grid — force 1 column + tighter gap */
            .tv2-dashboard-wrap div[style*="minmax(280px, 1fr)"] {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
            }
            /* Cards — compact padding + rounded */
            .tv2-dashboard-wrap div[style*="minmax(280px, 1fr)"] > div[style*="border-radius: 18px"] {
                padding: 14px 16px !important;
                border-radius: 14px !important;
            }
            /* Card titles — one line */
            .tv2-dashboard-wrap div[style*="minmax(280px, 1fr)"] h3 {
                font-size: 13px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                line-height: 1.2 !important;
            }
            /* Body text — compact */
            .tv2-dashboard-wrap div[style*="minmax(280px, 1fr)"] p {
                font-size: 12px !important;
                margin: 0 0 10px 0 !important;
                line-height: 1.4 !important;
            }
            /* Share Experience button */
            .tv2-dashboard-wrap div[style*="minmax(280px, 1fr)"] a[href*="reviews"] {
                padding: 9px 12px !important;
                font-size: 12px !important;
                border-radius: 10px !important;
            }
            /* Exchange rates — title + updated stacked */
            .tv2-dashboard-wrap #exchange-rates > div:first-child {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 4px !important;
                margin-bottom: 12px !important;
            }
            .tv2-dashboard-wrap #exchange-rates > div:first-child > span {
                font-size: 10px !important;
            }
            /* Exchange rate rows — tighter */
            .tv2-dashboard-wrap #exchange-rates > div[style*="flex-direction: column"] {
                gap: 6px !important;
            }
            .tv2-dashboard-wrap #exchange-rates > div[style*="flex-direction: column"] > div {
                font-size: 12px !important;
                padding: 3px 0 !important;
            }
        }
    </style>
    @stack('head')





    <!-- REORDER NOTIFICATION BELL (Mobile Fix) -->
    <style id="tv2-bell-reorder-css">
    @media (max-width: 767px) {
        #tv2BellMenu {
            position: fixed !important;
            top: 60px !important;
            right: 15px !important;
            left: auto !important;
            width: 90vw !important;
            max-width: 340px !important;
            z-index: 999999 !important;
            box-sizing: border-box !important;
        }
    }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Find Notification Bell Wrapper
        let bellMenu = document.getElementById('tv2BellMenu');
        if (!bellMenu) return;
        let bellWrapper = bellMenu.closest('.tv2-dropdown');

        // Find Cart Icon
        let cartIcon = document.querySelector('a[href*="cart"]');

        // Move Bell to be BEFORE Cart Icon (Between Currency and Cart)
        if (bellWrapper && cartIcon) {
            cartIcon.parentNode.insertBefore(bellWrapper, cartIcon);
        }
    });
    </script>

<!-- MOBILE DROPDOWN GLOBAL FIX -->
<style id="tv2-mobile-dropdown-fix">
@media (max-width: 767px) {
    /* Target all dropdown menus in the header */
    .tv2-dropdown-menu {
        position: fixed !important;
        top: 65px !important; /* Below the header */
        right: 15px !important; /* Safe margin from right edge */
        left: auto !important; /* Prevent left overflow */
        width: 90vw !important; 
        max-width: 320px !important; /* Ensure it fits on 360px screens */
        z-index: 999999 !important;
        box-sizing: border-box !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
        border-radius: 12px !important;
    }

    /* Fix any internal padding that might push content out */
    .tv2-dropdown-menu * {
        box-sizing: border-box !important;
    }
}
</style>

<!-- ULTIMATE LANDSCAPE DROPDOWN FIX -->
<style id="ultimate-landscape-dropdown-fix">
@media (orientation: landscape) and (max-height: 500px) {
    /* 1. Force Dropdowns to be Fixed to the Viewport */
    .tv2-dropdown-menu {
        position: fixed !important;
        top: 55px !important;       /* Just below the header */
        right: 15px !important;     /* Keep on screen */
        left: auto !important;
        width: 90vw !important;
        max-width: 320px !important;
        max-height: calc(100vh - 70px) !important; /* Fit within screen */
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        z-index: 999999 !important;
        transform: none !important; /* Remove any centering transforms */
        box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
    }

    /* 2. Ensure Header doesn't block clicks */
    .tv2-header {
        position: relative !important;
        z-index: 1000000 !important;
    }

    /* 3. Adjust Header Buttons to fit in one line */
    .tv2-header-actions {
        gap: 4px !important;
    }
    .tv2-header-btn {
        padding: 6px 8px !important;
    }
}
</style>



<!-- IPAD MINI WEATHER MINIMAL PUSH FIX -->
<style id="ipad-weather-minimal-fix">
/* Target ONLY iPad Mini Portrait width (768px to 820px) */
@media (min-width: 768px) and (max-width: 820px) {
    .tv2-weather-inline {
        /* Make it slightly smaller */
        transform: scale(0.85) !important;
        /* Anchor to top-right so it scales away from the text */
        transform-origin: top right !important;
        /* Minimal push: 5px up and 5px right */
        translate: 5px -5px !important; 
        margin-top: 0 !important;
        margin-right: 0 !important;
        position: relative !important;
        z-index: 10 !important;
    }
    
    /* Ensure the greeting text leaves a little room on the right */
    .tv2-hero h1 {
        padding-right: 10px !important;
    }
}
</style>

<!-- HERO TITLE WRAP FIX (Specific Devices) -->
<style id="hero-title-wrap-fix">
/* 1. Target Landscape Devices (Pixel, Pro Max, iPhone, SE) */
@media (orientation: landscape) and (max-height: 500px) {
    .tv2-hero h1 {
        font-size: 20px !important; /* Make text slightly smaller to fit in 2 lines */
        word-break: normal !important;
        overflow-wrap: normal !important;
        white-space: normal !important;
        max-width: 95% !important;
        line-height: 1.2 !important;
    }
}

/* 2. Target iPad Mini Portrait (768px to 820px) */
@media (min-width: 768px) and (max-width: 820px) {
    .tv2-hero h1 {
        font-size: 26px !important; /* Adjust font size for iPad */
        word-break: normal !important;
        overflow-wrap: normal !important;
        white-space: normal !important;
        max-width: 100% !important;
    }
}
</style>

<!-- TRAVELER MY TRIPS BUTTONS WRAP FIX -->
<style id="tv2-btnwrap-fix">
@media (max-width: 767px) {
    /* 1. Target the action buttons and status badges in My Trips */
    .tv2-trip-card .badge,
    .tv2-trip-card .status,
    .tv2-trip-card a,
    .tv2-trip-card button,
    .tv2-trip-card [class*="btn"] {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        min-width: max-content !important;
    }

    /* 2. Ensure the container doesn't force them to shrink */
    .tv2-trip-card .flex {
        flex-wrap: nowrap !important;
    }
    
    /* 3. Allow the title to shrink/wrap instead of pushing buttons */
    .tv2-trip-card .flex > div:first-child,
    .tv2-trip-card .title {
        flex: 1 1 auto !important;
        min-width: 0 !important;
    }
}
</style>



<!-- BADGE TEXT INSIDE FIX -->
<style id="badge-text-inside-fix">
@media (max-width: 767px) {
    /* 1. The row with Confirmed + View */
    .tv2-mytrips div[style*="justify-content: flex-end"] {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
        padding-right: 8px !important;
    }

    /* 2. The Confirmed badge - force inline-flex so content stays inside */
    .tv2-mytrips div[style*="justify-content: flex-end"] > span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        gap: 4px !important;
        line-height: 1.2 !important;
    }

    /* 3. The checkmark inside badge */
    .tv2-mytrips div[style*="justify-content: flex-end"] > span > span {
        display: inline !important;
        white-space: nowrap !important;
        line-height: 1 !important;
    }

    /* 4. The View button */
    .tv2-mytrips div[style*="justify-content: flex-end"] > a {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
    }
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
        {{-- Back to Home --}}
                    <a href="{{ url('/') }}" class="tv2-header-btn" title="Back to TravelAI Nepal Home" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none; color:#1e293b;">
                        <i class="fas fa-house"></i>
                        <span style="font-size:13px; font-weight:600;">Home</span>
                    </a>

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
                <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-dropdown-item">
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
        @hasSection('traveler-v2-content')
            @yield('traveler-v2-content')
        @else
            @yield('content')
        @endif
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



        {{-- ===== SOS Modal + JS (Phase 2: emergency levels) ===== --}}
<div id="sosModal" class="fixed inset-0 bg-black/60 hidden z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-lg text-gray-900">{{ __('messages.sos_modal_title') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_modal_subtitle') }}</p>
            </div>
        </div>

        <div id="sosGpsBanner" class="text-xs mb-3 px-3 py-2 rounded hidden"></div>
        <div id="sosStatus" class="text-sm mb-3 hidden"></div>

        {{-- Level selector --}}
        <div class="mb-4">
            <p class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.sos_level_question') }}
            </p>

            <label class="sos-level-card flex items-start gap-3 p-3 mb-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 transition">
                <input type="radio" name="sos_level" value="critical" class="mt-1" onchange="onSosLevelChange()">
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-900 flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-600"></span>
                        {{ __('messages.sos_level_critical_label') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_level_critical_desc') }}</div>
                </div>
            </label>

            <label class="sos-level-card flex items-start gap-3 p-3 mb-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-300 transition">
                <input type="radio" name="sos_level" value="urgent" class="mt-1" onchange="onSosLevelChange()">
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-900 flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-orange-500"></span>
                        {{ __('messages.sos_level_urgent_label') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_level_urgent_desc') }}</div>
                </div>
            </label>

            <label class="sos-level-card flex items-start gap-3 p-3 mb-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-yellow-300 transition">
                <input type="radio" name="sos_level" value="assistance" class="mt-1" onchange="onSosLevelChange()">
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-900 flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-yellow-500"></span>
                        {{ __('messages.sos_level_assistance_label') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_level_assistance_desc') }}</div>
                </div>
            </label>

            <label class="sos-level-card flex items-start gap-3 p-3 mb-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-400 transition">
                <input type="radio" name="sos_level" value="unclassified" class="mt-1" onchange="onSosLevelChange()">
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-900 flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-gray-800"></span>
                        {{ __('messages.sos_level_unclassified_label') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_level_unclassified_desc') }}</div>
                </div>
            </label>
        </div>

        <div class="mb-4">
            <label for="sosMessage" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('messages.sos_message_label') }}
            </label>
            <textarea id="sosMessage" rows="3" maxlength="500"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                      placeholder="{{ __('messages.sos_message_placeholder') }}"></textarea>
        </div>

        <div class="flex gap-2 mb-3">
            <button type="button" onclick="closeSosModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-medium transition">
                {{ __('messages.cancel') ?? 'Cancel' }}
            </button>
            <button type="button" id="sosSendBtn" onclick="sendSos()" disabled
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-bold transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-paper-plane mr-1"></i>
                <span>{{ __('messages.sos_send_now') }}</span>
            </button>
        </div>
                {{-- Active SOS Cancel Section --}}
        <div id="activeSosSection" class="hidden mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-center">
            <p class="text-sm text-red-800 font-semibold mb-2">You have an active SOS.</p>
            <button type="button" onclick="promptCancelSos()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold transition">
                Cancel Active SOS
            </button>
        </div>

        <div class="text-center text-xs text-gray-500 pt-2 border-t border-gray-100">
            <a href="#" onclick="closeSosModal(); return false;" class="text-blue-600 hover:underline">
                {{ __('messages.sos_general_help_link') }}
            </a>
        </div>
    </div>
</div>

<script>
async function openSosModal() {
    const m = document.getElementById('sosModal');
    m.classList.remove('hidden');
    document.getElementById('sosStatus').classList.add('hidden');
    document.getElementById('sosMessage').value = '';

    // Reset level selection
    document.querySelectorAll('input[name="sos_level"]').forEach(function (r) { r.checked = false; });
    document.querySelectorAll('.sos-level-card').forEach(function (c) {
        c.classList.remove('border-red-500', 'bg-red-50');
    });
    document.getElementById('sosSendBtn').disabled = true;

    // GPS banner
    checkGpsStatus();

    // Check active SOS (with fallback if localStorage is blocked)
    let activeSosId = null;
    try {
        activeSosId = localStorage.getItem('active_sos_id');
    } catch (e) {
        activeSosId = window.active_sos_id; // Fallback
    }
    
    const activeSection = document.getElementById('activeSosSection');
    if (activeSection) {
        if (activeSosId) {
            // लोड हुँदा लुकाउने
            activeSection.classList.add('hidden');
            
            // सर्भरलाई सोध्ने कि यो SOS अझै एक्टिभ छ कि छैन
            try {
                const res = await fetch(`/api/sos/${activeSosId}/status`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.is_active) {
                    activeSection.classList.remove('hidden');
                } else {
                    // एक्टिभ छैन भने LocalStorage क्लियर गर्ने
                    try { localStorage.removeItem('active_sos_id'); } catch (e) { window.active_sos_id = null; }
                }
            } catch (err) {
                // नेटवर्क एरर आएमा ब्यानर देखाउने (सुरक्षाको लागि)
                activeSection.classList.remove('hidden');
            }
        } else {
            activeSection.classList.add('hidden');
        }
    }
}

function closeSosModal() {
    document.getElementById('sosModal').classList.add('hidden');
}

function onSosLevelChange() {
    const selected = document.querySelector('input[name="sos_level"]:checked');
    const btn = document.getElementById('sosSendBtn');
    if (btn) btn.disabled = !selected;

    document.querySelectorAll('.sos-level-card').forEach(function (c) {
        c.classList.remove('border-red-500', 'bg-red-50');
    });
    if (selected) {
        const card = selected.closest('.sos-level-card');
        if (card) card.classList.add('border-red-500', 'bg-red-50');
    }
}

function checkGpsStatus() {
    const banner = document.getElementById('sosGpsBanner');
    if (!banner) return;
    banner.classList.add('hidden');
    if (!navigator.geolocation) {
        banner.className = 'text-xs mb-3 px-3 py-2 rounded bg-gray-100 border border-gray-300 text-gray-700';
        banner.textContent = '{{ __("messages.sos_gps_unknown") }}';
        banner.classList.remove('hidden');
    }
}

function sendSos() {
    const statusEl = document.getElementById('sosStatus');
    const btn = document.getElementById('sosSendBtn');

    const selectedLevel = document.querySelector('input[name="sos_level"]:checked');
    if (!selectedLevel) {
        statusEl.classList.remove('hidden');
        statusEl.className = 'text-sm mb-3 text-red-600';
        statusEl.textContent = '{{ __("messages.sos_level_question") }}';
        return;
    }

    statusEl.classList.remove('hidden');
    statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-blue-50 border border-blue-200 text-blue-700 font-medium';
    statusEl.textContent = '⏳ Getting location & sending SOS...'; // UX improvement
    btn.disabled = true;

    if (!navigator.geolocation) {
        statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 font-semibold';
        statusEl.textContent = '❌ {{ __("messages.sos_geo_unsupported") }}';
        btn.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const response = await fetch('{{ url("/api/sos") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        message: document.getElementById('sosMessage').value || null,
                        emergency_level: selectedLevel.value,
                    }),
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-green-50 border border-green-200 text-green-700 font-semibold';
                    statusEl.textContent = '✅ ' + (data.message || '{{ __("messages.sos_sent_success") }}');
                    
                    // Save to storage with fallback
                    try {
                        localStorage.setItem('active_sos_id', data.sos_id);
                    } catch (e) {
                        window.active_sos_id = data.sos_id; // Fallback
                    }
                    
                    setTimeout(() => closeSosModal(), 4000); // 4s ताकि पढ्न पाइयोस्
                } else {
                    statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 font-semibold';
                    statusEl.textContent = '❌ ' + (data.message || '{{ __("messages.sos_send_failed") }}');
                }
            } catch (err) {
                statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 font-semibold';
                statusEl.textContent = '❌ {{ __("messages.sos_network_error") }}';
            }
            btn.disabled = false;
        },
        (error) => {
            statusEl.className = 'text-sm mb-3 px-3 py-2 rounded bg-red-50 border border-red-200 text-red-700 font-semibold';
            statusEl.textContent = '❌ {{ __("messages.sos_location_denied") }}';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

async function promptCancelSos() {
    const reason = prompt("Why are you cancelling this SOS? (Required)");
    if (!reason || reason.trim() === '') return;

    let activeSosId = null;
    try {
        activeSosId = localStorage.getItem('active_sos_id');
    } catch (e) {
        activeSosId = window.active_sos_id; // Fallback
    }
    if (!activeSosId) return;

    try {
        const response = await fetch(`/api/sos/${activeSosId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ reason: reason })
        });
        const data = await response.json();

        if (response.ok && data.success) {
            try { localStorage.removeItem('active_sos_id'); } catch (e) { window.active_sos_id = null; }
            alert('SOS Cancelled.');
            closeSosModal();
            location.reload(); // Refresh to update state
        } else {
            alert(data.message || 'Failed to cancel SOS.');
        }
    } catch (err) {
        alert('Network error. Please try again.');
    }
}
</script>
    {{-- ===== End SOS Modal ===== --}}


<script>
// Bell mark-all (auto-injected)
document.addEventListener('DOMContentLoaded', function() {
    var menu = document.getElementById('tv2BellMenu');
    if (!menu) return;
    
    var observer = new MutationObserver(function() {
        if (menu.querySelector('.tv2-mark-all-trigger')) return;
        var hasItems = menu.querySelector('.tv2-dropdown-item');
        if (!hasItems) return;
        
        var markAll = document.createElement('a');
        markAll.href = '#';
        markAll.className = 'tv2-mark-all-trigger';
        markAll.style.cssText = 'display:block;padding:8px 16px;text-align:right;font-size:11px;color:#2563eb;font-weight:600;border-bottom:1px solid #f3f4f6;text-decoration:none;cursor:pointer;';
        markAll.textContent = 'Mark all read';
        markAll.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                var res = await fetch('{{ route("traveler.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    menu.dataset.loaded = '';
                    menu.innerHTML = '<div style="padding:16px;text-align:center;font-size:13px;color:#6b7280;">Loading...</div>';
                    var btn = document.getElementById('tv2BellBtn');
                    if (btn) { btn.click(); setTimeout(function(){ btn.click(); }, 50); }
                }
            } catch(err) { console.error('Mark all failed:', err); }
        });
        menu.prepend(markAll);
    });
    
    observer.observe(menu, { childList: true, subtree: false });
});
</script>
@stack('scripts')

<script id="tv2-btnwrap-js">
document.addEventListener("DOMContentLoaded", function() {
    if (window.innerWidth > 767) return;
    
    // Find all elements that look like badges or buttons in My Trips
    const allElements = document.querySelectorAll('.tv2-trip-card *, [id*="my-trips"] *, .my-trips *');
    allElements.forEach(el => {
        const text = (el.textContent || '').trim().toLowerCase();
        if (text === 'confirmed' || text === 'view' || text === 'pending' || text === 'cancelled' || text === 'completed') {
            el.style.setProperty('white-space', 'nowrap', 'important');
            el.style.setProperty('flex-shrink', '0', 'important');
            el.style.setProperty('min-width', 'max-content', 'important');
        }
    });
});
</script>


























</body>
</html>
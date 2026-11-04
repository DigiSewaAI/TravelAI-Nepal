{{--
    Traveler Dashboard V2 — Layout
    ------------------------------------------------
    - Extends layouts.public (top nav + footer untouched)
    - Adds sidebar + main content area inside @section('content')
    - Feature-flag gated via sidebar link conditional
    - Mobile: sidebar collapses to drawer with overlay
    - No changes to layouts/public.blade.php
--}}
@extends('layouts.public')

@push('head')
<style>
    /* Traveler V2 Shell */
    .tv2-shell {
        display: flex;
        min-height: calc(100vh - 80px);
        background: #f8fafc;
    }

    /* Sidebar (desktop) */
    .tv2-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        padding: 24px 16px;
        position: sticky;
        top: 80px;
        height: calc(100vh - 80px);
        overflow-y: auto;
    }

    .tv2-sidebar-section {
        margin-bottom: 24px;
    }

    .tv2-sidebar-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #9ca3af;
        padding: 0 12px 8px;
    }

    .tv2-sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
        margin-bottom: 2px;
    }

    .tv2-sidebar-link:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .tv2-sidebar-link.is-active {
        background: #2563eb;
        color: #ffffff;
        font-weight: 600;
    }

    .tv2-sidebar-link.is-active:hover {
        background: #1d4ed8;
    }

    .tv2-sidebar-link i {
        width: 18px;
        text-align: center;
        font-size: 15px;
    }

    .tv2-sidebar-link.is-danger {
        color: #dc2626;
    }

    .tv2-sidebar-link.is-danger:hover {
        background: #fef2f2;
    }

    /* Main content */
    .tv2-main {
        flex: 1;
        min-width: 0;
        padding: 32px;
    }

    /* Mobile: hide sidebar, show hamburger */
    .tv2-hamburger {
        display: none;
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 60;
        width: 56px;
        height: 56px;
        border-radius: 999px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        cursor: pointer;
        font-size: 22px;
    }

    .tv2-drawer-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 70;
    }

    .tv2-drawer {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 280px;
        background: #ffffff;
        z-index: 80;
        padding: 24px 16px;
        overflow-y: auto;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
    }

    .tv2-drawer.is-open {
        transform: translateX(0);
    }

    .tv2-drawer-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6b7280;
        cursor: pointer;
        margin-bottom: 16px;
    }

    @media (max-width: 1023px) {
        .tv2-sidebar { display: none; }
        .tv2-hamburger { display: flex; align-items: center; justify-content: center; }
        .tv2-main { padding: 24px 16px; }
        .tv2-drawer-backdrop.is-open { display: block; }
        .tv2-drawer { display: block; }
    }
</style>
@endpush

@section('content')
<div class="tv2-shell">

    {{-- DESKTOP SIDEBAR --}}
    <aside class="tv2-sidebar">
        {{-- Main --}}
        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_main') ?? 'Main' }}</div>
            <a href="{{ route('traveler.dashboard-v2') }}"
               class="tv2-sidebar-link {{ request()->routeIs('traveler.dashboard-v2') ? 'is-active' : '' }}">
                <i class="fas fa-house"></i>
                <span>{{ __('messages.nav_dashboard') ?? 'Dashboard' }}</span>
            </a>
            <a href="{{ route('traveler.dashboard-v2') }}" class="tv2-sidebar-link">
                <i class="fas fa-suitcase-rolling"></i>
                <span>{{ __('messages.nav_my_trips') ?? 'My Trips' }}</span>
            </a>
            <a href="{{ url('/') }}#ai-planner" class="tv2-sidebar-link">
                <i class="fas fa-robot"></i>
                <span>{{ __('messages.nav_ai_planner') ?? 'AI Planner' }}</span>
            </a>
            <a href="#exchange-rates" class="tv2-sidebar-link">
                <i class="fas fa-money-bill-transfer"></i>
                <span>{{ __('messages.nav_exchange_rates') ?? 'Exchange Rates' }}</span>
            </a>
            <a href="#memories" class="tv2-sidebar-link">
                <i class="fas fa-images"></i>
                <span>{{ __('messages.nav_travel_memories') ?? 'Travel Memories' }}</span>
            </a>
            <a href="{{ route('traveler.journey-replay') }}" class="tv2-sidebar-link">
                <i class="fas fa-clock-rotate-left"></i>
                <span>{{ __('messages.nav_journey_replay') ?? 'Journey Replay' }}</span>
            </a>
            <a href="{{ route('safety.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-shield-halved"></i>
                <span>{{ __('messages.nav_safety_center') ?? 'Safety Center' }}</span>
            </a>
            <a href="#sos" class="tv2-sidebar-link is-danger">
                <i class="fas fa-circle-exclamation"></i>
                <span>{{ __('messages.nav_emergency_sos') ?? 'Emergency SOS' }}</span>
            </a>
        </div>

        {{-- Marketplace --}}
        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_marketplace') ?? 'Marketplace' }}</div>
            <a href="{{ route('public.shop.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-bag-shopping"></i>
                <span>{{ __('messages.nav_shop') ?? 'Shop' }}</span>
            </a>
            <a href="{{ route('public.rental.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-car"></i>
                <span>{{ __('messages.nav_rental') ?? 'Rental' }}</span>
            </a>
            <a href="{{ route('public.wholesale.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-boxes-stacked"></i>
                <span>{{ __('messages.nav_wholesale') ?? 'Wholesale' }}</span>
            </a>
            <a href="{{ route('pages.pricing') }}" class="tv2-sidebar-link">
                <i class="fas fa-tag"></i>
                <span>{{ __('messages.nav_pricing') ?? 'Pricing' }}</span>
            </a>
        </div>

        {{-- Company --}}
        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_company') ?? 'Company' }}</div>
            <a href="{{ route('pages.about') }}" class="tv2-sidebar-link">
                <i class="fas fa-circle-info"></i>
                <span>{{ __('messages.nav_about') ?? 'About' }}</span>
            </a>
            <a href="{{ route('pages.contact') }}" class="tv2-sidebar-link">
                <i class="fas fa-envelope"></i>
                <span>{{ __('messages.nav_contact') ?? 'Contact' }}</span>
            </a>
        </div>

        {{-- My Account --}}
        <div class="tv2-sidebar-section">
            <div class="tv2-sidebar-label">{{ __('messages.sidebar_account') ?? 'My Account' }}</div>
            <a href="{{ route('traveler.my-bookings') }}" class="tv2-sidebar-link">
                <i class="fas fa-calendar-check"></i>
                <span>{{ __('messages.nav_my_bookings') ?? 'My Bookings' }}</span>
            </a>
            <a href="{{ route('orders.index') }}" class="tv2-sidebar-link">
                <i class="fas fa-shopping-bag"></i>
                <span>{{ __('messages.nav_my_orders') ?? 'My Orders' }}</span>
            </a>
        </div>
    </aside>

    {{-- MOBILE DRAWER --}}
    <div class="tv2-drawer-backdrop" id="tv2DrawerBackdrop"></div>
    <aside class="tv2-drawer" id="tv2Drawer">
        <button class="tv2-drawer-close" id="tv2DrawerClose" aria-label="Close menu">&times;</button>

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

    {{-- MAIN CONTENT --}}
    <main class="tv2-main">
        @yield('traveler-v2-content')
    </main>

    {{-- MOBILE HAMBURGER --}}
    <button class="tv2-hamburger" id="tv2Hamburger" aria-label="Open menu">
        <i class="fas fa-bars"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
(function () {
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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
})();
</script>
@endpush
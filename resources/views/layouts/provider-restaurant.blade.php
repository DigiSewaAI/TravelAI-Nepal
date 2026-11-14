@php
    use Illuminate\Support\Facades\Auth;
    $currentUser = Auth::user();
    $avatarName  = $currentUser?->name ?? __('messages.guest');
    $initials    = collect(explode(' ', $avatarName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') ?: 'GU';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', __('messages.restaurant_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#faf6f1;--card:#fff;--text:#1c1917;--muted:#78716c;--line:#efe7de;--accent:#f97316;--accent-d:#ea580c;--accent-soft:#fff2e6;--green:#16a34a;--green-soft:#e7f7ec;--amber:#f59e0b;--amber-soft:#fef5e3;--red:#dc2626;--red-soft:#fdeceb;--blue:#3b82f6;--blue-soft:#e9f1fe;--purple:#8b5cf6;--purple-soft:#f2ecfe;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:258px;background:linear-gradient(180deg,#1a1512 0%,#241b15 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:44px;height:44px;border-radius:14px;flex-shrink:0;background:linear-gradient(135deg,#fb923c,#f97316);display:grid;place-items:center;font-size:21px;box-shadow:0 10px 24px rgba(249,115,22,.42)}
  .brand h1{font-size:18px;font-weight:800;color:#fff;letter-spacing:-.5px;line-height:1.2}
  .brand span{font-size:10.5px;color:#a08b7c;font-weight:700;letter-spacing:1.2px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:10px;letter-spacing:1.4px;color:#5c4a3d;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a08b7c;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.06);color:#f0e6dd}
  .nav a.active{background:linear-gradient(90deg,rgba(249,115,22,.26),rgba(249,115,22,.05));color:#fb923c}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:#f97316}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(249,115,22,.22);color:#fb923c}
  .badge.soon{background:rgba(255,255,255,.08);color:#8a7666}
  .side-promo{margin-top:auto;border-radius:15px;padding:16px;background:linear-gradient(140deg,rgba(249,115,22,.2),rgba(239,68,68,.08));border:1px solid rgba(249,115,22,.25)}
  .side-promo h4{font-size:13px;font-weight:800;color:#fff;margin-bottom:4px}
  .side-promo p{font-size:11.5px;color:#a08b7c;line-height:1.55;font-weight:600;margin-bottom:12px}
  .side-promo button{width:100%;padding:9px;border:none;border-radius:9px;cursor:pointer;background:linear-gradient(135deg,#fb923c,#f97316);color:#fff;font-family:inherit;font-size:12px;font-weight:800}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#f59e0b,#f97316);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#f0e6dd;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#a08b7c;font-weight:600}
  .main{margin-left:258px;padding:24px 30px 46px;min-height:100vh}
  .topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:24px;flex-wrap:wrap}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px}
  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:230px;transition:.2s}
  .search:focus-within{border-color:var(--accent);box-shadow:0 0 0 4px rgba(249,115,22,.11)}
  .search svg{width:16px;height:16px;color:#b3a89e;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#78716c}
  .icon-btn:hover{background:#fff8f2;color:var(--accent-d);border-color:#fcd9b6}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#fb923c,#f97316);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(249,115,22,.32);white-space:nowrap}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(26,21,18,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s}
  .lang-select:hover{border-color:var(--accent);color:var(--accent-d)}
  .lang-select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(249,115,22,.15)}
</style>
@stack('restaurant-head')

<style id="provider-notif-fix">
@media (max-width: 767px) {
    /* A: Bell + Add button right edge मा push */
    #invNotifWrapper {
        margin-left: auto !important;
    }
    /* Bell + Add मा gap */
    #invNotifWrapper + button, #invNotifWrapper + a {
        margin-left: 6px !important;
    }
    /* B: Dropdown लाई screen center मा anchor (cut हटाउन) */
    #invNotifPanel {
        position: fixed !important;
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
        top: 64px !important;
        width: 90vw !important;
        max-width: 340px !important;
        box-sizing: border-box !important;
    }
    /* Dropdown भित्र content wrap नहोस् */
    #invNotifPanel * {
        word-break: break-word;
    }
}
</style>

<style id="provider-footer-fix">
@media (max-width: 767px) {
    /* Footer container लाई column layout मा बदल्ने */
    footer,
    .footer,
    .main-footer,
    .dashboard-footer,
    .content-wrapper footer {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        gap: 6px !important;
        padding: 16px 12px !important;
        width: 100% !important;
    }

    /* Footer भित्रको text wrap नहोस् र center होस् */
    footer *,
    .footer *,
    .main-footer *,
    .dashboard-footer * {
        text-align: center !important;
        white-space: normal !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* TravelAI OS v1.0 लाई सानो text बनाउने (optional) */
    footer span:last-child,
    footer div:last-child {
        font-size: 11px !important;
        opacity: 0.8 !important;
    }
}
</style>

<!-- LANDSCAPE SIDEBAR OVERLAP FIX (ALL CATEGORIES) -->
<style id="landscape-sidebar-fix-all">
@media (orientation: landscape) and (max-height: 500px) {
    /* 1. Make entire sidebar scrollable */
    .sidebar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding-bottom: 20px !important;
    }
    
    /* 2. Remove auto-margin pushing widget to bottom */
    .occ-card, .stock-card, .widget-card {
        margin-top: 12px !important;
        flex-shrink: 0 !important;
    }
    
    /* 3. Nav should NOT flex-collapse */
    .nav {
        flex: none !important;
        overflow-y: visible !important;
        max-height: none !important;
        margin-bottom: 12px !important;
    }
    
    /* 4. Compact elements for small height */
    .brand { padding-bottom: 10px !important; }
    .user-box { margin-top: 10px !important; }
    .nav a { padding: 8px 10px !important; font-size: 12px !important; }
    .nav-label { padding: 10px 10px 6px !important; font-size: 9px !important; }
    .nav-logout { padding: 8px 10px !important; font-size: 12px !important; margin-top: 6px !important; }
}
</style>

<!-- RESTAURANT MOBILE BUTTONS + OVERFLOW FIX -->
<style id="restaurant-mobile-buttons-fix">
@media (max-width: 767px) {
    /* 1. Prevent horizontal scroll globally */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    .main, main, .content-wrapper {
        max-width: 100vw !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }

    /* 2. Card head — stack on mobile */
    .card-head {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    /* 3. Title area — full width */
    .card-head > div:first-child {
        width: 100% !important;
        min-width: 0 !important;
    }

    /* 4. Button group — wrap to second row */
    .card-head > div:last-child {
        width: 100% !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        justify-content: flex-start !important;
    }

    /* 5. Buttons — equal width, no overflow */
    .card-head > div:last-child > a,
    .card-head > div:last-child > button {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        white-space: nowrap !important;
        font-size: 12px !important;
        padding: 8px 12px !important;
    }

    /* 6. Back button — full width on next row */
    .card-head > div:last-child > a[href*="dashboard"] {
        flex-basis: 100% !important;
    }

    /* 7. Card container — clip content */
    .card {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* 8. Table wrappers — allow internal scroll only */
    .tbl-wrap {
        max-width: 100% !important;
        overflow-x: auto !important;
    }
}
</style>

<!-- RESTAURANT ACTION BUTTONS SPACING FIX -->
<style id="restaurant-action-buttons-fix">
/* 1. Action cell — use flexbox with gap */
.tbl td:last-child {
    white-space: nowrap !important;
}
.tbl td:last-child form {
    display: inline-block !important;
    margin: 0 !important;
}
.tbl td:last-child .act-btn {
    margin: 0 !important;
}

/* 2. Desktop — flex row with gap */
.tbl td:last-child {
    display: flex !important;
    gap: 8px !important;
    justify-content: flex-end !important;
    align-items: center !important;
    flex-wrap: wrap !important;
}

/* 3. Buttons — bigger tap targets */
.act-btn {
    padding: 7px 12px !important;
    font-size: 11.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    white-space: nowrap !important;
}

/* 4. Mobile — smaller but still separated */
@media (max-width: 767px) {
    .tbl td:last-child {
        gap: 6px !important;
        padding: 10px 8px !important;
    }
    .act-btn {
        padding: 8px 10px !important;
        font-size: 11px !important;
    }
    /* Hide icon text on very small screens, show icon only */
    @media (max-width: 375px) {
        .act-btn span,
        .act-btn .btn-text {
            display: none !important;
        }
        .act-btn {
            padding: 8px !important;
        }
    }
}
</style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="brand" style="display: flex; flex-direction: column; align-items: flex-start; gap: 12px; padding-bottom: 20px;">
    <a href="{{ url('/') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
        <img src="{{ asset('images/logo.png') }}" alt="TravelAI" style="height: 26px; width: auto;" onerror="this.style.display='none'">
        <span style="font-size: 14px; font-weight: 800; color: #fff; letter-spacing: 0.5px;">TravelAI Nepal</span>
    </a>
    <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
        <div class="brand-logo" style="overflow:hidden;padding:0;">
            @if(!empty($provider->logo_url))
                <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name ?? 'Logo' }}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;">
            @else
                🍜
            @endif
        </div>
        <div>
          <h1 style="font-size: 16px; margin: 0;">{{ \Illuminate\Support\Str::limit($provider->name ?? 'Savory', 13) }}</h1>
          <span style="font-size: 9px; color: #8b9bb0;">RESTAURANT</span>
        </div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>
    {{-- ✅ Dashboard --}}
    <a href="{{ route('provider.restaurant.dashboard') }}" class="{{ request()->routeIs('provider.restaurant.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') ?? 'Dashboard' }}
    </a>
    {{-- ✅ Orders (Unlocked) --}}
    <a href="{{ route('provider.reviews.index') }}" class="{{ request()->routeIs('provider.reviews.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      {{ __('messages.reviews') ?? 'Reviews' }}
    </a>
    <a href="{{ route('provider.restaurant.orders.list') }}" class="{{ request()->routeIs('provider.restaurant.orders.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2.5-1.5L9 22l2.5-1.5L14 22l2.5-1.5L19 22l1-1V2H4z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
      {{ __('messages.orders') ?? 'Orders' }}
    </a>
    {{-- ✅ Menu (Unlocked) --}}
    <a href="{{ route('provider.restaurant.menu.list') }}" class="{{ request()->routeIs('provider.restaurant.menu.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2V2"/><path d="M5 11v11"/><path d="M19 2c-1.7 0-3 1.3-3 3v7h3v10"/></svg>
      {{ __('messages.menu') ?? 'Menu' }}
    </a>
    {{-- ✅ Tables (Unlocked) --}}
    <a href="{{ route('provider.restaurant.tables.list') }}" class="{{ request()->routeIs('provider.restaurant.tables.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
      {{ __('messages.tables') ?? 'Tables' }}
    </a>
    <a href="{{ route('provider.staff.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.staff') }}
    </a>
    <a href="{{ route('provider.suppliers.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      {{ __('messages.suppliers') }}
    </a>
    <a href="{{ route('provider.restaurant.reports.list') }}" class="{{ request()->routeIs('provider.restaurant.reports.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
      {{ __('messages.reports') }}
    </a>
    <div class="nav-label">{{ __('messages.others') }}</div>
    <a href="{{ route('provider.restaurant.offers.list') }}" class="{{ request()->routeIs('provider.restaurant.offers.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      {{ __('messages.offers') }}
    </a>
    {{-- Universal 5 Buttons (Phase 11) --}}
    @if(auth()->user()->isProviderOwner())
    <a href="{{ route('provider.sos-alerts.index') }}">
      <i class="fas fa-exclamation-triangle"></i>
      <span>{{ __('messages.sos_alerts_menu') }}</span>
    </a>
    @endif

    <a href="{{ route('provider.ads.index') }}">
      <i class="fas fa-bullhorn"></i>
      <span>{{ __('messages.ads_menu') }}</span>
    </a>

    @if(Route::has('provider.settings.payment-methods.index'))
    <a href="{{ route('provider.settings.payment-methods.index') }}">
      <i class="fas fa-wallet"></i>
      <span>{{ __('messages.pm_page_title') }}</span>
    </a>
    @endif

    @if(Route::has('provider.invoices.index'))
    <a href="{{ route('provider.invoices.index') }}">
      <i class="fas fa-file-invoice"></i>
      <span>{{ __('messages.invoices') }}</span>
    </a>
    @endif

    @if(auth()->user()->hasPermission('view_payments'))
    <a href="{{ route('provider.payments.index') }}">
      <i class="fas fa-credit-card"></i>
      <span>{{ __('messages.payments') }}</span>
    </a>
    @endif

    <a href="{{ route('provider.profile') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      {{ __('messages.profile') }}
    </a>

    {{-- Account Section --}}
    <div class="nav-label">{{ __('messages.account') }}</div>
    <a href="{{ route('provider.verification.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      {{ __('messages.verification') }}
    </a>
    <a href="{{ route('provider.subscriptions.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/><path d="M3 20h18"/></svg>
      {{ __('messages.subscription') }}
    </a>
        <a href="{{ route('traveler.my-bookings', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>{{ __('messages.my_bookings') }}</a>
        <a href="{{ route('orders.index', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>{{ __('messages.my_orders') }}</a>
        <a href="{{ route('wholesale.rfq.index', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/><path d="M9 21V9"/></svg>{{ __('messages.my_rfqs') }}</a>
        <a href="{{ route('provider.my-purchases') }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      {{ __('messages.my_purchases') }}
    </a>
<form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        {{ __('messages.logout') }}
      </button>
    </form>
  </nav>
  <div class="side-promo">
    <h4>{{ __('messages.weekend_offer_title') }}</h4>
    <p>{{ __('messages.weekend_offer_desc') }}</p>
    <button type="button">{{ __('messages.activate_now') }}</button>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.restaurant_owner') }}</div>
    </div>
  </div>
</aside>
<main class="main">
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:13px">
      <button class="icon-btn hamburger" id="hamburger" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/></svg></button>
      <div>
        <h2>@yield('page-title', __('messages.dashboard'))</h2>
        <p>@yield('page-subtitle', '')</p>
      </div>
    </div>
    <div class="topbar-right">
      <a href="{{ route('home') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;background:var(--accent);color:#fff;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;transition:filter .2s;" onmouseover="this.style.filter='brightness(.88)'" onmouseout="this.style.filter='none'">
  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
  <span>Home</span>
</a>
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" placeholder="{{ __('messages.search_orders_dishes') }}">
      </div>

      <select class="lang-select" onchange="window.location.href='/lang/' + this.value" style="display:none;">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>

      <div id="invNotifWrapper" style="position:relative">
  <button class="icon-btn" type="button" id="invNotifToggle" aria-label="Notifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    <span id="invNotifBadge" style="display:none;position:absolute;top:-3px;right:-3px;background:#ef4444;color:#fff;font-size:10px;font-weight:800;border-radius:10px;padding:1px 5px;min-width:18px;text-align:center;line-height:16px"></span>
  </button>
  <div id="invNotifPanel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;max-height:420px;overflow-y:auto;background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:14px;box-shadow:0 20px 50px rgba(20,26,46,.18);z-index:200">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid var(--line,#e7eaf3)">
      <strong style="font-size:14px;color:var(--text,#111827)">Notifications</strong>
      <a href="#" id="invNotifMarkAll" style="font-size:12px;color:var(--accent,#6366f1);font-weight:700;text-decoration:none">{{ __('messages.mark_all_read') }}</a>
    </div>
    <div id="invNotifList"><p style="padding:20px;text-align:center;color:var(--muted,#6b7280);font-size:13px">Loading…</p></div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  const wrapper = document.getElementById('invNotifWrapper');
  const toggle = document.getElementById('invNotifToggle');
  const panel = document.getElementById('invNotifPanel');
  const badge = document.getElementById('invNotifBadge');
  const list = document.getElementById('invNotifList');
  const markAll = document.getElementById('invNotifMarkAll');
  if(!wrapper || !toggle || !panel) return;
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  async function fetchNotifs(){
    try{
      const res = await fetch('{{ route("provider.notifications.index") }}', { headers:{'Accept':'application/json'} });
      if(!res.ok) return;
      const data = await res.json();
      if(data.unread_count > 0){ badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count; badge.style.display = 'inline-block'; }
      else { badge.style.display = 'none'; }
      if(!data.notifications || data.notifications.length === 0){
        list.innerHTML = '<p style="padding:20px;text-align:center;color:var(--muted,#6b7280);font-size:13px">No notifications</p>';
        return;
      }
      list.innerHTML = data.notifications.map(n =>
        '<a href="'+esc(n.url || '#')+'" data-id="'+esc(n.id)+'" class="inv-notif-item" style="display:block;padding:12px 16px;border-bottom:1px solid var(--line,#f1f3f9);text-decoration:none;'+(n.read ? 'background:var(--card,#fff)' : 'background:var(--accent-soft,#eef0fe)')+'">'
        + '<div style="font-size:13px;font-weight:700;color:var(--text,#111827)">'+esc(n.title)+'</div>'
        + '<div style="font-size:12px;color:var(--muted,#6b7280);margin-top:2px">'+esc(n.message)+'</div>'
        + '<div style="font-size:11px;color:var(--muted,#9ca3af);margin-top:4px">'+esc(n.created_at)+'</div>'
        + '</a>'
      ).join('');
      list.querySelectorAll('.inv-notif-item').forEach(function(el){
        el.addEventListener('click', async function(){
          try{ await fetch('{{ url("provider/notifications") }}/'+this.dataset.id+'/read', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} }); }catch(e){}
        });
      });
    }catch(e){}
  }
  toggle.addEventListener('click', function(e){
    e.stopPropagation();
    const open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    if(!open) fetchNotifs();
  });
  document.addEventListener('click', function(e){ if(!wrapper.contains(e.target)) panel.style.display = 'none'; });
  if(markAll) markAll.addEventListener('click', async function(e){
    e.preventDefault();
    try{ await fetch('{{ route("provider.notifications.mark-all-read") }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} }); fetchNotifs(); }catch(err){}
  });
  fetchNotifs();
});
</script>
      {{-- 🆕 Duplicate "New Order" button हटाइयो — dashboard action bar मा proper functional buttons छन् --}}
    </div>
  </header>
  @yield('content')


        {{-- Universal Footer --}}
        <footer style="margin-top: auto; padding: 20px 30px; border-top: 1px solid var(--line, #e5e7eb); display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--muted, #6b7280); background: transparent;">
            <span>&copy; {{ date('Y') }} TravelAI Nepal &middot; All rights reserved.</span>
            <span>TravelAI OS v1.0</span>
        </footer>
</main>
<script>
  const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay');
  document.getElementById('hamburger').addEventListener('click',()=>{sidebar.classList.add('open');overlay.classList.add('show')});
  overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show')});
</script>
@stack('restaurant-scripts')



<script src="{{ asset('js/sos-siren-global.js') }}"></script>
</body>
</html>

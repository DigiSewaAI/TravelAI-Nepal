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
<title>@yield('title', __('messages.transport_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f3f7fc;--card:#fff;--text:#0c1e33;--muted:#6b7f96;--line:#e4ecf6;--sky:#0ea5e9;--sky-d:#0284c7;--sky-soft:#e0f2fe;--navy:#0a1c33;--green:#10b981;--green-soft:#e6f8f1;--amber:#f59e0b;--amber-soft:#fef4e2;--red:#ef4444;--red-soft:#fdecec;--purple:#8b5cf6;--purple-soft:#f1ecfe;--accent:#0ea5e9;--accent-d:#0284c7;--accent-soft:#e0f2fe;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:262px;background:linear-gradient(180deg,#0a1c33 0%,#0f2744 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:44px;height:44px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,#38bdf8,#0ea5e9);display:grid;place-items:center;font-size:20px;box-shadow:0 10px 24px rgba(14,165,233,.42)}
  .brand h1{font-size:19px;font-weight:800;color:#fff;letter-spacing:-.4px;line-height:1.15}
  .brand span{font-size:9.5px;color:#6f88a6;font-weight:800;letter-spacing:2px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:9.5px;letter-spacing:1.5px;color:#3d5876;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#8ba0b8;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#d6e4f5}
  .nav a.active{background:linear-gradient(90deg,rgba(14,165,233,.22),rgba(14,165,233,.03));color:#38bdf8}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:var(--sky)}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,.08);color:#8ba0b8}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .fleet-card{margin-top:auto;border-radius:15px;padding:16px;background:linear-gradient(140deg,rgba(14,165,233,.16),rgba(139,92,246,.08));border:1px solid rgba(14,165,233,.24)}
  .fc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px}
  .fc-top span{font-size:10.5px;color:#6f88a6;font-weight:800;letter-spacing:.7px}
  .live{display:flex;align-items:center;gap:5px;font-size:9.5px;color:#38bdf8;font-weight:800}
  .live i{width:6px;height:6px;border-radius:50%;background:#38bdf8;animation:pulse 1.6s infinite}
  @keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(56,189,248,.6)}50%{opacity:.6;box-shadow:0 0 0 5px rgba(56,189,248,0)}}
  .fc-val{font-size:23px;font-weight:800;color:#fff;letter-spacing:-.7px}
  .fc-val small{font-size:12px;color:#6f88a6;font-weight:700;letter-spacing:0;text-transform:lowercase}
  .fc-legend{display:flex;gap:12px;margin-top:11px}
  .fc-legend span{display:flex;align-items:center;gap:5px;font-size:10px;font-weight:700;color:#8ba0b8}
  .fc-legend i{width:7px;height:7px;border-radius:50%}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#38bdf8,#6366f1);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#d6e4f5;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#6f88a6;font-weight:600}
  .main{margin-left:262px;padding:24px 30px 46px;min-height:100vh}

  /* 🔧 TOPBAR — Grid layout (prevents wrap) */
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}

  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:230px;transition:.2s}
  .search:focus-within{border-color:var(--sky);box-shadow:0 0 0 4px rgba(14,165,233,.12)}
  .search svg{width:16px;height:16px;color:#9aa9bc;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#6b7f96}
  .icon-btn:hover{background:#f0f8ff;color:var(--sky-d);border-color:#bfe0f5}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#38bdf8,#0ea5e9);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(14,165,233,.35);white-space:nowrap;flex-shrink:0}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:var(--sky);color:var(--sky-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(10,28,51,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('transport-head')
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
        <div class="brand-logo">🚌</div>
        <div>
          <h1 style="font-size: 16px; margin: 0;">{{ \Illuminate\Support\Str::limit($provider->name ?? 'SkyRoute', 13) }}</h1>
          <span style="font-size: 9px; color: #8b9bb0;">TRANSPORT</span>
        </div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>
    <a href="{{ route('provider.transport.dashboard') }}" class="{{ request()->routeIs('provider.transport.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>
    <a href="{{ route('provider.transport.bookings.index') }}" class="{{ request()->routeIs('provider.transport.bookings.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2.5-1.5L9 22l2.5-1.5L14 22l2.5-1.5L19 22l1-1V2H4z"/><path d="M8 7h8"/><path d="M8 11h8"/></svg>
      {{ __('messages.bookings') ?? 'Bookings' }}
    </a>
    <a href="{{ route('provider.transport.vehicles') }}" class="{{ request()->routeIs('provider.transport.vehicles') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M6 17V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><path d="M9 7V4"/><path d="M15 7V4"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>
      {{ __('messages.land_transport') }}
    </a>
    <a href="{{ route('provider.transport.routes') }}" class="{{ request()->routeIs('provider.transport.routes') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20l-5.5-2V4L9 6"/><path d="M15 4l5.5 2v14L15 18"/><path d="M9 6l6-2"/><path d="M9 20l6-2"/></svg>
      {{ __('messages.routes') }}
    </a>
    <a href="{{ route('provider.transport.drivers') }}" class="{{ request()->routeIs('provider.transport.drivers') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.drivers') }}
    </a>
    
    <a href="{{ route('provider.transport.reports') }}" class="{{ request()->routeIs('provider.transport.reports') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
      {{ __('messages.reports') }}
    </a>

    <a href="{{ route('provider.transport.offers') }}" class="{{ request()->routeIs('provider.transport.offers') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      {{ __('messages.offers') }}
    </a>
    {{-- Universal 5 Buttons (Phase 11) --}}
    @if(auth()->user()->isProviderOwner())
    <a href="{{ route('provider.sos-alerts.index') }}" class="{{ request()->routeIs('provider.sos-alerts.index.*') ? 'active' : '' }}">
      <i class="fas fa-exclamation-triangle"></i>
      <span>{{ __('messages.sos_alerts_menu') }}</span>
    </a>
    @endif

    <a href="{{ route('provider.ads.index') }}" class="{{ request()->routeIs('provider.ads.index.*') ? 'active' : '' }}">
      <i class="fas fa-bullhorn"></i>
      <span>{{ __('messages.ads_menu') }}</span>
    </a>

    @if(Route::has('provider.settings.payment-methods.index'))
    <a href="{{ route('provider.settings.payment-methods.index') }}" class="{{ request()->routeIs('provider.settings.payment-methods.index.*') ? 'active' : '' }}">
      <i class="fas fa-wallet"></i>
      <span>{{ __('messages.pm_page_title') }}</span>
    </a>
    @endif

    @if(Route::has('provider.invoices.index'))
    <a href="{{ route('provider.invoices.index') }}" class="{{ request()->routeIs('provider.invoices.index.*') ? 'active' : '' }}">
      <i class="fas fa-file-invoice"></i>
      <span>{{ __('messages.invoices') }}</span>
    </a>
    @endif

    @if(auth()->user()->hasPermission('view_payments'))
    <a href="{{ route('provider.payments.index') }}" class="{{ request()->routeIs('provider.payments.index.*') ? 'active' : '' }}">
      <i class="fas fa-credit-card"></i>
      <span>{{ __('messages.payments') }}</span>
    </a>
    @endif

    <a href="{{ route('provider.profile') }}" class="{{ request()->routeIs('provider.profile') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      {{ __('messages.profile') }}
    </a>

    <a href="{{ route('provider.staff.index') }}" class="{{ request()->routeIs('provider.staff.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.staff') }}
    </a>

    <a href="{{ route('provider.suppliers.index') }}" class="{{ request()->routeIs('provider.suppliers.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      {{ __('messages.suppliers') }}
    </a>

    <div class="nav-label">{{ __('messages.account') }}</div>
    <a href="{{ route('provider.verification.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      {{ __('messages.verification') }}
    </a>
    <a href="{{ route('provider.subscriptions.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/><path d="M3 20h18"/></svg>
      {{ __('messages.subscription') }}
    </a>
        <a href="{{ route('traveler.my-bookings', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg> My Bookings</a>
        <a href="{{ route('orders.index', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> My Orders</a>
        <a href="{{ route('wholesale.rfq.index', ['ctx' => 'provider']) }}" style="display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);font-size:13.5px;font-weight:700;text-decoration:none;margin-bottom:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/><path d="M9 21V9"/></svg> My RFQs</a>
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
  <div class="fleet-card">
    <div class="fc-top"><span>{{ __('messages.fleet_status_upper') }}</span><span class="live"><i></i>{{ __('messages.live_upper') }}</span></div>
    <div class="fc-val" id="fleetVal">{{ $activeFleet ?? 0 }} <small>/ {{ $totalFleet ?? 0 }} {{ \Illuminate\Support\Str::lower(__('messages.active')) }}</small></div>
    <div class="fc-legend">
      <span><i style="background:#22c55e"></i>{{ __('messages.available') }}</span>
      <span><i style="background:#0ea5e9"></i>{{ __('messages.on_route') }}</span>
    </div>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.operations_manager') }}</div>
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
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" placeholder="{{ __('messages.search_vehicles_routes') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
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
      <a href="#" id="invNotifMarkAll" style="font-size:12px;color:var(--accent,#6366f1);font-weight:700;text-decoration:none">Mark all read</a>
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
      {{-- 🆕 Duplicate "New Trip" button हटाइयो — dashboard action bar मा functional छ --}}
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

{{-- Fleet Widget Sync --}}
@php
    if (!isset($activeFleet) || !isset($totalFleet)) {
        $__fp = auth()->check() ? auth()->user()->ownProvider() : null;
        if ($__fp) {
            $activeFleet = \App\Models\TransportVehicle::where('provider_id', $__fp->id)->whereIn('status', ['available', 'on-route'])->count();
            $totalFleet = \App\Models\TransportVehicle::where('provider_id', $__fp->id)->count();
        } else { $activeFleet = 0; $totalFleet = 0; }
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const active = {{ (int) $activeFleet }};
    const total = {{ (int) $totalFleet }};
    const el = document.getElementById('fleetVal');
    if (el) el.innerHTML = active + ' <small>/ ' + total + ' {{ \Illuminate\Support\Str::lower(__('messages.active')) }}</small>';
});
</script>
@stack('transport-scripts')

<script src="{{ asset('js/sos-siren-global.js') }}"></script>
</body>
</html>
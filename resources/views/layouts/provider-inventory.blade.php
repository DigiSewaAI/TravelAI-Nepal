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
<title>@yield('title', __('messages.inventory_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f5f6fb;--card:#fff;--text:#111827;--muted:#6b7280;--line:#e7eaf3;--indigo:#6366f1;--indigo-d:#4f46e5;--indigo-soft:#eef0fe;--green:#10b981;--green-soft:#e7f8f1;--amber:#f59e0b;--amber-soft:#fef4e2;--red:#ef4444;--red-soft:#fdecec;--blue:#3b82f6;--blue-soft:#e9f1fe;--purple:#8b5cf6;--purple-soft:#f2ecfe;--accent:#6366f1;--accent-d:#4f46e5;--accent-soft:#eef0fe;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:262px;background:linear-gradient(180deg,#141a2e 0%,#1c2440 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:44px;height:44px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,#818cf8,#6366f1);display:grid;place-items:center;color:#fff;box-shadow:0 10px 24px rgba(99,102,241,.42);font-size:20px}
  .brand h1{font-size:19px;font-weight:800;color:#fff;letter-spacing:-.4px;line-height:1.15}
  .brand span{font-size:9.5px;color:#7c88a8;font-weight:800;letter-spacing:2px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:9.5px;letter-spacing:1.5px;color:#465375;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#8e9ab5;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#dbe2f0}
  .nav a.active{background:linear-gradient(90deg,rgba(99,102,241,.24),rgba(99,102,241,.03));color:#a5b4fc}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:var(--indigo)}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,.08);color:#8a98b3}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .stock-card{margin-top:auto;border-radius:15px;padding:16px;background:linear-gradient(140deg,rgba(99,102,241,.18),rgba(16,185,129,.08));border:1px solid rgba(99,102,241,.25)}
  .sc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px}
  .sc-top span{font-size:10.5px;color:#7c88a8;font-weight:800;letter-spacing:.7px}
  .live{display:flex;align-items:center;gap:5px;font-size:9.5px;color:#a5b4fc;font-weight:800}
  .live i{width:6px;height:6px;border-radius:50%;background:#a5b4fc;animation:pulse 1.6s infinite}
  @keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(165,180,252,.6)}50%{opacity:.6;box-shadow:0 0 0 5px rgba(165,180,252,0)}}
  .sc-val{font-size:23px;font-weight:800;color:#fff;letter-spacing:-.7px}
  .sc-val small{font-size:12px;color:#7c88a8;font-weight:700;letter-spacing:0}
  .sc-bar{height:6px;border-radius:6px;background:rgba(255,255,255,.09);margin-top:11px;overflow:hidden}
  .sc-bar div{height:100%;width:0;border-radius:6px;background:linear-gradient(90deg,#34d399,#10b981);transition:width 1.1s cubic-bezier(.3,.9,.4,1)}
  .sc-legend{display:flex;gap:12px;margin-top:11px}
  .sc-legend span{display:flex;align-items:center;gap:5px;font-size:10px;font-weight:700;color:#8e9ab5}
  .sc-legend i{width:7px;height:7px;border-radius:50%}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#818cf8,#8b5cf6);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#dbe2f0;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#7c88a8;font-weight:600}
  .main{margin-left:262px;padding:24px 30px 46px;min-height:100vh}
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}
  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:250px;transition:.2s}
  .search:focus-within{border-color:var(--indigo);box-shadow:0 0 0 4px rgba(99,102,241,.12)}
  .search svg{width:16px;height:16px;color:#9ca3af;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .search input::placeholder{color:#9ca3af}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#6b7280}
  .icon-btn:hover{background:#f4f5ff;color:var(--indigo-d);border-color:#d6d9fb}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#818cf8,#6366f1);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;transition:.2s;box-shadow:0 8px 20px rgba(99,102,241,.35);white-space:nowrap;flex-shrink:0}
  .btn-primary:hover{filter:brightness(1.07);transform:translateY(-1px);box-shadow:0 11px 25px rgba(99,102,241,.45)}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:var(--indigo);color:var(--indigo-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(20,26,46,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('inv-head')
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
        <div class="brand-logo">📦</div>
        <div>
          <h1 style="font-size: 16px; margin: 0;">{{ \Illuminate\Support\Str::limit($provider->name ?? 'Stockly', 13) }}</h1>
          <span style="font-size: 9px; color: #8b9bb0;">INVENTORY</span>
        </div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>

    <a href="{{ route('provider.inventory.dashboard') }}" class="{{ request()->routeIs('provider.inventory.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>

    <a href="{{ route('provider.inventory.products') }}" class="{{ request()->routeIs('provider.inventory.products') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
      {{ __('messages.nav_products') }}
    </a>

    <a href="{{ route('provider.inventory.inventory') }}" class="{{ request()->routeIs('provider.inventory.inventory') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
      {{ __('messages.nav_inventory') }}
    </a>

    <a href="{{ route('provider.inventory.orders') }}" class="{{ request()->routeIs('provider.inventory.orders') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2.5-1.5L9 22l2.5-1.5L14 22l2.5-1.5L19 22l1-1V2H4z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
      {{ __('messages.nav_orders') }}
    </a>

    <a href="{{ route('provider.inventory.suppliers') }}" class="{{ request()->routeIs('provider.inventory.suppliers') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M6 17V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>
      {{ __('messages.nav_suppliers') }}
    </a>

    <a href="{{ route('provider.inventory.reports') }}" class="{{ request()->routeIs('provider.inventory.reports') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
      {{ __('messages.nav_reports') }}
    </a>

    <a href="{{ route('provider.inventory.offers') }}" class="{{ request()->routeIs('provider.inventory.offers') ? 'active' : '' }}">
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

    <div class="nav-label">{{ __('messages.account') }}</div>
    <a href="{{ route('provider.verification.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      {{ __('messages.verification') }}
    </a>
    <a href="{{ route('provider.subscriptions.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/><path d="M3 20h18"/></svg>
      {{ __('messages.subscription') }}
    </a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        {{ __('messages.logout') }}
      </button>
    </form>
  </nav>
  <div class="stock-card">
    <div class="sc-top">
      <span>{{ __('messages.stock_health_upper') }}</span>
      <span class="live"><i></i>{{ __('messages.live_upper') }}</span>
    </div>
    <div class="sc-val" id="scVal">— <small>{{ __('messages.in_stock_label') }}</small></div>
    <div class="sc-bar"><div id="scBar"></div></div>
    <div class="sc-legend">
      <span><i style="background:#10b981"></i>{{ __('messages.healthy') }}</span>
      <span><i style="background:#f59e0b"></i>{{ __('messages.low') }}</span>
      <span><i style="background:#ef4444"></i>{{ __('messages.out') }}</span>
    </div>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.inventory_manager') }}</div>
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
        <input type="text" placeholder="{{ __('messages.search_products_skus') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
      {{-- 🆕 Duplicate "New Product" button हटाइयो — dashboard action bar मा functional button छ --}}
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
@stack('inv-scripts')
</body>
</html>
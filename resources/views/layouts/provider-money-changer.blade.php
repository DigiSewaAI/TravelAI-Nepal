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
<title>@yield('title', __('messages.money_changer_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f4f6fb;--card:#fff;--text:#0f172a;--muted:#64748b;--line:#e8edf5;--green:#10b981;--green-d:#059669;--green-soft:#e6f7f1;--red:#ef4444;--red-soft:#fdeaea;--blue:#3b82f6;--blue-soft:#e8f1fe;--amber:#f59e0b;--amber-soft:#fef4e2;--purple:#8b5cf6;--purple-soft:#f0eafe;--accent:#10b981;--accent-d:#059669;--accent-soft:#e6f7f1;--radius:16px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:264px;background:linear-gradient(180deg,#0f172a 0%,#111c33 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 24px}
  .brand-logo{width:42px;height:42px;border-radius:12px;flex-shrink:0;background:linear-gradient(135deg,#10b981,#059669);display:grid;place-items:center;color:#fff;box-shadow:0 8px 22px rgba(16,185,129,.4);font-size:20px}
  .brand h1{font-size:17px;font-weight:800;color:#fff;letter-spacing:-.4px;line-height:1.2}
  .brand span{font-size:10.5px;color:#7d8aa3;font-weight:600;letter-spacing:.4px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1;padding-bottom:10px}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:10px;letter-spacing:1.3px;color:#4d5d78;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:#94a3b8;font-size:13.5px;font-weight:600;text-decoration:none;transition:background .2s,color .2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#e2e8f0}
  .nav a.active{background:linear-gradient(90deg,rgba(16,185,129,.2),rgba(16,185,129,.04));color:#34d399}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:#10b981}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,.18);color:#f87171}
  .badge.soon{background:rgba(255,255,255,.08);color:#8a98b3}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .sidebar-footer{margin-top:auto;display:flex;flex-direction:column;gap:12px;padding-top:10px}
  .rate-card{background:linear-gradient(135deg,rgba(16,185,129,.16),rgba(59,130,246,.1));border:1px solid rgba(16,185,129,.22);border-radius:14px;padding:14px}
  .rate-card .rc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
  .rate-card .rc-top span{font-size:10.5px;color:#7d8aa3;font-weight:700;letter-spacing:.6px}
  .live{display:flex;align-items:center;gap:5px;font-size:10px;color:#34d399;font-weight:700}
  .live i{width:6px;height:6px;border-radius:50%;background:#34d399;animation:pulse 1.6s infinite}
  @keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(52,211,153,.6)}50%{opacity:.6;box-shadow:0 0 0 5px rgba(52,211,153,0)}}
  .rate-card .rc-val{font-size:20px;font-weight:800;color:#fff;letter-spacing:-.5px}
  .rate-card .rc-val small{font-size:11px;color:#7d8aa3;font-weight:600;letter-spacing:0}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:12px;background:rgba(255,255,255,.045);margin-top:6px}
  .avatar{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#e2e8f0;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#7d8aa3;font-weight:600}
  .main{margin-left:264px;padding:24px 30px 44px;min-height:100vh}

  /* 🔧 TOPBAR — Grid layout to prevent wrap */
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-size:22px;font-weight:800;letter-spacing:-.6px}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}

  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:11px;padding:9px 14px;width:220px;transition:.2s}
  .search:focus-within{border-color:#10b981;box-shadow:0 0 0 4px rgba(16,185,129,.1)}
  .search svg{width:16px;height:16px;color:#94a3b8;stroke-width:2.2;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .search input::placeholder{color:#a8b4c6}
  .icon-btn{width:40px;height:40px;border-radius:11px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#64748b}
  .icon-btn:hover{background:#f8fafc;color:var(--text);border-color:#d9e2ef}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:9px;right:10px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:#10b981;color:#10b981}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}

  /* Layout default btn-primary — prevent huge stretch */
  .btn-primary{display:inline-flex;align-items:center;gap:7px;padding:10px 16px;border:none;border-radius:11px;background:linear-gradient(135deg,#34d399,#10b981);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 6px 16px rgba(16,185,129,.3);white-space:nowrap;line-height:1.2;flex-shrink:0}
  .btn-primary svg{width:15px;height:15px;stroke-width:2.6;flex-shrink:0}
  .btn-primary:hover{filter:brightness(1.05);transform:translateY(-1px)}

  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 40px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.main{padding:16px 14px 36px}}
</style>
@stack('mc-head')
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
        <div class="brand-logo">💱</div>
        <div>
          <h1 style="font-size: 16px; margin: 0;">{{ \Illuminate\Support\Str::limit($provider->name ?? 'MoneyX', 13) }}</h1>
          <span style="font-size: 9px; color: #8b9bb0;">MONEY CHANGER</span>
        </div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>
    <a href="{{ route('provider.money-changer.dashboard') }}" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>
    <a href="#" class="disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 2l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
      {{ __('messages.nav_exchange_rates') }} <span class="badge soon">{{ __('messages.soon') }}</span>
    </a>
    <a href="#" class="disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2.5-1.5L9 22l2.5-1.5L14 22l2.5-1.5L19 22l1-1V2H4z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
      {{ __('messages.nav_transactions') }} <span class="badge soon">{{ __('messages.soon') }}</span>
    </a>
    <a href="#" class="disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.nav_customers') }} <span class="badge soon">{{ __('messages.soon') }}</span>
    </a>
    <a href="#" class="disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
      {{ __('messages.nav_reports') }} <span class="badge soon">{{ __('messages.soon') }}</span>
    </a>
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
  <div class="sidebar-footer">
    @if(isset($liveUsd) && $liveUsd)
    <div class="rate-card">
      <div class="rc-top">
        <span>USD / NPR</span>
        <span class="live"><i></i>{{ __('messages.live_upper') }}</span>
      </div>
      <div class="rc-val">{{ number_format($liveUsd['sell'], 2) }} <small>NPR</small></div>
    </div>
    @endif
    <div class="user-box">
      <div class="avatar">{{ $initials }}</div>
      <div>
        <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
        <div class="u-role">{{ __('messages.money_changer_role') }}</div>
      </div>
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
        <input type="text" placeholder="{{ __('messages.search_transactions') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
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
@stack('mc-scripts')
</body>
</html>
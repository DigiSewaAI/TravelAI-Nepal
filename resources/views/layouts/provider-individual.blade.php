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
<title>@yield('title', __('messages.individual_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f4f8f5;--card:#fff;--text:#0d1f17;--muted:#6b7d73;--line:#e2ebe5;--emerald:#10b981;--emerald-d:#059669;--emerald-soft:#e7f7ef;--forest:#0f2419;--amber:#f59e0b;--amber-soft:#fef4e2;--purple:#8b5cf6;--purple-soft:#f2ecfe;--blue:#3b82f6;--blue-soft:#e9f1fe;--red:#ef4444;--red-soft:#fdecec;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:262px;background:linear-gradient(180deg,#0f2419 0%,#163324 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:44px;height:44px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,#34d399,#10b981);display:grid;place-items:center;color:#fff;box-shadow:0 10px 24px rgba(16,185,129,.42);font-size:20px}
  .brand h1{font-size:19px;font-weight:800;color:#fff;letter-spacing:-.4px;line-height:1.15}
  .brand span{font-size:9.5px;color:#7ca08d;font-weight:800;letter-spacing:1.6px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:9.5px;letter-spacing:1.5px;color:#42604f;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#95b0a1;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#dceee3}
  .nav a.active{background:linear-gradient(90deg,rgba(16,185,129,.24),rgba(16,185,129,.03));color:#34d399}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:var(--emerald)}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,.08);color:#8a98b3}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .rating-card{margin-top:auto;border-radius:15px;padding:16px;background:linear-gradient(140deg,rgba(245,158,11,.2),rgba(16,185,129,.1));border:1px solid rgba(245,158,11,.3);text-align:center}
  .rating-card .rc-stars{font-size:15px;color:#fbbf24;letter-spacing:2px;line-height:1;margin-bottom:6px}
  .rating-card .rc-score{font-size:28px;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1}
  .rating-card .rc-score small{font-size:13px;color:#7ca08d;font-weight:700;letter-spacing:0}
  .rating-card .rc-meta{font-size:10.5px;color:#95b0a1;font-weight:700;margin-top:6px}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#34d399,#059669);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#dceee3;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#7ca08d;font-weight:600}
  .main{margin-left:262px;padding:24px 30px 46px;min-height:100vh}
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}
  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:230px;transition:.2s}
  .search:focus-within{border-color:var(--emerald);box-shadow:0 0 0 4px rgba(16,185,129,.12)}
  .search svg{width:16px;height:16px;color:#9cb0a4;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#6b7d73}
  .icon-btn:hover{background:#f2faf5;color:var(--emerald-d);border-color:#bfe6d2}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot-notif{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#34d399,#10b981);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(16,185,129,.35);white-space:nowrap;flex-shrink:0}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:var(--emerald);color:var(--emerald-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(15,36,25,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('ind-head')
</head>
<body>
<div class="overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-logo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m2 22 5-14 5 6 4-9 6 17z"/></svg>
    </div>
    <div>
      <h1>{{ \Illuminate\Support\Str::limit($provider?->name ?? __('messages.wayfarer_brand_default'), 13) }}</h1>
      <span>{{ __('messages.guide_porter_photo') }}</span>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>

    <a href="{{ route('provider.individual.dashboard') }}" class="{{ request()->routeIs('provider.individual.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>

    <a href="{{ route('provider.individual.bookings.list') }}" class="{{ request()->routeIs('provider.individual.bookings.list') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
      {{ __('messages.nav_bookings') }}
    </a>

    <a href="{{ route('provider.individual.calendar') }}" class="{{ request()->routeIs('provider.individual.calendar') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/></svg>
      {{ __('messages.nav_calendar') }}
    </a>

    <a href="{{ route('provider.individual.earnings') }}" class="{{ request()->routeIs('provider.individual.earnings') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      {{ __('messages.nav_earnings') }}
    </a>

    <a href="{{ route('provider.individual.reviews') }}" class="{{ request()->routeIs('provider.individual.reviews') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
      {{ __('messages.reviews') }}
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
  <div class="rating-card">
    <div class="rc-stars">★★★★★</div>
    <div class="rc-score" id="avgRatingVal">{{ $avgRating ?? '4.9' }}<small>/5</small></div>
    <div class="rc-meta" id="reviewCountVal">{{ __('messages.n_reviews', ['count' => $reviewCount ?? 127]) }}</div>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.service_provider') }}</div>
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
        <input type="text" placeholder="{{ __('messages.search_bookings_clients') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot-notif"></span></button>
      {{-- 🆕 Duplicate "New Booking" button हटाइयो — dashboard action bar मा functional "New Assignment" छ --}}
    </div>
  </header>
  @yield('content')
</main>
<script>
  const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay');
  document.getElementById('hamburger').addEventListener('click',()=>{sidebar.classList.add('open');overlay.classList.add('show')});
  overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show')});
</script>
@stack('ind-scripts')
</body>
</html>
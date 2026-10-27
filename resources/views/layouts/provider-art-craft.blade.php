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
<title>@yield('title', __('messages.art_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#faf6f0;--card:#fff;--text:#2b1e16;--muted:#8a7565;--line:#ede1d3;--terracotta:#c65d3b;--terracotta-d:#a8492b;--terracotta-soft:#f8e5da;--burgundy:#6b2c3f;--burgundy-d:#4e1f2e;--burgundy-soft:#f5e1e7;--sand:#d9b382;--sand-soft:#f4ebdc;--green:#6b8e5a;--green-soft:#e8f0e2;--amber:#d4a24c;--amber-soft:#fbf1dc;--red:#b54434;--red-soft:#fbe3df;--charcoal:#1e1612;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;line-height:1.5}
  .sidebar{position:fixed;inset:0 auto 0 0;width:268px;background:linear-gradient(180deg,#1e1612 0%,#2b1e16 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:46px;height:46px;border-radius:14px;flex-shrink:0;background:linear-gradient(135deg,#e88a5a,#c65d3b);display:grid;place-items:center;color:#fff;font-family:'Playfair Display',serif;font-weight:700;font-size:22px;box-shadow:0 10px 24px rgba(198,93,59,.42)}
  .brand h1{font-family:'Playfair Display',serif;font-size:21px;font-weight:700;color:#fff;letter-spacing:-.3px;line-height:1.15}
  .brand span{font-size:9.5px;color:#9b8578;font-weight:800;letter-spacing:2.2px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:9.5px;letter-spacing:1.5px;color:#5a463a;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#a08b7c;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#f0e3d6}
  .nav a.active{background:linear-gradient(90deg,rgba(198,93,59,.26),rgba(198,93,59,.04));color:#e88a5a}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:var(--terracotta)}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(198,93,59,.22);color:#e88a5a}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .artist-card{margin-top:auto;border-radius:16px;padding:16px;background:linear-gradient(140deg,rgba(198,93,59,.18),rgba(107,44,63,.12));border:1px solid rgba(198,93,59,.28);text-align:center}
  .artist-avatar{width:56px;height:56px;border-radius:50%;margin:0 auto 10px;background:linear-gradient(135deg,#e88a5a,#a8492b);display:grid;place-items:center;color:#fff;font-family:'Playfair Display',serif;font-weight:700;font-size:22px;box-shadow:0 8px 22px rgba(198,93,59,.4)}
  .artist-name{font-family:'Playfair Display',serif;font-size:15px;font-weight:700;color:#fff;line-height:1.2;margin-bottom:3px}
  .artist-role{font-size:10.5px;color:#a08b7c;font-weight:700;margin-bottom:10px}
  .artist-stars{font-size:12px;color:#f5c063;letter-spacing:1px;margin-bottom:10px}
  .artist-meta{display:flex;justify-content:space-around;padding-top:10px;border-top:1px solid rgba(255,255,255,.1)}
  .artist-meta div{text-align:center}
  .artist-meta b{display:block;font-size:15px;font-weight:800;color:#fff;line-height:1}
  .artist-meta span{font-size:9.5px;color:#a08b7c;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:12px}
  .user-av{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#d9b382,#c65d3b);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#f0e3d6;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#a08b7c;font-weight:600}
  .main{margin-left:268px;padding:24px 30px 46px;min-height:100vh}
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;letter-spacing:-.6px}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}
  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:240px;transition:.2s}
  .search:focus-within{border-color:var(--terracotta);box-shadow:0 0 0 4px rgba(198,93,59,.12)}
  .search svg{width:16px;height:16px;color:#b3a090;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#8a7565}
  .icon-btn:hover{background:#fbf1ea;color:var(--terracotta-d);border-color:#f0cdb8}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot-n{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:8px;padding:11px 18px;border:none;border-radius:12px;background:linear-gradient(135deg,#e88a5a,#c65d3b);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(198,93,59,.35);white-space:nowrap;flex-shrink:0}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:var(--terracotta);color:var(--terracotta-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(30,22,18,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:22px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('art-head')
</head>
<body>
<div class="overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-logo">A</div>
    <div>
      <h1>{{ \Illuminate\Support\Str::limit($provider->name ?? __('messages.art_brand_default'), 13) }}</h1>
      <span>{{ __('messages.art_craft_upper') }}</span>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>

    <a href="{{ route('provider.art-craft.dashboard') }}" class="{{ request()->routeIs('provider.art-craft.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>

    <a href="{{ route('provider.art-craft.gallery') }}" class="{{ request()->routeIs('provider.art-craft.gallery') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
      {{ __('messages.nav_gallery') }}
    </a>

    <a href="{{ route('provider.art-craft.orders') }}" class="{{ request()->routeIs('provider.art-craft.orders') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2.5-1.5L9 22l2.5-1.5L14 22l2.5-1.5L19 22l1-1V2H4z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
      {{ __('messages.nav_custom_orders') }}
    </a>

    <a href="#" class="disabled">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.nav_artists') }} <span class="badge">{{ __('messages.soon') }}</span>
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
  <div class="artist-card">
    <div class="artist-avatar">{{ substr($provider?->name ?? 'A', 0, 1) }}</div>
    <div class="artist-name">{{ $provider?->name ?? __('messages.artist_fallback') }}</div>
    <div class="artist-role">{{ __('messages.contemporary_artist') }}</div>
    <div class="artist-stars">★★★★★</div>
    <div class="artist-meta">
      <div><b>{{ $completedWorks ?? 0 }}</b><span>{{ __('messages.works_label') }}</span></div>
      <div><b>{{ $rating ?? '4.9' }}</b><span>{{ __('messages.rating_label') }}</span></div>
      <div><b>{{ $clients ?? 0 }}</b><span>{{ __('messages.clients_label') }}</span></div>
    </div>
  </div>
  <div class="user-box">
    <div class="user-av">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.studio_owner') }}</div>
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
        <input type="text" placeholder="{{ __('messages.search_artworks_orders') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot-n"></span></button>
      {{-- 🆕 Duplicate "Add New Artwork" button हटाइयो — dashboard action bar मा functional छ --}}
    </div>
  </header>
  @yield('content')
</main>
<script>
  const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay');
  document.getElementById('hamburger').addEventListener('click',()=>{sidebar.classList.add('open');overlay.classList.add('show')});
  overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show')});
</script>
@stack('art-scripts')
</body>
</html>
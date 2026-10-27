@php
    use Illuminate\Support\Facades\Auth;
    $currentUser = Auth::user();
    $avatarName  = $currentUser?->name ?? 'Guest';
    $initials    = collect(explode(' ', $avatarName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') ?: 'GU';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Transport Dashboard') — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f3f7fc;--card:#fff;--text:#0c1e33;--muted:#6b7f96;--line:#e4ecf6;--sky:#0ea5e9;--sky-d:#0284c7;--sky-soft:#e0f2fe;--navy:#0a1c33;--green:#10b981;--green-soft:#e6f8f1;--amber:#f59e0b;--amber-soft:#fef4e2;--red:#ef4444;--red-soft:#fdecec;--purple:#8b5cf6;--purple-soft:#f1ecfe;--radius:18px}
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
  .fc-val small{font-size:12px;color:#6f88a6;font-weight:700;letter-spacing:0}
  .fc-legend{display:flex;gap:12px;margin-top:11px}
  .fc-legend span{display:flex;align-items:center;gap:5px;font-size:10px;font-weight:700;color:#8ba0b8}
  .fc-legend i{width:7px;height:7px;border-radius:50%}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#38bdf8,#6366f1);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#d6e4f5;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#6f88a6;font-weight:600}
  .main{margin-left:262px;padding:24px 30px 46px;min-height:100vh}
  .topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:24px;flex-wrap:wrap}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar-right{display:flex;align-items:center;gap:11px}
  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:230px;transition:.2s}
  .search:focus-within{border-color:var(--sky);box-shadow:0 0 0 4px rgba(14,165,233,.12)}
  .search svg{width:16px;height:16px;color:#9aa9bc;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#6b7f96}
  .icon-btn:hover{background:#f0f8ff;color:var(--sky-d);border-color:#bfe0f5}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#38bdf8,#0ea5e9);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(14,165,233,.35);white-space:nowrap}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s}
  .lang-select:hover{border-color:var(--sky);color:var(--sky-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(10,28,51,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('transport-head')
</head>
<body>
<div class="overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-logo">🚌</div>
    <div>
      <h1>{{ \Illuminate\Support\Str::limit($provider->name ?? 'SkyRoute', 13) }}</h1>
      <span>TRANSPORT</span>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">Main</div>
    <a href="{{ route('provider.transport.dashboard') }}" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>Dashboard</a>
    <a href="#" class="disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M6 17V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><path d="M9 7V4"/><path d="M15 7V4"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>Land Transport <span class="badge">Soon</span></a>
    <a href="#" class="disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20l-5.5-2V4L9 6"/><path d="M15 4l5.5 2v14L15 18"/><path d="M9 6l6-2"/><path d="M9 20l6-2"/></svg>Routes <span class="badge">Soon</span></a>
    <a href="#" class="disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Drivers <span class="badge">Soon</span></a>
    <div class="nav-label">Account</div>
    <a href="{{ route('provider.verification.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>Verification</a>
    <a href="{{ route('provider.subscriptions.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/><path d="M3 20h18"/></svg>Subscription</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>Logout</button>
    </form>
  </nav>
  <div class="fleet-card">
    <div class="fc-top"><span>FLEET STATUS</span><span class="live"><i></i>LIVE</span></div>
    <div class="fc-val" id="fleetVal">— <small>/ — active</small></div>
    <div class="fc-legend">
      <span><i style="background:#22c55e"></i>Available</span>
      <span><i style="background:#0ea5e9"></i>On Route</span>
    </div>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? 'Guest' }}</div>
      <div class="u-role">Operations Manager</div>
    </div>
  </div>
</aside>
<main class="main">
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:13px">
      <button class="icon-btn hamburger" id="hamburger" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/></svg></button>
      <div>
        <h2>@yield('page-title', 'Dashboard')</h2>
        <p>@yield('page-subtitle', '')</p>
      </div>
    </div>
    <div class="topbar-right">
      <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg><input type="text" placeholder="Search vehicles, routes…"></div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
      <button class="btn-primary" type="button" title="Coming soon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg><span>New Trip</span></button>
    </div>
  </header>
  @yield('content')
</main>
<script>
  const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('overlay');
  document.getElementById('hamburger').addEventListener('click',()=>{sidebar.classList.add('open');overlay.classList.add('show')});
  overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('show')});
</script>
@stack('transport-scripts')
</body>
</html>
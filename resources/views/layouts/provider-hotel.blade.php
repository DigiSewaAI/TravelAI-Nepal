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
<title>@yield('title', __('messages.hotel_dashboard_title')) — TravelAI Nepal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
  :root{--bg:#f5f7fb;--card:#fff;--text:#0e1726;--muted:#6b7a90;--line:#e6ecf4;--gold:#c9a227;--gold-d:#a8861c;--gold-soft:#fbf5e2;--navy:#0e1726;--green:#10b981;--green-soft:#e7f8f1;--blue:#3b82f6;--blue-soft:#e9f1fe;--amber:#f59e0b;--amber-soft:#fef5e3;--red:#ef4444;--red-soft:#fdecec;--purple:#8b5cf6;--purple-soft:#f2ecfe;--accent:#c9a227;--accent-d:#a8861c;--accent-soft:#fbf5e2;--radius:18px}
  *{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
  body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
  .sidebar{position:fixed;inset:0 auto 0 0;width:262px;background:linear-gradient(180deg,#0e1726 0%,#132033 100%);padding:24px 16px;display:flex;flex-direction:column;z-index:100;transition:transform .32s cubic-bezier(.4,0,.2,1)}
  .brand{display:flex;align-items:center;gap:12px;padding:0 8px 26px}
  .brand-logo{width:44px;height:44px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,#d9b64a,#c9a227);display:grid;place-items:center;font-size:21px;box-shadow:0 10px 24px rgba(201,162,39,.4)}
  .brand h1{font-size:19px;font-weight:800;color:#fff;letter-spacing:-.4px;line-height:1.15}
  .brand span{font-size:9.5px;color:#8b9bb0;font-weight:800;letter-spacing:2.2px}
  .nav{display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex:1}
  .nav::-webkit-scrollbar{width:0}
  .nav-label{font-size:9.5px;letter-spacing:1.5px;color:#3d4c62;font-weight:800;padding:16px 12px 8px;text-transform:uppercase}
  .nav a{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#8b9bb0;font-size:13.5px;font-weight:600;text-decoration:none;transition:.2s;position:relative}
  .nav a svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .nav a:hover{background:rgba(255,255,255,.055);color:#e2e8f0}
  .nav a.active{background:linear-gradient(90deg,rgba(201,162,39,.22),rgba(201,162,39,.03));color:#e3c357}
  .nav a.active::before{content:'';position:absolute;left:-16px;top:50%;transform:translateY(-50%);width:3px;height:20px;border-radius:0 3px 3px 0;background:var(--gold)}
  .nav a.disabled{cursor:not-allowed;opacity:.55}
  .badge{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,.08);color:#8a98b3}
  .nav-logout{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:11px;color:#f87171;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;width:100%;transition:.2s;margin-top:8px}
  .nav-logout:hover{background:rgba(239,68,68,.18);color:#fca5a5;transform:translateX(2px)}
  .nav-logout svg{width:18px;height:18px;flex-shrink:0;stroke-width:2}
  .occ-card{margin-top:auto;border-radius:15px;padding:16px;background:linear-gradient(140deg,rgba(201,162,39,.16),rgba(59,130,246,.08));border:1px solid rgba(201,162,39,.24)}
  .occ-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px}
  .occ-top span{font-size:10.5px;color:#8b9bb0;font-weight:800;letter-spacing:.7px}
  .live{display:flex;align-items:center;gap:5px;font-size:9.5px;color:#e3c357;font-weight:800}
  .live i{width:6px;height:6px;border-radius:50%;background:#e3c357;animation:pulse 1.6s infinite}
  @keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(227,195,87,.6)}50%{opacity:.6;box-shadow:0 0 0 5px rgba(227,195,87,0)}}
  .occ-val{font-size:23px;font-weight:800;color:#fff;letter-spacing:-.7px}
  .occ-val small{font-size:12px;color:#8b9bb0;font-weight:700;letter-spacing:0}
  .occ-bar{height:6px;border-radius:6px;background:rgba(255,255,255,.09);margin-top:11px;overflow:hidden}
  .occ-bar div{height:100%;width:0;border-radius:6px;background:linear-gradient(90deg,#d9b64a,#c9a227);transition:width 1.2s cubic-bezier(.3,.9,.4,1)}
  .user-box{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;background:rgba(255,255,255,.05);margin-top:14px}
  .avatar{width:37px;height:37px;border-radius:11px;flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px}
  .user-box .u-name{font-size:12.5px;font-weight:700;color:#e2e8f0;line-height:1.3}
  .user-box .u-role{font-size:10.5px;color:#8b9bb0;font-weight:600}
  .main{margin-left:262px;padding:24px 30px 46px;min-height:100vh}

  /* Topbar — Grid layout (prevents wrap) */
  .topbar{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;margin-bottom:24px}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2{font-size:23px;font-weight:800;letter-spacing:-.7px}
  .topbar p{font-size:13px;color:var(--muted);margin-top:3px;font-weight:500}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar-right{display:flex;align-items:center;gap:11px;flex-shrink:0;flex-wrap:nowrap}

  .search{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 14px;width:230px;transition:.2s}
  .search:focus-within{border-color:var(--gold);box-shadow:0 0 0 4px rgba(201,162,39,.13)}
  .search svg{width:16px;height:16px;color:#9aa8bc;flex-shrink:0}
  .search input{border:none;outline:none;font-family:inherit;font-size:13px;width:100%;background:transparent;color:var(--text)}
  .icon-btn{width:42px;height:42px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;position:relative;transition:.2s;color:#6b7a90}
  .icon-btn:hover{background:#fbf8ee;color:var(--gold-d);border-color:#ecdfb4}
  .icon-btn svg{width:18px;height:18px;stroke-width:2}
  .dot{position:absolute;top:10px;right:11px;width:7px;height:7px;border-radius:50%;background:#ef4444;border:2px solid #fff}
  .btn-primary{display:flex;align-items:center;gap:7px;padding:11px 17px;border:none;border-radius:12px;background:linear-gradient(135deg,#d9b64a,#c9a227);color:#fff;cursor:pointer;font-family:inherit;font-size:13px;font-weight:800;box-shadow:0 8px 20px rgba(201,162,39,.35);white-space:nowrap;flex-shrink:0}
  .btn-primary svg{width:16px;height:16px;stroke-width:2.6}
  .lang-select{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px 10px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;outline:none;transition:.2s;flex-shrink:0}
  .lang-select:hover{border-color:var(--gold);color:var(--gold-d)}
  .hamburger{display:none}
  .overlay{position:fixed;inset:0;background:rgba(14,23,38,.55);backdrop-filter:blur(2px);opacity:0;pointer-events:none;transition:.3s;z-index:90}
  .overlay.show{opacity:1;pointer-events:auto}
  @media (max-width:1080px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding:20px 18px 42px}.hamburger{display:grid}.search{width:170px}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr}}
  @media (max-width:640px){.topbar h2{font-size:19px}.search{display:none}.btn-primary span{display:none}.btn-primary{padding:11px}.main{padding:16px 14px 38px}}
</style>
@stack('hotel-head')
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
        <div class="brand-logo">🏨</div>
        <div>
          <h1 style="font-size: 16px; margin: 0;">{{ \Illuminate\Support\Str::limit($provider->name ?? 'Aurelia', 13) }}</h1>
          <span style="font-size: 9px; color: #8b9bb0;">HOTEL & RESORT</span>
        </div>
    </div>
  </div>
  <nav class="nav">
    <div class="nav-label">{{ __('messages.main') }}</div>

    <a href="{{ route('provider.hotel.dashboard') }}" class="{{ request()->routeIs('provider.hotel.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      {{ __('messages.dashboard') }}
    </a>

    <a href="{{ route('provider.hotel.bookings') }}" class="{{ request()->routeIs('provider.hotel.bookings') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
      {{ __('messages.bookings') }}
    </a>

    <a href="{{ route('provider.hotel.rooms') }}" class="{{ request()->routeIs('provider.hotel.rooms') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
      {{ __('messages.rooms') }}
    </a>

    <a href="{{ route('provider.hotel.guests') }}" class="{{ request()->routeIs('provider.hotel.guests') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      {{ __('messages.guests') }}
    </a>

    <a href="{{ route('provider.hotel.housekeeping') }}" class="{{ request()->routeIs('provider.hotel.housekeeping') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6"/><path d="M15 2v6"/><path d="M4 8h16l-1 13H5L4 8z"/></svg>
      {{ __('messages.housekeeping') }}
    </a>

    <a href="{{ route('provider.hotel.reports') }}" class="{{ request()->routeIs('provider.hotel.reports') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
      {{ __('messages.reports') }}
    </a>

    <a href="{{ route('provider.hotel.offers') }}" class="{{ request()->routeIs('provider.hotel.offers') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      {{ __('messages.offers') }}
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
  <div class="occ-card">
    <div class="occ-top"><span>{{ __('messages.occupancy') }}</span><span class="live"><i></i>{{ __('messages.live_upper') }}</span></div>
    <div class="occ-val" id="occVal">— <small>— / —</small></div>
    <div class="occ-bar"><div id="occBar"></div></div>
  </div>
  <div class="user-box">
    <div class="avatar">{{ $initials }}</div>
    <div>
      <div class="u-name">{{ $currentUser?->name ?? __('messages.guest') }}</div>
      <div class="u-role">{{ __('messages.general_manager') }}</div>
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
        <input type="text" placeholder="{{ __('messages.search_guests_rooms') }}">
      </div>
      <select class="lang-select" onchange="window.location.href='/lang/' + this.value">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 EN</option>
        <option value="np" {{ app()->getLocale() === 'np' ? 'selected' : '' }}>🇳🇵 NP</option>
      </select>
      <button class="icon-btn" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
      <button class="btn-primary" id="newBookingBtn" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        <span>{{ __('messages.new_booking') }}</span>
      </button>
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

{{-- New Booking Modal (universal) --}}
@php
    $__modalRooms = $rooms ?? collect();
    if ($__modalRooms->isEmpty() ?? true) {
        $__modalP = auth()->check() ? auth()->user()->ownProvider() : null;
        if ($__modalP) {
            $__modalRooms = \App\Models\HotelRoom::where('provider_id', $__modalP->id)->orderBy('room_number')->get();
        }
    }
@endphp
<div id="bookingModal" style="display:none;position:fixed;inset:0;background:rgba(14,23,38,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:520px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.new_booking') }}</h3>
            <button type="button" id="closeBookingModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.hotel.bookings.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.guest_name') }}</label>
                    <input type="text" name="guest_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.email_optional') }}</label>
                    <input type="email" name="guest_email" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.room') }}</label>
                    <select name="room_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        @foreach($__modalRooms as $r)
                            <option value="{{ $r->id }}">{{ __('messages.room') }} {{ $r->room_number }} ({{ $r->room_type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount_rs') }}</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.checkin_col') }}</label>
                    <input type="date" name="check_in_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.checkout_col') }}</label>
                    <input type="date" name="check_out_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.payment_status') }}</label>
                    <select name="payment_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="paid">{{ __('messages.paid') }}</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_booking') }}</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var bm = document.getElementById('bookingModal');
    document.getElementById('newBookingBtn')?.addEventListener('click', function() {
        if (bm) bm.style.display = 'flex';
    });
    document.getElementById('closeBookingModal')?.addEventListener('click', function() {
        if (bm) bm.style.display = 'none';
    });
    if (bm) {
        bm.addEventListener('click', function(e) {
            if (e.target === bm) bm.style.display = 'none';
        });
    }
});
</script>

@stack('hotel-scripts')
</body>
</html>
@extends('layouts.public')

@section('title', $provider->name . ' — ' . $service->name)
@section('meta_description', 'Explore handmade art & custom commissions by ' . $provider->name . '. Traditional Nepali crafts, sculptures, paintings & more.')
@section('meta_keywords', 'artcraft, ' . $provider->name . ', ' . ($service->category->name ?? 'art') . ', handmade, Nepal art')
@section('og_meta')
<meta property="og:title" content="{{ $provider->name }} - ArtCraft">
<meta property="og:description" content="Explore handmade art & custom commissions by {{ $provider->name }}.">
@if($provider->cover_image)
<meta property="og:image" content="{{ Storage::url($provider->cover_image) }}">
@endif
@endsection

@section('content')
<style>
    *, *::before, *::after { box-sizing: border-box; }
    .ac-hero { height: 340px; background-color: #7c3aed; background-size: cover; background-position: center; border-radius: 0 0 28px 28px; position: relative; overflow: hidden; }
    .ac-hero-overlay { position:absolute; inset:0; background: linear-gradient(180deg, rgba(15,23,42,0.15) 0%, rgba(15,23,42,0.35) 100%); z-index:1; }
    .ac-hero-content { position:absolute; inset:0; z-index:2; display:flex; align-items:center; justify-content:center; padding:0 24px; text-align:center; }
    .ac-hero-inner { display:flex; flex-direction:column; align-items:center; gap:14px; }
    .ac-hero-logo { width:96px; height:96px; background:rgba(255,255,255,0.18); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:50px; backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); border:2px solid rgba(255,255,255,0.4); box-shadow:0 8px 24px rgba(0,0,0,0.25); }
    .ac-hero-name { font-size:28px; font-weight:900; color:#fff; letter-spacing:-0.5px; text-shadow:0 3px 12px rgba(0,0,0,0.6); line-height:1.15; margin:0; }
    .ac-hero-sub { font-size:12px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:2px; text-shadow:0 2px 8px rgba(0,0,0,0.5); }

    .ac-profile-container { max-width:1120px; margin:-80px auto 40px; padding:0 24px; position:relative; z-index:10; }
    .ac-profile-card { background:#fff; border-radius:22px; padding:28px 36px; box-shadow:0 15px 40px rgba(0,0,0,0.08); border:1px solid #ede9fe; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px; }
    .ac-profile-left { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
    .ac-logo { width:76px; height:76px; border-radius:16px; background:linear-gradient(135deg,#7c3aed,#5b21b6); display:flex; align-items:center; justify-content:center; color:#fff; font-size:32px; font-weight:700; overflow:hidden; }
    .ac-logo img { width:100%; height:100%; object-fit:cover; }
    .ac-name { font-size:24px; font-weight:700; color:#0f172a; margin:0 0 4px; }
    .ac-meta { color:#64748b; font-size:14px; display:flex; gap:16px; flex-wrap:wrap; }
    .ac-verified { display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; }
    .ac-cta { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; border-radius:12px; font-weight:700; font-size:14px; text-decoration:none; transition:filter .2s; }
    .ac-cta:hover { filter:brightness(1.08); }

    .ac-section { max-width:1120px; margin:0 auto 40px; padding:0 24px; }
    .ac-section-title { font-size:22px; color:#0f172a; margin:0 0 20px; display:flex; align-items:center; gap:10px; }
    .ac-filter { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
    .ac-filter-btn { padding:8px 16px; border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; background:#fff; color:#64748b; border:1px solid #e2e8f0; }
    .ac-filter-btn.active { background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; box-shadow:0 4px 12px rgba(124,58,237,0.2); }

    .ac-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:20px; }
    .ac-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; text-decoration:none; color:inherit; display:flex; flex-direction:column; }
    .ac-card-img { height:220px; background:linear-gradient(135deg,#ede9fe,#c4b5fd); display:flex; align-items:center; justify-content:center; font-size:80px; overflow:hidden; position:relative; }
    .ac-card-img img { width:100%; height:100%; object-fit:cover; }
    .ac-card-img-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#ede9fe 0%,#c4b5fd 60%,#a78bfa 100%); }
    .ac-card-img-ph span { font-size:80px; line-height:1; filter:drop-shadow(0 4px 10px rgba(91,33,182,0.25)); }
    .ac-card-body { padding:16px 18px; flex:1; display:flex; flex-direction:column; }
    .ac-card-cat { display:inline-block; background:#ede9fe; color:#5b21b6; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:8px; width:max-content; }
    .ac-card-name { font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px; line-height:1.3; }
    .ac-card-artist { font-size:12px; color:#64748b; margin:0 0 10px; }
    .ac-card-footer { display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:10px; border-top:1px dashed #e2e8f0; }
    .ac-card-price { font-size:18px; font-weight:800; color:#0f172a; }
    .ac-card-price small { font-size:11px; color:#64748b; font-weight:600; }
    .ac-card-badge { font-size:10px; font-weight:800; padding:3px 8px; border-radius:6px; text-transform:uppercase; }
    .ac-card-badge.available { background:#dcfce7; color:#166534; }
    .ac-card-badge.sold { background:#fdecec; color:#991b1b; }

    .ac-empty { text-align:center; padding:60px 20px; background:#fff; border:2px dashed #cbd5e1; border-radius:16px; }
    .ac-empty h3 { font-size:16px; margin:0 0 8px; }
    .ac-empty p { color:#64748b; font-size:13px; margin:0; }
    .ac-alert { padding:14px 18px; border-radius:12px; margin:20px 24px; max-width:1120px; font-size:13px; font-weight:700; }

    @media(max-width:640px){
        .ac-profile-card{padding:20px;}
        .ac-name{font-size:20px;}
        .ac-hero{height:280px;}
        .ac-hero-name{font-size:22px;}
        .ac-hero-logo{width:72px;height:72px;font-size:38px;}
    }
</style>

<!-- HERO -->
<div class="ac-hero" style="background-image: url('{{ $provider->cover_image ? Storage::url($provider->cover_image) : asset('images/artcraft-cover.jpg') }}');">
    <div class="ac-hero-overlay"></div>
    <div class="ac-hero-content">
        <div class="ac-hero-inner">
            <h1 class="ac-hero-name">{{ $provider->name }}</h1>
            <div class="ac-hero-sub">{{ __('messages.artcraft_hero_subtitle') }}</div>
        </div>
    </div>
</div>

<!-- PROFILE -->
<div class="ac-profile-container">
    <div class="ac-profile-card">
        <div class="ac-profile-left">
            <div class="ac-logo">
                @if($provider->logo_url)
                    <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}">
                @else
                    🎨
                @endif
            </div>
            <div>
                <h1 class="ac-name">{{ $provider->name }}</h1>
                <div class="ac-meta">
                    <span>📍 {{ $provider->address ?? 'Kathmandu, Nepal' }}</span>
                    @if($provider->is_verified ?? false)
                        <span class="ac-verified">✓ Verified</span>
                    @endif
                    @if($provider->average_rating > 0)
                        <span>⭐ {{ number_format($provider->average_rating, 1) }} ({{ $provider->reviews_count }})</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('public.artcraft.custom', $service->slug) }}" class="ac-cta">
            <span>✨</span> {{ __('messages.artcraft_custom_commission') }}
        </a>
    </div>
</div>

<!-- SUCCESS ALERT -->
@if(session('success'))
<div class="ac-alert" style="background:#dcfce7;color:#166534;border:1px solid #86efac">
    ✅ {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="ac-alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
    @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
</div>
@endif

<!-- GALLERY -->
<div class="ac-section">
    <h2 class="ac-section-title">🎨 {{ __('messages.artcraft_gallery_heading') }}</h2>

    @if($categories->count() > 1)
    <div class="ac-filter">
        <a href="{{ route('public.artcraft.show', $service->slug) }}" class="ac-filter-btn {{ !request('cat') ? 'active' : '' }}">{{ __('messages.tab_all') ?? 'All' }}</a>
        @foreach($categories as $cat)
            @php
                $__catKey = 'artcraft_category_' . strtolower($cat);
                $__catLabel = \Illuminate\Support\Facades\Lang::has('messages.' . $__catKey)
                    ? __('messages.' . $__catKey)
                    : $cat;
            @endphp
            <a href="{{ route('public.artcraft.show', $service->slug) }}?cat={{ urlencode($cat) }}" class="ac-filter-btn {{ request('cat') === $cat ? 'active' : '' }}">{{ $__catLabel }}</a>
        @endforeach
    </div>
    @endif

    @php
        $filtered = request('cat') ? $artworks->where('category', request('cat')) : $artworks;
    @endphp

    @if($filtered->isEmpty())
    <div class="ac-empty">
        <div style="font-size:64px;margin-bottom:12px">🎨</div>
        <h3>{{ __('messages.artcraft_no_artworks') ?? 'No artworks yet' }}</h3>
        <p>{{ __('messages.artcraft_no_artworks_hint') ?? 'Check back soon or request a custom commission.' }}</p>
    </div>
    @else
    <div class="ac-grid">
        @foreach($filtered as $artwork)
            <a href="{{ route('public.artcraft.artwork-detail', [$service->slug, $artwork->id]) }}" class="ac-card">
                <div class="ac-card-img">
                    @if($artwork->image_path)
                        <img src="{{ Storage::url($artwork->image_path) }}" alt="{{ $artwork->name }}">
                    @else
                        <div class="ac-card-img-ph">
                            <span>{{ $artwork->emoji ?: '🎨' }}</span>
                        </div>
                    @endif
                </div>
                <div class="ac-card-body">
                    @php
                        $__catKey = 'artcraft_category_' . strtolower($artwork->category);
                        $__catLabel = $artwork->category && \Illuminate\Support\Facades\Lang::has('messages.' . $__catKey)
                            ? __('messages.' . $__catKey)
                            : ($artwork->category ?: __('messages.artcraft_art'));
                    @endphp
                    <span class="ac-card-cat">{{ $__catLabel }}</span>
                    <h3 class="ac-card-name">{{ $artwork->name }}</h3>
                    <p class="ac-card-artist">{{ __('messages.artcraft_by') }} {{ $artwork->artist_name ?: $provider->name }}</p>
                    <div class="ac-card-footer">
                        <div class="ac-card-price">Rs {{ number_format($artwork->price, 0) }}<small> NPR</small></div>
                        <span class="ac-card-badge {{ $artwork->status === 'available' ? 'available' : 'sold' }}">
                            {{ __('messages.artcraft_' . $artwork->status) ?? ucfirst($artwork->status) }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
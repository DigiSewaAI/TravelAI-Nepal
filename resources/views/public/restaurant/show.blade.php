@extends('layouts.public')

@section('title', $service->name)

@section('content')
<style>
    /* ========== GLOBAL ========== */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* ========== PREMIUM HERO ========== */
    .rest-banner {
        height: 320px;
        background-color: #ea580c;
        background-size: cover;
        background-position: center;
        border-radius: 0 0 30px 30px;
        position: relative;
    }
    .rest-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%);
        border-radius: 0 0 30px 30px;
    }
    
    .rest-profile-container {
        max-width: 1100px;
        margin: -80px auto 40px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
    }
    .rest-profile-card {
        background: #fff;
        border-radius: 24px;
        padding: 30px 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        border: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
    }
    .rest-profile-left {
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }
    .rest-profile-logo-wrapper {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        border: 5px solid #fff;
        background: #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        margin-top: -80px;
        overflow: hidden;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    }
    .rest-profile-logo { width: 100%; height: 100%; object-fit: cover; }
    .rest-profile-logo-fallback {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        display: flex; align-items: center; justify-content: center;
        font-size: 46px; color: #f97316;
    }
    
    .rest-profile-info { flex: 1; min-width: 250px; }
    .rest-profile-title-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
    .rest-profile-title-row h1 { font-size: 34px; font-weight: 900; color: #111; margin: 0; letter-spacing: -0.5px; }
    
    .verified-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
        padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase;
    }
    .rest-profile-location {
        font-size: 15px; color: #6b7280; margin: 0; display: flex; align-items: center; gap: 6px; font-weight: 500;
    }
    
    .rest-profile-stats {
        display: flex;
        align-items: center;
        gap: 30px;
        background: #f9fafb;
        padding: 15px 30px;
        border-radius: 16px;
        border: 1px solid #f3f4f6;
    }
    .rest-stat { text-align: center; }
    .rest-stat-value { display: block; font-size: 24px; font-weight: 900; color: #111; line-height: 1.1; }
    .rest-stat-label { display: block; font-size: 11px; font-weight: 800; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    @media (max-width: 768px) {
        .rest-banner { height: 220px; border-radius: 0 0 20px 20px; }
        .rest-profile-container { margin-top: -60px; }
        .rest-profile-card { padding: 20px; flex-direction: column; align-items: flex-start; text-align: left; }
        .rest-profile-logo-wrapper { width: 100px; height: 100px; margin-top: -70px; }
        .rest-profile-title-row h1 { font-size: 26px; }
        .rest-profile-stats { width: 100%; justify-content: space-between; padding: 15px; gap: 10px; }
        .rest-stat-value { font-size: 20px; }
    }

    /* ========== CONTAINER ========== */
    .rest-container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
    .rest-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 32px; padding: 50px 0; }
    @media (max-width: 992px) { .rest-layout { grid-template-columns: 1fr; gap: 40px; padding: 30px 0; } }

        /* ========== OFFERS ========== */
    .offers-grid { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 40px; margin-top: 10px; }
    .offer-card {
        flex: 1; min-width: 280px;
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border: 2px dashed #fdba74; border-radius: 18px;
        padding: 20px 24px; display: flex; align-items: center; gap: 20px;
        box-shadow: 0 8px 20px rgba(249,115,22,0.08);
        transition: transform 0.2s ease;
    }
    .offer-card:hover { transform: translateY(-3px); }
    .offer-icon { font-size: 42px; line-height: 1; }
    .offer-content { flex: 1; }
    .offer-title { font-size: 18px; font-weight: 800; color: #111; margin: 0 0 6px; letter-spacing: -0.3px; }
    .offer-discount {
        display: inline-block; background: #f97316; color: #fff;
        padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 800; margin-bottom: 8px;
        box-shadow: 0 4px 10px rgba(249,115,22,0.3);
    }
    .offer-validity { font-size: 12px; color: #78716c; margin: 0; font-weight: 600; }

    /* ========== SECTION TITLES ========== */
    .section-title { display: flex; align-items: center; gap: 12px; font-size: 26px; font-weight: 900; color: #111; margin: 0 0 24px; letter-spacing: -0.5px; }
    .section-title i { color: #f97316; font-size: 22px; }
    .section-title::after { content: ''; flex: 1; height: 2px; background: linear-gradient(90deg, #f97316 0%, #eee 60%); border-radius: 2px; }

    /* ========== MENU ========== */
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .menu-card { background: #fff; border: 1px solid #eee; border-radius: 18px; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
    .menu-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(249,115,22,0.15); border-color: #fdba74; }
    .menu-image { height: 180px; position: relative; background: linear-gradient(135deg, #fff7ed, #ffedd5); overflow: hidden; display: flex; align-items: center; justify-content: center; }
    .menu-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
    .menu-card:hover .menu-image img { transform: scale(1.08); }
    .menu-image .placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; color: #fb923c; }
    .menu-image .placeholder i { font-size: 48px; }
    .menu-image .placeholder span { font-size: 12px; font-weight: 700; color: #92400e; }
    .menu-info { padding: 18px; }
    .menu-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 6px; }
    .menu-name { font-size: 16px; font-weight: 800; color: #111; margin: 0; line-height: 1.3; }
    .menu-price { font-size: 16px; font-weight: 900; color: #f97316; white-space: nowrap; }
    .menu-category { display: inline-block; font-size: 10px; text-transform: uppercase; font-weight: 800; color: #78716c; background: #f5f5f4; padding: 4px 10px; border-radius: 20px; margin-bottom: 8px; }
    .menu-desc { font-size: 12.5px; color: #78716c; line-height: 1.5; margin: 0; }

    /* ========== TABLES ========== */
    .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 50px; }
    .table-card { background: #fff; border: 2px solid #eee; border-radius: 16px; overflow: hidden; cursor: pointer; transition: all 0.25s ease; }
    .table-card:hover { border-color: #f97316; transform: translateY(-3px); box-shadow: 0 12px 24px rgba(249,115,22,0.12); }
    .table-card.is-occupied { opacity: 0.6; }
    .table-image { height: 120px; background: linear-gradient(135deg, #fff7ed, #ffedd5); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
    .table-image img { width: 100%; height: 100%; object-fit: cover; }
    .table-image .placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: #fb923c; }
    .table-image .placeholder i { font-size: 36px; }
    .table-image .placeholder span { font-size: 11px; font-weight: 700; color: #92400e; }
    .table-info { padding: 14px 12px; text-align: center; }
    .table-name { font-size: 15px; font-weight: 800; color: #111; margin: 0 0 4px; }
    .table-seats { font-size: 12px; color: #78716c; margin: 0 0 8px; }
    .table-seats i { color: #fb923c; margin-right: 4px; }
    .table-status { display: inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.5px; }
    .table-status.available { background: #dcfce7; color: #166534; }
    .table-status.occupied { background: #fee2e2; color: #991b1b; }
    .table-status.reserved { background: #fef3c7; color: #92400e; }
    .table-status.cleaning { background: #e0e7ff; color: #3730a3; }

    /* ========== SIDEBAR ========== */
    .info-sidebar { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 20px; height: fit-content; }
    .info-card { background: #fff; border: 1px solid #eee; border-radius: 18px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .info-card h3 { font-size: 15px; font-weight: 800; color: #111; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 2px solid #fff7ed; display: flex; align-items: center; gap: 8px; }
    .info-card h3 i { color: #f97316; }
    .info-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .info-item:last-child { margin-bottom: 0; }
    .info-icon { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #fff7ed, #ffedd5); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .info-icon i { color: #f97316; font-size: 14px; }
    .info-content { flex: 1; min-width: 0; }
    .info-label { font-size: 10px; font-weight: 800; color: #a8a29e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .info-value { font-size: 13px; font-weight: 700; color: #111; word-break: break-word; line-height: 1.5; }
    .info-value a { color: #f97316; text-decoration: none; }
    .info-value a:hover { text-decoration: underline; }
    .info-desc { font-size: 13px; color: #78716c; line-height: 1.6; margin: 0; }
    .cta-card { background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fdba74; }
    .cta-card h3 { border-color: #fdba74; }
    .cta-text { font-size: 13px; color: #78716c; margin: 0 0 16px; line-height: 1.5; }
    .cta-btn { display: block; background: #f97316; color: #fff; padding: 13px; border-radius: 10px; font-weight: 800; font-size: 14px; text-align: center; text-decoration: none; transition: 0.2s; }
    .cta-btn:hover { background: #ea580c; transform: translateY(-1px); }

    /* ========== RESERVATION ========== */
    .reservation-section { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 32px; padding: 50px; margin-bottom: 50px; border: 1px solid #fdba74; position: relative; overflow: hidden; }
    .reservation-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start; position: relative; }
    @media (max-width: 900px) { .reservation-grid { grid-template-columns: 1fr; gap: 30px; } .reservation-section { padding: 30px 20px; border-radius: 24px; } }
    .reservation-info h2 { font-size: 36px; font-weight: 900; color: #111; margin: 0 0 16px; letter-spacing: -1px; line-height: 1.15; }
    .reservation-info p { font-size: 16px; color: #57534e; line-height: 1.6; margin: 0 0 24px; }
    .reservation-features { list-style: none; padding: 0; margin: 0; }
    .reservation-features li { display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 600; color: #374151; margin-bottom: 14px; }
    .reservation-features li i { color: #f97316; background: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; box-shadow: 0 2px 6px rgba(249,115,22,0.15); }
    .reservation-form-box { background: #fff; padding: 32px; border-radius: 20px; box-shadow: 0 20px 50px rgba(249,115,22,0.15); box-sizing: border-box; overflow: hidden; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 11px; font-weight: 800; color: #44403c; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-input { width: 100%; padding: 13px 15px; border: 1.5px solid #e7e5e4; border-radius: 11px; font-family: inherit; font-size: 14px; background: #fafaf9; color: #111; outline: none; transition: all 0.2s; box-sizing: border-box; }
    .form-input:focus { border-color: #f97316; background: #fff; box-shadow: 0 0 0 4px rgba(249,115,22,0.1); }
    .btn-reserve { width: 100%; padding: 16px; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; border: none; border-radius: 11px; font-size: 15px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 10px; letter-spacing: 0.3px; box-shadow: 0 8px 20px rgba(249,115,22,0.3); }
    .btn-reserve:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(249,115,22,0.4); }

    /* ========== ALERTS ========== */
    .alert-success { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 700; border: 1px solid #6ee7b7; display: flex; align-items: center; gap: 10px; font-size: 14px; animation: slideDown 0.4s ease; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* ========== EMPTY STATE ========== */
    .empty-state { text-align: center; padding: 60px 20px; color: #78716c; }
    .empty-state i { font-size: 56px; color: #d6d3d1; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: 14px; margin: 0; }

    /* ========== HIGHLIGHT ========== */
    .highlight-pulse { animation: pulseHighlight 2s ease; }
    @keyframes pulseHighlight { 0%,100% { box-shadow: 0 20px 50px rgba(249,115,22,0.15); } 50% { box-shadow: 0 20px 50px rgba(249,115,22,0.5), 0 0 0 6px rgba(249,115,22,0.1); } }

    /* ========== MOBILE ========== */
    @media (max-width: 767px) {
        .rest-hero h1 { font-size: 32px; }
        .hero-stats { gap: 20px; padding: 14px 24px; }
        .hero-stat-num { font-size: 22px; }
        .hero-stat-label { font-size: 10px; }
        .section-title { font-size: 22px; }
        .menu-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
        .menu-image { height: 140px; }
        .tables-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .table-image { height: 100px; }
        .info-sidebar { position: static; }
        .reservation-info h2 { font-size: 26px; }
    }

    /* ========== MOBILE FIX (added 2026-11-14) ========== */
    @media (max-width: 900px) {
        .reservation-section { padding: 30px 16px !important; border-radius: 24px !important; }
        .reservation-form-box { padding: 20px 16px !important; }
        .reservation-info h2 { font-size: 24px !important; }
        .reservation-info p { font-size: 14px !important; }
        .reservation-features li { font-size: 13px !important; }
    }
    @media (max-width: 480px) {
        .reservation-grid { gap: 20px !important; }
        .reservation-form-box > form > div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        .reservation-info h2 { font-size: 22px !important; }
    }
    @media (max-width: 480px) {
        .menu-grid { grid-template-columns: 1fr; }
        .hero-stats { flex-direction: column; gap: 10px; padding: 16px 20px; }
        .hero-stat-divider { width: 40px; height: 1px; }
    }
</style>

{{-- SUCCESS --}}
@if(session('success'))
    <div style="max-width: 1280px; margin: 24px auto 0; padding: 0 24px;">
        <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    </div>
@endif

{{-- PREMIUM HERO & PROFILE --}}
<div class="rest-banner" style="background-image: url('{{ $provider->cover_image ? Storage::url($provider->cover_image) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1200' }}');"></div>

<div class="rest-profile-container">
    <div class="rest-profile-card">
        <div class="rest-profile-left">
            <div class="rest-profile-logo-wrapper">
                @if($provider->logo_url)
                    <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}" class="rest-profile-logo">
                @else
                    <div class="rest-profile-logo-fallback"><i class="fas fa-utensils"></i></div>
                @endif
            </div>
            <div class="rest-profile-info">
                <div class="rest-profile-title-row">
                    <h1>{{ $provider->name }}</h1>
                    @if($provider->verification_status === 'verified')
                        <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified Provider</span>
                    @endif
                </div>
                <p class="rest-profile-location">
                    <i class="fas fa-map-marker-alt" style="color:#f97316;"></i> {{ $provider->address ?? 'Kathmandu, Nepal' }}
                </p>
            </div>
        </div>

        <div class="rest-profile-stats">
            <div class="rest-stat">
                <span class="rest-stat-value">{{ $menuItems->count() }}</span>
                <span class="rest-stat-label">Menu Items</span>
            </div>
            <div class="rest-stat">
                <span class="rest-stat-value">{{ $tables->count() }}</span>
                <span class="rest-stat-label">Tables</span>
            </div>
            <div class="rest-stat">
                <span class="rest-stat-value">{{ $provider->average_rating ? number_format($provider->average_rating, 1) : 'New' }}</span>
                <span class="rest-stat-label">Rating</span>
            </div>
        </div>
    </div>
</div>

<div class="rest-container">
    
    {{-- 🆕 OFFERS SECTION --}}
    @if(isset($offers) && $offers->count() > 0)
        <div class="offers-grid">
            @foreach($offers as $offer)
                <div class="offer-card">
                    <div class="offer-icon">🎉</div>
                    <div class="offer-content">
                        <h3 class="offer-title">{{ $offer->title }}</h3>
                        <span class="offer-discount">{{ $offer->discount }}</span>
                        <p class="offer-validity">Valid until: {{ \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="rest-layout">
        {{-- LEFT --}}
        <div>
            {{-- MENU --}}
            <h2 class="section-title"><i class="fas fa-utensils"></i> Explore Our Menu</h2>
            @if($menuItems->isEmpty())
                <div class="empty-state"><i class="fas fa-utensils"></i><p>Menu is being updated. Please check back later.</p></div>
            @else
                <div class="menu-grid">
                    @foreach($menuItems as $item)
                        <div class="menu-card">
                            <div class="menu-image">
                                @if(!empty($item->image_path))
                                    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}">
                                @else
                                    <div class="placeholder"><i class="fas fa-utensils"></i><span>{{ $item->name }}</span></div>
                                @endif
                            </div>
                            <div class="menu-info">
                                <div class="menu-header">
                                    <h3 class="menu-name">{{ $item->name }}</h3>
                                    <span class="menu-price">Rs. {{ number_format($item->price, 0) }}</span>
                                </div>
                                <span class="menu-category">{{ $item->category }}</span>
                                @if($item->description)
                                    <p class="menu-desc">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- TABLES --}}
            <h2 class="section-title" style="margin-top: 50px;"><i class="fas fa-chair"></i> Our Tables</h2>
            @if($tables->isEmpty())
                <div class="empty-state"><i class="fas fa-chair"></i><p>No tables available at the moment.</p></div>
            @else
                <div class="tables-grid">
                    @foreach($tables as $table)
                        <div class="table-card {{ $table->status === 'occupied' ? 'is-occupied' : '' }}"
                             data-table-id="{{ $table->id }}"
                             onclick="selectTable({{ $table->id }})"
                             title="Click to reserve this table">
                            <div class="table-image">
                                @if(!empty($table->image_path))
                                    <img src="{{ Storage::url($table->image_path) }}" alt="Table {{ $table->table_number }}">
                                @else
                                    <div class="placeholder"><i class="fas fa-chair"></i><span>Table {{ $table->table_number }}</span></div>
                                @endif
                            </div>
                            <div class="table-info">
                                <h3 class="table-name">Table {{ $table->table_number }}</h3>
                                <p class="table-seats"><i class="fas fa-users"></i> {{ $table->seats }} Seats</p>
                                <span class="table-status {{ $table->status }}">{{ ucfirst(str_replace('_', ' ', $table->status)) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- SIDEBAR --}}
        <aside class="info-sidebar">
            <div class="info-card">
                <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                @if($provider->contact_email)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-content"><div class="info-label">Email</div><div class="info-value"><a href="mailto:{{ $provider->contact_email }}">{{ $provider->contact_email }}</a></div></div>
                </div>
                @endif
                @if($provider->contact_phone)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone"></i></div>
                    <div class="info-content"><div class="info-label">Phone</div><div class="info-value"><a href="tel:{{ $provider->contact_phone }}">{{ $provider->contact_phone }}</a></div></div>
                </div>
                @endif
                @if($provider->website)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-globe"></i></div>
                    <div class="info-content"><div class="info-label">Website</div><div class="info-value"><a href="{{ $provider->website }}" target="_blank" rel="noopener">{{ $provider->website }}</a></div></div>
                </div>
                @endif
                @if($provider->address)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content"><div class="info-label">Address</div><div class="info-value">{{ $provider->address }}</div></div>
                </div>
                @endif
            </div>

            @if($provider->description)
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> About Us</h3>
                <p class="info-desc">{{ $provider->description }}</p>
            </div>
            @endif

            <div class="info-card cta-card">
                <h3><i class="fas fa-calendar-check"></i> Reserve a Table</h3>
                <p class="cta-text">Book your spot instantly — no advance payment needed.</p>
                <a href="#reservation" class="cta-btn">Book Now ↓</a>
            </div>
        </aside>
    </div>

    {{-- RESERVATION --}}
    <div class="reservation-section" id="reservation">
        <div class="reservation-grid">
            <div class="reservation-info">
                <h2>Book Your Table</h2>
                <p>Skip the wait and secure your spot. Reserve a table for your family, friends, or a special date night.</p>
                <ul class="reservation-features">
                    <li><i class="fas fa-check"></i> Instant Confirmation</li>
                    <li><i class="fas fa-check"></i> Choose Your Preferred Table</li>
                    <li><i class="fas fa-check"></i> No Advance Payment Required</li>
                </ul>
            </div>

            <div class="reservation-form-box" id="reservationFormBox">
                <form action="{{ route('public.restaurant.reserve', $service->slug) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Select Table</label>
                        <select name="table_id" id="tableSelect" required class="form-input">
                            <option value="">Choose a table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->seats }} Seats)</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div class="form-group"><label class="form-label">Date</label><input type="date" name="reservation_date" required class="form-input" min="{{ date('Y-m-d') }}"></div>
                        <div class="form-group"><label class="form-label">Time</label><input type="time" name="reservation_time" required class="form-input"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Number of Guests</label><input type="number" name="guests" required min="1" max="50" class="form-input" placeholder="e.g. 2"></div>
                    <div class="form-group"><label class="form-label">Your Name</label><input type="text" name="customer_name" required class="form-input" placeholder="John Doe"></div>
                    <div class="form-group"><label class="form-label">Phone Number</label><input type="text" name="customer_phone" required class="form-input" placeholder="+977-98XXXXXXXX"></div>
                    <div class="form-group"><label class="form-label">Email (Optional)</label><input type="email" name="customer_email" class="form-input" placeholder="john@example.com"></div>
                    <div class="form-group"><label class="form-label">Special Requests (Optional)</label><textarea name="special_requests" rows="2" class="form-input" placeholder="Window seat, birthday setup, etc."></textarea></div>
                    <button type="submit" class="btn-reserve"><i class="fas fa-calendar-check"></i> Confirm Reservation</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectTable(tableId) {
        const select = document.getElementById('tableSelect');
        if (select) select.value = tableId;
        document.getElementById('reservation').scrollIntoView({ behavior: 'smooth', block: 'start' });
        const formBox = document.getElementById('reservationFormBox');
        if (formBox) {
            formBox.classList.add('highlight-pulse');
            setTimeout(() => formBox.classList.remove('highlight-pulse'), 2000);
        }
    }
</script>
@endsection
@extends('layouts.public')

@section('title', $provider->name . ' — ' . $service->name)
@section('meta_description', 'Book a room at ' . $provider->name . '. View amenities, check room availability, and book your stay instantly at ' . ($provider->address ?? 'Kathmandu') . '.')
@section('meta_keywords', 'hotel, ' . $provider->name . ', ' . ($service->category->name ?? 'accommodation') . ', book room, ' . ($provider->address ?? 'Kathmandu'))
@section('og_meta')
<meta property="og:title" content="{{ $provider->name }} - Hotel">
<meta property="og:description" content="Book your stay at {{ $provider->name }}. Explore rooms and reserve instantly.">
@if($provider->cover_image)
<meta property="og:image" content="{{ Storage::url($provider->cover_image) }}">
@endif
@endsection

@section('content')
<style>
    /* ========== GLOBAL ========== */
    *, *::before, *::after { box-sizing: border-box; }
    
    /* ========== PREMIUM HOTEL HERO ========== */
    .hotel-hero {
        position: relative;
        height: 480px;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        overflow: hidden;
    }
    .hotel-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.75) 100%);
    }
    .hotel-hero::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: linear-gradient(180deg, transparent, #f9fafb);
    }
    
    /* ========== FLOATING PROFILE CARD ========== */
    .hotel-profile-container {
        max-width: 1100px;
        margin: -140px auto 40px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
    }
    .hotel-profile-card {
        background: #fff;
        border-radius: 28px;
        padding: 36px 40px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .hotel-profile-left { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
    .hotel-logo-wrapper {
        width: 130px;
        height: 130px;
        border-radius: 24px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 12px 30px rgba(14,165,233,0.35);
        overflow: hidden;
    }
    .hotel-logo-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .hotel-logo-wrapper i { font-size: 56px; color: #fff; }
    .hotel-profile-title { font-size: 38px; font-weight: 900; color: #0f172a; margin: 0 0 10px; letter-spacing: -0.5px; line-height: 1.1; }
    .hotel-profile-location { font-size: 15px; color: #64748b; margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
    .hotel-profile-location i { color: #0ea5e9; }
    .hotel-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .hotel-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #ecfdf5; color: #059669;
        padding: 6px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
        border: 1px solid #a7f3d0;
    }
    .hotel-badge.star { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    
    .hotel-profile-stats {
        display: flex;
        gap: 8px;
        background: #f8fafc;
        padding: 20px 28px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }
    .hotel-stat { text-align: center; padding: 0 16px; }
    .hotel-stat:not(:last-child) { border-right: 1px solid #e2e8f0; }
    .hotel-stat-value { display: block; font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1; }
    .hotel-stat-label { display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 6px; }
    
    /* ========== MAIN CONTAINER ========== */
    .hotel-container { max-width: 1280px; margin: 0 auto; padding: 0 24px 60px; }
    .hotel-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
    @media (max-width: 992px) { .hotel-layout { grid-template-columns: 1fr; gap: 30px; } }
    
    /* ========== SECTION HEADERS ========== */
    .section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .section-header h2 { font-size: 28px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.5px; }
    .section-header .icon-circle {
        width: 48px; height: 48px; border-radius: 14px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px rgba(14,165,233,0.3);
    }
    .section-header .icon-circle i { color: #fff; font-size: 20px; }
    
    /* ========== AMENITIES ========== */
    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
        margin-bottom: 50px;
    }
    .amenity-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 16px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .amenity-card:hover {
        transform: translateY(-4px);
        border-color: #7dd3fc;
        box-shadow: 0 12px 24px rgba(14,165,233,0.12);
    }
    .amenity-icon {
        width: 52px; height: 52px;
        margin: 0 auto 10px;
        border-radius: 14px;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        display: flex; align-items: center; justify-content: center;
    }
    .amenity-icon i { color: #0284c7; font-size: 22px; }
    .amenity-name { font-size: 13px; font-weight: 700; color: #334155; margin: 0; }
    
    /* ========== OFFERS BANNER ========== */
    .offers-wrap { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 40px; }
    .offer-banner {
        flex: 1; min-width: 280px;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border: 2px dashed #fdba74;
        border-radius: 16px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .offer-emoji { font-size: 34px; }
    .offer-title { font-size: 16px; font-weight: 800; color: #111; margin: 0 0 4px; }
    .offer-discount {
        display: inline-block;
        background: #f97316; color: #fff;
        padding: 3px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 800;
    }
    .offer-valid { font-size: 11px; color: #78716c; margin-top: 4px; }
    
    /* ========== ROOM CARDS ========== */
    .rooms-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    .room-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .room-card:hover {
        transform: translateY(-6px);
        border-color: #7dd3fc;
        box-shadow: 0 20px 40px rgba(14,165,233,0.15);
    }
    .room-image {
        height: 200px;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        position: relative;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .room-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
    .room-card:hover .room-image img { transform: scale(1.05); }
    .room-image i { font-size: 64px; color: #bae6fd; }
    .room-status-badge {
        position: absolute;
        top: 12px; right: 12px;
        padding: 5px 12px; border-radius: 20px;
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
    }
    .room-status-badge.available { background: #dcfce7; color: #15803d; }
    .room-status-badge.occupied { background: #fee2e2; color: #b91c1c; }
    .room-status-badge.reserved { background: #fef3c7; color: #92400e; }
    .room-status-badge.dirty { background: #f1f5f9; color: #475569; }
    .room-status-badge.maintenance { background: #fef2f2; color: #991b1b; }
    
    .room-type-badge {
        position: absolute;
        top: 12px; left: 12px;
        padding: 5px 12px; border-radius: 20px;
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px;
        background: rgba(255,255,255,0.95);
        color: #0f172a;
        backdrop-filter: blur(4px);
    }
    
    .room-info { padding: 20px; display: flex; flex-direction: column; flex: 1; }
    .room-name { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
    .room-meta { display: flex; gap: 16px; margin-bottom: 14px; font-size: 12px; color: #64748b; }
    .room-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .room-meta i { color: #0ea5e9; }
    
    .room-price-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        margin-bottom: 14px;
    }
    .room-price { font-size: 24px; font-weight: 900; color: #0ea5e9; line-height: 1; }
    .room-price-label { font-size: 11px; color: #94a3b8; font-weight: 600; text-align: right; line-height: 1.3; }
    .room-price-label strong { display: block; color: #475569; }
    
    .btn-book-room {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-family: inherit;
        box-shadow: 0 6px 16px rgba(14,165,233,0.25);
    }
    .btn-book-room:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(14,165,233,0.4);
    }
    .btn-book-room:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
    }
    
    /* ========== SIDEBAR ========== */
    .hotel-sidebar { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 24px; height: fit-content; }
    .sidebar-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .sidebar-card h3 {
        font-size: 15px; font-weight: 800; color: #0f172a;
        margin: 0 0 16px; padding-bottom: 12px;
        border-bottom: 2px solid #f0f9ff;
        display: flex; align-items: center; gap: 8px;
    }
    .sidebar-card h3 i { color: #0ea5e9; }
    
    .info-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .info-row:last-child { margin-bottom: 0; }
    .info-icon-box {
        width: 38px; height: 38px; border-radius: 10px;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .info-icon-box i { color: #0284c7; font-size: 14px; }
    .info-content-wrap { flex: 1; min-width: 0; }
    .info-lbl { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .info-val { font-size: 13px; font-weight: 700; color: #0f172a; word-break: break-word; line-height: 1.4; }
    .info-val a { color: #0ea5e9; text-decoration: none; }
    .info-val a:hover { text-decoration: underline; }
    
    .check-time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .check-time-box {
        background: #f8fafc;
        padding: 12px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    .check-time-label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.4px; }
    .check-time-value { font-size: 15px; font-weight: 900; color: #0f172a; margin-top: 4px; }
    
    .sidebar-cta {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
        border: none;
    }
    .sidebar-cta h3 { color: #fff; border-color: rgba(255,255,255,0.15); }
    .sidebar-cta h3 i { color: #38bdf8; }
    .sidebar-cta p { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.5; margin: 0 0 16px; }
    .sidebar-cta a {
        display: block;
        background: #0ea5e9;
        color: #fff;
        padding: 13px;
        border-radius: 10px;
        text-align: center;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        transition: 0.2s;
    }
    .sidebar-cta a:hover { background: #38bdf8; transform: translateY(-1px); }
    
    /* ========== EMPTY STATE ========== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        color: #94a3b8;
    }
    .empty-state i { font-size: 56px; color: #cbd5e1; margin-bottom: 12px; display: block; }
    .empty-state p { font-size: 15px; margin: 0; font-weight: 600; }
    
    /* ========== ALERTS ========== */
    .alert-success {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 700;
        border: 1px solid #6ee7b7;
        display: flex; align-items: center; gap: 10px;
        font-size: 14px;
    }
    
    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .hotel-hero { height: 320px; }
        .hotel-profile-container { margin-top: -100px; }
        .hotel-profile-card { padding: 24px 20px; gap: 20px; }
        .hotel-logo-wrapper { width: 90px; height: 90px; border-radius: 18px; }
        .hotel-logo-wrapper i { font-size: 40px; }
        .hotel-profile-title { font-size: 26px; }
        .hotel-profile-location { font-size: 13px; }
        .hotel-profile-stats { width: 100%; padding: 16px; gap: 0; }
        .hotel-stat { flex: 1; padding: 0 8px; }
        .hotel-stat-value { font-size: 20px; }
        .hotel-stat-label { font-size: 9px; }
        .hotel-container { padding: 0 16px 40px; }
        .section-header h2 { font-size: 22px; }
        .rooms-grid { grid-template-columns: 1fr; }
        .amenities-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .amenity-card { padding: 14px 10px; }
        .amenity-icon { width: 42px; height: 42px; margin-bottom: 8px; }
        .amenity-icon i { font-size: 18px; }
        .amenity-name { font-size: 12px; }
        .hotel-sidebar { position: static; }
    }
    @media (max-width: 480px) {
        .hotel-profile-title { font-size: 22px; }
        .room-image { height: 160px; }
    }
</style>

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div style="max-width: 1280px; margin: 20px auto 0; padding: 0 24px;">
        <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    </div>
@endif

{{-- HERO SECTION --}}
<div class="hotel-hero" style="background-image: url('{{ $provider->cover_image ? Storage::url($provider->cover_image) : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1600' }}');"></div>

{{-- FLOATING PROFILE CARD --}}
<div class="hotel-profile-container">
    <div class="hotel-profile-card">
        <div class="hotel-profile-left">
            <div class="hotel-logo-wrapper">
                @if($provider->logo_url)
                    <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}">
                @else
                    <i class="fas fa-hotel"></i>
                @endif
            </div>
            <div>
                <h1 class="hotel-profile-title">{{ $provider->name }}</h1>
                <p class="hotel-profile-location">
                    <i class="fas fa-map-marker-alt"></i> {{ $provider->address ?? 'Kathmandu, Nepal' }}
                </p>
                <div class="hotel-badges">
                    @if($provider->verification_status === 'verified')
                        <span class="hotel-badge"><i class="fas fa-shield-alt"></i> {{ __('messages.public_verified') }}</span>
                    @endif
                    @if($hotelDetail && $hotelDetail->star_rating)
                        <span class="hotel-badge star"><i class="fas fa-star"></i> {{ $hotelDetail->star_rating }}{{ __('messages.public_star_hotel') }}</span>
                    @endif
                    @if($hotelDetail && $hotelDetail->property_type)
                        <span class="hotel-badge" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
                            <i class="fas fa-building"></i> {{ ucfirst($hotelDetail->property_type) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="hotel-profile-stats">
            <div class="hotel-stat">
                <span class="hotel-stat-value">{{ $rooms->count() }}</span>
                <span class="hotel-stat-label">{{ __('messages.public_rooms') }}</span>
            </div>
            <div class="hotel-stat">
                <span class="hotel-stat-value">{{ $hotelDetail->room_count ?? $rooms->count() }}</span>
                <span class="hotel-stat-label">{{ __('messages.public_total_capacity') }}</span>
            </div>
            <div class="hotel-stat">
                <span class="hotel-stat-value">{{ $hotelDetail && $hotelDetail->amenities ? count($hotelDetail->amenities) : 2 }}</span>
                <span class="hotel-stat-label">{{ __('messages.public_amenities') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div class="hotel-container">

    {{-- OFFERS (if any) --}}
    @if(isset($offers) && $offers->count() > 0)
    <div class="offers-wrap">
        @foreach($offers as $offer)
            <div class="offer-banner">
                <div class="offer-emoji">🎉</div>
                <div>
                    <h3 class="offer-title">{{ $offer->title }}</h3>
                    <span class="offer-discount">{{ $offer->discount }}</span>
                    <div class="offer-valid">{{ __('messages.public_valid_until') }}: {{ \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') }}</div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="hotel-layout">
        {{-- LEFT: Amenities + Rooms --}}
        <div>
            {{-- AMENITIES --}}
            <div class="section-header">
                <div class="icon-circle"><i class="fas fa-concierge-bell"></i></div>
                <h2>{{ __('messages.public_hotel_amenities') }}</h2>
            </div>

            @php
                $amenityIcons = [
                    'WiFi' => 'fa-wifi', 'Free WiFi' => 'fa-wifi', 'Internet' => 'fa-wifi',
                    'Parking' => 'fa-parking', 'Pool' => 'fa-swimming-pool', 'Swimming' => 'fa-swimming-pool',
                    'Gym' => 'fa-dumbbell', 'Fitness' => 'fa-dumbbell',
                    'Restaurant' => 'fa-utensils', 'Breakfast' => 'fa-coffee',
                    'Spa' => 'fa-spa', 'Bar' => 'fa-cocktail',
                    'AC' => 'fa-snowflake', 'Air Conditioning' => 'fa-snowflake',
                    'TV' => 'fa-tv', 'Laundry' => 'fa-tshirt',
                    'Room Service' => 'fa-concierge-bell', 'Airport Shuttle' => 'fa-shuttle-van',
                ];
                $amenities = $hotelDetail && $hotelDetail->amenities ? $hotelDetail->amenities : ['Free WiFi', 'Parking'];
            @endphp

            <div class="amenities-grid">
                @foreach($amenities as $amenity)
                    @php
                        $icon = $amenityIcons[$amenity] ?? 'fa-check-circle';
                    @endphp
                    <div class="amenity-card">
                        <div class="amenity-icon"><i class="fas {{ $icon }}"></i></div>
                        <p class="amenity-name">{{ $amenity }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ROOMS --}}
            <div class="section-header" style="margin-top: 20px;">
                <div class="icon-circle"><i class="fas fa-bed"></i></div>
                <h2>{{ __('messages.public_available_rooms') }}</h2>
            </div>

            @if($rooms->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-bed"></i>
                    <p>{{ __('messages.public_no_rooms') }}</p>
                </div>
            @else
                <div class="rooms-grid">
                    @foreach($rooms as $room)
                        @php
                            $statusLabel = ucfirst($room->status === 'dirty' ? 'Cleaning' : $room->status);
                            $statusClass = in_array($room->status, ['available','free']) ? 'available' :
                                          ($room->status === 'occupied' ? 'occupied' :
                                          ($room->status === 'reserved' ? 'reserved' :
                                          ($room->status === 'dirty' ? 'dirty' : 'maintenance')));
                            $isBookable = in_array($room->status, ['available','free']);
                        @endphp
                        <div class="room-card">
                            <div class="room-image">
                                @if(!empty($room->image_path))
                                    <img src="{{ Storage::url($room->image_path) }}" alt="Room {{ $room->room_number }}">
                                @else
                                    <i class="fas fa-bed"></i>
                                @endif
                                <span class="room-type-badge">{{ $room->room_type }}</span>
                                <span class="room-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="room-info">
                                <h3 class="room-name">{{ __('messages.public_room') }} {{ $room->room_number }}</h3>
                                <div class="room-meta">
                                    <span><i class="fas fa-layer-group"></i> {{ __('messages.public_floor') }} {{ $room->floor }}</span>
                                    <span><i class="fas fa-door-open"></i> {{ $room->room_type }}</span>
                                </div>
                                <div class="room-price-row">
                                    <div class="room-price">Rs. {{ number_format($room->base_price ?? 0) }}</div>
                                    <div class="room-price-label">
                                        <strong>{{ __('messages.public_per_night_label') }}</strong>
                                        {{ __('messages.public_taxes_excluded') }}
                                    </div>
                                </div>
                                <form action="{{ route('public.hotel.book', $service->slug) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                    <button type="submit" class="btn-book-room" {{ $isBookable ? '' : 'disabled' }}>
                                        <i class="fas fa-calendar-check"></i>
                                        {{ $isBookable ? __('messages.public_book_room') : __('messages.public_not_available') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT: SIDEBAR --}}
        <aside class="hotel-sidebar">
            {{-- CHECK-IN / CHECK-OUT --}}
            @if($hotelDetail && ($hotelDetail->check_in_time || $hotelDetail->check_out_time))
            <div class="sidebar-card">
                <h3><i class="fas fa-clock"></i> {{ __('messages.public_check_times') }}</h3>
                <div class="check-time-grid">
                    <div class="check-time-box">
                        <div class="check-time-label">{{ __('messages.public_check_in') }}</div>
                        <div class="check-time-value">{{ \Carbon\Carbon::parse($hotelDetail->check_in_time ?? '14:00')->format('h:i A') }}</div>
                    </div>
                    <div class="check-time-box">
                        <div class="check-time-label">{{ __('messages.public_check_out') }}</div>
                        <div class="check-time-value">{{ \Carbon\Carbon::parse($hotelDetail->check_out_time ?? '12:00')->format('h:i A') }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- CONTACT --}}
            <div class="sidebar-card">
                <h3><i class="fas fa-address-card"></i> {{ __('messages.public_contact_info') }}</h3>
                @if($provider->contact_phone)
                <div class="info-row">
                    <div class="info-icon-box"><i class="fas fa-phone"></i></div>
                    <div class="info-content-wrap">
                        <div class="info-lbl">{{ __('messages.public_phone') }}</div>
                        <div class="info-val"><a href="tel:{{ $provider->contact_phone }}">{{ $provider->contact_phone }}</a></div>
                    </div>
                </div>
                @endif
                @if($provider->contact_email)
                <div class="info-row">
                    <div class="info-icon-box"><i class="fas fa-envelope"></i></div>
                    <div class="info-content-wrap">
                        <div class="info-lbl">{{ __('messages.public_email_optional', [], null) === '' ? 'Email' : 'Email' }}</div>
                        <div class="info-val"><a href="mailto:{{ $provider->contact_email }}">{{ $provider->contact_email }}</a></div>
                    </div>
                </div>
                @endif
                @if($provider->website)
                <div class="info-row">
                    <div class="info-icon-box"><i class="fas fa-globe"></i></div>
                    <div class="info-content-wrap">
                        <div class="info-lbl">Website</div>
                        <div class="info-val"><a href="{{ $provider->website }}" target="_blank">{{ $provider->website }}</a></div>
                    </div>
                </div>
                @endif
                @if($provider->address)
                <div class="info-row">
                    <div class="info-icon-box"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content-wrap">
                        <div class="info-lbl">Address</div>
                        <div class="info-val">{{ $provider->address }}</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ABOUT --}}
            @if($provider->description)
            <div class="sidebar-card">
                <h3><i class="fas fa-info-circle"></i> {{ __('messages.public_about_us') }}</h3>
                <p style="font-size: 13px; color: #475569; line-height: 1.7; margin: 0;">{{ $provider->description }}</p>
            </div>
            @endif

            {{-- CTA --}}
            <div class="sidebar-card sidebar-cta">
                <h3><i class="fas fa-concierge-bell"></i> {{ __('messages.public_need_help') }}</h3>
                <p>{{ __('messages.public_help_desc') }}</p>
                @if($provider->contact_phone)
                <a href="tel:{{ $provider->contact_phone }}"><i class="fas fa-phone"></i> {{ __('messages.public_call_us') }}</a>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
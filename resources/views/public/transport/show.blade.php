@extends('layouts.public')

@section('title', $provider->name . ' — ' . $service->name)
@section('meta_description', 'Book your transport with ' . $provider->name . '. View vehicles, routes, seat capacity and schedules. Instant booking at ' . ($provider->address ?? 'Kathmandu') . '.')
@section('meta_keywords', 'transport, ' . $provider->name . ', ' . ($service->category->name ?? 'travel') . ', book vehicle, ' . ($provider->address ?? 'Kathmandu'))
@section('og_meta')
<meta property="og:title" content="{{ $provider->name }} - Transport">
<meta property="og:description" content="Book your ride with {{ $provider->name }}. Browse vehicles and reserve instantly.">
@if($provider->cover_image)
<meta property="og:image" content="{{ Storage::url($provider->cover_image) }}">
@endif
@endsection

@section('content')
<style>
    *, *::before, *::after { box-sizing: border-box; }

    /* ===== HERO (sky/blue — different from Restaurant/Hotel) ===== */
    .tr-hero {
        height: 340px;
        background-color: #0284c7;
        background-size: cover;
        background-position: center;
        border-radius: 0 0 28px 28px;
        position: relative;
    }
    .tr-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(2,132,199,0.15) 0%, rgba(0,0,0,0.65) 100%);
        border-radius: 0 0 28px 28px;
    }
    /* Placeholder when no cover image */
    .tr-hero-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 140px;
        opacity: 0.25;
        z-index: 1;
        background:
            radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(255,255,255,0.15) 0%, transparent 40%),
            linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    }

    /* ===== PROFILE CARD ===== */
    .tr-profile-container {
        max-width: 1120px;
        margin: -80px auto 40px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
    }
    .tr-profile-card {
        background: #fff;
        border-radius: 22px;
        padding: 28px 36px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        border: 1px solid #e0f2fe;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;
    }
    .tr-profile-left { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .tr-logo {
        width: 76px; height: 76px;
        border-radius: 16px;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 32px;
        font-weight: 700;
        overflow: hidden;
    }
    .tr-logo img { width: 100%; height: 100%; object-fit: cover; }
    .tr-name { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 4px; }
    .tr-meta { color: #64748b; font-size: 14px; display: flex; gap: 16px; flex-wrap: wrap; }
    .tr-verified {
        display: inline-flex; align-items: center; gap: 6px;
        background: #dcfce7; color: #166534;
        padding: 4px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }

    /* ===== OFFERS STRIP ===== */
    .tr-offers {
        max-width: 1120px;
        margin: 0 auto 32px;
        padding: 0 24px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
    }
    .tr-offer {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #bae6fd;
        padding: 14px 18px;
        border-radius: 12px;
    }
    .tr-offer-title { font-weight: 600; color: #0369a1; margin: 0 0 4px; font-size: 14px; }
    .tr-offer-valid { color: #64748b; font-size: 12px; margin: 0; }

    /* ===== SECTION HEADINGS ===== */
    .tr-section {
        max-width: 1120px;
        margin: 0 auto 40px;
        padding: 0 24px;
    }
    .tr-section-title {
        font-size: 22px;
        color: #0f172a;
        margin: 0 0 20px;
        display: flex; align-items: center; gap: 10px;
    }
    .tr-section-title i { color: #0284c7; }

    /* ===== ROUTE TIMELINE ===== */
    .tr-route-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .tr-route-line {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px dashed #cbd5e1;
    }
    .tr-route-line:last-child { border-bottom: 0; }
    .tr-route-dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        background: #0284c7;
        flex-shrink: 0;
    }
    .tr-route-dot.end { background: #10b981; }
    .tr-route-info { flex: 1; }
    .tr-route-name { font-weight: 600; color: #0f172a; font-size: 15px; margin: 0 0 2px; }
    .tr-route-sub { color: #64748b; font-size: 13px; margin: 0; }
    .tr-route-arrow { color: #0284c7; font-size: 18px; }

    /* ===== VEHICLE CARDS ===== */
    .tr-vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
    }
    .tr-vehicle {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all .2s;
    }
    .tr-vehicle:hover {
        border-color: #0284c7;
        box-shadow: 0 8px 24px rgba(2,132,199,0.12);
        transform: translateY(-2px);
    }
    .tr-vehicle-img {
        height: 140px;
        background: linear-gradient(135deg, #e0f2fe, #bae6fd);
        display: flex; align-items: center; justify-content: center;
        font-size: 56px;
        overflow: hidden;
    }
    .tr-vehicle-img img { width: 100%; height: 100%; object-fit: cover; }
    .tr-vehicle-body { padding: 16px 18px; }
    .tr-vehicle-type {
        display: inline-block;
        background: #e0f2fe; color: #0369a1;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        margin-bottom: 8px;
    }
    .tr-vehicle-plate {
        font-family: 'Courier New', monospace;
        font-weight: 700; color: #0f172a; font-size: 15px;
        margin: 0 0 8px;
        padding: 4px 8px;
        background: #f1f5f9;
        display: inline-block;
        border-radius: 4px;
        border: 2px solid #cbd5e1;
        letter-spacing: 1px;
    }
    .tr-vehicle-info {
        display: flex; gap: 12px; color: #64748b;
        font-size: 13px; margin: 8px 0;
    }
    .tr-vehicle-info i { color: #0284c7; }

    /* ===== BOOKING FORM ===== */
    .tr-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
    }
    @media (max-width: 900px) { .tr-main-grid { grid-template-columns: 1fr; } }

    .tr-booking-card {
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
        border-radius: 20px;
        padding: 28px;
        border: 1px solid #bae6fd;
        box-shadow: 0 10px 30px rgba(2,132,199,0.06);
    }
    .tr-booking-card h2 {
        color: #0369a1; margin: 0 0 20px;
        font-size: 20px;
    }
    .tr-form-group { margin-bottom: 16px; }
    .tr-form-label {
        display: block;
        color: #0f172a; font-size: 13px; font-weight: 600;
        margin-bottom: 6px;
    }
    .tr-form-input {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        transition: border-color .2s;
    }
    .tr-form-input:focus {
        outline: none;
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2,132,199,0.15);
    }
    .tr-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 500px) { .tr-form-row { grid-template-columns: 1fr; } }

    .tr-btn-book {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: #fff; border: 0; border-radius: 12px;
        font-size: 15px; font-weight: 600;
        cursor: pointer;
        transition: transform .15s;
    }
    .tr-btn-book:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(2,132,199,0.3); }

    .tr-alert {
        padding: 12px 16px; border-radius: 10px;
        font-size: 14px; margin-bottom: 16px;
    }
    .tr-alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .tr-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    /* ===== SIDEBAR ===== */
    .tr-side-card {
        background: #fff; border-radius: 16px;
        padding: 22px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    .tr-side-card h3 {
        color: #0f172a; font-size: 15px;
        margin: 0 0 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .tr-side-card h3 i { color: #0284c7; }
    .tr-side-card p { color: #64748b; font-size: 13px; margin: 8px 0; }
    .tr-side-card p strong { color: #0f172a; }

    /* ===== NEW: Search Bar ===== */
    .tr-search-section { max-width: 1120px; margin: 0 auto 32px; padding: 0 24px; }
    .tr-search-box { background: #fff; border: 2px solid #bae6fd; border-radius: 20px; padding: 24px 28px; box-shadow: 0 12px 32px rgba(2,132,199,0.08); }
    .tr-search-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .tr-search-sub { font-size: 12.5px; color: #64748b; margin: 0 0 18px; font-weight: 600; }
    .tr-search-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
    @media (max-width: 720px) { .tr-search-grid { grid-template-columns: 1fr; } }
    .tr-search-field label { display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
    .tr-search-field input, .tr-search-field select { width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; font-family: inherit; background: #fff; }
    .tr-search-field input:focus, .tr-search-field select:focus { outline: none; border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2,132,199,0.15); }
    .tr-search-btn { padding: 11px 24px; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .tr-search-btn:hover { filter: brightness(1.08); }

    /* ===== NEW: Trip Results ===== */
    .tr-trip-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 14px; transition: .2s; }
    .tr-trip-card:hover { border-color: #0284c7; box-shadow: 0 8px 24px rgba(2,132,199,0.12); }
    .tr-trip-time { font-size: 24px; font-weight: 900; color: #0284c7; font-family: 'Courier New', monospace; letter-spacing: -1px; line-height: 1; }
    .tr-trip-vehicle { font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 6px; }
    .tr-trip-route { font-size: 13px; color: #64748b; font-weight: 700; margin-top: 2px; }
    .tr-trip-price { font-size: 20px; font-weight: 800; color: #0f172a; }
    .tr-trip-price small { font-size: 12px; font-weight: 600; color: #64748b; }
    .tr-trip-meta { display: flex; gap: 16px; flex-wrap: wrap; font-size: 12.5px; color: #64748b; font-weight: 600; padding: 12px 0; margin-top: 12px; border-top: 1px dashed #e2e8f0; border-bottom: 1px dashed #e2e8f0; }
    .tr-book-btn { margin-top: 14px; padding: 11px 20px; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border: 0; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; width: 100%; }
    .tr-book-btn:hover { filter: brightness(1.08); }
    .tr-empty-results { text-align: center; padding: 48px 20px; background: #fff; border: 2px dashed #cbd5e1; border-radius: 16px; }
    .tr-empty-results h3 { font-size: 16px; margin: 0 0 8px; color: #0f172a; }
    .tr-empty-results p { font-size: 13px; color: #64748b; margin: 0; }

    /* ===== NEW: Booking Modal ===== */
    .tr-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(10,28,51,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(3px); }
    .tr-modal-box { background: #fff; border-radius: 20px; width: 100%; max-width: 520px; padding: 28px; box-shadow: 0 25px 60px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; }
    .tr-modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .tr-modal-head h3 { font-size: 18px; font-weight: 800; margin: 0; color: #0f172a; }
    .tr-modal-close { background: none; border: none; font-size: 26px; cursor: pointer; color: #64748b; line-height: 1; }
    .tr-trip-summary { background: #f0f9ff; border: 1px solid #bae6fd; padding: 14px 16px; border-radius: 12px; margin-bottom: 18px; font-size: 13px; color: #0f172a; line-height: 1.7; }
    .tr-trip-summary strong { color: #0369a1; }
    .tr-radio-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; }
    .tr-radio-option { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; cursor: pointer; transition: .15s; }
    .tr-radio-option:hover { border-color: #0284c7; background: #f0f9ff; }
    .tr-radio-option input[type="radio"] { width: 18px; height: 18px; cursor: pointer; margin: 0; }
    .tr-radio-option label { cursor: pointer; font-size: 14px; font-weight: 600; color: #0f172a; flex: 1; }
    .tr-total-box { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #bae6fd; padding: 14px 16px; border-radius: 12px; margin: 18px 0; display: flex; justify-content: space-between; align-items: center; }
    .tr-total-box span:first-child { font-size: 13px; font-weight: 700; color: #0369a1; text-transform: uppercase; }
    .tr-total-box span:last-child { font-size: 22px; font-weight: 900; color: #0f172a; }
</style>

<!-- HERO -->
<div class="tr-hero" @if($provider->cover_image) style="background-image: url('{{ Storage::url($provider->cover_image) }}'); background-color: #0284c7;" @endif>
    @if(!$provider->cover_image)
        <div class="tr-hero-placeholder">🚌</div>
    @endif
</div>

<!-- PROFILE CARD -->
<div class="tr-profile-container">
    <div class="tr-profile-card">
        <div class="tr-profile-left">
            <div class="tr-logo">
                @if($provider->logo_url)
                    <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}">
                @else
                    🚌
                @endif
            </div>
            <div>
                <h1 class="tr-name">{{ $provider->name }}</h1>
                <div class="tr-meta">
                    <span><i class="fas fa-map-marker-alt"></i> {{ $provider->address ?? 'Kathmandu, Nepal' }}</span>
                    @if($provider->is_verified ?? false)
                        <span class="tr-verified">✓ Verified</span>
                    @endif
                    @if($provider->average_rating > 0)
                        <span><i class="fas fa-star" style="color: #fbbf24;"></i> {{ number_format($provider->average_rating, 1) }} ({{ $provider->reviews_count }})</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="tr-search-section" style="margin-bottom: 12px;">
    <div class="tr-alert tr-alert-success">{{ session('success') }}</div>
</div>
@endif
@if($errors->any())
<div class="tr-search-section" style="margin-bottom: 12px;">
    <div class="tr-alert tr-alert-error">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
</div>
@endif

<!-- SEARCH BAR -->
<div class="tr-search-section">
    <div class="tr-search-box">
        <h2 class="tr-search-title">🔍 {{ __('messages.public_search_title') ?? 'Find Available Departures' }}</h2>
        <p class="tr-search-sub">{{ __('messages.public_search_subtitle') ?? 'Pick a date and route to see available trips' }}</p>
        <form method="GET" action="{{ route('public.transport.show', $service->slug) }}">
            <div class="tr-search-grid">
                <div class="tr-search-field">
                    <label>{{ __('messages.public_travel_date') ?? 'Travel Date' }}</label>
                    <input type="date" name="date" required min="{{ date('Y-m-d') }}" value="{{ $travelDate ?? '' }}">
                </div>
                <div class="tr-search-field">
                    <label>{{ __('messages.public_from') ?? 'From' }}</label>
                    <select name="from" required>
                        <option value="">— {{ __('messages.public_transport_choose') ?? 'Choose' }} —</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected($fromLocId == $loc->id)>{{ $loc->city }}@if($loc->state) — {{ $loc->state }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="tr-search-field">
                    <label>{{ __('messages.public_to') ?? 'To' }}</label>
                    <select name="to" required>
                        <option value="">— {{ __('messages.public_transport_choose') ?? 'Choose' }} —</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected($toLocId == $loc->id)>{{ $loc->city }}@if($loc->state) — {{ $loc->state }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="tr-search-btn">🔍 {{ __('messages.public_search_trips') ?? 'Search Trips' }}</button>
            </div>
        </form>
    </div>
</div>

<!-- OFFERS -->
@if($offers->count())
<div class="tr-offers">
    @foreach($offers as $offer)
    <div class="tr-offer">
        <p class="tr-offer-title">{{ $offer->title }}</p>
        <p class="tr-offer-valid">Valid until {{ \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') }}</p>
    </div>
    @endforeach
</div>
@endif

<!-- MAIN GRID -->
<div class="tr-section">
    <div class="tr-main-grid">
        <!-- LEFT: Vehicles + Booking -->
        <div>
            @if(!$hasSearch)
            <!-- Routes -->
            <div class="tr-route-card">
                <h2 class="tr-section-title" style="margin-top: 0;">
                    <i class="fas fa-route"></i> {{ __('messages.public_transport_routes') ?? 'Available Routes' }}
                </h2>
                @if($service->transportDetail && $service->transportDetail->fromLocation && $service->transportDetail->toLocation)
                    <div class="tr-route-line">
                        <div class="tr-route-dot"></div>
                        <div class="tr-route-info">
                            <p class="tr-route-name">{{ $service->transportDetail->fromLocation->city }}</p>
                            <p class="tr-route-sub">{{ __('messages.tr_pickup_point') }}</p>
                        </div>
                    </div>
                    <div class="tr-route-line" style="border-bottom: 0; padding-left: 4px; color: #0284c7;">
                        <i class="fas fa-arrow-down" style="font-size: 12px; margin-left: 2px;"></i>
                        <div class="tr-route-info">
                            <p class="tr-route-sub">
                                {{ $service->transportDetail->duration_minutes ?? '—' }} min ·
                                {{ $service->transportDetail->ac_available ? 'AC' : 'Non-AC' }} ·
                                {{ ucfirst($service->transportDetail->private_shared ?? 'shared') }}
                            </p>
                        </div>
                    </div>
                    <div class="tr-route-line">
                        <div class="tr-route-dot end"></div>
                        <div class="tr-route-info">
                            <p class="tr-route-name">{{ $service->transportDetail->toLocation->city }}</p>
                            <p class="tr-route-sub">{{ __('messages.tr_destination') }}</p>
                        </div>
                    </div>
                @else
                    {{-- Fallback: use vehicle route names --}}
                    @php $routes = $vehicles->pluck('route_name')->filter()->unique(); @endphp
                    @forelse($routes as $route)
                        <div class="tr-route-line">
                            <div class="tr-route-dot"></div>
                            <div class="tr-route-info">
                                <p class="tr-route-name">{{ $route }}</p>
                            </div>
                        </div>
                    @empty
                        <p style="color: #64748b; font-size: 14px;">{{ __('messages.tr_routes_updating') }}</p>
                    @endforelse
                @endif
            </div>

            <!-- Vehicles -->
            <h2 class="tr-section-title">
                <i class="fas fa-bus"></i> {{ __('messages.public_transport_fleet') ?? 'Our Fleet' }}
            </h2>
            <div class="tr-vehicles-grid">
                @forelse($vehicles as $vehicle)
                @php
                    $icon = match(strtolower($vehicle->vehicle_type)) {
                        'bus' => '🚌',
                        'van' => '🚐',
                        'car' => '🚗',
                        'jeep' => '🚙',
                        'bike', 'motorcycle' => '🏍️',
                        default => '🚐',
                    };
                @endphp
                <div class="tr-vehicle">
                    <div class="tr-vehicle-img">
                        @if($vehicle->image_path)
                            <img src="{{ Storage::url($vehicle->image_path) }}" alt="{{ $vehicle->vehicle_type }}">
                        @else
                            {{ $icon }}
                        @endif
                    </div>
                    <div class="tr-vehicle-body">
                        <span class="tr-vehicle-type">{{ $vehicle->vehicle_type }}</span>
                        <div class="tr-vehicle-plate">{{ $vehicle->vehicle_number }}</div>
                        <div class="tr-vehicle-info">
                            <span><i class="fas fa-user-friends"></i> {{ $vehicle->capacity }} seats</span>
                        </div>
                        @if($vehicle->driver_name)
                        <div class="tr-vehicle-info">
                            <span><i class="fas fa-id-badge"></i> {{ $vehicle->driver_name }}</span>
                        </div>
                        @endif
                        @if($vehicle->route_name)
                        <div class="tr-vehicle-info">
                            <span><i class="fas fa-route"></i> {{ $vehicle->route_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <p style="color: #64748b;">{{ __('messages.tr_no_vehicles') }}</p>
                @endforelse
            </div>
            @else
            {{-- SEARCH RESULTS --}}
            <h2 class="tr-section-title">
                <i class="fas fa-bus"></i> {{ __('messages.public_available_departures') ?? 'Available Departures' }}
                @if($travelDate) — {{ \Carbon\Carbon::parse($travelDate)->format('D, M d, Y') }} @endif
            </h2>
            @forelse($trips as $trip)
                @php
                    $vt = strtolower($trip->vehicle->vehicle_type ?? '');
                    $icon = str_contains($vt, 'bus') ? '🚌' : (str_contains($vt, 'van') || str_contains($vt, 'hiace') ? '🚐' : (str_contains($vt, 'jeep') || str_contains($vt, 'suv') ? '🚙' : (str_contains($vt, 'bike') ? '🏍️' : '🚗')));
                @endphp
                <div class="tr-trip-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
                        <div>
                            <div class="tr-trip-time">{{ substr($trip->departure_time, 0, 5) }}</div>
                            <div class="tr-trip-vehicle">{{ $icon }} {{ $trip->vehicle->vehicle_type ?? '' }} — {{ $trip->vehicle->vehicle_number ?? '' }}</div>
                            <div class="tr-trip-route">{{ $trip->route->fromLocation->city ?? '' }} → {{ $trip->route->toLocation->city ?? '' }}</div>
                        </div>
                        <div style="text-align:right">
                            <div class="tr-trip-price">Rs {{ number_format($trip->price_per_seat, 0) }}<small>/seat</small></div>
                            <div style="font-size:12px;color:#64748b;margin-top:4px;font-weight:700">🪑 {{ $trip->seats_available }} {{ __('messages.public_seats_left') ?? 'seats left' }}</div>
                        </div>
                    </div>
                    <div class="tr-trip-meta">
                        @if($trip->route->duration_minutes)
                        <span>⏱ {{ intdiv($trip->route->duration_minutes, 60) }}h {{ $trip->route->duration_minutes % 60 }}m</span>
                        @endif
                        @if($trip->driver_name)
                        <span>👤 {{ $trip->driver_name }}</span>
                        @endif
                        @if($trip->pickup_point)
                        <span>📍 {{ $trip->pickup_point }}</span>
                        @endif
                        @if($trip->price_per_vehicle)
                        <span>🚐 Rs {{ number_format($trip->price_per_vehicle, 0) }}/vehicle</span>
                        @endif
                    </div>
                    <button type="button" class="tr-book-btn" onclick="openBookingModal({{ $trip->id }})">
                        {{ __('messages.public_book_now') ?? 'Book Now' }} →
                    </button>
                </div>
            @empty
                <div class="tr-empty-results">
                    <div style="font-size:56px;margin-bottom:12px">{{ ($allBookedOut ?? false) ? '🔴' : '🚌' }}</div>
                    @if($allBookedOut ?? false)
                        <h3>{{ __('messages.fully_booked_no_trips') ?? 'All departures are fully booked on this date' }}</h3>
                        <p>{{ __('messages.fully_booked_hint') ?? 'Try a different date, or check back later.' }}</p>
                    @else
                        <h3>{{ __('messages.public_no_trips_found') ?? 'No departures found' }}</h3>
                        <p>{{ __('messages.public_no_trips_hint') ?? 'Try a different date or route.' }}</p>
                    @endif
                </div>
            @endforelse
            @endif
        </div>

        <!-- RIGHT: Contact + About (Booking मodal मा छ) -->
        <div>
            <div class="tr-side-card">
                <h3><i class="fas fa-search"></i> {{ __('messages.public_search_hint_title') ?? 'Search for a trip' }}</h3>
                <p>{{ __('messages.public_search_hint_body') ?? 'Use the search bar above to find available departures on your route.' }}</p>
            </div>

        </div>

            <div class="tr-side-card">
                <h3><i class="fas fa-address-card"></i> {{ __('messages.public_contact_info') ?? 'Contact' }}</h3>
                @if($provider->contact_phone)
                    <p><strong>{{ __('messages.tr_phone_label') }}:</strong> {{ $provider->contact_phone }}</p>
                @endif
                @if($provider->contact_email)
                    <p><strong>{{ __('messages.tr_email_label') }}:</strong> {{ $provider->contact_email }}</p>
                @endif
                @if($provider->address)
                    <p><strong>{{ __('messages.tr_address_label') }}:</strong> {{ $provider->address }}</p>
                @endif
            </div>

            <div class="tr-side-card">
                <h3><i class="fas fa-info-circle"></i> {{ __('messages.public_about_us') ?? 'About' }}</h3>
                <p>{{ $service->description ?? 'Trusted transport operator in Nepal.' }}</p>
            </div>
        </div>
    </div>
</div>
{{-- BOOKING MODAL --}}
<div id="bookingModal" class="tr-modal-overlay">
    <div class="tr-modal-box">
        <div class="tr-modal-head">
            <h3>🎫 {{ __('messages.public_book_now') ?? 'Book Your Trip' }}</h3>
            <button type="button" class="tr-modal-close" onclick="closeBookingModal()">&times;</button>
        </div>

        <div class="tr-trip-summary" id="tripSummary"></div>

        <div id="seatsWarning" style="display:none;background:#fef4e2;border:1px solid #fbbf24;color:#92400e;padding:10px 14px;border-radius:10px;font-size:12.5px;font-weight:700;margin-bottom:14px;"></div>

        <form id="bookingForm" method="POST" action="{{ route('public.transport.book', $service->slug) }}">
            @csrf
            <input type="hidden" name="trip_id" id="bm_trip_id">

            <div class="tr-form-group">
                <label class="tr-form-label">{{ __('messages.public_booking_type') ?? 'Booking Type' }}</label>
                <div class="tr-radio-group">
                    <div class="tr-radio-option">
                        <input type="radio" name="booking_type" value="per_seat" id="bt_seat" checked onchange="recalcTotal()">
                        <label for="bt_seat" id="bt_seat_label">{{ __('messages.public_per_seat') ?? 'Per Seat' }}</label>
                    </div>
                    <div class="tr-radio-option" id="bt_full_option">
                        <input type="radio" name="booking_type" value="full_vehicle" id="bt_full" onchange="recalcTotal()">
                        <label for="bt_full" id="bt_full_label">{{ __('messages.public_full_vehicle') ?? 'Full Vehicle (Private)' }}</label>
                    </div>
                </div>
            </div>

            <div class="tr-form-group" id="passengersGroup">
                <label class="tr-form-label">{{ __('messages.public_passengers') ?? 'Number of Passengers' }}</label>
                <input type="number" name="passenger_count" id="bm_passengers" value="1" min="1" class="tr-form-input" onchange="recalcTotal()">
            </div>

            @if(isset($paymentModel) && $paymentModel === 'both')
            <div class="tr-form-group">
                <label class="tr-form-label">{{ __('messages.public_payment_option') ?? 'Payment Option' }}</label>
                <div class="tr-radio-group">
                    @if($hasActiveMethods ?? false)
                    <div class="tr-radio-option">
                        <input type="radio" name="payment_model" value="advance" id="pm_advance" checked>
                        <label for="pm_advance">{{ __('messages.public_pay_advance') ?? 'Pay Now (Advance)' }}</label>
                    </div>
                    @endif
                    <div class="tr-radio-option">
                        <input type="radio" name="payment_model" value="pay_on_arrival" id="pm_pay_arrival" {{ ($hasActiveMethods ?? false) ? '' : 'checked' }}>
                        <label for="pm_pay_arrival">{{ __('messages.public_pay_on_arrival') ?? 'Pay at Departure' }}</label>
                    </div>
                </div>
            </div>
            @elseif(isset($paymentModel) && $paymentModel === 'pay_on_arrival')
            <div class="tr-form-group">
                <div style="background:#f0f9ff;border:1px solid #bae6fd;padding:12px 14px;border-radius:10px;font-size:13px;color:#0369a1;">
                    💵 {{ __('messages.public_pay_on_arrival_note') ?? 'You will pay at departure.' }}
                </div>
            </div>
            @endif

            <div class="tr-form-group">
                <label class="tr-form-label">{{ __('messages.public_your_name') ?? 'Your Name' }}</label>
                <input type="text" name="passenger_name" required class="tr-form-input">
            </div>

            <div class="tr-form-group">
                <label class="tr-form-label">{{ __('messages.public_email') ?? 'Email' }}</label>
                <input type="email" name="passenger_email" required class="tr-form-input">
            </div>

            <div class="tr-form-group">
                <label class="tr-form-label">📍 {{ __('messages.pickup_point') }}</label>
                <input type="text" name="pickup_point" id="bm_pickup" class="tr-form-input" placeholder="{{ __('messages.pickup_point_hint') }}">
            </div>

            <div class="tr-total-box">
                <span>{{ __('messages.public_total') ?? 'Total' }}</span>
                <span id="totalAmount">Rs 0</span>
            </div>

            <button type="submit" class="tr-btn-book">
                {{ __('messages.public_confirm_booking') ?? 'Confirm Booking' }}
            </button>
        </form>
    </div>
</div>

<script>
const ALL_TRIPS = @json($tripsForJs);

function openBookingModal(tripId) {
    const t = ALL_TRIPS.find(x => x.id === tripId);
    if (!t) return;

    document.getElementById('bm_trip_id').value = t.id;
    document.getElementById('tripSummary').innerHTML =
        '<strong>🚌 ' + t.vehicle_type + ' — ' + t.vehicle_number + '</strong><br>' +
        t.route_name + '<br>' +
        '📅 ' + t.departure_date + ' at ' + t.departure_time + '<br>' +
        '💰 Rs ' + t.price_per_seat.toLocaleString() + '/seat';

    const passInput = document.getElementById('bm_passengers');
    passInput.max = t.seats_available;
    passInput.value = 1;

    // Show seats available prominently
    const seatsWarn = document.getElementById('seatsWarning');
    if (t.seats_available <= 5) {
        seatsWarn.innerHTML = '⚠️ ' + '{{ __("messages.seats_available_count", ["count" => ":count"]) }}'.replace(':count', t.seats_available);
        seatsWarn.style.display = 'block';
    } else {
        seatsWarn.style.display = 'none';
    }

    document.getElementById('bt_seat').checked = true;
    document.getElementById('bt_seat_label').textContent =
        '{{ __("messages.per_seat_price", ["price" => ":price"]) }}'.replace(':price', t.price_per_seat.toLocaleString());

    const fullOption = document.getElementById('bt_full_option');
    if (t.price_per_vehicle) {
        fullOption.style.display = 'flex';
        document.getElementById('bt_full_label').textContent =
            '{{ __("messages.full_vehicle_price", ["price" => ":price"]) }}'.replace(':price', t.price_per_vehicle.toLocaleString());
    } else {
        fullOption.style.display = 'none';
    }

    document.getElementById('bm_pickup').value = t.pickup_point || '';
    document.getElementById('bookingForm').dataset.priceSeat = t.price_per_seat;
    document.getElementById('bookingForm').dataset.priceVehicle = t.price_per_vehicle || '';
    document.getElementById('bookingForm').dataset.maxSeats = t.seats_available;

    recalcTotal();
    document.getElementById('bookingModal').style.display = 'flex';
}

function recalcTotal() {
    const form = document.getElementById('bookingForm');
    const priceSeat = parseFloat(form.dataset.priceSeat) || 0;
    const priceVehicle = parseFloat(form.dataset.priceVehicle) || 0;
    const maxSeats = parseInt(form.dataset.maxSeats) || 1;
    const type = document.querySelector('input[name="booking_type"]:checked').value;
    const passInput = document.getElementById('bm_passengers');

    const seatsWarn = document.getElementById('seatsWarning');
    if (type === 'full_vehicle') {
        passInput.disabled = true;
        passInput.value = maxSeats;
        document.getElementById('totalAmount').textContent = 'Rs ' + priceVehicle.toLocaleString();
        seatsWarn.style.display = 'none';
    } else {
        passInput.disabled = false;
        let n = parseInt(passInput.value) || 1;
        if (n > maxSeats) {
            n = maxSeats;
            passInput.value = n;
            // Show warning message
            seatsWarn.innerHTML = '⚠️ ' + '{{ __("messages.only_seats_available", ["count" => ":count"]) }}'.replace(/:count/g, maxSeats);
            seatsWarn.style.display = 'block';
        } else if (maxSeats <= 5) {
            seatsWarn.innerHTML = '⚠️ ' + '{{ __("messages.seats_available_count", ["count" => ":count"]) }}'.replace(':count', maxSeats);
            seatsWarn.style.display = 'block';
        } else {
            seatsWarn.style.display = 'none';
        }
        document.getElementById('totalAmount').textContent = 'Rs ' + (n * priceSeat).toLocaleString();
    }
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

document.getElementById('bookingModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBookingModal();
});
</script>
{{-- PAYMENT MODAL (Advance Payment — auto-opens after booking) --}}
@if(session('payment_booking'))
@php $pb = session('payment_booking'); @endphp
<div id="paymentModal" class="tr-modal-overlay" style="display:flex">
    <div class="tr-modal-box">
        <div class="tr-modal-head">
            <h3>💳 {{ __('messages.public_complete_payment') ?? 'Complete Payment' }}</h3>
            <button type="button" class="tr-modal-close" onclick="closePaymentModal()">&times;</button>
        </div>

        <div style="background:#fffbeb;border:1px solid #fbbf24;padding:14px 16px;border-radius:12px;margin-bottom:16px;">
            <div style="font-size:11px;color:#92400e;font-weight:800;text-transform:uppercase;letter-spacing:.5px">{{ __('messages.tr_booking_ref') }}</div>
            <div style="font-size:18px;font-weight:800;color:#92400e;margin-top:4px">{{ $pb['ref'] }}</div>
            <div style="font-size:20px;font-weight:800;color:#92400e;margin-top:8px">Rs {{ number_format($pb['amount'], 2) }}</div>
        </div>

        <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:10px">Step 1 — Send payment to:</div>
        @foreach($pb['methods'] as $i => $m)
        <label style="display:block;border:2px solid {{ $i === 0 ? '#0284c7' : '#e2e8f0' }};border-radius:12px;padding:14px;margin-bottom:10px;cursor:pointer;background:{{ $i === 0 ? '#f0f9ff' : '#fff' }}" class="payment-method-option">
            <input type="radio" name="pm_select" value="{{ $m['id'] }}" {{ $i === 0 ? 'checked' : '' }} onchange="selectPaymentMethod(this)" style="margin-right:8px">
            <strong style="font-size:14px">{{ $m['label'] }}</strong>
            @if($m['is_primary'])
                <span style="font-size:10px;background:#fef4e2;color:#b45309;padding:2px 8px;border-radius:6px;margin-left:6px;font-weight:800">⭐ PRIMARY</span>
            @endif
            <div style="margin-top:8px;font-size:12px;color:#0f172a;padding-left:24px;line-height:1.7">
                @if($m['identifier'])<div><strong>Phone/Email:</strong> {{ $m['identifier'] }}</div>@endif
                @if($m['account_name'])<div><strong>Name:</strong> {{ $m['account_name'] }}</div>@endif
                @if($m['account_number'])<div><strong>Account:</strong> {{ $m['account_number'] }}</div>@endif
                @if($m['bank_name'])<div><strong>Bank:</strong> {{ $m['bank_name'] }}</div>@endif
                @if($m['instructions'])<div style="color:#64748b;margin-top:4px">{{ $m['instructions'] }}</div>@endif
            </div>
            @if($m['qr_url'])
                <div style="margin-top:10px;padding-left:24px">
                    <img src="{{ $m['qr_url'] }}" alt="QR" style="width:120px;height:120px;border:2px solid #e2e8f0;border-radius:8px;background:#fff;padding:4px">
                    <div style="font-size:11px;color:#64748b;margin-top:4px">{{ __('messages.tr_scan_qr') }}</div>
                </div>
            @endif
        </label>
        @endforeach

        <div style="font-size:13px;font-weight:700;color:#0f172a;margin:16px 0 10px">Step 2 — Upload proof:</div>

        <form method="POST" action="{{ route('public.transport.payment-proof', $pb['id']) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="email" value="{{ $pb['email'] }}">
            <input type="hidden" name="service_slug" value="{{ $service->slug }}">
            <input type="hidden" name="payment_method_id" id="pm_selected_id" value="{{ $pb['methods'][0]['id'] ?? '' }}">

            <div class="tr-form-group">
                <label class="tr-form-label">Transaction Reference / ID</label>
                <input type="text" name="payment_ref" required placeholder="e.g. TXN-12345" class="tr-form-input">
            </div>

            <div class="tr-form-group">
                <label class="tr-form-label">{{ __('messages.tr_payment_screenshot') }}</label>
                <input type="file" name="payment_proof" accept="image/*" required class="tr-form-input">
                <div style="font-size:11px;color:#64748b;margin-top:4px">Max 5MB · JPG, PNG, WEBP</div>
            </div>

            <div style="display:flex;gap:10px;margin-top:16px">
                <button type="button" onclick="closePaymentModal()" style="flex:1;padding:12px;background:#f1f5f9;color:#0f172a;border:none;border-radius:10px;font-weight:700;cursor:pointer">
                    Pay Later
                </button>
                <button type="submit" style="flex:2;padding:12px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer">
                    Submit Payment Proof
                </button>
            </div>
        </form>

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#64748b">
            {{ __('messages.tr_complete_payment_later') }}
        </div>
    </div>
</div>

<script>
function selectPaymentMethod(radio) {
    document.getElementById('pm_selected_id').value = radio.value;
    document.querySelectorAll('.payment-method-option').forEach(el => {
        el.style.borderColor = '#e2e8f0';
        el.style.background = '#fff';
    });
    radio.closest('.payment-method-option').style.borderColor = '#0284c7';
    radio.closest('.payment-method-option').style.background = '#f0f9ff';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    // Clear session flash on close (backend session will clear on next request)
    window.location.href = '{{ route("public.transport.show", $service->slug) }}';
}
</script>
@endif
@endsection
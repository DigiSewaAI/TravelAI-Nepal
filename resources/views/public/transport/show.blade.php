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
                            <p class="tr-route-sub">Pickup point</p>
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
                            <p class="tr-route-sub">Destination</p>
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
                        <p style="color: #64748b; font-size: 14px;">Routes will be updated soon.</p>
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
                <p style="color: #64748b;">No vehicles listed yet.</p>
                @endforelse
            </div>
        </div>

        <!-- RIGHT: Booking Form + Contact -->
        <div>
            <div class="tr-booking-card">
                <h2>🚌 {{ __('messages.public_transport_book') ?? 'Book Your Ride' }}</h2>

                @if(session('success'))
                    <div class="tr-alert tr-alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="tr-alert tr-alert-error">
                        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('public.transport.book', $service->slug) }}">
                    @csrf

                    <div class="tr-form-group">
                        <label class="tr-form-label">{{ __('messages.public_transport_vehicle') ?? 'Select Vehicle' }}</label>
                        <select name="vehicle_id" required class="tr-form-input">
                            <option value="">— {{ __('messages.public_transport_choose') ?? 'Choose' }} —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" @selected(old('vehicle_id') == $v->id)>
                                    {{ $v->vehicle_type }} — {{ $v->vehicle_number }} ({{ $v->capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="tr-form-group">
                        <label class="tr-form-label">{{ __('messages.public_transport_route') ?? 'Route' }}</label>
                        <input type="text" name="route_name" value="{{ old('route_name', $vehicles->first()->route_name ?? '') }}" required class="tr-form-input">
                    </div>

                    <div class="tr-form-row">
                        <div class="tr-form-group">
                            <label class="tr-form-label">{{ __('messages.public_date') ?? 'Date' }}</label>
                            <input type="date" name="travel_date" required min="{{ date('Y-m-d') }}" value="{{ old('travel_date') }}" class="tr-form-input">
                        </div>
                        <div class="tr-form-group">
                            <label class="tr-form-label">{{ __('messages.public_time') ?? 'Time (Optional)' }}</label>
                            <input type="time" name="travel_time" value="{{ old('travel_time') }}" class="tr-form-input">
                        </div>
                    </div>

                    <div class="tr-form-group">
                        <label class="tr-form-label">{{ __('messages.public_your_name') ?? 'Your Name' }}</label>
                        <input type="text" name="passenger_name" required value="{{ old('passenger_name') }}" class="tr-form-input">
                    </div>

                    <div class="tr-form-group">
                        <label class="tr-form-label">{{ __('messages.public_email') ?? 'Email' }}</label>
                        <input type="email" name="passenger_email" required value="{{ old('passenger_email') }}" class="tr-form-input">
                    </div>

                    <button type="submit" class="tr-btn-book">
                        <i class="fas fa-ticket-alt"></i> {{ __('messages.public_confirm_booking') ?? 'Confirm Booking' }}
                    </button>
                </form>
            </div>

            <div class="tr-side-card">
                <h3><i class="fas fa-address-card"></i> {{ __('messages.public_contact_info') ?? 'Contact' }}</h3>
                @if($provider->contact_phone)
                    <p><strong>Phone:</strong> {{ $provider->contact_phone }}</p>
                @endif
                @if($provider->contact_email)
                    <p><strong>Email:</strong> {{ $provider->contact_email }}</p>
                @endif
                @if($provider->address)
                    <p><strong>Address:</strong> {{ $provider->address }}</p>
                @endif
            </div>

            <div class="tr-side-card">
                <h3><i class="fas fa-info-circle"></i> {{ __('messages.public_about_us') ?? 'About' }}</h3>
                <p>{{ $service->description ?? 'Trusted transport operator in Nepal.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
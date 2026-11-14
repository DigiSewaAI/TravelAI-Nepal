@extends('layouts.public')

@section('title', $service->name)

@section('content')
<style>
    /* Hero Section */
    .rest-hero {
        position: relative;
        background: linear-gradient(135deg, #f97316, #ea580c);
        background-size: cover;
        background-position: center;
        padding: 80px 20px;
        text-align: center;
        color: #fff;
        border-radius: 0 0 40px 40px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .rest-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        border-radius: 0 0 40px 40px;
    }
    .rest-hero-content { position: relative; z-index: 2; }
    .rest-hero img {
        width: 120px; height: 120px;
        border-radius: 50%; object-fit: cover;
        border: 4px solid #fff; background: #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        margin-bottom: 20px;
    }
    .rest-hero h1 { font-size: 42px; font-weight: 900; margin: 0 0 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    .rest-hero p { font-size: 18px; opacity: 0.9; margin: 0; }
    .rest-hero .badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,0.2); backdrop-filter: blur(5px);
        padding: 8px 20px; border-radius: 30px; font-size: 14px; font-weight: 700;
        margin-top: 15px; border: 1px solid rgba(255,255,255,0.3);
    }

    /* Main Container */
    .rest-container { max-width: 1300px; margin: 0 auto; padding: 0 20px; }

    /* 2-Column Layout */
    .rest-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 50px; }
    @media (max-width: 992px) { .rest-layout { grid-template-columns: 1fr; } }

    .section-title {
        font-size: 26px; font-weight: 900; color: #111; margin-bottom: 25px;
        display: flex; align-items: center; gap: 10px;
    }
    .section-title::after { content: ''; flex: 1; height: 2px; background: #eee; }

    /* Menu Cards */
    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .menu-card {
        background: #fff; border: 1px solid #eee; border-radius: 16px;
        overflow: hidden; transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .menu-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); border-color: #f97316; }
    .menu-image {
        height: 180px; background: #f9fafb; display: flex; align-items: center; justify-content: center;
        font-size: 50px; color: #d1d5db; border-bottom: 1px solid #eee; overflow: hidden;
    }
    .menu-image img { width: 100%; height: 100%; object-fit: cover; }
    .menu-info { padding: 18px; }
    .menu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 8px; }
    .menu-name { font-size: 17px; font-weight: 800; color: #111; margin: 0; }
    .menu-price { font-size: 17px; font-weight: 900; color: #f97316; white-space: nowrap; }
    .menu-category { font-size: 10px; text-transform: uppercase; font-weight: 800; color: #6b7280; background: #f3f4f6; padding: 4px 10px; border-radius: 20px; display: inline-block; margin-bottom: 8px; }
    .menu-desc { font-size: 12.5px; color: #6b7280; line-height: 1.5; margin: 0; }

    /* Info Sidebar */
    .info-sidebar { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 20px; height: fit-content; }
    .info-card { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .info-card h3 { font-size: 16px; font-weight: 800; color: #111; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee; }
    .info-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .info-item:last-child { margin-bottom: 0; }
    .info-icon {
        width: 32px; height: 32px; border-radius: 8px; background: #fff7ed;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .info-icon i { color: #f97316; font-size: 14px; }
    .info-content { flex: 1; min-width: 0; }
    .info-label { font-size: 10px; font-weight: 800; color: #9ca3af; text-transform: uppercase; margin-bottom: 2px; }
    .info-value { font-size: 13px; font-weight: 700; color: #111; word-break: break-word; line-height: 1.4; }
    .info-value a { color: #f97316; text-decoration: none; }
    .info-value a:hover { text-decoration: underline; }
    .info-desc { font-size: 13px; color: #6b7280; line-height: 1.6; margin: 0; }

    /* Reservation Section */
    .reservation-section {
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border-radius: 24px; padding: 40px; margin-bottom: 40px;
        border: 1px solid #fdba74;
    }
    .reservation-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start; }
    @media (max-width: 900px) { .reservation-grid { grid-template-columns: 1fr; } }
    .reservation-info h2 { font-size: 32px; font-weight: 900; color: #111; margin: 0 0 15px; }
    .reservation-info p { font-size: 16px; color: #4b5563; line-height: 1.6; margin-bottom: 20px; }
    .reservation-features { list-style: none; padding: 0; margin: 0; }
    .reservation-features li { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 600; color: #374151; margin-bottom: 12px; }
    .reservation-features li i { color: #f97316; }

    .reservation-form-box { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(249, 115, 22, 0.15); }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 12px; font-weight: 800; color: #374151; margin-bottom: 6px; text-transform: uppercase; }
    .form-input { width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 10px; font-family: inherit; font-size: 14px; background: #f9fafb; outline: none; transition: 0.2s; }
    .form-input:focus { border-color: #f97316; background: #fff; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
    .btn-reserve { width: 100%; padding: 14px; background: #f97316; color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .btn-reserve:hover { filter: brightness(0.9); transform: translateY(-1px); }
    .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; border: 1px solid #065f46; display: flex; align-items: center; gap: 8px; }
</style>

{{-- Success Message --}}
@if(session('success'))
    <div style="max-width: 1300px; margin: 20px auto 0; padding: 0 20px;">
        <div class="alert-success"><span>✓</span> {{ session('success') }}</div>
    </div>
@endif

{{-- 1. HERO SECTION --}}
<div class="rest-hero" style="background-image: url('{{ $service->provider->cover_image ? Storage::url($service->provider->cover_image) : '' }}');">
    <div class="rest-hero-content">
        @if($service->provider->logo_url)
            <img src="{{ Storage::url($service->provider->logo_url) }}" alt="{{ $service->provider->name }}">
        @else
            <div style="width:120px; height:120px; border-radius:50%; background:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:50px; margin-bottom:20px; box-shadow:0 10px 20px rgba(0,0,0,0.2);">🍽️</div>
        @endif
        <h1>{{ $service->provider->name }}</h1>
        <p>{{ $service->provider->address ?? 'Kathmandu, Nepal' }}</p>
        @if($service->provider->verification_status === 'verified')
        <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
            Verified Provider
        </div>
        @endif
    </div>
</div>

<div class="rest-container">
    <div class="rest-layout">
        {{-- LEFT: MENU --}}
        <div>
            <h2 class="section-title">Explore Our Menu</h2>
            @if($menuItems->isEmpty())
                <p style="color:#6b7280; text-align:center; padding:40px;">Menu is being updated. Please check back later.</p>
            @else
                <div class="menu-grid">
                    @foreach($menuItems as $item)
                    <div class="menu-card">
                        <div class="menu-image">
                            @if(isset($item->image_path) && $item->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}">
                            @else
                                <span>🍽️</span>
                            @endif
                        </div>
                        <div class="menu-info">
                            <div class="menu-header">
                                <h3 class="menu-name">{{ $item->name }}</h3>
                                <span class="menu-price">Rs. {{ number_format($item->price, 2) }}</span>
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
        </div>

        {{-- RIGHT: INFO SIDEBAR --}}
        <aside class="info-sidebar">
            {{-- Contact Card --}}
            <div class="info-card">
                <h3>Contact Information</h3>
                @if($service->provider->contact_email)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-content">
                        <div class="info-label">Email</div>
                        <div class="info-value"><a href="mailto:{{ $service->provider->contact_email }}">{{ $service->provider->contact_email }}</a></div>
                    </div>
                </div>
                @endif
                @if($service->provider->contact_phone)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone"></i></div>
                    <div class="info-content">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><a href="tel:{{ $service->provider->contact_phone }}">{{ $service->provider->contact_phone }}</a></div>
                    </div>
                </div>
                @endif
                @if($service->provider->website)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-globe"></i></div>
                    <div class="info-content">
                        <div class="info-label">Website</div>
                        <div class="info-value"><a href="{{ $service->provider->website }}" target="_blank" rel="noopener">{{ $service->provider->website }}</a></div>
                    </div>
                </div>
                @endif
                @if($service->provider->address)
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content">
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $service->provider->address }}</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- About Card --}}
            @if($service->provider->description)
            <div class="info-card">
                <h3>About Us</h3>
                <p class="info-desc">{{ $service->provider->description }}</p>
            </div>
            @endif

            {{-- Quick CTA --}}
            <div class="info-card" style="background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fdba74;">
                <h3 style="border-color: #fdba74;">Reserve a Table</h3>
                <p style="font-size:13px; color:#6b7280; margin:0 0 15px; line-height:1.5;">Book your spot instantly — no advance payment needed.</p>
                <a href="#reservation" style="display:block; text-align:center; background:#f97316; color:#fff; padding:12px; border-radius:10px; font-weight:800; font-size:14px; text-decoration:none;">Book Now ↓</a>
            </div>
        </aside>
    </div>

    {{-- RESERVATION SECTION --}}
    <div class="reservation-section" id="reservation">
        <div class="reservation-grid">
            <div class="reservation-info">
                <h2>Book Your Table</h2>
                <p>Skip the wait and secure your spot. Reserve a table for your family, friends, or a special date night.</p>
                <ul class="reservation-features">
                    <li><i class="fas fa-check-circle"></i> Instant Confirmation</li>
                    <li><i class="fas fa-check-circle"></i> Choose Your Preferred Table</li>
                    <li><i class="fas fa-check-circle"></i> No Advance Payment Required</li>
                </ul>
            </div>

            <div class="reservation-form-box">
                <form action="{{ route('public.restaurant.reserve', $service->slug) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Select Table</label>
                        <select name="table_id" required class="form-input">
                            <option value="">Choose a table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->seats }} Seats)</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="reservation_date" required class="form-input" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input type="time" name="reservation_time" required class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Number of Guests</label>
                        <input type="number" name="guests" required min="1" max="50" class="form-input" placeholder="e.g. 2">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="customer_name" required class="form-input" placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="customer_phone" required class="form-input" placeholder="+977-98XXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email (Optional)</label>
                        <input type="email" name="customer_email" class="form-input" placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Special Requests (Optional)</label>
                        <textarea name="special_requests" rows="2" class="form-input" placeholder="Window seat, birthday setup, etc."></textarea>
                    </div>
                    <button type="submit" class="btn-reserve">Confirm Reservation</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
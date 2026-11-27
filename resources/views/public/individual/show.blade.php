@extends('layouts.public')

@section('title', $provider->name . ' — ' . $service->name)
@section('meta_description', 'Book ' . $provider->name . ' as your guide. ' . ($detail?->bio ? \Illuminate\Support\Str::limit($detail->bio, 155) : 'Verified guide for treks and tours in ' . ($provider->address ?? 'Nepal')))
@section('og_meta')
<meta property="og:title" content="{{ $provider->name }} - Guide">
<meta property="og:description" content="Book {{ $provider->name }} as your guide.">
@if($provider->cover_image)
<meta property="og:image" content="{{ Storage::url($provider->cover_image) }}">
@endif
@endsection

@section('content')
<style>
    *, *::before, *::after { box-sizing: border-box; }
    :root { --indigo: #6366f1; --indigo-d: #4f46e5; --indigo-soft: #e0e7ff; }

    .ind-hero {
        position: relative;
        height: 380px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        background-size: cover;
        background-position: center;
        border-radius: 0 0 30px 30px;
        overflow: hidden;
    }
    .ind-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);
        border-radius: 0 0 30px 30px;
    }

    .ind-profile-wrap {
        max-width: 1100px;
        margin: -100px auto 40px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
    }
    .ind-profile-card {
        background: #fff;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.10);
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }
    .ind-avatar {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        border: 5px solid #fff;
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        box-shadow: 0 8px 20px rgba(99,102,241,0.20);
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: var(--indigo);
    }
    .ind-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .ind-info { flex: 1; min-width: 260px; }
    .ind-name { font-size: 34px; font-weight: 900; color: #111; margin: 0 0 8px; letter-spacing: -0.5px; line-height: 1.1; }
    .ind-location { font-size: 15px; color: #6b7280; margin: 0 0 12px; display: flex; align-items: center; gap: 6px; }
    .ind-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .ind-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 800; text-transform: uppercase;
    }
    .ind-badge.verified { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .ind-badge.exp { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .ind-badge.type { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

    .ind-container { max-width: 1200px; margin: 0 auto; padding: 0 24px 60px; }
    .ind-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 32px; }
    @media (max-width: 992px) { .ind-layout { grid-template-columns: 1fr; gap: 24px; } }

    .ind-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        margin-bottom: 20px;
    }
    .ind-card h2 {
        font-size: 20px;
        font-weight: 900;
        color: #111;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ind-card h2 i { color: var(--indigo); }
    .ind-bio { font-size: 15px; color: #4b5563; line-height: 1.7; margin: 0; white-space: pre-line; }

    .ind-tag-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .ind-tag {
        padding: 8px 16px; border-radius: 20px;
        background: var(--indigo-soft); color: var(--indigo-d);
        font-size: 13px; font-weight: 700;
    }

    .ind-form {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 28px;
        position: sticky;
        top: 20px;
    }
    .ind-form h3 {
        font-size: 18px;
        font-weight: 900;
        color: #111;
        margin: 0 0 6px;
    }
    .ind-form .price-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    .ind-form .price { font-size: 28px; font-weight: 900; color: var(--indigo); }
    .ind-form .price-label { font-size: 13px; color: #6b7280; }

    .ind-field { margin-bottom: 14px; }
    .ind-field label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
    }
    .ind-input {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        background: #fafafa;
        outline: none;
        transition: 0.2s;
    }
    .ind-input:focus { border-color: var(--indigo); background: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,0.12); }
    .ind-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 480px) { .ind-grid-2 { grid-template-columns: 1fr; } }

    .ind-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--indigo), var(--indigo-d));
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.2s;
        margin-top: 8px;
        box-shadow: 0 8px 20px rgba(99,102,241,0.35);
        font-family: inherit;
    }
    .ind-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(99,102,241,0.45); }
    .ind-btn:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; }

    .ind-alert {
        max-width: 1200px;
        margin: 20px auto 0;
        padding: 0 24px;
    }
    .ind-alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #6ee7b7;
        font-weight: 700;
        font-size: 14px;
    }
    .ind-alert-error {
        background: #fef2f2;
        color: #991b1b;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #fecaca;
        font-weight: 700;
        font-size: 14px;
    }

    .ind-price-note {
        padding: 12px;
        background: #fef4e2;
        color: #b45309;
        border-radius: 8px;
        font-size: 12px;
        text-align: center;
        margin-bottom: 12px;
    }

    .ind-empty {
        background: #f8fafc;
        border: 2px dashed #e5e7eb;
        border-radius: 18px;
        padding: 60px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .ind-empty i { font-size: 48px; color: #cbd5e1; margin-bottom: 12px; display: block; }
</style>

{{-- Success / Errors --}}
@if(session('success'))
    <div class="ind-alert">
        <div class="ind-alert-success">✓ {{ session('success') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="ind-alert">
        <div class="ind-alert-error">
            <strong>Please fix:</strong>
            <ul style="margin: 6px 0 0; padding-left: 20px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

{{-- HERO --}}
<div class="ind-hero" style="background-image: url('{{ ($detail?->cover_image_path ? Storage::url($detail->cover_image_path) : ($provider->cover_image ? Storage::url($provider->cover_image) : 'https://images.unsplash.com/photo-1551632811-561732d1e306?q=80&w=1600')) }}');"></div>

{{-- PROFILE --}}
<div class="ind-profile-wrap">
    <div class="ind-profile-card">
        <div class="ind-avatar">
            @if($detail?->avatar_path)
                <img src="{{ Storage::url($detail->avatar_path) }}" alt="{{ $provider->name }}">
            @elseif($provider->logo_url)
                <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}">
            @else
                <i class="fas fa-user-tie"></i>
            @endif
        </div>

        <div class="ind-info">
            <h1 class="ind-name">{{ $provider->name }}</h1>
            <p class="ind-location">
                <i class="fas fa-map-marker-alt" style="color:var(--indigo);"></i>
                {{ $provider->address ?? 'Nepal' }}
            </p>
            <div class="ind-badges">
                @if($provider->verification_status === 'verified')
                    <span class="ind-badge verified"><i class="fas fa-shield-alt"></i> Verified Guide</span>
                @endif
                @if($detail?->experience_years)
                    <span class="ind-badge exp"><i class="fas fa-award"></i> {{ $detail->experience_years }}+ Years Experience</span>
                @endif
                @if($detail?->specialties && count($detail->specialties) > 0)
                    <span class="ind-badge type"><i class="fas fa-mountain"></i> {{ $detail->specialties[0] }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MAIN LAYOUT --}}
<div class="ind-container">
    <div class="ind-layout">

        {{-- LEFT: Details --}}
        <div>
            @if(!$detail)
                <div class="ind-empty">
                    <i class="fas fa-user-tie"></i>
                    <p style="font-size: 15px; font-weight: 600; margin: 0;">
                        Guide profile is being set up. Please check back later.
                    </p>
                </div>
            @else
                @if($detail->bio)
                    <div class="ind-card">
                        <h2><i class="fas fa-info-circle"></i> About the Guide</h2>
                        <p class="ind-bio">{{ $detail->bio }}</p>
                    </div>
                @endif

                @if($detail->specialties && count($detail->specialties) > 0)
                    <div class="ind-card">
                        <h2><i class="fas fa-mountain"></i> Specialties</h2>
                        <div class="ind-tag-row">
                            @foreach($detail->specialties as $s)
                                <span class="ind-tag">{{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($detail->languages && count($detail->languages) > 0)
                    <div class="ind-card">
                        <h2><i class="fas fa-language"></i> Languages</h2>
                        <div class="ind-tag-row">
                            @foreach($detail->languages as $l)
                                <span class="ind-tag">{{ $l }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($detail->service_areas && count($detail->service_areas) > 0)
                    <div class="ind-card">
                        <h2><i class="fas fa-map"></i> Service Areas</h2>
                        <div class="ind-tag-row">
                            @foreach($detail->service_areas as $area)
                                <span class="ind-tag">{{ $area }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($provider->description)
                    <div class="ind-card">
                        <h2><i class="fas fa-building"></i> About {{ $provider->name }}</h2>
                        <p class="ind-bio">{{ $provider->description }}</p>
                    </div>
                @endif
            @endif
        </div>

        {{-- RIGHT: Booking Form --}}
        <div>
            <form class="ind-form" method="POST" action="{{ route('public.individual.book', $service->slug) }}">
                @csrf
                <h3><i class="fas fa-calendar-check" style="color:var(--indigo);"></i> Book This Guide</h3>

                @if($pricePerDay > 0)
                    <div class="price-row">
                        <span class="price">Rs. {{ number_format($pricePerDay, 0) }}</span>
                        <span class="price-label">per person / day</span>
                    </div>
                @else
                    <div class="price-row">
                        <span class="price" style="font-size: 20px;">Price on request</span>
                    </div>
                @endif

                <div class="ind-field">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" required class="ind-input" min="{{ date('Y-m-d') }}" value="{{ old('start_date') }}">
                </div>

                <div class="ind-grid-2">
                    <div class="ind-field">
                        <label>Days *</label>
                        <input type="number" name="duration_days" required min="1" max="365" class="ind-input" value="{{ old('duration_days', 1) }}">
                    </div>
                    <div class="ind-field">
                        <label>Persons *</label>
                        <input type="number" name="pax" required min="1" max="50" class="ind-input" value="{{ old('pax', 1) }}">
                    </div>
                </div>

                <div class="ind-field">
                    <label>Your Name *</label>
                    <input type="text" name="client_name" required maxlength="100" class="ind-input" value="{{ old('client_name') }}" placeholder="Full name">
                </div>

                <div class="ind-field">
                    <label>Email *</label>
                    <input type="email" name="customer_email" required maxlength="150" class="ind-input" value="{{ old('customer_email') }}" placeholder="you@example.com">
                </div>

                <div class="ind-field">
                    <label>Phone (optional)</label>
                    <input type="text" name="customer_phone" maxlength="30" class="ind-input" value="{{ old('customer_phone') }}" placeholder="+977-...">
                </div>

                <div class="ind-field">
                    <label>Special Requests (optional)</label>
                    <textarea name="notes" rows="3" maxlength="1000" class="ind-input" placeholder="Any specific requirements...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="ind-btn">
                    <i class="fas fa-paper-plane"></i> Send Booking Request
                </button>

                <p style="font-size: 11px; color: #9ca3af; text-align: center; margin-top: 12px; line-height: 1.5;">
                    No payment required now. The guide will contact you to confirm details.
                </p>
            </form>
        </div>

    </div>
</div>
@endsection
@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.provider_profile_page_title') ?? 'Profile')
@section('page-title', __('messages.provider_profile_header') ?? 'Profile')
@section('page-subtitle', __('messages.manage_profile_desc') ?? 'Manage your profile and contact information')

@section('content')
{{-- 🆕 Universal Premium CSS (सबै लेआउटमा मिल्ने) --}}
<style>
    .profile-hero {
        background: linear-gradient(135deg, var(--accent, #3b82f6), var(--accent-d, #2563eb));
        border-radius: 20px;
        padding: 30px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 24px;
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .profile-avatar {
        width: 90px;
        height: 90px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.4);
        display: grid;
        place-items: center;
        font-size: 32px;
        font-weight: 800;
        flex-shrink: 0;
        backdrop-filter: blur(10px);
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 18px; }
    .profile-info h2 { font-size: 26px; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px; }
    .profile-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .p-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px; border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        font-size: 12px; font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .profile-actions { display: flex; gap: 10px; margin-left: auto; flex-wrap: wrap; }
    .p-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 700; text-decoration: none;
        transition: all 0.2s; cursor: pointer; border: none;
    }
    .p-btn-primary { background: #fff; color: var(--accent-d, #2563eb); }
    .p-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
    .p-btn-ghost { background: rgba(255, 255, 255, 0.15); color: #fff; border: 1px solid rgba(255, 255, 255, 0.3); }
    .p-btn-ghost:hover { background: rgba(255, 255, 255, 0.25); }

    .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
    .profile-card {
        background: var(--card, #fff);
        border: 1px solid var(--line, #e5e7eb);
        border-radius: 16px;
        padding: 24px;
    }
    .card-title {
        font-size: 15px; font-weight: 800; color: var(--text, #1f2937);
        margin-bottom: 20px; padding-bottom: 12px;
        border-bottom: 1px solid var(--line, #e5e7eb);
        display: flex; align-items: center; gap: 10px;
    }
    .info-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; }
    .info-row:last-child { margin-bottom: 0; }
    .info-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted, #6b7280); }
    .info-value { font-size: 14px; font-weight: 600; color: var(--text, #1f2937); line-height: 1.5; word-break: break-word; }
    .info-value a { color: var(--accent, #3b82f6); text-decoration: none; }
    .info-value a:hover { text-decoration: underline; }
    .empty-state { color: #9ca3af; font-style: italic; font-weight: 500; }
</style>

{{-- Hero Section --}}
<div class="profile-hero">
    <div class="profile-avatar">
        @if($provider->logo_url)
            <img src="{{ Storage::url($provider->logo_url) }}" alt="Logo">
        @else
            {{ strtoupper(substr($provider->name ?? 'P', 0, 1)) }}
        @endif
    </div>
    <div class="profile-info">
        <h2>{{ $provider->name ?? 'Your Business' }}</h2>
        <div class="profile-badges">
            <span class="p-badge">
                <i class="fas fa-hashtag"></i> ID: {{ $provider->id }}
            </span>
            @if($provider->verification_status === 'verified')
                <span class="p-badge" style="background: rgba(34, 197, 94, 0.3); border-color: rgba(34, 197, 94, 0.5);">
                    <i class="fas fa-check-circle"></i> {{ __('messages.verified') ?? 'Verified' }}
                </span>
            @endif
        </div>
    </div>
    <div class="profile-actions">
        <a href="{{ route('provider.profile.edit') }}" class="p-btn p-btn-primary">
            <i class="fas fa-edit"></i> {{ __('messages.edit_profile') ?? 'Edit Profile' }}
        </a>
        <a href="{{ route('provider.dashboard') }}" class="p-btn p-btn-ghost">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_dashboard') ?? 'Back to Dashboard' }}
        </a>
    </div>
</div>

{{-- Info Cards --}}
<div class="profile-grid">
    {{-- Contact Info --}}
    <div class="profile-card">
        <h3 class="card-title">
            <i class="fas fa-address-card" style="color: var(--accent, #3b82f6);"></i>
            {{ __('messages.contact_information') ?? 'Contact Information' }}
        </h3>
        <div class="info-row">
            <span class="info-label">{{ __('messages.contact_email') ?? 'Email' }}</span>
            @php $email = $provider->contact_email ?? $provider->user->email ?? null; @endphp
            <span class="info-value {{ $email ? '' : 'empty-state' }}">{{ $email ?? __('messages.na') ?? 'Not provided' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.contact_phone') ?? 'Phone' }}</span>
            <span class="info-value {{ ($provider->contact_phone ?? null) ? '' : 'empty-state' }}">{{ $provider->contact_phone ?? __('messages.na') ?? 'Not provided' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.website') ?? 'Website' }}</span>
            @if($provider->website)
                <a href="{{ $provider->website }}" target="_blank" class="info-value" style="color: var(--accent, #3b82f6);">
                    {{ $provider->website }} <i class="fas fa-external-link-alt" style="font-size: 10px;"></i>
                </a>
            @else
                <span class="info-value empty-state">{{ __('messages.na') ?? 'Not provided' }}</span>
            @endif
        </div>
    </div>

    {{-- Address & About --}}
    <div class="profile-card">
        <h3 class="card-title">
            <i class="fas fa-map-marker-alt" style="color: var(--accent, #3b82f6);"></i>
            {{ __('messages.location_details') ?? 'Location & About' }}
        </h3>
        <div class="info-row">
            <span class="info-label">{{ __('messages.address') ?? 'Address' }}</span>
            <span class="info-value {{ ($provider->address ?? null) ? '' : 'empty-state' }}">{{ $provider->address ?? __('messages.na') ?? 'Not provided' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.description') ?? 'Description' }}</span>
            <span class="info-value {{ ($provider->description ?? null) ? '' : 'empty-state' }}">
                {{ $provider->description ?? __('messages.no_description_provided') ?? 'No description added yet.' }}
            </span>
        </div>
    </div>
</div>
@endsection
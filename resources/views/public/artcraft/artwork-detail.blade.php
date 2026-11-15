@extends('layouts.public')
@section('title', $artwork->name . ' — ' . $provider->name)
@section('meta_description', 'View ' . $artwork->name . ' — handmade ' . $artwork->category . ' by ' . ($artwork->artist_name ?: $provider->name) . '.')
@section('content')
<style>
    .acd-container { max-width:1100px; margin:40px auto; padding:0 24px; }
    .acd-grid { display:grid; grid-template-columns:1.2fr 1fr; gap:40px; }
    @media(max-width:900px){.acd-grid{grid-template-columns:1fr}}
    .acd-img { background:linear-gradient(135deg,#ede9fe,#c4b5fd); border-radius:20px; overflow:hidden; display:flex; align-items:center; justify-content:center; font-size:160px; min-height:450px; }
    .acd-img img { width:100%; height:auto; max-height:600px; object-fit:contain; }
    .acd-title { font-size:28px; font-weight:800; color:#0f172a; margin:0 0 8px; }
    .acd-cat { display:inline-block; background:#ede9fe; color:#5b21b6; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; margin-bottom:12px; }
    .acd-artist { color:#64748b; font-size:14px; margin-bottom:20px; }
    .acd-price { font-size:32px; font-weight:900; color:#7c3aed; margin:20px 0; }
    .acd-price small { font-size:14px; color:#64748b; }
    .acd-specs { background:#f8fafc; border-radius:14px; padding:20px; margin:20px 0; }
    .acd-specs-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e2e8f0; font-size:13px; }
    .acd-specs-row:last-child { border-bottom:none; }
    .acd-specs-row span:first-child { color:#64748b; font-weight:600; }
    .acd-specs-row span:last-child { color:#0f172a; font-weight:700; }
    .acd-desc { color:#475569; font-size:14px; line-height:1.7; margin:20px 0; }
    .acd-actions { display:flex; gap:12px; margin-top:24px; }
    .acd-btn { padding:14px 24px; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; border:none; text-decoration:none; text-align:center; flex:1; }
    .acd-btn-primary { background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; }
    .acd-btn-secondary { background:#fff; color:#7c3aed; border:2px solid #7c3aed; }
    .acd-badge { display:inline-block; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:800; text-transform:uppercase; margin-bottom:16px; }
    .acd-badge-available { background:#dcfce7; color:#166534; }
    .acd-badge-sold { background:#fdecec; color:#991b1b; }
    .acd-modal-bg { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.7); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px); }
    .acd-modal-bg.open { display:flex; }
    .acd-modal-box { background:#fff; border-radius:20px; width:100%; max-width:500px; padding:28px; max-height:90vh; overflow-y:auto; }
    .acd-field { margin-bottom:14px; }
    .acd-field label { display:block; font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:6px; }
    .acd-field input, .acd-field textarea { width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:10px; font-family:inherit; font-size:13px; }
    .acd-field input:focus, .acd-field textarea:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.15); }
</style>

<div class="acd-container">
    {{-- ✅ Edit 2A: Back to Gallery --}}
    <a href="{{ route('public.artcraft.show', $service->slug) }}" style="display:inline-flex;align-items:center;gap:6px;color:#7c3aed;font-weight:700;text-decoration:none;font-size:13px;margin-bottom:20px">← {{ __('messages.artcraft_back_to_gallery') ?? 'Back to Gallery' }}</a>

    <div class="acd-grid">
        <div class="acd-img">
            @if($artwork->image_path)
                <img src="{{ Storage::url($artwork->image_path) }}" alt="{{ $artwork->name }}">
            @else
                {{ $artwork->emoji ?: '🎨' }}
            @endif
        </div>

        <div>
            {{-- ✅ Edit 1D-जस्तै: category fallback translated --}}
            @php
                $__catKey = 'artcraft_category_' . strtolower($artwork->category);
                $__catLabel = $artwork->category && \Illuminate\Support\Facades\Lang::has('messages.' . $__catKey)
                    ? __('messages.' . $__catKey)
                    : ($artwork->category ?: __('messages.artcraft_art'));
            @endphp
            <span class="acd-cat">{{ $__catLabel }}</span>
            <h1 class="acd-title">{{ $artwork->name }}</h1>
            <p class="acd-artist">{{ __('messages.artcraft_artist') }}: <strong>{{ $artwork->artist_name ?: $provider->name }}</strong></p>

            {{-- ✅ Edit 2B: dynamic status badge --}}
            <span class="acd-badge {{ $artwork->status === 'available' ? 'acd-badge-available' : 'acd-badge-sold' }}">
                {{ __('messages.artcraft_' . $artwork->status) ?? ucfirst($artwork->status) }}
            </span>
            <div class="acd-price">Rs {{ number_format($artwork->price, 2) }}<small> NPR</small></div>

            @if($artwork->description)
            <p class="acd-desc">{{ $artwork->description }}</p>
            @endif

            @if($artwork->materials || $artwork->dimensions)
            <div class="acd-specs">
                {{-- ✅ Edit 2C: specs labels translated --}}
                @if($artwork->materials)
                <div class="acd-specs-row"><span>{{ __('messages.artcraft_materials') }}</span><span>{{ $artwork->materials }}</span></div>
                @endif
                @if($artwork->dimensions)
                <div class="acd-specs-row"><span>{{ __('messages.artcraft_dimensions') }}</span><span>{{ $artwork->dimensions }}</span></div>
                @endif
            </div>
            @endif

            @if($artwork->status === 'available')
            <div class="acd-actions">
                {{-- ✅ Edit 2D: buttons translated --}}
                <button type="button" onclick="document.getElementById('enquireModal').classList.add('open')" class="acd-btn acd-btn-primary">
                    ✉️ {{ __('messages.artcraft_enquire') }}
                </button>
                <a href="{{ route('public.artcraft.custom', $service->slug) }}" class="acd-btn acd-btn-secondary">
                    ✨ {{ __('messages.artcraft_custom_request') ?? 'Custom Request' }}
                </a>
            </div>
            @else
            <div style="background:#fef4e2;border:1px solid #fbbf24;padding:14px 18px;border-radius:12px;font-size:13px;color:#92400e;font-weight:600;margin-top:20px">
                {{ __('messages.artcraft_' . $artwork->status) ?? ucfirst($artwork->status) }} — {{ __('messages.artcraft_sold_hint') ?? 'You can still request a custom commission.' }}
            </div>
            <div class="acd-actions" style="margin-top:16px">
                {{-- ✅ File 4 key प्रयोग --}}
                <a href="{{ route('public.artcraft.custom', $service->slug) }}" class="acd-btn acd-btn-primary">✨ {{ __('messages.artcraft_request_commission') }}</a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ENQUIRE MODAL --}}
<div id="enquireModal" class="acd-modal-bg">
    <div class="acd-modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            {{-- ✅ Edit 2E: modal title translated --}}
            <h3 style="margin:0;font-size:18px;font-weight:800">✉️ {{ __('messages.artcraft_enquire_about') ?? 'Enquire about this artwork' }}</h3>
            <button type="button" onclick="document.getElementById('enquireModal').classList.remove('open')" style="background:none;border:none;font-size:24px;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" action="{{ route('public.artcraft.enquire', [$service->slug, $artwork->id]) }}">
            @csrf
            <div class="acd-field">
                <label>{{ __('messages.public_your_name') ?? 'Your Name' }} *</label>
                <input type="text" name="client_name" required maxlength="150" value="{{ old('client_name') }}">
            </div>
            <div class="acd-field">
                <label>{{ __('messages.public_email') ?? 'Your Email' }} *</label>
                <input type="email" name="client_email" required maxlength="150" value="{{ old('client_email') }}">
            </div>
            <div class="acd-field">
                <label>{{ __('messages.artcraft_message') ?? 'Message (Optional)' }}</label>
                <textarea name="message" rows="3" maxlength="1000" placeholder="{{ __('messages.artcraft_message_hint') ?? 'Ask about availability, delivery, customization...' }}">{{ old('message') }}</textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="button" onclick="document.getElementById('enquireModal').classList.remove('open')" style="flex:1;padding:12px;background:#f1f5f9;color:#0f172a;border:none;border-radius:10px;font-weight:700;cursor:pointer">{{ __('messages.pm_cancel') ?? 'Cancel' }}</button>
                <button type="submit" style="flex:2;padding:12px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer">{{ __('messages.artcraft_send_enquiry') ?? 'Send Enquiry' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
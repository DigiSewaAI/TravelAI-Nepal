@extends('layouts.public')
@section('title', __('messages.artcraft_request_commission') . ' — ' . $provider->name)
@section('content')
<style>
    .acc-container { max-width:800px; margin:40px auto; padding:0 24px; }
    .acc-card { background:#fff; border:1px solid #ede9fe; border-radius:20px; padding:32px; box-shadow:0 10px 30px rgba(124,58,237,0.06); }
    .acc-title { font-size:26px; font-weight:800; color:#0f172a; margin:0 0 6px; }
    .acc-sub { color:#64748b; font-size:14px; margin:0 0 24px; }
    .acc-field { margin-bottom:16px; }
    .acc-field label { display:block; font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:.3px; }
    .acc-field input, .acc-field select, .acc-field textarea { width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:10px; font-family:inherit; font-size:13px; background:#fff; color:#0f172a; }
    .acc-field input:focus, .acc-field select:focus, .acc-field textarea:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,0.15); }
    .acc-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media(max-width:640px){.acc-row{grid-template-columns:1fr}}
    .acc-btn { width:100%; padding:14px; background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; border:none; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; margin-top:8px; }
    .acc-btn:hover { filter:brightness(1.1); }
    .acc-help { font-size:11px; color:#64748b; margin-top:4px; }
    .acc-alert { padding:14px 18px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:700; }
</style>

<div class="acc-container">
    {{-- ✅ Edit 3A: Back to Gallery --}}
    <a href="{{ route('public.artcraft.show', $service->slug) }}" style="display:inline-flex;align-items:center;gap:6px;color:#7c3aed;font-weight:700;text-decoration:none;font-size:13px;margin-bottom:20px">← {{ __('messages.artcraft_back_to_gallery') ?? 'Back to Gallery' }}</a>

    <div class="acc-card">
        {{-- ✅ Edit 3B: title + subtitle translated --}}
        <h1 class="acc-title">✨ {{ __('messages.artcraft_request_commission') ?? 'Request a Custom Commission' }}</h1>
        <p class="acc-sub">{{ __('messages.artcraft_request_commission_sub') ?? 'Tell us about your dream artwork. The artist will review and send you a quote.' }}</p>

        @if($errors->any())
        <div class="acc-alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
            @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('public.artcraft.custom.submit', $service->slug) }}" enctype="multipart/form-data">
            @csrf
            <div class="acc-row">
                <div class="acc-field">
                    <label>{{ __('messages.public_your_name') ?? 'Your Name' }} *</label>
                    <input type="text" name="client_name" required maxlength="150" value="{{ old('client_name') }}">
                </div>
                <div class="acc-field">
                    <label>{{ __('messages.public_email') ?? 'Your Email' }} *</label>
                    <input type="email" name="client_email" required maxlength="150" value="{{ old('client_email') }}">
                </div>
            </div>

            <div class="acc-row">
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_artwork_type') ?? 'Artwork Type' }} *</label>
                    <select name="type" required>
                        <option value="">— {{ __('messages.artcraft_select_type') ?? 'Select Type' }} —</option>
                        @foreach(['Painting','Sculpture','Pottery','Wood Carving','Thangka','Paubha','Metal Craft','Handmade'] as $t)
                            <option value="{{ $t }}" @selected(old('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_quantity') ?? 'Quantity' }} *</label>
                    <input type="number" name="quantity" min="1" max="100" value="{{ old('quantity', 1) }}" required>
                </div>
            </div>

            <div class="acc-field">
                <label>{{ __('messages.artcraft_description') ?? 'Description' }} *</label>
                <textarea name="description" required maxlength="2000" rows="4" placeholder="{{ __('messages.artcraft_description_hint') ?? 'Describe what you want — size, style, colors, theme...' }}">{{ old('description') }}</textarea>
            </div>

            <div class="acc-row">
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_materials') }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                    <input type="text" name="materials" maxlength="255" value="{{ old('materials') }}" placeholder="{{ __('messages.artcraft_materials_hint') ?? 'e.g. wood, brass, canvas' }}">
                </div>
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_dimensions') }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                    <input type="text" name="dimensions" maxlength="100" value="{{ old('dimensions') }}" placeholder="{{ __('messages.artcraft_dimensions_hint') ?? 'e.g. 30cm × 20cm' }}">
                </div>
            </div>

            <div class="acc-row">
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_budget') ?? 'Budget' }} (Rs) *</label>
                    <input type="number" name="budget" min="0" step="0.01" required value="{{ old('budget') }}">
                </div>
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_deadline') ?? 'Deadline' }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                    <input type="date" name="deadline" min="{{ date('Y-m-d') }}" value="{{ old('deadline') }}">
                </div>
            </div>

            <div class="acc-field">
                <label>{{ __('messages.artcraft_reference_image') ?? 'Reference Image' }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                <input type="file" name="reference_image" accept="image/jpeg,image/png,image/jpg,image/webp">
                <p class="acc-help">{{ __('messages.artcraft_reference_hint') ?? 'Max 5MB · JPG, PNG, WEBP · Private — only the artist will see it' }}</p>
            </div>

            <div class="acc-row">
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_pickup_address') ?? 'Pickup Address' }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                    <input type="text" name="pickup_address" maxlength="255" value="{{ old('pickup_address') }}">
                </div>
                <div class="acc-field">
                    <label>{{ __('messages.artcraft_delivery_address') ?? 'Delivery Address' }} ({{ __('messages.optional') ?? 'Optional' }})</label>
                    <input type="text" name="delivery_address" maxlength="255" value="{{ old('delivery_address') }}">
                </div>
            </div>

            {{-- ✅ Edit 3C: submit button translated --}}
            <button type="submit" class="acc-btn">{{ __('messages.artcraft_submit_commission') ?? 'Submit Custom Commission Request' }}</button>
        </form>
    </div>
</div>
@endsection
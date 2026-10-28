@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.products_create_btn'))
@section('header', __('messages.products_create_btn'))

@section('content')
@php
    if (!isset($stockHealth)) {
        $__p = $provider ?? (auth()->check() ? auth()->user()->ownProvider() : null);
        if ($__p) {
            $__total = \App\Models\Product::where('provider_id', $__p->id)->count();
            $__in = \App\Models\Product::where('provider_id', $__p->id)->where('stock_quantity', '>', 0)->count();
            $stockHealth = $__total > 0 ? (int) round(($__in / $__total) * 100) : 100;
        } else { $stockHealth = 0; }
    }
@endphp

<style>
  .pcf-wrap{max-width:920px;margin:0 auto;}
  .pcf-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));border-radius:18px;padding:26px 28px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);margin-bottom:20px;}
  .pcf-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .pcf-hero h1{font-size:20px;font-weight:800;margin:0 0 5px;letter-spacing:-.3px;position:relative;z-index:1;}
  .pcf-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}

  .pcf-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:16px;padding:26px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.02);}
  .pcf-card-title{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);margin:0 0 20px;padding-bottom:14px;border-bottom:1px solid var(--line,#e5e7eb);display:flex;align-items:center;gap:10px;}
  .pcf-card-title::before{content:'';width:4px;height:18px;border-radius:2px;background:var(--accent,#2563eb);}

  .pcf-form label:not(.pcf-radio-label){display:block !important;font-size:11.5px !important;font-weight:800 !important;text-transform:uppercase !important;letter-spacing:.5px !important;color:var(--muted,#6b7280) !important;margin-bottom:8px !important;}
  .pcf-form input[type="text"],.pcf-form input[type="number"],.pcf-form input[type="email"],.pcf-form input[type="url"],.pcf-form input[type="file"],.pcf-form select,.pcf-form textarea{width:100% !important;padding:11px 14px !important;border:1.5px solid var(--line,#e5e7eb) !important;border-radius:10px !important;background:var(--card,#fff) !important;color:var(--text,#1f2937) !important;font-family:inherit !important;font-size:13.5px !important;transition:.15s !important;box-sizing:border-box !important;}
  .pcf-form input:focus,.pcf-form select:focus,.pcf-form textarea:focus{outline:none !important;border-color:var(--accent,#2563eb) !important;box-shadow:0 0 0 3px var(--accent-soft,#eff6ff) !important;}
  .pcf-form input[type="file"]{padding:8px 12px !important;cursor:pointer;}
  .pcf-form input[type="radio"]{accent-color:var(--accent,#2563eb) !important;width:16px !important;height:16px !important;margin:0 !important;}
  .pcf-form h2,.pcf-form h3,.pcf-form h4{font-size:12px !important;font-weight:800 !important;text-transform:uppercase !important;letter-spacing:.6px !important;color:var(--accent-d,#1d4ed8) !important;margin:20px 0 12px !important;padding-bottom:8px !important;border-bottom:1px dashed var(--line,#e5e7eb) !important;}

  .pcf-radio-group{display:flex;gap:10px;flex-wrap:wrap;}
  .pcf-radio-label{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border:1.5px solid var(--line,#e5e7eb);border-radius:10px;cursor:pointer;transition:.15s;background:var(--card,#fff);font-weight:700;font-size:13px;color:var(--text,#1f2937);}
  .pcf-radio-label:hover{border-color:var(--accent,#2563eb);background:var(--accent-soft,#eff6ff);}
  .pcf-radio-label:has(input:checked){border-color:var(--accent,#2563eb);background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);}

  .pcf-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px;padding-top:20px;border-top:1px solid var(--line,#e5e7eb);}
  .pcf-submit{display:inline-flex;align-items:center;gap:7px;padding:12px 24px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#2563eb);transition:.15s;}
  .pcf-submit:hover{transform:translateY(-1px);box-shadow:0 12px 24px -8px var(--accent,#2563eb);}
  .pcf-cancel{padding:12px 22px;border-radius:11px;background:var(--card,#fff);border:1.5px solid var(--line,#e5e7eb);color:var(--muted,#6b7280);font-size:13px;font-weight:800;text-decoration:none;transition:.15s;display:inline-flex;align-items:center;}
  .pcf-cancel:hover{border-color:var(--accent,#2563eb);color:var(--accent-d,#1d4ed8);}

  .pcf-alert{padding:12px 16px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;border-left:4px solid;}
  .pcf-error{background:var(--amber-soft,#fef3c7);color:var(--amber,#b45309);border-color:var(--amber,#f59e0b);}
  .pcf-error ul{margin:6px 0 0;padding-left:20px;}
  .pcf-danger{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);border-color:var(--red,#dc2626);}
</style>

<div class="pcf-wrap">
  <div class="pcf-hero">
    <h1>📦 {{ __('messages.products_create_btn') }}</h1>
    <p>{{ __('messages.products_create_btn') }} — TravelAI Nepal</p>
  </div>

  @if(session('error'))
    <div class="pcf-alert pcf-danger"><span>⚠</span><div>{{ session('error') }}</div></div>
  @endif

  @if ($errors->any())
    <div class="pcf-alert pcf-error">
      <span>⚠</span>
      <div>
        <strong>{{ __('messages.validation_error') ?? 'Please check the form' }}</strong>
        <ul>@foreach ($errors->all() as $message) <li>{{ $message }}</li> @endforeach</ul>
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('provider.products.store') }}" enctype="multipart/form-data" class="pcf-form">
    @csrf

    <div class="pcf-card">
      <div class="pcf-card-title">{{ __('messages.product_type') }} *</div>
      <div class="pcf-radio-group">
        @if(in_array('shop', $allowedTypes ?? []))
          <label class="pcf-radio-label">
            <input type="radio" name="product_type" value="shop" data-type-toggle {{ old('product_type', $type) === 'shop' ? 'checked' : '' }}>
            <span>{{ __('messages.product_type_shop') }}</span>
          </label>
        @endif
        @if(in_array('rental', $allowedTypes ?? []))
          <label class="pcf-radio-label">
            <input type="radio" name="product_type" value="rental" data-type-toggle {{ old('product_type', $type) === 'rental' ? 'checked' : '' }}>
            <span>{{ __('messages.product_type_rental') }}</span>
          </label>
        @endif
        @if(in_array('wholesale', $allowedTypes ?? []))
          <label class="pcf-radio-label">
            <input type="radio" name="product_type" value="wholesale" data-type-toggle {{ old('product_type', $type) === 'wholesale' ? 'checked' : '' }}>
            <span>{{ __('messages.product_type_wholesale') }}</span>
          </label>
        @endif
      </div>
    </div>

    <div class="pcf-card">
      <div class="pcf-card-title">{{ __('messages.product_name') }}</div>
      <div style="margin-bottom:16px;">
        <label>{{ __('messages.product_name') }} *</label>
        <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
      </div>
      <div>
        <label>{{ __('messages.product_description') }}</label>
        <textarea name="description" rows="4" maxlength="5000">{{ old('description') }}</textarea>
      </div>
    </div>

    <div class="pcf-card">
      <div class="pcf-card-title">{{ __('messages.product_price') }}</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div>
          <label>{{ __('messages.product_price') }} *</label>
          <input type="number" name="price" step="0.01" min="0" required value="{{ old('price') }}">
        </div>
        <div>
          <label>{{ __('messages.product_currency') }} *</label>
          <select name="currency" required>
            <option value="NPR" {{ old('currency', 'NPR') === 'NPR' ? 'selected' : '' }}>NPR</option>
            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
          </select>
        </div>
      </div>
    </div>

    <div class="pcf-card">
      <div class="pcf-card-title">Media & Status</div>
      <div style="margin-bottom:16px;">
        <label>{{ __('messages.product_cover_image') }}</label>
        <input type="file" name="cover_image" accept="image/*">
        <p style="font-size:12px;color:var(--muted,#6b7280);margin-top:6px;">JPG, PNG, WEBP · Max 5MB</p>
      </div>
      <div>
        <label>{{ __('messages.product_status') }} *</label>
        <select name="status" required>
          <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('messages.product_status_active') }}</option>
          <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.product_status_inactive') }}</option>
        </select>
      </div>
    </div>

    <div class="pcf-card">
      <div class="pcf-card-title">Type-Specific Details</div>
      <div data-type-section="shop" style="display:none;">
        @include('provider.products._fields_shop', ['product' => null])
      </div>
      <div data-type-section="rental" style="display:none;">
        @include('provider.products._fields_rental', ['product' => null])
      </div>
      <div data-type-section="wholesale" style="display:none;">
        @include('provider.products._fields_wholesale', ['product' => null])
      </div>
    </div>

    <div class="pcf-actions">
      <a href="{{ route('provider.products.index') }}" class="pcf-cancel">{{ __('messages.cancel') }}</a>
      <button type="submit" class="pcf-submit">{{ __('messages.products_create_btn') }}</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('[data-type-toggle]');
    const sections = document.querySelectorAll('[data-type-section]');
    function updateSections() {
        const selected = document.querySelector('[data-type-toggle]:checked');
        const type = selected ? selected.value : 'shop';
        sections.forEach(sec => {
            const isActive = sec.dataset.typeSection === type;
            sec.style.display = isActive ? 'block' : 'none';
            sec.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = !isActive; });
        });
    }
    toggles.forEach(t => t.addEventListener('change', updateSections));
    updateSections();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SH = {{ (int)($stockHealth ?? 0) }};
    const el = document.getElementById('scVal');
    const bar = document.getElementById('scBar');
    if (el) el.innerHTML = SH + '% <small>{{ __('messages.in_stock_label') }}</small>';
    if (bar) bar.style.width = SH + '%';
});
</script>
@endsection
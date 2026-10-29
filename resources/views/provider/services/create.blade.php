@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.create_service_page_title'))
@section('header', __('messages.create_service_header'))

@section('content')
<style>
  .svcf-wrap{max-width:860px;margin:0 auto;}
  .svcf-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));border-radius:18px;padding:26px 28px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);margin-bottom:20px;}
  .svcf-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .svcf-hero h1{font-size:20px;font-weight:800;margin:0 0 5px;letter-spacing:-.3px;position:relative;z-index:1;}
  .svcf-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .svcf-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:16px;padding:26px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.02);}
  .svcf-card-title{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);margin:0 0 20px;padding-bottom:14px;border-bottom:1px solid var(--line,#e5e7eb);display:flex;align-items:center;gap:10px;}
  .svcf-card-title::before{content:'';width:4px;height:18px;border-radius:2px;background:var(--accent,#2563eb);}

  /* === Universal Form Field Styling (applies to partials too) === */
  .svcf-form .svcf-card label,
  .svcf-form .svcf-card .block.text-gray-700{
    display:block !important;
    font-size:11.5px !important;
    font-weight:800 !important;
    text-transform:uppercase !important;
    letter-spacing:.5px !important;
    color:var(--muted,#6b7280) !important;
    margin-bottom:8px !important;
  }
  .svcf-form .svcf-card input[type="text"],
  .svcf-form .svcf-card input[type="number"],
  .svcf-form .svcf-card input[type="email"],
  .svcf-form .svcf-card input[type="url"],
  .svcf-form .svcf-card input[type="tel"],
  .svcf-form .svcf-card input[type="time"],
  .svcf-form .svcf-card input[type="date"],
  .svcf-form .svcf-card input[type="password"],
  .svcf-form .svcf-card select,
  .svcf-form .svcf-card textarea{
    width:100% !important;
    padding:11px 14px !important;
    border:1.5px solid var(--line,#e5e7eb) !important;
    border-radius:10px !important;
    background:var(--card,#fff) !important;
    color:var(--text,#1f2937) !important;
    font-family:inherit !important;
    font-size:13.5px !important;
    transition:.15s !important;
  }
  .svcf-form .svcf-card input:focus,
  .svcf-form .svcf-card select:focus,
  .svcf-form .svcf-card textarea:focus{
    outline:none !important;
    border-color:var(--accent,#2563eb) !important;
    box-shadow:0 0 0 3px var(--accent-soft,#eff6ff) !important;
  }
  .svcf-form .svcf-card input[type="file"]{
    padding:8px 12px !important;
    cursor:pointer;
  }
  .svcf-form .svcf-card input[type="checkbox"],
  .svcf-form .svcf-card input[type="radio"]{
    accent-color:var(--accent,#2563eb) !important;
  }

  /* Section headings inside partials (Restaurant Details, etc.) */
  .svcf-form .svcf-card h2,
  .svcf-form .svcf-card h3,
  .svcf-form .svcf-card h4{
    font-size:12px !important;
    font-weight:800 !important;
    text-transform:uppercase !important;
    letter-spacing:.6px !important;
    color:var(--accent-d,#1d4ed8) !important;
    margin:20px 0 12px !important;
    padding-bottom:8px !important;
    border-bottom:1px dashed var(--line,#e5e7eb) !important;
  }

  /* Category fields wrapper */
  .svcf-form .category-fields{
    padding-top:4px;
  }

  /* Form spacing */
  .svcf-form .svcf-card .space-y-4 > div{
    margin-bottom:16px !important;
  }
  .svcf-form .svcf-card .space-y-4 > div:last-child{
    margin-bottom:0 !important;
  }

  /* Actions */
  .svcf-actions{display:flex;gap:10px;margin-top:16px;padding-top:20px;border-top:1px solid var(--line,#e5e7eb);}
  .svcf-submit{display:inline-flex;align-items:center;gap:7px;padding:12px 24px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#2563eb);transition:.15s;}
  .svcf-submit:hover{transform:translateY(-1px);box-shadow:0 12px 24px -8px var(--accent,#2563eb);}
  .svcf-cancel{padding:12px 22px;border-radius:11px;background:var(--card,#fff);border:1.5px solid var(--line,#e5e7eb);color:var(--muted,#6b7280);font-size:13px;font-weight:800;text-decoration:none;transition:.15s;display:inline-flex;align-items:center;}
  .svcf-cancel:hover{border-color:var(--accent,#2563eb);color:var(--accent-d,#1d4ed8);}

  /* Alerts */
  .svcf-alert{padding:12px 16px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;border-left:4px solid;}
  .svcf-success{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);border-color:var(--green,#16a34a);}
  .svcf-error{background:var(--amber-soft,#fef3c7);color:var(--amber,#b45309);border-color:var(--amber,#f59e0b);}
  .svcf-alert ul{margin:6px 0 0;padding-left:20px;}
  /* Fix: Tailwind hidden class not compiled - force hide */
  .category-fields.hidden{display:none !important;}
</style>

<div class="svcf-wrap">

  <div class="svcf-hero">
    <h1>✨ {{ __('messages.create_service_header') }}</h1>
    <p>{{ __('messages.create_service_page_title') }}</p>
  </div>

  @if(session('success'))
    <div class="svcf-alert svcf-success">
      <span>✓</span>
      <div><strong>{{ __('messages.success') }}</strong> {{ session('success') }}</div>
    </div>
  @endif

  @php
    $generalErrors = $errors->getMessages();
    unset($generalErrors['limit']);
  @endphp

  @if (!empty($generalErrors))
    <div class="svcf-alert svcf-error">
      <span>⚠</span>
      <div>
        <strong>{{ __('messages.validation_error') }}</strong>
        <ul>
          @foreach ($generalErrors as $field => $messages)
            @foreach ($messages as $message)
              <li>{{ $message }}</li>
            @endforeach
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  @error('limit')
    <div class="svcf-alert svcf-error">
      <span>⚠</span>
      <div>{!! $message !!}</div>
    </div>
  @enderror

  <form method="POST" action="{{ route('provider.services.store') }}" enctype="multipart/form-data" class="svcf-form">
    @csrf

    <div class="svcf-card">
      <div class="svcf-card-title">{{ __('messages.service_name') ?? 'Service Details' }}</div>

      <div class="space-y-4">
        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.service_name') }} *</label>
          <input type="text" name="name" value="{{ old('name') }}" required class="@error('name') border-red-500 @enderror">
          @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.category') }} *</label>
          @if($allowedCategories->count() === 1)
            <select disabled class="bg-gray-100 cursor-not-allowed">
              <option selected>{{ $allowedCategories->first()->name }}</option>
            </select>
            <input type="hidden" name="service_category_id" value="{{ $allowedCategories->first()->id }}" data-slug="{{ $allowedCategories->first()->slug }}">
            <p class="text-xs text-gray-500 mt-1">
              {{ __('messages.category_locked_info', ['type' => $providerType->name, 'category' => $allowedCategories->first()->name]) }}
            </p>
          @else
            <select name="service_category_id" required class="@error('service_category_id') border-red-500 @enderror">
              <option value="">— {{ __('messages.select_category') }} —</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          @endif
          @error('service_category_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.price') }}</label>
          <div style="display:flex;gap:8px;">
            <input type="number" name="price" value="{{ old('price') }}" step="0.01" style="flex:1;" class="@error('price') border-red-500 @enderror">
            <select name="currency" style="max-width:110px;">
              <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
              <option value="NPR" {{ old('currency', 'USD') == 'NPR' ? 'selected' : '' }}>NPR</option>
            </select>
          </div>
          @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          @error('currency')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.description') }}</label>
          <textarea name="description" rows="4" class="@error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
          @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.cover_image') }}</label>
          <input type="file" name="cover_image" accept="image/*" class="@error('cover_image') border-red-500 @enderror">
          @error('cover_image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.status') }}</label>
          <select name="status">
            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="svcf-card">
      <div class="svcf-card-title">Additional Details</div>
      @include('provider.services._fields_trek', ['service' => null])
      @include('provider.services._fields_tour', ['service' => null])
      @include('provider.services._fields_hotel', ['service' => null])
      @include('provider.services._fields_activity', ['service' => null])
      @include('provider.services._fields_experience', ['service' => null])
      @include('provider.services._fields_transport', ['service' => null])
      @include('provider.services._fields_restaurant', ['service' => null])
      @include('provider.services._fields_money_changer', ['service' => null])
      @include('provider.services._fields_art_craft', ['service' => null])
    </div>

    <div class="svcf-actions">
      <button type="submit" class="svcf-submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        {{ __('messages.create_service') }}
      </button>
      <a href="{{ route('provider.services.index') }}" class="svcf-cancel">{{ __('messages.cancel') }}</a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var categorySelect = document.querySelector('select[name="service_category_id"]');
    var hiddenCategory = document.querySelector('input[type="hidden"][name="service_category_id"]');
    if (!categorySelect && !hiddenCategory) return;

    var fieldGroups = document.querySelectorAll('.category-fields');
    if (fieldGroups.length === 0) {
        console.warn('[Services] No .category-fields found — partials might not have the class.');
        return;
    }

    function updateCategoryFields() {
        var slug = '';
        if (hiddenCategory) {
            slug = hiddenCategory.getAttribute('data-slug') || '';
        } else if (categorySelect && categorySelect.selectedIndex > 0) {
            var opt = categorySelect.options[categorySelect.selectedIndex];
            slug = opt ? (opt.getAttribute('data-slug') || '') : '';
        }

        fieldGroups.forEach(function(group) {
            var __groupSlugs = (group.getAttribute('data-slug') || '').split(',');
            var isActive = slug !== '' && __groupSlugs.indexOf(slug) !== -1;
            group.classList.toggle('hidden', !isActive);
            group.querySelectorAll('input, select, textarea').forEach(function(input) {
                if (isActive) {
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('disabled', 'disabled');
                }
            });
        });
    }

    if (categorySelect) categorySelect.addEventListener('change', updateCategoryFields);
    updateCategoryFields();
});
</script>
@endsection
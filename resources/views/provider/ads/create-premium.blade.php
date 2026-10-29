@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.ads_buy_new'))
@section('content')
<style>
.pad-wrap{max-width:960px;margin:0 auto;padding:0 0 24px}
.pad-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted,#64748b);text-decoration:none;font-weight:700;margin-bottom:16px;transition:.2s}
.pad-back:hover{color:var(--accent,#2563eb)}
.pad-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3)}
.pad-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.pad-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px;color:#fff;position:relative;z-index:1}
.pad-hero p{font-size:13px;margin:0;opacity:.92;color:#fff;position:relative;z-index:1}
.pad-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;box-shadow:0 4px 24px -8px rgba(15,23,42,.08);overflow:hidden;margin-bottom:18px}
.pad-card-head{padding:18px 24px;border-bottom:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc);display:flex;align-items:center;gap:10px}
.pad-card-head h3{font-size:15px;font-weight:800;color:var(--text,#0f172a);margin:0;display:flex;align-items:center;gap:8px}
.pad-card-head i{color:var(--accent,#2563eb)}
.pad-card-body{padding:22px 24px}
.pad-errors{background:var(--red-soft,#fdecec);border-left:4px solid var(--red,#ef4444);color:var(--red,#ef4444);padding:14px 18px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:18px}
.pad-errors ul{margin:0;padding-left:20px}
.pad-errors li{margin:4px 0}
.pad-field{margin-bottom:16px}
.pad-field:last-child{margin-bottom:0}
.pad-field label{display:block;font-size:12.5px;font-weight:800;color:var(--text,#0f172a);margin-bottom:6px}
.pad-field label span.req{color:var(--red,#ef4444);margin-left:2px}
.pad-field input[type="text"],
.pad-field input[type="file"],
.pad-field select,
.pad-field textarea{width:100%;padding:10px 14px;border:1px solid var(--line,#e8edf5);border-radius:10px;background:var(--card,#fff);color:var(--text,#0f172a);font-family:inherit;font-size:13px;transition:.2s;box-sizing:border-box}
.pad-field input:focus,.pad-field select:focus,.pad-field textarea:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 3px var(--accent-soft,rgba(37,99,235,.12))}
.pad-field input[type="file"]{padding:8px 12px}
.pad-field input[type="file"]::file-selector-button{margin-right:10px;padding:6px 14px;border:0;border-radius:8px;background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb);font-family:inherit;font-size:12px;font-weight:800;cursor:pointer}
.pad-field textarea{resize:vertical;min-height:80px}
.pad-hint{font-size:11.5px;color:var(--muted,#64748b);margin-top:6px;line-height:1.6;display:flex;align-items:flex-start;gap:6px}
.pad-hint i{color:var(--accent,#2563eb);font-size:11px;margin-top:2px;flex-shrink:0}
.pad-hint code{background:var(--accent-soft,rgba(37,99,235,.08));color:var(--accent,#2563eb);padding:2px 6px;border-radius:5px;font-size:11px;font-weight:700}
.pad-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.pad-row{grid-template-columns:1fr}}
.pad-duration-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px}
.pad-duration{position:relative;cursor:pointer}
.pad-duration input[type="radio"]{position:absolute;opacity:0;pointer-events:none}
.pad-duration-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:18px 12px;border:2px solid var(--line,#e8edf5);border-radius:14px;background:var(--card,#fff);transition:.2s;text-align:center}
.pad-duration input[type="radio"]:checked + .pad-duration-card{border-color:var(--accent,#2563eb);background:var(--accent-soft,rgba(37,99,235,.08));box-shadow:0 6px 18px -6px var(--accent,#2563eb)}
.pad-duration-card:hover{border-color:var(--accent,#2563eb)}
.pad-duration-card .days{font-size:16px;font-weight:800;color:var(--text,#0f172a);margin-bottom:4px}
.pad-duration-card .price{font-size:14px;font-weight:800;color:var(--accent,#2563eb)}
.pad-info{background:var(--accent-soft,rgba(37,99,235,.08));border-left:4px solid var(--accent,#2563eb);border-radius:10px;padding:12px 16px;font-size:12.5px;color:var(--text,#0f172a);line-height:1.6;margin-top:12px;display:flex;align-items:flex-start;gap:8px;font-weight:500}
.pad-info i{color:var(--accent,#2563eb);font-size:13px;margin-top:2px;flex-shrink:0}
.pad-actions{display:flex;gap:12px;padding-top:6px}
.pad-btn{flex:1;padding:14px 24px;border-radius:12px;font-size:14px;font-weight:800;text-decoration:none;text-align:center;border:none;cursor:pointer;font-family:inherit;transition:.2s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
.pad-btn.cancel{background:var(--bg,#f4f6fb);color:var(--muted,#64748b);border:1px solid var(--line,#e8edf5)}
.pad-btn.cancel:hover{background:var(--line,#e8edf5);color:var(--text,#0f172a)}
.pad-btn.submit{background:var(--accent,#2563eb);color:#fff;box-shadow:0 10px 26px -8px var(--accent,#2563eb)}
.pad-btn.submit:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-1px);box-shadow:0 14px 32px -10px var(--accent,#2563eb)}
</style>

<div class="pad-wrap">
    <a href="{{ route('provider.ads.index') }}" class="pad-back">
        <i class="fas fa-arrow-left"></i> {{ __('messages.ads_back') }}
    </a>

    <div class="pad-hero">
        <h1><i class="fas fa-bullhorn"></i> {{ __('messages.ads_buy_new') }}</h1>
        <p>{{ __('messages.ads_buy_subtitle') }}</p>
    </div>

    @if($errors->any())
        <div class="pad-errors">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('provider.ads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Ad Content --}}
        <div class="pad-card">
            <div class="pad-card-head">
                <h3><i class="fas fa-info-circle"></i> Ad Content</h3>
            </div>
            <div class="pad-card-body">
                <div class="pad-field">
                    <label>{{ __('messages.ads_title_label') }} <span class="req">*</span></label>
                    <input type="text" name="title" required value="{{ old('title') }}">
                </div>
                <div class="pad-field">
                    <label>{{ __('messages.ads_description_label') }}</label>
                    <textarea name="description" rows="3" maxlength="500">{{ old('description') }}</textarea>
                </div>
                <div class="pad-field">
                    <label>{{ __('messages.ads_image_label') }} <span class="req">*</span> (1200×300)</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
            </div>
        </div>

        {{-- Link & CTA --}}
        <div class="pad-card">
            <div class="pad-card-head">
                <h3><i class="fas fa-link"></i> Link & Call-to-Action</h3>
            </div>
            <div class="pad-card-body">
                <div class="pad-row">
                    <div class="pad-field">
                        <label>{{ __('messages.ads_link_label') }} <span class="req">*</span></label>
                        <input type="text" name="link_url" required value="{{ old('link_url') }}" placeholder="/explore/service/your-trek-slug">
                        <p class="pad-hint">
                            <i class="fas fa-lightbulb"></i>
                            <span>
                                {{ __('messages.ads_link_example_label') }}<br>
                                <code>/explore/service/everest-base-camp-trek-14-days</code>
                            </span>
                        </p>
                    </div>
                    <div class="pad-field">
                        <label>{{ __('messages.ads_link_target_label') }}</label>
                        <select name="link_target">
                            <option value="_blank">New Tab</option>
                            <option value="_self">Same Tab</option>
                        </select>
                    </div>
                </div>
                <div class="pad-field">
                    <label>{{ __('messages.ads_cta_label') }}</label>
                    <input type="text" name="cta_text" value="{{ old('cta_text', 'Learn More') }}" maxlength="50">
                </div>
            </div>
        </div>

        {{-- Duration --}}
        <div class="pad-card">
            <div class="pad-card-head">
                <h3><i class="fas fa-clock"></i> {{ __('messages.ads_duration_label') }}</h3>
            </div>
            <div class="pad-card-body">
                <div class="pad-duration-grid">
                    @foreach($pricing as $days => $price)
                        <label class="pad-duration">
                            <input type="radio" name="duration_days" value="{{ $days }}"
                                   {{ old('duration_days') == $days ? 'checked' : ($loop->first ? 'checked' : '') }}>
                            <div class="pad-duration-card">
                                <div class="days">{{ $days }} {{ __('messages.ads_days') }}</div>
                                <div class="price">Rs. {{ number_format($price) }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Targeting (optional) --}}
        <div class="pad-card">
            <div class="pad-card-head">
                <h3><i class="fas fa-crosshairs"></i> Targeting <span style="font-weight:600;font-size:12px;color:var(--muted);margin-left:4px">(optional)</span></h3>
            </div>
            <div class="pad-card-body">
                <div class="pad-row">
                    <div class="pad-field">
                        <label>{{ __('messages.ads_target_destination_label') }}</label>
                        <input type="text" name="target_destination" value="{{ old('target_destination') }}" placeholder="e.g. Everest Base Camp">
                    </div>
                    <div class="pad-field">
                        <label>{{ __('messages.ads_target_category_label') }}</label>
                        <select name="target_category_id">
                            <option value="">— {{ __('messages.ads_target_any') }} —</option>
                            @foreach(\App\Models\ServiceCategory::orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}" {{ old('target_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="pad-card">
            <div class="pad-card-head">
                <h3><i class="fas fa-credit-card"></i> {{ __('messages.ads_payment_section') }}</h3>
            </div>
            <div class="pad-card-body">
                <div class="pad-row">
                    <div class="pad-field">
                        <label>{{ __('messages.ads_payment_method_label') }} <span class="req">*</span></label>
                        <select name="payment_method" required>
                            <option value="">— Select —</option>
                            <option value="eSewa">eSewa</option>
                            <option value="Khalti">Khalti</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="IME Pay">IME Pay</option>
                        </select>
                    </div>
                    <div class="pad-field">
                        <label>{{ __('messages.ads_payment_ref_label') }}</label>
                        <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="Transaction ID (optional)">
                    </div>
                </div>
                <div class="pad-field">
                    <label>{{ __('messages.ads_payment_proof_label') }} <span class="req">*</span> (screenshot)</label>
                    <input type="file" name="payment_proof" accept="image/*" required>
                </div>
                <div class="pad-info">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ __('messages.ads_payment_info') }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="pad-actions">
            <a href="{{ route('provider.ads.index') }}" class="pad-btn cancel">
                {{ __('messages.cancel') }}
            </a>
            <button type="submit" class="pad-btn submit">
                <i class="fas fa-paper-plane"></i> {{ __('messages.ads_submit_btn') }}
            </button>
        </div>
    </form>
</div>
@endsection
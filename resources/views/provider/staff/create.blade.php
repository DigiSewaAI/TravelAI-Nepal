@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.add_staff_member'))
@section('header', __('messages.add_staff'))

@section('content')
<style>
  .form-wrap{max-width:680px;margin:0 auto;}
  .form-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:18px;overflow:hidden;box-shadow:0 10px 30px -15px rgba(0,0,0,.12);}
  .form-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));padding:24px 28px;color:#fff;position:relative;overflow:hidden;}
  .form-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .form-hero h1{font-size:20px;font-weight:800;margin:0 0 5px;letter-spacing:-.3px;position:relative;z-index:1;}
  .form-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .form-body{padding:26px 28px;}
  .form-group{margin-bottom:20px;}
  .form-label{display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);margin-bottom:8px;}
  .form-input{width:100%;padding:12px 14px;border:1.5px solid var(--line,#e5e7eb);border-radius:11px;font-family:inherit;font-size:14px;color:var(--text,#1f2937);background:var(--card,#fff);transition:.15s;box-sizing:border-box;}
  .form-input:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 4px var(--accent-soft,#eff6ff);}
  .form-input::placeholder{color:#b8b3ab;}
  .form-error{font-size:12px;color:var(--red,#dc2626);margin-top:6px;font-weight:600;}
  .form-hint{font-size:12px;color:var(--muted,#6b7280);margin-top:6px;font-weight:500;}
  .form-actions{display:flex;gap:10px;align-items:center;padding-top:18px;border-top:1px solid var(--line,#e5e7eb);margin-top:6px;}
  .btn-primary-grad{display:inline-flex;align-items:center;gap:7px;padding:12px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#2563eb);transition:.15s;}
  .btn-primary-grad:hover{transform:translateY(-1px);box-shadow:0 12px 24px -8px var(--accent,#2563eb);}
  .btn-cancel{padding:12px 20px;border-radius:11px;background:var(--card,#fff);border:1.5px solid var(--line,#e5e7eb);color:var(--muted,#6b7280);font-size:13px;font-weight:800;text-decoration:none;transition:.15s;}
  .btn-cancel:hover{border-color:var(--accent,#2563eb);color:var(--accent-d,#1d4ed8);}
  .alert{padding:12px 16px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;border-left:4px solid;}
  .alert-success{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);border-color:var(--green,#16a34a);}
  .alert-error{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);border-color:var(--red,#dc2626);}
  .alert a{color:inherit;font-weight:800;text-decoration:underline;}
</style>

<div class="form-wrap">
  @if(session('error'))
    <div class="alert alert-error">
      <span>⚠</span>
      <div>
        {{ session('error') }}
        @if(str_contains(session('error'), 'staff limit') || str_contains(session('error'), 'Staff limit'))
          <br><a href="{{ route('provider.subscriptions.index') }}">→ {{ __('messages.upgrade_your_plan') }}</a>
        @endif
      </div>
    </div>
  @endif
  @if(session('success'))
    <div class="alert alert-success"><span>✓</span><span>{{ session('success') }}</span></div>
  @endif

  <form method="POST" action="{{ route('provider.staff.store') }}" class="form-card">
    @csrf
    <div class="form-hero">
      <h1>👤 {{ __('messages.add_staff_member') }}</h1>
      <p>{{ __('messages.add_staff_hero_desc') }}</p>
    </div>
    <div class="form-body">
      <div class="form-group">
        <label class="form-label">{{ __('messages.full_name') }} *</label>
        <input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="{{ __('messages.full_name_placeholder') }}">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.email') }} *</label>
        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="{{ __('messages.email_placeholder') }}">
        @error('email') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.password') }} *</label>
        <input type="password" name="password" class="form-input" required placeholder="{{ __('messages.password_min_hint') }}">
        <p class="form-hint">{{ __('messages.password_min_hint') }}</p>
        @error('password') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.confirm_password') }} *</label>
        <input type="password" name="password_confirmation" class="form-input" required placeholder="{{ __('messages.confirm_password_placeholder') }}">
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.role_optional') }}</label>
        <input type="text" name="role" class="form-input" value="{{ old('role') }}" placeholder="{{ __('messages.role_placeholder') }}">
        <p class="form-hint">{{ __('messages.role_blank_hint') }}</p>
        @error('role') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary-grad">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M20 6L9 17l-5-5"/></svg>
          {{ __('messages.add_staff') }}
        </button>
        <a href="{{ route('provider.staff.index') }}" class="btn-cancel">{{ __('messages.cancel') }}</a>
      </div>
    </div>
  </form>
</div>
@endsection
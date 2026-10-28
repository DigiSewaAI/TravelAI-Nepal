@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.edit_staff_title'))
@section('header', __('messages.edit_staff_header'))

@section('content')
<style>
  .form-wrap{max-width:680px;margin:0 auto;}
  .form-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:18px;overflow:hidden;box-shadow:0 10px 30px -15px rgba(0,0,0,.12);}
  .form-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));padding:24px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;position:relative;overflow:hidden;}
  .form-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .form-hero h1{font-size:20px;font-weight:800;margin:0 0 5px;letter-spacing:-.3px;position:relative;z-index:1;}
  .form-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .hero-avatar{width:52px;height:52px;border-radius:15px;background:rgba(255,255,255,.22);border:2px solid rgba(255,255,255,.45);display:grid;place-items:center;font-size:22px;font-weight:800;flex-shrink:0;position:relative;z-index:1;backdrop-filter:blur(8px);}
  .form-body{padding:26px 28px;}
  .form-group{margin-bottom:20px;}
  .form-label{display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);margin-bottom:8px;}
  .form-input{width:100%;padding:12px 14px;border:1.5px solid var(--line,#e5e7eb);border-radius:11px;font-family:inherit;font-size:14px;color:var(--text,#1f2937);background:var(--card,#fff);transition:.15s;box-sizing:border-box;}
  .form-input:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 4px var(--accent-soft,#eff6ff);}
  .form-input::placeholder{color:#b8b3ab;}
  .readonly-box{padding:13px 15px;border:1.5px dashed var(--line,#e5e7eb);border-radius:11px;background:var(--bg,#f9fafb);}
  .readonly-box .ro-email{font-size:14px;color:var(--text,#1f2937);font-weight:700;}
  .readonly-box .ro-hint{font-size:11.5px;color:var(--muted,#6b7280);margin-top:4px;font-weight:500;}
  .form-error{font-size:12px;color:var(--red,#dc2626);margin-top:6px;font-weight:600;}
  .form-actions{display:flex;gap:10px;align-items:center;padding-top:18px;border-top:1px solid var(--line,#e5e7eb);margin-top:6px;}
  .btn-primary-grad{display:inline-flex;align-items:center;gap:7px;padding:12px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#2563eb);transition:.15s;}
  .btn-primary-grad:hover{transform:translateY(-1px);box-shadow:0 12px 24px -8px var(--accent,#2563eb);}
  .btn-cancel{padding:12px 20px;border-radius:11px;background:var(--card,#fff);border:1.5px solid var(--line,#e5e7eb);color:var(--muted,#6b7280);font-size:13px;font-weight:800;text-decoration:none;transition:.15s;}
  .btn-cancel:hover{border-color:var(--accent,#2563eb);color:var(--accent-d,#1d4ed8);}
  .alert{padding:12px 16px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;border-left:4px solid;}
  .alert-success{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);border-color:var(--green,#16a34a);}
  .alert-error{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);border-color:var(--red,#dc2626);}
  .alert a{color:inherit;font-weight:800;text-decoration:underline;}
  .perm-section{margin-top:6px;padding-top:22px;border-top:2px dashed var(--line,#e5e7eb);}
  .perm-title{font-size:15px;font-weight:800;color:var(--text,#1f2937);margin:0 0 4px;display:flex;align-items:center;gap:9px;}
  .perm-subtitle{font-size:12.5px;color:var(--muted,#6b7280);margin:0 0 16px;font-weight:500;}
  .perm-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  @media(max-width:600px){.perm-grid{grid-template-columns:1fr;}}
  .perm-option{display:flex;align-items:center;gap:11px;padding:13px 15px;border:1.5px solid var(--line,#e5e7eb);border-radius:11px;cursor:pointer;transition:.15s;background:var(--card,#fff);}
  .perm-option:hover{border-color:var(--accent,#2563eb);background:var(--accent-soft,#eff6ff);}
  .perm-option.all{grid-column:1/-1;border-color:var(--accent,#2563eb);background:var(--accent-soft,#eff6ff);}
  .perm-option.all:hover{background:var(--accent,#2563eb);border-color:var(--accent-d,#1d4ed8);}
  .perm-option.all:hover .perm-label{color:#fff;}
  .perm-option input[type=checkbox]{width:18px;height:18px;cursor:pointer;accent-color:var(--accent,#2563eb);flex-shrink:0;}
  .perm-label{font-size:13px;font-weight:700;color:var(--text,#1f2937);}
  .perm-option.all .perm-label{font-size:13.5px;color:var(--accent-d,#1d4ed8);font-weight:800;}
</style>

<div class="form-wrap">
  @if(session('error'))
    <div class="alert alert-error">
      <span>⚠</span>
      <div>
        {{ session('error') }}
        @if(str_contains(session('error'), 'staff limit') || str_contains(session('error'), 'Staff limit'))
          <br><a href="{{ route('provider.subscriptions.index') }}">→ {{ __('messages.upgrade_plan') }}</a>
        @endif
      </div>
    </div>
  @endif
  @if(session('success'))
    <div class="alert alert-success"><span>✓</span><span>{{ session('success') }}</span></div>
  @endif

  <form method="POST" action="{{ route('provider.staff.update', $staff) }}" class="form-card">
    @csrf
    @method('PUT')
    <div class="form-hero">
      <div>
        <h1>{{ __('messages.edit_staff_title') }}</h1>
        <p>{{ __('messages.update_staff_details_desc') }}</p>
      </div>
      <div class="hero-avatar">{{ strtoupper(substr($staff->user->name, 0, 1)) }}</div>
    </div>
    <div class="form-body">
      <div class="form-group">
        <label class="form-label">{{ __('messages.full_name') }} *</label>
        <input type="text" name="name" class="form-input" value="{{ old('name', $staff->user->name) }}" required>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.email') }}</label>
        <div class="readonly-box">
          <div class="ro-email">{{ $staff->user->email }}</div>
          <div class="ro-hint">{{ __('messages.email_cannot_be_changed') }}</div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('messages.role_optional') }}</label>
        <input type="text" name="role" class="form-input" value="{{ old('role', $staff->role) }}" placeholder="{{ __('messages.role_placeholder') }}">
        @error('role') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      {{-- STAFF-PERMISSIONS-02: Access Control --}}
      @php
        $currentPermissions = $staff->permissions ?? [];
        $availablePermissions = [
            'view_bookings'   => __('messages.view_bookings'),
            'manage_checkins' => __('messages.manage_checkins'),
            'view_payments'   => __('messages.view_payments'),
        ];
        $hasAllAccess = in_array('all', $currentPermissions);
      @endphp
      <div class="perm-section">
        <h3 class="perm-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--accent-d,#1d4ed8);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
          {{ __('messages.staff_access_permissions') }}
        </h3>
        <p class="perm-subtitle">{{ __('messages.select_modules') }}</p>

        <div class="perm-grid">
          <label class="perm-option all">
            <input type="checkbox" name="permissions[]" value="all" id="all_access" {{ $hasAllAccess ? 'checked' : '' }}>
            <span class="perm-label">{{ __('messages.full_access') }}</span>
          </label>

          @foreach($availablePermissions as $key => $label)
            <label class="perm-option">
              <input type="checkbox" name="permissions[]" value="{{ $key }}" class="permission-item" {{ (in_array($key, $currentPermissions) && !$hasAllAccess) ? 'checked' : '' }}>
              <span class="perm-label">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary-grad">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M20 6L9 17l-5-5"/></svg>
          {{ __('messages.update_staff') }}
        </button>
        <a href="{{ route('provider.staff.index') }}" class="btn-cancel">{{ __('messages.cancel') }}</a>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allAccess = document.getElementById('all_access');
        const items = document.querySelectorAll('.permission-item');
        if(allAccess) {
            allAccess.addEventListener('change', function() {
                if(this.checked) { items.forEach(item => item.checked = false); }
            });
        }
        items.forEach(item => {
            item.addEventListener('change', function() {
                if(this.checked && allAccess) { allAccess.checked = false; }
            });
        });
    });
</script>
@endpush
@endsection
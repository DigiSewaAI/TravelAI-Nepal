@extends($provider_layout ?? 'layouts.provider')

@section('title', 'Team Management')
@section('header', 'Team Members')

@section('content')
<style>
  .staff-wrap{max-width:1200px;margin:0 auto;}
  .staff-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));border-radius:18px;padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:18px;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);margin-bottom:22px;position:relative;overflow:hidden;flex-wrap:wrap;}
  .staff-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .staff-hero-text{position:relative;z-index:1;}
  .staff-hero h1{font-size:22px;font-weight:800;letter-spacing:-.4px;margin:0 0 6px;}
  .staff-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;}
  .staff-hero-actions{position:relative;z-index:1;display:flex;gap:10px;flex-wrap:wrap;}
  .btn-add{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.96);color:var(--accent-d,#1d4ed8);padding:11px 18px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.15);transition:.2s;}
  .btn-add:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.22);}
  .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:768px){.stats-row{grid-template-columns:1fr;}}
  .stat-box{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;transition:.2s;}
  .stat-box:hover{box-shadow:0 8px 20px -10px rgba(0,0,0,.08);}
  .stat-icon{width:42px;height:42px;border-radius:12px;background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);display:grid;place-items:center;flex-shrink:0;}
  .stat-icon svg{width:20px;height:20px;stroke-width:2.2;}
  .stat-label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#6b7280);font-weight:800;}
  .stat-value{font-size:20px;font-weight:800;color:var(--text,#1f2937);margin-top:2px;}
  .alert{padding:12px 16px;border-radius:11px;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;border-left:4px solid;}
  .alert-success{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);border-color:var(--green,#16a34a);}
  .alert-error{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);border-color:var(--red,#dc2626);}
  .alert a{color:inherit;font-weight:800;text-decoration:underline;}
  .team-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:16px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.02);}
  .team-head{padding:18px 22px;border-bottom:1px solid var(--line,#e5e7eb);display:flex;justify-content:space-between;align-items:center;}
  .team-head h2{font-size:15px;font-weight:800;color:var(--text,#1f2937);margin:0;}
  .member-row{display:flex;align-items:center;gap:14px;padding:16px 22px;border-bottom:1px solid var(--line,#e5e7eb);transition:.15s;}
  .member-row:last-child{border-bottom:none;}
  .member-row:hover{background:#f9fafb;}
  .avatar-circle{width:44px;height:44px;border-radius:13px;background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);display:grid;place-items:center;font-weight:800;font-size:16px;flex-shrink:0;}
  .member-info{flex:1;min-width:0;}
  .member-name{font-size:14px;font-weight:800;color:var(--text,#1f2937);margin:0 0 2px;}
  .member-email{font-size:12.5px;color:var(--muted,#6b7280);font-weight:600;word-break:break-all;}
  .role-pill{padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;text-transform:capitalize;white-space:nowrap;}
  .role-staff{background:var(--blue-soft,#dbeafe);color:var(--blue,#2563eb);}
  .role-manager{background:var(--purple-soft,#ede9fe);color:var(--purple,#8b5cf6);}
  .role-other{background:var(--line,#e5e7eb);color:var(--muted,#6b7280);}
  .member-actions{display:flex;gap:8px;align-items:center;flex-shrink:0;}
  .btn-icon{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:800;text-decoration:none;border:none;cursor:pointer;font-family:inherit;transition:.15s;}
  .btn-edit{background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);}
  .btn-edit:hover{background:var(--accent,#2563eb);color:#fff;}
  .btn-del{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);}
  .btn-del:hover{background:var(--red,#dc2626);color:#fff;}
  .empty-state{padding:48px 20px;text-align:center;color:var(--muted,#6b7280);}
  .empty-state svg{width:56px;height:56px;margin:0 auto 12px;opacity:.4;stroke-width:1.5;}
  .empty-state h3{font-size:15px;font-weight:800;color:var(--text,#1f2937);margin:0 0 6px;}
  .empty-state p{font-size:13px;margin:0;}
  @media(max-width:640px){.member-row{flex-wrap:wrap;}.member-actions{width:100%;justify-content:flex-end;}.role-pill{display:none;}}
</style>

<div class="staff-wrap">
  <div class="staff-hero">
    <div class="staff-hero-text">
      <h1>👥 Team Members</h1>
      <p>Manage your staff, roles, and access permissions</p>
    </div>
    <div class="staff-hero-actions">
      <a href="{{ route('provider.staff.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M12 5v14M5 12h14"/></svg>
        Add Staff
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success"><span>✓</span><span>{{ session('success') }}</span></div>
  @endif
  @if(session('error'))
    <div class="alert alert-error">
      <span>⚠</span>
      <div>
        {{ session('error') }}
        @if(str_contains(session('error'), 'staff limit') || str_contains(session('error'), 'Staff limit'))
          <br><a href="{{ route('provider.subscriptions.index') }}">→ Upgrade Your Plan</a>
        @endif
      </div>
    </div>
  @endif

  @php
    $isLimitReached = $maxStaff != -1 && $maxStaff != null && $staff->count() >= $maxStaff;
  @endphp

  <div class="stats-row">
    <div class="stat-box">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="stat-label">Total Staff</div>
        <div class="stat-value">{{ $staff->count() }}</div>
      </div>
    </div>
    <div class="stat-box">
      <div class="stat-icon" style="background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div>
        <div class="stat-label">Max Allowed</div>
        <div class="stat-value">{{ ($maxStaff == -1 || $maxStaff == null) ? '∞ Unlimited' : $maxStaff }}</div>
      </div>
    </div>
    <div class="stat-box">
      <div class="stat-icon" style="background:{{ $isLimitReached ? 'var(--red-soft,#fee2e2)' : 'var(--blue-soft,#dbeafe)' }};color:{{ $isLimitReached ? 'var(--red,#dc2626)' : 'var(--blue,#2563eb)' }};">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      </div>
      <div>
        <div class="stat-label">Slots Left</div>
        <div class="stat-value">
          @if($maxStaff == -1 || $maxStaff == null)
            ∞
          @else
            {{ max(0, $maxStaff - $staff->count()) }}
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="team-card">
    <div class="team-head">
      <h2>All Members ({{ $staff->count() }})</h2>
      @if($isLimitReached)
        <a href="{{ route('provider.subscriptions.index') }}" style="font-size:12px;font-weight:800;color:var(--accent-d,#1d4ed8);text-decoration:none;">Upgrade Plan →</a>
      @endif
    </div>
    @forelse($staff as $member)
      <div class="member-row">
        <div class="avatar-circle">{{ strtoupper(substr($member->user->name, 0, 1)) }}</div>
        <div class="member-info">
          <div class="member-name">{{ $member->user->name }}</div>
          <div class="member-email">{{ $member->user->email }}</div>
        </div>
        @php $roleClass = $member->role === 'manager' ? 'role-manager' : ($member->role === 'staff' ? 'role-staff' : 'role-other'); @endphp
        <span class="role-pill {{ $roleClass }}">{{ ucfirst($member->role ?? 'Staff') }}</span>
        <div class="member-actions">
          <a href="{{ route('provider.staff.edit', $member) }}" class="btn-icon btn-edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </a>
          <form action="{{ route('provider.staff.destroy', $member) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Remove this staff member?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-icon btn-del">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
              Remove
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
        <h3>No staff members yet</h3>
        <p>Click "Add Staff" above to invite your first team member.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
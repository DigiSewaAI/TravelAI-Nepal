@extends('layouts.provider-rescue')

@section('title', 'Staff & Crew')
@section('page-title', 'Staff & Crew')
@section('page-sub', 'Pilots, medics & coordinators')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Staff & Crew</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Pilots, medics & coordinators</p>
    </div>
    <button onclick="document.getElementById('staffModal').classList.add('show')" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Add Staff
    </button>
</div>

<div class="grid-3">
    @forelse(($rescueProvider->staff_members ?? []) as $staff)
        <div class="person-card">
            <div class="person-top">
                <div class="person-avatar" style="background:linear-gradient(135deg,#0d9488,#2dd4bf)">
                    {{ strtoupper(substr($staff['name'] ?? 'NA', 0, 2)) }}
                </div>
                <div>
                    <div class="person-name">{{ $staff['name'] ?? 'Unknown' }}</div>
                    <div class="person-role">{{ $staff['role'] ?? '—' }}</div>
                </div>
            </div>
            <div class="person-meta">
                @if(!empty($staff['license']))
                    <span><i data-lucide="award"></i> {{ $staff['license'] }}</span>
                @endif
                @if(!empty($staff['hours']))
                    <span><i data-lucide="clock"></i> {{ $staff['hours'] }}</span>
                @endif
                @if(!empty($staff['phone']))
                    <span><i data-lucide="phone"></i> {{ $staff['phone'] }}</span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted);">
            <i data-lucide="users" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No staff members added yet.</div>
        </div>
    @endforelse
</div>

{{-- Add Staff Modal --}}
<div class="modal-overlay" id="staffModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="users"></i></div>
            <div>
                <div class="modal-title">Add Staff Member</div>
                <div class="modal-desc">Enter details to add a new crew member.</div>
            </div>
        </div>
        <form action="{{ route('provider.rescue.staff.store') }}" method="POST">
            @csrf
            <label>Name <span class="req">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Hari Thapa">

            <label>Role <span class="req">*</span></label>
            <select name="role" required>
                <option value="Pilot">Pilot</option>
                <option value="Medic">Medic</option>
                <option value="Coordinator">Coordinator</option>
            </select>

            <label>License / Certification</label>
            <input type="text" name="license" placeholder="e.g. CAAN #PL-4821">

            <label>Flight Hours / Experience</label>
            <input type="text" name="hours" placeholder="e.g. 1,500 hours">

            <label>Contact Phone <span class="req">*</span></label>
            <input type="tel" name="phone" required placeholder="e.g. +977 9841234567">

            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('staffModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Member</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('rescue-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
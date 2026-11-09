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
    <button onclick="openAddStaff()" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Add Staff
    </button>
</div>

<div class="grid-3">
    @forelse(($rescueProvider->staff_members ?? []) as $index => $staff)
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
            <div style="display:flex;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <button type="button"
                        onclick='openEditStaff({{ $index }}, @json($staff))'
                        class="btn-link" style="color:var(--info)">
                    <i data-lucide="edit-2" style="width:12px;height:12px"></i> Edit
                </button>
                <form action="{{ route('provider.rescue.staff.destroy', $index) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Remove {{ $staff['name'] ?? 'this staff member' }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-link" style="color:var(--danger)">
                        <i data-lucide="trash-2" style="width:12px;height:12px"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted);">
            <i data-lucide="users" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No staff members added yet.</div>
        </div>
    @endforelse
</div>

{{-- Add/Edit Staff Modal --}}
<div class="modal-overlay" id="staffModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="users"></i></div>
            <div>
                <div class="modal-title" id="staffModalTitle">Add Staff Member</div>
                <div class="modal-desc">Enter details for this crew member.</div>
            </div>
        </div>
        <form id="staffForm" action="{{ route('provider.rescue.staff.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="staffMethod" value="POST">

            <label>Name <span class="req">*</span></label>
            <input type="text" name="name" id="staffName" required placeholder="e.g. Hari Thapa">

            <label>Role <span class="req">*</span></label>
            <select name="role" id="staffRole" required>
                <option value="Pilot">Pilot</option>
                <option value="Medic">Medic</option>
                <option value="Coordinator">Coordinator</option>
            </select>

            <label>License / Certification</label>
            <input type="text" name="license" id="staffLicense" placeholder="e.g. CAAN #PL-4821">

            <label>Flight Hours / Experience</label>
            <input type="text" name="hours" id="staffHours" placeholder="e.g. 1,500 hours">

            <label>Contact Phone <span class="req">*</span></label>
            <input type="tel" name="phone" id="staffPhone" required placeholder="e.g. +977 9841234567">

            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('staffModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary" id="staffSaveBtn">Save Member</button>
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

function openAddStaff() {
    const form = document.getElementById('staffForm');
    form.reset();
    document.getElementById('staffMethod').value = 'POST';
    form.action = "{{ route('provider.rescue.staff.store') }}";
    document.getElementById('staffModalTitle').textContent = 'Add Staff Member';
    document.getElementById('staffSaveBtn').textContent = 'Save Member';
    document.getElementById('staffModal').classList.add('show');
}

function openEditStaff(index, staff) {
    const form = document.getElementById('staffForm');
    document.getElementById('staffMethod').value = 'PUT';
    form.action = '/provider/rescue/staff/' + index;
    document.getElementById('staffModalTitle').textContent = 'Edit Staff Member';
    document.getElementById('staffSaveBtn').textContent = 'Update Member';

    document.getElementById('staffName').value    = staff.name || '';
    document.getElementById('staffRole').value    = staff.role || 'Pilot';
    document.getElementById('staffLicense').value = staff.license || '';
    document.getElementById('staffHours').value   = staff.hours || '';
    document.getElementById('staffPhone').value   = staff.phone || '';

    document.getElementById('staffModal').classList.add('show');
}
</script>
@endpush
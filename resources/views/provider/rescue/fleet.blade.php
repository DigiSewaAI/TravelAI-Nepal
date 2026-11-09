@extends('layouts.provider-rescue')

@section('title', 'Fleet Management')
@section('page-title', 'Fleet Management')
@section('page-sub', 'Helicopters, ground ambulances & medical teams')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Fleet Management</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Helicopters, ground ambulances & medical teams</p>
    </div>
    <button onclick="openFleetModal()" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Add Vehicle
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card stat-teal">
        <div class="stat-icon"><i data-lucide="plane"></i></div>
        <div class="stat-body">
            <span class="stat-label">Total Fleet</span>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon"><i data-lucide="check"></i></div>
        <div class="stat-body">
            <span class="stat-label">Available</span>
            <div class="stat-value">{{ $stats['available'] }}</div>
        </div>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-icon"><i data-lucide="navigation"></i></div>
        <div class="stat-body">
            <span class="stat-label">On Mission</span>
            <div class="stat-value">{{ $stats['on_mission'] }}</div>
        </div>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon"><i data-lucide="wrench"></i></div>
        <div class="stat-body">
            <span class="stat-label">Maintenance</span>
            <div class="stat-value">{{ $stats['maintenance'] }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="plane"></i>
            <h2>Registered Vehicles</h2>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Registration</th>
                    <th>Type</th>
                    <th>Model</th>
                    <th>Max Alt</th>
                    <th>Pilot</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fleets as $fleet)
                    <tr>
                        <td class="cell-strong cell-mono">{{ $fleet->registration_number }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $fleet->type)) }}</td>
                        <td class="cell-muted">{{ $fleet->model ?? '—' }}</td>
                        <td class="cell-muted">{{ $fleet->max_altitude_ft ? number_format($fleet->max_altitude_ft) . ' ft' : '—' }}</td>
                        <td>{{ $fleet->pilot_name ?? '—' }}</td>
                        <td>
                            @php
                                $badge = match($fleet->status) {
                                    'available' => 'badge-green',
                                    'on_mission' => 'badge-blue',
                                    'maintenance' => 'badge-medium',
                                    'offline' => 'badge-gray',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $fleet->status)) }}</span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button onclick="editFleet({{ $fleet }})" class="btn-link" style="color:var(--info)">Edit</button>
                                <form action="{{ route('provider.rescue.fleet.destroy', $fleet) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link" style="color:var(--danger)">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            <i data-lucide="plane" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
                            <div>No vehicles registered yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal (Add/Edit) --}}
<div class="modal-overlay" id="fleetModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="plane"></i></div>
            <div>
                <div class="modal-title" id="modalTitle">Add Vehicle</div>
                <div class="modal-desc">Enter details to register a new vehicle.</div>
            </div>
        </div>
        <form id="fleetForm" method="POST" action="{{ route('provider.rescue.fleet.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <label>Registration Number <span class="req">*</span></label>
            <input type="text" name="registration_number" id="f_reg" required placeholder="e.g. 9N-AJH">

            <label>Vehicle Type <span class="req">*</span></label>
            <select name="type" id="f_type" required>
                <option value="helicopter">Helicopter</option>
                <option value="ground_ambulance">Ground Ambulance</option>
                <option value="medical_team">Medical Team</option>
            </select>

            <label>Model</label>
            <input type="text" name="model" id="f_model" placeholder="e.g. H125 Airbus">

            <label>Max Altitude (ft)</label>
            <input type="number" name="max_altitude_ft" id="f_alt" placeholder="e.g. 23000">

            <label>Pilot Name</label>
            <input type="text" name="pilot_name" id="f_pilot" placeholder="e.g. Capt. R. Sherpa">

            <label>Status <span class="req">*</span></label>
            <select name="status" id="f_status" required>
                <option value="available">Available</option>
                <option value="on_mission">On Mission</option>
                <option value="maintenance">Maintenance</option>
                <option value="offline">Offline</option>
            </select>

            <div class="modal-actions">
                <button type="button" onclick="closeFleetModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Vehicle</button>
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

    function openFleetModal() {
        document.getElementById('fleetForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('fleetForm').action = "{{ route('provider.rescue.fleet.store') }}";
        document.getElementById('modalTitle').innerText = "Add Vehicle";
        document.getElementById('fleetModal').classList.add('show');
    }

    function editFleet(fleet) {
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('fleetForm').action = `/provider/rescue/fleet/${fleet.id}`;
        document.getElementById('modalTitle').innerText = "Edit Vehicle";
        
        document.getElementById('f_reg').value = fleet.registration_number;
        document.getElementById('f_type').value = fleet.type;
        document.getElementById('f_model').value = fleet.model || '';
        document.getElementById('f_alt').value = fleet.max_altitude_ft || '';
        document.getElementById('f_pilot').value = fleet.pilot_name || '';
        document.getElementById('f_status').value = fleet.status;
        
        document.getElementById('fleetModal').classList.add('show');
    }

    function closeFleetModal() {
        document.getElementById('fleetModal').classList.remove('show');
    }
</script>
@endpush
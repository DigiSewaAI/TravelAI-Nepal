@extends('layouts.provider-transport')
@section('title', __('messages.fleet_management'))
@section('page-title', __('messages.fleet_management'))
@section('page-subtitle', __('messages.manage_fleet_subtitle'))

@push('transport-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9aa9bc;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);white-space:nowrap;}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f1f6fc;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafcff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
  .veh-icon{font-size:20px; margin-right:8px;}
  .btn-back{background:var(--sky); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700; white-space:nowrap; transition:.18s; display:inline-flex; align-items:center; gap:6px}
  .btn-back:hover{filter:brightness(1.08)}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.fleet_management') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_fleet_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.transport.dashboard') }}" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;stroke-width:2.5"><path d="m15 18-6-6 6-6"/></svg>
            {{ __('messages.back_to_dashboard') }}
        </a>
    </div>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.vehicle_number') }}</th>
                    <th>{{ __('messages.vehicle_type_col') }}</th>
                    <th>{{ __('messages.capacity_col') }}</th>
                    <th>{{ __('messages.driver_col') }}</th>
                    <th>{{ __('messages.route_col') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $v)
                <tr>
                    <td><span style="font-weight:800; font-family:monospace; color:var(--sky-d);">{{ $v->vehicle_number }}</span></td>
                    <td>
                        @php
                            $type = strtolower($v->vehicle_type);
                            $icon = str_contains($type, 'bus')  ? '🚌'
                                  : (str_contains($type, 'hiace') || str_contains($type, 'van') || str_contains($type, 'micro') ? '🚐'
                                  : (str_contains($type, 'suv') || str_contains($type, 'jeep') ? '🚙'
                                  : '🚗'));
                        @endphp
                        <span class="veh-icon">{{ $icon }}</span>{{ $v->vehicle_type }}
                    </td>
                    <td>{{ $v->capacity }} {{ __('messages.seats') }}</td>
                    <td>{{ $v->driver_name ?? '—' }}</td>
                    <td>{{ $v->route_name ?? '—' }}</td>
                    <td>
                        @php
                            $statusKey = match($v->status) {
                                'available' => 'available',
                                'on-route' => 'on_route',
                                'maintenance' => 'maintenance',
                                'charging' => 'charging',
                                default => 'available'
                            };
                            $statusColor = match($v->status) {
                                'available' => 'var(--green-soft); color:var(--green)',
                                'on-route' => 'var(--sky-soft); color:var(--sky-d)',
                                'maintenance' => 'var(--red-soft); color:var(--red)',
                                'charging' => 'var(--purple-soft); color:var(--purple)',
                                default => 'var(--line); color:var(--muted)'
                            };
                        @endphp
                        <span class="pill" style="background:{{ $statusColor }};">
                            {{ __('messages.' . $statusKey) }}
                        </span>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" onclick="openEditVehicleModal({{ $v->id }}, '{{ addslashes($v->vehicle_number) }}', '{{ addslashes($v->vehicle_type) }}', {{ $v->capacity }}, '{{ addslashes($v->driver_name ?? '') }}', '{{ addslashes($v->route_name ?? '') }}', '{{ $v->status }}')" style="padding:6px 10px;border-radius:7px;border:none;background:var(--sky-soft);color:var(--sky-d);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;margin-right:4px;"><i class="fas fa-edit"></i> {{ __('messages.edit') }}</button>
                        <form action="{{ route('provider.transport.vehicles.destroy', $v) }}" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:6px 10px;border-radius:7px;border:none;background:var(--red-soft);color:var(--red);font-size:11px;font-weight:800;cursor:pointer;font-family:inherit;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_vehicles_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Vehicle Modal --}}
<div id="editVehicleModal" style="display:none;position:fixed;inset:0;background:rgba(10,28,51,.65);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
  <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:460px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:18px;font-weight:800;color:var(--text);margin:0">{{ __('messages.edit_vehicle') }}</h3>
      <button type="button" onclick="closeEditVehicleModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
    </div>
    <form id="editVehicleForm" method="POST">
      @csrf @method('PUT')
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.vehicle_number') }}</label>
        <input type="text" name="vehicle_number" id="ev_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.vehicle_type_col') }}</label>
          <select name="vehicle_type" id="ev_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
            <option value="Bus">Bus</option><option value="Deluxe Bus">Deluxe Bus</option>
            <option value="Van">Van</option><option value="Hiace">Hiace</option>
            <option value="Car">Car</option><option value="SUV">SUV</option>
            <option value="Jeep">Jeep</option><option value="Bike">Bike</option>
          </select>
        </div>
        <div>
          <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.capacity_col') }}</label>
          <input type="number" name="capacity" id="ev_capacity" min="1" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.driver_col') }}</label>
        <input type="text" name="driver_name" id="ev_driver" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.route_col') }}</label>
        <input type="text" name="route_name" id="ev_route" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.status_col') }}</label>
        <select name="status" id="ev_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
          <option value="available">{{ __('messages.available') }}</option>
          <option value="on-route">{{ __('messages.on_route') }}</option>
          <option value="maintenance">{{ __('messages.maintenance') }}</option>
          <option value="charging">{{ __('messages.charging') }}</option>
        </select>
      </div>
      <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--sky,#0ea5e9),var(--sky-d,#0284c7));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.update_vehicle') }}</button>
    </form>
  </div>
</div>

<script>
function openEditVehicleModal(id, number, type, capacity, driver, route, status) {
    document.getElementById('editVehicleForm').action = '/provider/transport/vehicles/' + id;
    document.getElementById('ev_number').value = number;
    document.getElementById('ev_type').value = type;
    document.getElementById('ev_capacity').value = capacity;
    document.getElementById('ev_driver').value = driver;
    document.getElementById('ev_route').value = route;
    document.getElementById('ev_status').value = status;
    document.getElementById('editVehicleModal').style.display = 'flex';
}
function closeEditVehicleModal() { document.getElementById('editVehicleModal').style.display = 'none'; }
document.getElementById('editVehicleModal').addEventListener('click', function(e) { if (e.target === this) closeEditVehicleModal(); });
</script>
@endsection
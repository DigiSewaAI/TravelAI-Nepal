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
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_vehicles_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
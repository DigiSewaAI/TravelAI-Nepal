@extends('layouts.provider-transport')
@section('title', __('messages.route_management'))
@section('page-title', __('messages.route_management'))
@section('page-subtitle', __('messages.manage_routes_subtitle'))

@push('transport-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9aa9bc;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f1f6fc;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafcff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.route_management') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_routes_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.transport.dashboard') }}" class="btn-primary" style="background:var(--sky); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Back to Dashboard</a>
    </div>
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.route_col') }}</th>
                    <th>{{ __('messages.assigned_drivers') }}</th>
                    <th>{{ __('messages.vehicle_number') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $routeName => $routeVehicles)
                <tr>
                    <td><strong style="color:var(--sky-d);">{{ $routeName }}</strong></td>
                    <td>{{ $routeVehicles->pluck('driver_name')->filter()->unique()->join(', ') ?: '—' }}</td>
                    <td>{{ $routeVehicles->pluck('vehicle_number')->join(', ') }}</td>
                    <td>
                        <span class="pill" style="background:var(--green-soft); color:var(--green);">
                            {{ __('messages.active') ?? 'Active' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_routes_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
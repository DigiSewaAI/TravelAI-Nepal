@extends('layouts.provider-transport')
@section('title', __('messages.driver_management'))
@section('page-title', __('messages.driver_management'))
@section('page-subtitle', __('messages.manage_drivers_subtitle'))

@push('transport-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9aa9bc;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f1f6fc;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafcff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
  .c-av{width:34px; height:34px; border-radius:50%; background:var(--sky-soft); color:var(--sky-d); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; margin-right:10px;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.driver_management') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_drivers_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.transport.dashboard') }}" class="btn-primary" style="background:var(--sky); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.driver_col') }}</th>
                    <th>{{ __('messages.assigned_vehicle') }}</th>
                    <th>{{ __('messages.route_col') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $d)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center;">
                            <div class="c-av">{{ substr($d->driver_name, 0, 1) }}</div>
                            <strong>{{ $d->driver_name }}</strong>
                        </div>
                    </td>
                    <td>{{ $d->vehicle_number }} ({{ $d->vehicle_type }})</td>
                    <td>{{ $d->route_name ?? '—' }}</td>
                    <td>
                        <span class="pill" style="background:var(--sky-soft); color:var(--sky-d);">
                            {{ __('messages.' . ($d->status === 'on-route' ? 'on_route' : $d->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_drivers_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
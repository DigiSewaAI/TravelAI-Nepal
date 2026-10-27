@extends('layouts.provider-transport')
@section('title', __('messages.driver_management'))
@section('page-title', __('messages.driver_management'))
@section('page-subtitle', __('messages.manage_drivers_subtitle'))

@push('transport-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:500px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa9bc;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f1f6fc;white-space:nowrap}
  .tbl tbody tr:hover{background:#fafcff}
  .empty{text-align:center;padding:44px 20px;font-size:13px;font-weight:600;color:var(--muted)}
  .empty div{font-size:38px;margin-bottom:10px;opacity:.5}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>{{ __('messages.driver_col') }}</th>
          <th>{{ __('messages.assigned_vehicle') }}</th>
          <th>{{ __('messages.route') }}</th>
          <th>{{ __('messages.status') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($drivers as $d)
          <tr>
            <td><strong>{{ $d->driver_name }}</strong></td>
            <td>{{ $d->vehicle_number }} ({{ $d->vehicle_type }})</td>
            <td>{{ $d->route_name ?? '—' }}</td>
            <td>
                <span class="pill" style="background:var(--sky-soft); color:var(--sky-d);">
                    {{ __('messages.' . ($d->status === 'on-route' ? 'on_route' : $d->status)) }}
                </span>
            </td>
          </tr>
        @empty
          <tr><td colspan="4"><div class="empty"><div>👤</div>{{ __('messages.no_drivers_found') }}</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
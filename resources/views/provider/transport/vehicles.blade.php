@extends('layouts.provider-transport')
@section('title', __('messages.fleet_management'))
@section('page-title', __('messages.fleet_management'))
@section('page-subtitle', __('messages.manage_fleet_subtitle'))

@section('content')
<div class="card" style="margin-bottom:20px;">
    <div class="card-head">
        <div><h3>{{ __('messages.fleet_management') }}</h3></div>
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
                    <td><strong>{{ $v->vehicle_number }}</strong></td>
                    <td>{{ $v->vehicle_type }}</td>
                    <td>{{ $v->capacity }}</td>
                    <td>{{ $v->driver_name ?? 'N/A' }}</td>
                    <td>{{ $v->route_name ?? 'N/A' }}</td>
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
                                'available' => '#10b981',
                                'on-route' => '#0ea5e9',
                                'maintenance' => '#ef4444',
                                'charging' => '#8b5cf6',
                                default => '#6b7f96'
                            };
                        @endphp
                        <span class="pill" style="background:{{ $statusColor }}15; color:{{ $statusColor }}; border:1px solid {{ $statusColor }}30;">
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
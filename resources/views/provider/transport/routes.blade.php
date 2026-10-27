@extends('layouts.provider-transport')
@section('title', __('messages.route_management'))
@section('page-title', __('messages.route_management'))
@section('page-subtitle', __('messages.manage_routes_subtitle'))

@section('content')
<div class="card" style="margin-bottom:20px;">
    <div class="card-head">
        <div><h3>{{ __('messages.route_management') }}</h3></div>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.route_col') }}</th>
                    <th>{{ __('messages.assigned_drivers') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $routeName => $routeVehicles)
                <tr>
                    <td><strong>{{ $routeName }}</strong></td>
                    <td>
                        {{ $routeVehicles->pluck('driver_name')->filter()->unique()->join(', ') ?: 'Unassigned' }}
                    </td>
                    <td>
                        <span class="pill" style="background:var(--green-soft); color:var(--green);">
                            {{ __('messages.active') ?? 'Active' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_routes_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
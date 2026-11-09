@extends('layouts.admin')

@section('title', 'Active Rescue Missions')
@section('page-title', 'Active Missions')

@section('content')

<style>
    .am-wrap { max-width: 1400px; margin: 0 auto; }
    .am-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .am-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
    .am-header p { font-size: 13px; color: #64748b; margin: 4px 0 0; }
    
    .am-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; }
    .am-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .am-table th { text-align: left; padding: 14px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .am-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .am-table tbody tr:last-child td { border-bottom: none; }
    .am-table tbody tr:hover { background: #f8fafc; }
    
    .am-badge { display: inline-flex; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .bg-dispatched { background: #fef3c7; color: #b45309; }
    .bg-accepted { background: #dbeafe; color: #1d4ed8; }
    .bg-enroute { background: #e0e7ff; color: #3730a3; }
    .bg-onscene { background: #ccfbf1; color: #0f766e; }
    .bg-extracting { background: #fce7f3; color: #9d174d; }
    
    .am-empty { padding: 60px 20px; text-align: center; color: #94a3b8; font-size: 14px; }
</style>

<div class="am-wrap">
    <div class="am-header">
        <div>
            <h2>Active Rescue Missions</h2>
            <p>Live view of all ongoing rescue operations system-wide.</p>
        </div>
    </div>

    <div class="am-card">
        <div style="overflow-x: auto;">
            <table class="am-table">
                <thead>
                    <tr>
                        <th>Mission ID</th>
                        <th>Traveler</th>
                        <th>Rescue Provider</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missions as $mission)
                        <tr>
                            <td style="font-family: monospace; font-weight: 800; color: #991b1b;">
                                {{ $mission->mission_number }}
                            </td>
                            <td style="font-weight: 600;">
                                {{ $mission->sos->traveler->name ?? 'Unknown' }}
                            </td>
                            <td>
                                {{ $mission->provider->name ?? 'Unknown Provider' }}
                            </td>
                            <td style="color: #64748b;">
                                {{ $mission->fleet->registration_number ?? 'Unassigned' }}
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($mission->status) {
                                        'dispatched' => 'bg-dispatched',
                                        'accepted' => 'bg-accepted',
                                        'en_route' => 'bg-enroute',
                                        'on_scene' => 'bg-onscene',
                                        'extracting' => 'bg-extracting',
                                        default => 'bg-dispatched',
                                    };
                                @endphp
                                <span class="am-badge {{ $badgeClass }}">{{ $mission->status }}</span>
                            </td>
                            <td style="color: #64748b; font-size: 12px;">
                                {{ $mission->created_at->format('M d, H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.rescue.sos.show', $mission->sos_alert_id) }}" style="color: #0d9488; font-weight: 700; text-decoration: none;">View SOS</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="am-empty">
                                No active rescue missions at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
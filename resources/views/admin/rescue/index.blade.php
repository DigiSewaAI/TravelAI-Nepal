@extends('layouts.admin')

@section('title', 'Rescue Command Center')
@section('page-title', 'Command Center')

@section('content')

<style>
    .rescue-admin-wrap { max-width: 1400px; margin: 0 auto; }
    .rescue-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px; }
    .rescue-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
    .rescue-header p { font-size: 13px; color: #64748b; margin: 4px 0 0; }
    
    /* Stats Grid */
    .rescue-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .rescue-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; position: relative; overflow: hidden; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .rescue-stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
    .rescue-stat-card.danger::before { background: #dc2626; }
    .rescue-stat-card.warning::before { background: #f59e0b; }
    .rescue-stat-card.info::before { background: #3b82f6; }
    .rescue-stat-card.success::before { background: #10b981; }
    .rescue-stat-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; }
    .rescue-stat-value { font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 6px; line-height: 1; }
    
    /* Cards */
    .rescue-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; margin-bottom: 20px; }
    .rescue-card-head { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .rescue-card-head h3 { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0; }
    
    /* Layout grid */
    .rescue-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    @media (max-width: 1100px) { .rescue-grid { grid-template-columns: 1fr; } }
    @media (max-width: 720px) { .rescue-stats { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 480px) { .rescue-stats { grid-template-columns: 1fr; } }
    
    /* Escalation cards */
    .esc-card { border: 1px solid #e2e8f0; border-left: 4px solid #dc2626; border-radius: 10px; padding: 14px; margin: 0 16px 12px; background: #fff; }
    .esc-card:first-child { margin-top: 16px; }
    .esc-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .esc-id { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 800; color: #991b1b; }
    .esc-time { font-size: 12px; color: #64748b; }
    .esc-name { font-weight: 700; color: #0f172a; font-size: 14px; }
    .esc-loc { font-size: 12px; color: #64748b; margin-top: 4px; }
    .esc-actions { margin-top: 12px; display: flex; gap: 8px; }
    
    /* Table */
    .rescue-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rescue-table th { text-align: left; padding: 11px 16px; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .rescue-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
    .rescue-table tbody tr:last-child td { border-bottom: none; }
    .rescue-table tbody tr:hover { background: #f8fafc; }
    
    /* Badges */
    .rescue-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    .badge-amber { background: #fef3c7; color: #b45309; }
    .badge-blue { background: #dbeafe; color: #1d4ed8; }
    .badge-green { background: #d1fae5; color: #047857; }
    .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    
    /* Buttons */
    .rescue-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.16s; }
    .rescue-btn.primary { background: #0d9488; color: #fff; }
    .rescue-btn.primary:hover { background: #0f766e; }
    .rescue-btn.danger { background: #dc2626; color: #fff; }
    .rescue-btn.danger:hover { background: #991b1b; }
    .rescue-btn.ghost { background: #fff; border-color: #e2e8f0; color: #475569; }
    .rescue-btn.ghost:hover { background: #f8fafc; }
    .rescue-btn.link { background: none; color: #0d9488; padding: 0; }
    .rescue-btn.link:hover { color: #0f766e; }
    
    /* Provider rows */
    .provider-row { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
    .provider-row:last-child { border-bottom: none; }
    .provider-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg,#0d9488,#2dd4bf); display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 13px; flex-shrink: 0; }
    .provider-info { flex: 1; min-width: 0; }
    .provider-name { font-size: 13px; font-weight: 700; color: #0f172a; }
    .provider-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
    
    .empty-state { padding: 40px 20px; text-align: center; color: #94a3b8; font-size: 13px; }
</style>

<div class="rescue-admin-wrap">

    {{-- Header --}}
    <div class="rescue-header">
        <div>
            <h2>Global Rescue Coordination</h2>
            <p>System-wide SOS monitoring & manual dispatch</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="rescue-stats">
        <div class="rescue-stat-card danger">
            <div class="rescue-stat-label">Live SOS · Global</div>
            <div class="rescue-stat-value">{{ $liveSosCount }}</div>
        </div>
        <div class="rescue-stat-card warning">
            <div class="rescue-stat-label">SLA Breaches</div>
            <div class="rescue-stat-value">{{ $slaBreaches }}</div>
        </div>
        <div class="rescue-stat-card info">
            <div class="rescue-stat-label">Active Missions</div>
            <div class="rescue-stat-value">{{ $activeMissionsCount }}</div>
        </div>
        <div class="rescue-stat-card success">
            <div class="rescue-stat-label">Providers Online</div>
            <div class="rescue-stat-value">{{ $onlineProviders }} / {{ $totalProviders }}</div>
        </div>
    </div>

    <div class="rescue-grid">
        
        {{-- Left Column --}}
        <div>
            {{-- Escalation Queue --}}
            <div class="rescue-card">
                <div class="rescue-card-head">
                    <h3>🚨 Escalation Queue (SLA Breach)</h3>
                    <span class="rescue-badge badge-red">{{ $escalations->count() }} active</span>
                </div>
                @forelse($escalations as $sos)
                    <div class="esc-card">
                        <div class="esc-top">
                            <span class="esc-id">SOS-{{ str_pad($sos->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="esc-time">{{ $sos->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="esc-name">{{ $sos->traveler->name ?? 'Unknown Traveler' }}</div>
                        <div class="esc-loc">
                            📍 Lat: {{ number_format($sos->latitude, 4) }}, Lng: {{ number_format($sos->longitude, 4) }}
                        </div>
                        <div class="esc-actions">
                            <a href="{{ route('admin.rescue.sos.show', $sos) }}" class="rescue-btn danger">
                                Manual Dispatch
                            </a>
                            <a href="https://maps.google.com/?q={{ $sos->latitude }},{{ $sos->longitude }}" target="_blank" class="rescue-btn ghost">
                                Open Map
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">No escalations at this time.</div>
                @endforelse
            </div>

            {{-- All Active SOS --}}
            <div class="rescue-card">
                <div class="rescue-card-head">
                    <h3>All Active SOS (System-wide)</h3>
                    <span class="rescue-badge badge-blue">{{ $activeSos->count() }} total</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="rescue-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Traveler</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeSos as $sos)
                                <tr>
                                    <td style="font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #991b1b; font-size: 12px;">
                                        SOS-{{ str_pad($sos->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td style="font-weight: 600;">{{ $sos->traveler->name ?? 'Unknown' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($sos->status) {
                                                'pending', 'sent' => 'badge-red',
                                                'acknowledged' => 'badge-amber',
                                                'responding' => 'badge-blue',
                                                'resolved' => 'badge-green',
                                                default => 'badge-gray',
                                            };
                                        @endphp
                                        <span class="rescue-badge {{ $badgeClass }}">{{ $sos->status }}</span>
                                    </td>
                                    <td style="color: #64748b; font-size: 12px;">{{ $sos->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.rescue.sos.show', $sos) }}" class="rescue-btn link">View →</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">No active SOS</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            <div class="rescue-card">
                <div class="rescue-card-head">
                    <h3>Providers Live Status</h3>
                    <span class="rescue-badge badge-green">{{ $onlineProviders }} online</span>
                </div>
                @forelse($providers as $rp)
                    <div class="provider-row">
                        <div class="provider-avatar">
                            {{ strtoupper(substr($rp->provider->name ?? 'P', 0, 2)) }}
                        </div>
                        <div class="provider-info">
                            <div class="provider-name">{{ $rp->provider->name ?? 'Provider' }}</div>
                            <div class="provider-meta">
                                {{ $rp->fleet_size }} vehicles · {{ $rp->coverage_radius_km }} km radius
                            </div>
                        </div>
                        <span class="rescue-badge {{ $rp->is_available ? 'badge-green' : 'badge-gray' }}">
                            {{ $rp->is_available ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                @empty
                    <div class="empty-state">No providers registered.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
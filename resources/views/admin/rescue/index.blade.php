@extends('layouts.admin')

@section('title', 'Rescue Command Center')
@section('header', 'Command Center')

@section('content')

<style>
    /* Dark Theme — matches HTML reference */
    .rescue-admin-wrap { max-width: 1400px; margin: 0 auto; padding: 24px; background: #0f172a; color: #e2e8f0; border-radius: 12px; }
    .rescue-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px; }
    .rescue-header h2 { font-size: 24px; font-weight: 800; color: #f8fafc; margin: 0; letter-spacing: -0.02em; }
    .rescue-header p { font-size: 13px; color: #94a3b8; margin: 4px 0 0; }

    /* Alert Banner */
    .rescue-alert-banner { background: #7f1d1d; border: 1px solid #ef4444; border-radius: 10px; padding: 14px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 14px; color: #fca5a5; flex-wrap: wrap; }
    .rescue-alert-banner .title { color: #fff; font-weight: 700; font-size: 14px; }
    .rescue-alert-banner .sub { font-size: 12px; color: #fca5a5; margin-top: 2px; }
    .rescue-alert-banner i { color: #ef4444; font-size: 20px; flex-shrink: 0; }

    /* Stats Grid */
    .rescue-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px; }
    .rescue-stat-card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 18px; position: relative; overflow: hidden; color: #f8fafc; }
    .rescue-stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
    .rescue-stat-card.danger::before { background: #ef4444; }
    .rescue-stat-card.warning::before { background: #f59e0b; }
    .rescue-stat-card.info::before { background: #3b82f6; }
    .rescue-stat-card.success::before { background: #10b981; }
    .rescue-stat-card.purple::before { background: #8b5cf6; }
    .rescue-stat-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
    .rescue-stat-value { font-size: 28px; font-weight: 800; color: #f8fafc; margin-top: 6px; line-height: 1; }
    .rescue-stat-sub { font-size: 11px; color: #94a3b8; margin-top: 4px; }

    /* Cards */
    .rescue-card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; margin-bottom: 20px; }
    .rescue-card-head { padding: 16px 20px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; background: #0f172a; }
    .rescue-card-head h3 { font-size: 15px; font-weight: 800; color: #f8fafc; margin: 0; }

    /* Layout grid */
    .rescue-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }

    /* Escalation cards */
    .esc-card { border: 1px solid #334155; border-left: 4px solid #ef4444; border-radius: 10px; padding: 14px; margin: 0 16px 12px; background: #1e293b; }
    .esc-card:first-child { margin-top: 16px; }
    .esc-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .esc-id { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 800; color: #f87171; }
    .esc-time { font-size: 12px; color: #94a3b8; }
    .esc-name { font-weight: 700; color: #f8fafc; font-size: 14px; }
    .esc-loc { font-size: 12px; color: #94a3b8; margin-top: 4px; }
    .esc-actions { margin-top: 12px; display: flex; gap: 8px; }

    /* Table */
    .rescue-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rescue-table th { text-align: left; padding: 11px 16px; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #334155; background: #0f172a; }
    .rescue-table td { padding: 12px 16px; border-bottom: 1px solid #334155; color: #cbd5e1; }
    .rescue-table tbody tr:last-child td { border-bottom: none; }
    .rescue-table tbody tr:hover { background: #334155; }

    /* Badges */
    .rescue-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .badge-red { background: rgba(220,38,38,0.2); color: #f87171; }
    .badge-amber { background: rgba(245,158,11,0.2); color: #fbbf24; }
    .badge-blue { background: rgba(59,130,246,0.2); color: #60a5fa; }
    .badge-green { background: rgba(16,185,129,0.2); color: #34d399; }
    .badge-gray { background: rgba(148,163,184,0.2); color: #94a3b8; border: 1px solid #475569; }

    /* Buttons */
    .rescue-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: all 0.16s; }
    .rescue-btn.primary { background: #0d9488; color: #fff; }
    .rescue-btn.primary:hover { background: #0f766e; }
    .rescue-btn.danger { background: #ef4444; color: #fff; }
    .rescue-btn.danger:hover { background: #dc2626; }
    .rescue-btn.ghost { background: transparent; border-color: #475569; color: #cbd5e1; }
    .rescue-btn.ghost:hover { background: #334155; }
    .rescue-btn.link { background: none; color: #2dd4bf; padding: 0; }
    .rescue-btn.link:hover { color: #14b8a6; }

    /* Provider rows */
    .provider-row { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid #334155; }
    .provider-row:last-child { border-bottom: none; }
    .provider-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg,#0d9488,#2dd4bf); display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 13px; flex-shrink: 0; }
    .provider-info { flex: 1; min-width: 0; }
    .provider-name { font-size: 13px; font-weight: 700; color: #f8fafc; }
    .provider-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }

    /* Admin Actions */
    .admin-action-row { display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #334155; align-items: flex-start; }
    .admin-action-row:last-child { border-bottom: none; }
    .admin-action-time { font-size: 11px; color: #94a3b8; font-family: monospace; flex-shrink: 0; min-width: 44px; }
    .admin-action-body { flex: 1; font-size: 12px; color: #cbd5e1; line-height: 1.45; }
    .admin-action-body .who { font-weight: 700; color: #f8fafc; }

    /* Map */
    .map-container { height: 250px; background: #0f172a; position: relative; overflow: hidden; }
    .map-container svg { width: 100%; height: 100%; display: block; }
    .map-legend { position: absolute; bottom: 10px; left: 10px; font-size: 11px; color: #cbd5e1; background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px; }
    .map-coverage { position: absolute; bottom: 10px; right: 10px; font-size: 11px; color: #cbd5e1; background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px; }
    .map-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 4px; }

    .empty-state { padding: 40px 20px; text-align: center; color: #94a3b8; font-size: 13px; }

    /* Responsive */
    @media (max-width: 1200px) { .rescue-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 1100px) { .rescue-grid { grid-template-columns: 1fr; } }
    @media (max-width: 720px) { .rescue-stats { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 480px) { .rescue-stats { grid-template-columns: 1fr; } }
</style>

<div class="rescue-admin-wrap">

    {{-- Top Alert Banner — only if SLA breaches --}}
    @if($slaBreaches > 0)
    <div class="rescue-alert-banner">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <div class="title">{{ $slaBreaches }} SOS unassigned · over 10 min SLA</div>
                <div class="sub">Manual dispatch required immediately.</div>
            </div>
        </div>
        <a href="#escalations" class="rescue-btn danger">Dispatch now</a>
    </div>
    @endif

    {{-- Header --}}
    <div class="rescue-header">
        <div>
            <h2>Global Rescue Coordination</h2>
            <p>System-wide SOS monitoring & manual dispatch</p>
        </div>
    </div>

    {{-- Stats Grid — 5 cards --}}
    <div class="rescue-stats">
        <div class="rescue-stat-card danger">
            <div class="rescue-stat-label">Live SOS · Global</div>
            <div class="rescue-stat-value">{{ $liveSosCount }}</div>
            <div class="rescue-stat-sub">{{ $liveSosCount > 0 ? 'Priority-sorted · real-time' : 'All clear' }}</div>
        </div>
        <div class="rescue-stat-card warning">
            <div class="rescue-stat-label">SLA Breaches</div>
            <div class="rescue-stat-value">{{ $slaBreaches }}</div>
            <div class="rescue-stat-sub">Needs manual dispatch</div>
        </div>
        <div class="rescue-stat-card info">
            <div class="rescue-stat-label">Active Missions</div>
            <div class="rescue-stat-value">{{ $activeMissionsCount }}</div>
            <div class="rescue-stat-sub">In progress</div>
        </div>
        <div class="rescue-stat-card success">
            <div class="rescue-stat-label">Providers Online</div>
            <div class="rescue-stat-value">{{ $onlineProviders }} / {{ $totalProviders }}</div>
            <div class="rescue-stat-sub">{{ $totalProviders - $onlineProviders }} offline</div>
        </div>
        <div class="rescue-stat-card purple">
            <div class="rescue-stat-label">Pending Docs</div>
            <div class="rescue-stat-value">{{ $pendingDocsCount ?? 0 }}</div>
            <div class="rescue-stat-sub">Awaiting verification</div>
        </div>
    </div>

    <div class="rescue-grid">
        {{-- Left Column --}}
        <div>
            {{-- Escalation Queue --}}
            <div class="rescue-card" id="escalations">
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
                            <a href="{{ route('admin.rescue.sos.show', $sos) }}" class="rescue-btn danger">Manual Dispatch</a>
                            <a href="https://maps.google.com/?q={{ $sos->latitude }},{{ $sos->longitude }}" target="_blank" class="rescue-btn ghost">Open Map</a>
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
                                    <td style="font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #f87171; font-size: 12px;">
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
                                    <td style="color: #94a3b8; font-size: 12px;">{{ $sos->created_at->diffForHumans() }}</td>
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

            {{-- National Operations Map --}}
            <div class="rescue-card">
                <div class="rescue-card-head">
                    <h3>National Operations Map</h3>
                    <span class="rescue-badge badge-green">LIVE</span>
                </div>
                <div class="map-container">
                    <svg viewBox="0 0 800 250" preserveAspectRatio="xMidYMid meet">
                        <rect width="800" height="250" fill="#0f172a"/>
                        <path d="M80 200 Q200 90 400 140 T720 90 L740 110 Q600 170 400 200 T80 220 Z" fill="#1e293b" opacity="0.4"/>
                        <path d="M120 190 Q250 130 400 155 T680 110" stroke="#334155" stroke-width="2" fill="none" stroke-dasharray="4 4"/>
                        <circle cx="120" cy="190" r="6" fill="#2dd4bf"/>
                        <text x="120" y="212" fill="#5eead4" font-size="10" text-anchor="middle" font-weight="700">KTM BASE</text>
                        <circle cx="400" cy="155" r="10" fill="none" stroke="#fbbf24" stroke-width="1.5" stroke-dasharray="3 3"/>
                        <circle cx="400" cy="155" r="6" fill="#ef4444"/>
                        <text x="400" y="180" fill="#fca5a5" font-size="10" text-anchor="middle" font-weight="700">SOS CLUSTER</text>
                        <circle cx="680" cy="110" r="6" fill="#3b82f6"/>
                        <text x="680" y="132" fill="#93c5fd" font-size="10" text-anchor="middle" font-weight="700">POKHARA</text>
                    </svg>
                    <div class="map-legend">
                        <span class="map-dot" style="background:#2dd4bf"></span>Base
                        <span class="map-dot" style="background:#ef4444;margin-left:10px"></span>SOS
                        <span class="map-dot" style="background:#3b82f6;margin-left:10px"></span>Provider
                    </div>
                    <div class="map-coverage">
                        Live: {{ $liveSosCount }} SOS · {{ $activeMissionsCount }} missions
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Providers Live Status --}}
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
                                {{ $rp->actual_fleet_count ?? $rp->fleet_size }} vehicles · {{ $rp->coverage_radius_km }} km radius
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

            {{-- Admin Actions Log --}}
            <div class="rescue-card">
                <div class="rescue-card-head">
                    <h3>Admin Actions</h3>
                    <span class="rescue-badge badge-gray">{{ $recentActions->count() }} recent</span>
                </div>
                <div>
                    @forelse($recentActions as $action)
                        <div class="admin-action-row">
                            <span class="admin-action-time">{{ $action->created_at->format('H:i') }}</span>
                            <span class="rescue-badge badge-gray" style="font-size: 9px; padding: 1px 6px; flex-shrink: 0;">{{ $action->action }}</span>
                            <div class="admin-action-body">
                                <span class="who">System</span> {{ $action->notes ?? 'Action logged' }}
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No recent actions.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
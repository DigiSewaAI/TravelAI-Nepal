@extends('layouts.provider-rescue')

@section('title', 'Operations Overview')
@section('page-title', 'Operations Overview')
@section('page-sub', 'Target SLA · dispatch under 5 min · rescue operations live')

@section('content')

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card stat-danger">
        <div class="stat-icon"><i data-lucide="siren"></i></div>
        <div class="stat-body">
            <span class="stat-label">Active SOS Alerts</span>
            <div class="stat-value">
                {{ $liveSosCount ?? 0 }}
                @if(($liveSosCount ?? 0) > 0)
                <span class="stat-trend live">LIVE</span>
                @endif
            </div>
            <span class="stat-sub">Priority-sorted · real-time</span>
        </div>
    </div>

    <div class="stat-card stat-teal">
        <div class="stat-icon"><i data-lucide="ambulance"></i></div>
        <div class="stat-body">
            <span class="stat-label">Active Missions</span>
            <div class="stat-value">{{ $activeMissionsCount ?? 0 }}</div>
            <span class="stat-sub">In progress</span>
        </div>
    </div>

    <div class="stat-card stat-green">
        <div class="stat-icon"><i data-lucide="timer"></i></div>
        <div class="stat-body">
            <span class="stat-label">Avg Response Time</span>
            <div class="stat-value">
                {{ $avgResponseMinutes > 0 ? $avgResponseMinutes . 'm' : '—' }}
            </div>
            <span class="stat-sub">Target < 5min</span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon"><i data-lucide="wallet"></i></div>
        <div class="stat-body">
            <span class="stat-label">Revenue</span>
            <div class="stat-value">NPR {{ number_format($totalRevenue ?? 0, 2) }}</div>
            <span class="stat-sub">Completed missions</span>
        </div>
    </div>
</div>

<div class="content-grid">
    {{-- Main Column --}}
    <div class="col-main">

        {{-- Live SOS Queue --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="siren"></i>
                    <h2>Live SOS Queue</h2>
                    @if(($sosQueue ?? collect())->count() > 0)
                        <span class="pill pill-danger">{{ $sosQueue->count() }} active</span>
                    @endif
                </div>
                <a href="{{ route('provider.rescue.sos') }}" class="btn-link">
                    View all <i data-lucide="chevron-right"></i>
                </a>
            </div>
            <div style="padding:12px">
                @forelse($sosQueue ?? [] as $alert)
                    @php
                        $isCritical = in_array($alert->status, ['pending', 'sent']);
                        $badgeClass = $isCritical ? 'badge-critical' : ($alert->status === 'acknowledged' ? 'badge-medium' : 'badge-low');
                        $cardClass = $isCritical ? 'critical' : 'medium';
                    @endphp
                    <div class="sos-card" style="border:1px solid var(--border);border-radius:12px;padding:13px 14px;margin-bottom:11px;background:var(--surface);{{ $isCritical ? 'border-left:4px solid var(--danger);background:linear-gradient(90deg,rgba(220,38,38,.03),transparent 42%);' : 'border-left:4px solid var(--warning);background:linear-gradient(90deg,rgba(245,158,11,.03),transparent 42%);' }}">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span class="badge {{ $badgeClass }}">
                                    @if($isCritical)
                                        <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;animation:blink 1.6s infinite"></span>
                                    @endif
                                    {{ strtoupper($alert->status) }}
                                </span>
                                <span style="font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;color:var(--text-muted)">SOS-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <span style="font-size:11px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px">
                                <i data-lucide="clock" style="width:12px;height:12px"></i>
                                {{ $alert->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-end;flex-wrap:wrap">
                            <div style="min-width:0;flex:1">
                                <h3 style="font-size:14px;font-weight:700;margin-bottom:5px">
                                    {{ $alert->traveler->name ?? 'Unknown Traveler' }}
                                </h3>
                                <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:11.5px;color:var(--text-muted)">
                                    <span><i data-lucide="map-pin" style="width:12px;height:12px;display:inline"></i> {{ number_format($alert->latitude, 4) }}, {{ number_format($alert->longitude, 4) }}</span>
                                </div>
                            </div>
                            <div style="display:flex;gap:7px;flex-shrink:0">
                                <a href="{{ route('provider.sos-alerts.show', $alert) }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="eye" style="width:12px;height:12px"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding:40px 20px;text-align:center;color:var(--text-muted)">
                        <i data-lucide="check-circle" style="width:32px;height:32px;margin:0 auto 8px;color:var(--success)"></i>
                        <div style="font-size:13px;font-weight:600">No active SOS alerts</div>
                        <div style="font-size:11.5px;margin-top:4px">All clear — you will be notified when a new SOS arrives</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Active Mission --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="ambulance"></i>
                    <h2>Active Mission</h2>
                    @if($activeMission)
                        <span class="pill pill-teal">1 in progress</span>
                    @endif
                </div>
                <a href="{{ route('provider.rescue.missions') }}" class="btn-link">
                    View all <i data-lucide="chevron-right"></i>
                </a>
            </div>
            <div style="padding:16px">
                @if($activeMission)
                    <div class="mission-card" style="margin:0">
                        <div class="mission-head">
                            <span class="mission-id">{{ $activeMission->mission_number }}</span>
                            <span class="badge badge-blue">RESCUE: {{ strtoupper($activeMission->status) }}</span>
                        </div>
                        <h3 style="font-size:14px;font-weight:700;margin-bottom:6px">
                            {{ $activeMission->sos->traveler->name ?? 'Unknown Traveler' }}
                        </h3>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:12px">
                            <span style="display:inline-flex;align-items:center;gap:4px">
                                <i data-lucide="map-pin" style="width:12px;height:12px"></i>
                                {{ number_format($activeMission->pickup_lat ?? 0, 4) }}, {{ number_format($activeMission->pickup_lng ?? 0, 4) }}
                            </span>
                            · Cost: <strong>NPR {{ number_format($activeMission->cost_estimate ?? 0, 2) }}</strong>
                        </div>
                        <div style="display:flex;justify-content:flex-end">
                            <a href="{{ route('provider.rescue.missions') }}" class="btn btn-primary btn-sm">
                                <i data-lucide="navigation" style="width:12px;height:12px"></i> Manage Mission
                            </a>
                        </div>
                    </div>
                @else
                    <div style="padding:40px 20px;text-align:center;color:var(--text-muted)">
                        <i data-lucide="plane" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
                        <div style="font-size:13px;font-weight:600">No active missions</div>
                        <div style="font-size:11.5px;margin-top:4px">Missions will appear when you accept an SOS dispatch</div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Side Column --}}
    <div class="col-side">

        {{-- Weather & Flight Safety --}}
        <div class="card" style="background:linear-gradient(160deg,#0b1f2a,#0a1620);border:1px solid #12262f;padding:14px;color:#e2e8f0">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                <div style="font-size:12.5px;font-weight:700;color:#fff;display:flex;align-items:center;gap:6px">
                    <i data-lucide="cloud-sun" style="width:14px;height:14px;color:#2dd4bf"></i> Weather & Flight Safety
                </div>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.02em;white-space:nowrap;background:rgba(245,158,11,.15);color:#fcd34d;border:1px solid rgba(245,158,11,.3)">ADVISORY</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                    <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Wind</span>
                    <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">12 km/h</strong>
                </div>
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                    <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Visibility</span>
                    <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">8 km</strong>
                </div>
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                    <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Ceiling</span>
                    <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">3,500 m</strong>
                </div>
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                    <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Temp</span>
                    <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">-2 °C</strong>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:10px;color:#64748b;padding-top:9px;border-top:1px solid rgba(255,255,255,.06);flex-wrap:wrap">
                <span style="color:#6ee7b7;font-weight:600;display:inline-flex;align-items:center;gap:4px"><i data-lucide="check-circle-2" style="width:10px;height:10px"></i> DHM Aviation API</span>
                <span style="font-family:'JetBrains Mono',monospace;color:#94a3b8">Updated 4m ago</span>
            </div>
        </div>

        {{-- GPS Map --}}
        <div class="card" style="background:linear-gradient(160deg,#0a1620,#0b1f2a);border:1px solid #12262f;overflow:hidden;padding:0">
            <div style="padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center">
                <div style="display:flex;align-items:center;gap:8px">
                    <i data-lucide="map" style="width:16px;height:16px;color:#2dd4bf"></i>
                    <h2 style="color:#e2e8f0;font-size:14px;font-weight:700">GPS · Last-known</h2>
                </div>
                <span style="font-size:10px;font-weight:700;color:#fcd34d;background:rgba(245,158,11,.15);padding:4px 9px;border-radius:99px;border:1px solid rgba(245,158,11,.3);display:inline-flex;align-items:center;gap:5px">
                    <span style="width:5px;height:5px;border-radius:50%;background:#fbbf24"></span> STALE
                </span>
            </div>
            <div style="position:relative;height:200px;background:#0b1f2a">
                <svg style="width:100%;height:100%;display:block" viewBox="0 0 420 260" preserveAspectRatio="xMidYMid slice">
                    <rect width="420" height="260" fill="#0a1620"/>
                    <path d="M0 200 Q60 155 125 180 T255 165 T420 190 V260 H0 Z" fill="rgba(45,212,191,0.045)"/>
                    <path d="M0 220 Q75 195 155 210 T305 195 T420 218 V260 H0 Z" fill="rgba(45,212,191,0.07)"/>
                    <path d="M70 195 L155 145 L255 108 L340 72" stroke="#2dd4bf" stroke-width="2" stroke-dasharray="6 6" fill="none" opacity=".6"/>
                    <circle cx="70" cy="195" r="6" fill="#2dd4bf"/>
                    <text x="70" y="220" text-anchor="middle" fill="#5eead4" font-size="8.5" font-weight="700">BASE</text>
                    <circle cx="255" cy="108" r="22" fill="none" stroke="#fbbf24" stroke-width="1.2" stroke-dasharray="3 3" opacity=".65"/>
                    <circle cx="255" cy="108" r="6" fill="#ef4444"/>
                    <text x="255" y="140" text-anchor="middle" fill="#fca5a5" font-size="8.5" font-weight="700">LAST-KNOWN</text>
                    <circle cx="155" cy="145" r="5.5" fill="#3b82f6"/>
                    <text x="155" y="170" text-anchor="middle" fill="#93c5fd" font-size="8.5" font-weight="700">9N-AJH</text>
                </svg>
            </div>
        </div>

        {{-- Audit Log --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="scroll-text"></i><h2>Audit Log</h2>
                </div>
                <a href="{{ route('provider.rescue.audit') }}" class="btn-link">Full log <i data-lucide="chevron-right"></i></a>
            </div>
            <div>
                @forelse($auditLogs as $log)
                    <div style="display:flex;gap:10px;padding:11px 16px;border-bottom:1px solid var(--border)">
                        <span style="font-size:10px;color:var(--text-light);font-family:'JetBrains Mono',monospace;font-weight:600;min-width:44px;padding-top:2px">
                            {{ $log->created_at ? $log->created_at->format('H:i') : '—' }}
                        </span>
                        <span style="width:7px;height:7px;border-radius:50%;margin-top:6px;background:var(--info)"></span>
                        <div style="flex:1;font-size:11.5px;color:var(--text-muted);line-height:1.45">
                            <span style="font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:99px;background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border);margin-right:4px;text-transform:uppercase">
                                {{ $log->action ?? 'action' }}
                            </span>
                            <span style="color:var(--text);font-weight:600">System</span>
                            {{ $log->notes ?? (($log->from_status ?? '') . ' → ' . ($log->to_status ?? '')) }}
                        </div>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px">
                        No audit logs yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Fleet Status --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="plane"></i><h2>Fleet Status</h2>
                </div>
                <a href="{{ route('provider.rescue.fleet') }}" class="btn-link">Manage <i data-lucide="chevron-right"></i></a>
            </div>
            <div style="display:flex;flex-direction:column">
                @forelse($fleets as $fleet)
                    @php
                        $statusColor = match($fleet->status) {
                            'available' => ['info-light', 'info-dark'],
                            'on_mission' => ['warning-light', 'warning-dark'],
                            'maintenance' => ['danger-light', 'danger-dark'],
                            default => ['surface-3', 'text-muted'],
                        };
                    @endphp
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border)">
                        <div style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:var(--{{ $statusColor[0] }});color:var(--{{ $statusColor[1] }})">
                            <i data-lucide="plane" style="width:15px;height:15px"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:12px;font-weight:600;line-height:1.3">
                                {{ $fleet->registration_number }}{{ $fleet->model ? ' · ' . $fleet->model : '' }}
                            </div>
                        </div>
                        <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:var(--{{ $statusColor[0] }});color:var(--{{ $statusColor[1] }})">
                            {{ ucfirst(str_replace('_', ' ', $fleet->status)) }}
                        </span>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px">
                        No vehicles added.
                    </div>
                @endforelse
            </div>
        </div>

@endsection

@push('rescue-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
@extends('layouts.provider-rescue')

@section('title', 'Operations Overview')
@section('page-title', 'Operations Overview')
@section('page-sub', 'Target SLA · dispatch under 5 min · rescue operations live')
@push('rescue-head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Fix for missing default Leaflet icons */
    .leaflet-default-icon-path { background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png); }
</style>
@endpush
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
                                <a href="{{ route('provider.rescue.sos') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="eye" style="width:12px;height:12px"></i> View Queue
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

        {{-- Weather & Flight Safety — Real Data (Open-Meteo) --}}
        <div class="card" style="background:linear-gradient(160deg,#0b1f2a,#0a1620);border:1px solid #12262f;padding:14px;color:#e2e8f0">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                <div style="font-size:12.5px;font-weight:700;color:#fff;display:flex;align-items:center;gap:6px">
                    <i data-lucide="cloud-sun" style="width:14px;height:14px;color:#2dd4bf"></i> Weather & Flight Safety
                </div>
                @if($weather)
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.02em;white-space:nowrap;background:rgba(16,185,129,.15);color:#6ee7b7;border:1px solid rgba(16,185,129,.3)">LIVE</span>
                @else
                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.02em;white-space:nowrap;background:rgba(245,158,11,.15);color:#fcd34d;border:1px solid rgba(245,158,11,.3)">OFFLINE</span>
                @endif
            </div>
            @if($weather)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px">
                    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                        <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Temp</span>
                        <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">{{ round($weather['temperature'] ?? 0) }} °C</strong>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                        <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Wind</span>
                        <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">{{ $weather['wind_speed'] ?? 'N/A' }} km/h</strong>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                        <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Humidity</span>
                        <strong style="font-size:12.5px;color:#fff;font-weight:700;display:block;margin-top:2px;font-family:'JetBrains Mono',monospace">{{ $weather['humidity'] ?? 'N/A' }}%</strong>
                    </div>
                    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:7px;padding:7px 9px">
                        <span style="font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:600;display:block">Condition</span>
                        <strong style="font-size:11px;color:#fff;font-weight:700;display:block;margin-top:4px;font-family:'Inter',sans-serif">{{ \App\Services\OpenMeteoService::describeWmoCode($weather['weather_code'] ?? null)['label'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:10px;color:#64748b;padding-top:9px;border-top:1px solid rgba(255,255,255,.06);flex-wrap:wrap">
                    <span style="color:#6ee7b7;font-weight:600;display:inline-flex;align-items:center;gap:4px"><i data-lucide="check-circle-2" style="width:10px;height:10px"></i> Open-Meteo (Free)</span>
                    <span style="font-family:'JetBrains Mono',monospace;color:#94a3b8">Live now</span>
                </div>
            @else
                <div style="padding:20px;text-align:center;color:#94a3b8;font-size:12px">Weather data unavailable</div>
            @endif
        </div>

        {{-- GPS Map — Real Data (Leaflet + OpenStreetMap) --}}
        <div class="card" style="background:linear-gradient(160deg,#0a1620,#0b1f2a);border:1px solid #12262f;overflow:hidden;padding:0">
            <div style="padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center">
                <div style="display:flex;align-items:center;gap:8px">
                    <i data-lucide="map" style="width:16px;height:16px;color:#2dd4bf"></i>
                    <h2 style="color:#e2e8f0;font-size:14px;font-weight:700">GPS · Last-known</h2>
                </div>
                @if($isStale)
                    <span style="font-size:10px;font-weight:700;color:#fcd34d;background:rgba(245,158,11,.15);padding:4px 9px;border-radius:99px;border:1px solid rgba(245,158,11,.3);display:inline-flex;align-items:center;gap:5px">
                        <span style="width:5px;height:5px;border-radius:50%;background:#fbbf24"></span> STALE
                    </span>
                @else
                    <span style="font-size:10px;font-weight:700;color:#6ee7b7;background:rgba(16,185,129,.15);padding:4px 9px;border-radius:99px;border:1px solid rgba(16,185,129,.3);display:inline-flex;align-items:center;gap:5px">
                        <span style="width:5px;height:5px;border-radius:50%;background:#10b981"></span> LIVE
                    </span>
                @endif
            </div>
            <div id="rescueGpsMap" style="height: 200px; width: 100%; z-index: 1;"></div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var mapElement = document.getElementById('rescueGpsMap');
    if (!mapElement) return;

    var baseLat = {{ $baseLat }}, baseLng = {{ $baseLng }};
    var lastLat = {{ $lastLat }}, lastLng = {{ $lastLng }};
    var isStale = {{ $isStale ? 'true' : 'false' }};

    var map = L.map('rescueGpsMap').setView([lastLat, lastLng], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // Base marker (teal)
    var baseIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background-color:#2dd4bf;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 4px rgba(45,212,191,.3)'></div>",
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });
    L.marker([baseLat, baseLng], {icon: baseIcon}).addTo(map).bindPopup('Base (Pokhara)');

    // Last-known marker (red, if not stale)
    if (!isStale && (lastLat !== baseLat || lastLng !== baseLng)) {
        var lastIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#ef4444;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 4px rgba(239,68,68,.3);animation:blink 1.5s infinite'></div>",
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
        L.marker([lastLat, lastLng], {icon: lastIcon}).addTo(map).bindPopup('Last-known Position').openPopup();
        map.setView([lastLat, lastLng], 11);
    }
});
</script>
@endpush
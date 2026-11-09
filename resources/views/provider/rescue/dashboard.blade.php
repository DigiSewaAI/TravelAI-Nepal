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
            <div class="stat-value">—</div>
            <span class="stat-sub">Target < 5min</span>
        </div>
    </div>

    <div class="stat-card stat-purple">
        <div class="stat-icon"><i data-lucide="wallet"></i></div>
        <div class="stat-body">
            <span class="stat-label">Revenue</span>
            <div class="stat-value">NPR 0</div>
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
                    <h2>Active Missions</h2>
                    <span class="pill pill-teal">{{ $activeMissionsCount ?? 0 }} in progress</span>
                </div>
                <a href="{{ route('provider.rescue.missions') }}" class="btn-link">
                    View all <i data-lucide="chevron-right"></i>
                </a>
            </div>
            <div style="padding:40px 20px;text-align:center;color:var(--text-muted)">
                <i data-lucide="plane" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
                <div style="font-size:13px;font-weight:600">No active missions</div>
                <div style="font-size:11.5px;margin-top:4px">Missions will appear when you accept an SOS dispatch</div>
            </div>
        </div>

    </div>

    {{-- Side Column --}}
    <div class="col-side">

        {{-- Provider Status --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="badge-check"></i>
                    <h2>Provider Status</h2>
                </div>
            </div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                    <div style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,#0d9488,#2dd4bf);display:grid;place-items:center;color:#fff;font-weight:800;font-size:15px">
                        {{ strtoupper(substr($provider->name ?? 'RP', 0, 2)) }}
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:14px;font-weight:700">{{ $provider->name ?? 'Rescue Provider' }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted)">Rescue Service</div>
                    </div>
                </div>

                @if($rescueProvider)
                <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:8px;font-size:12px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--text-muted)">Fleet Size</span>
                        <strong>{{ $rescueProvider->fleet_size }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--text-muted)">Coverage Radius</span>
                        <strong>{{ $rescueProvider->coverage_radius_km }} km</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--text-muted)">Max Altitude</span>
                        <strong>{{ $rescueProvider->max_altitude_ft ?? '—' }} ft</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--text-muted)">24/7 Operations</span>
                        <strong>{{ $rescueProvider->is_24_7 ? 'Yes' : 'No' }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="color:var(--text-muted)">Availability</span>
                        @if($rescueProvider->is_available)
                            <span class="badge badge-green">On Duty</span>
                        @else
                            <span class="badge badge-gray">Off Duty</span>
                        @endif
                    </div>
                </div>
                @else
                <div style="border-top:1px solid var(--border);padding-top:12px;text-align:center;color:var(--text-muted);font-size:12px">
                    Rescue profile not configured
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="zap"></i>
                    <h2>Quick Actions</h2>
                </div>
            </div>
            <div style="padding:12px;display:flex;flex-direction:column;gap:8px">
                <a href="{{ route('provider.rescue.sos') }}" class="btn btn-ghost" style="justify-content:flex-start;width:100%">
                    <i data-lucide="siren"></i> Live SOS Queue
                </a>
                <a href="{{ route('provider.rescue.fleet') }}" class="btn btn-ghost" style="justify-content:flex-start;width:100%">
                    <i data-lucide="plane"></i> Manage Fleet
                </a>
                <a href="{{ route('provider.rescue.coverage') }}" class="btn btn-ghost" style="justify-content:flex-start;width:100%">
                    <i data-lucide="map-pin"></i> Coverage Areas
                </a>
                <a href="{{ route('provider.rescue.pricing') }}" class="btn btn-ghost" style="justify-content:flex-start;width:100%">
                    <i data-lucide="wallet"></i> Pricing Config
                </a>
                <a href="{{ route('provider.rescue.documents') }}" class="btn btn-ghost" style="justify-content:flex-start;width:100%">
                    <i data-lucide="file-text"></i> Documents
                </a>
            </div>
        </div>

        {{-- Verification Status --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="shield-check"></i>
                    <h2>Verification</h2>
                </div>
            </div>
            <div class="card-body">
                @if($provider->verification_status === 'verified')
                    <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--success-light);border:1px solid rgba(16,185,129,.3);border-radius:10px">
                        <i data-lucide="check-circle" style="color:var(--success-dark);width:20px;height:20px"></i>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:var(--success-dark)">Verified</div>
                            <div style="font-size:11px;color:var(--success-dark);opacity:.85">Ready for dispatch</div>
                        </div>
                    </div>
                @else
                    <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--warning-light);border:1px solid rgba(245,158,11,.3);border-radius:10px">
                        <i data-lucide="clock" style="color:var(--warning-dark);width:20px;height:20px"></i>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:var(--warning-dark)">Pending</div>
                            <div style="font-size:11px;color:var(--warning-dark);opacity:.85">Upload KYC to go live</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

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
@extends('layouts.provider-rescue')

@section('title', 'Live SOS Queue')
@section('page-title', 'Live SOS Queue')
@section('page-sub', 'Priority-sorted · atomic locks · 60s confirmation window · auto-expiry')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin-bottom:16px;flex-wrap:wrap">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Pending Dispatches</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">SOS alerts from travelers in your coverage area</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('provider.rescue.sos') }}" class="btn btn-ghost btn-sm"><i data-lucide="refresh-cw"></i> Refresh</a>
    </div>
</div>

@if(($alerts ?? collect())->count() > 0)
    <div class="sos-list" style="display:flex;flex-direction:column;gap:14px">
        @foreach($alerts as $alert)
            @php
                $isPending = in_array($alert->status, ['pending', 'sent']);
                $isAck = $alert->status === 'acknowledged';
                $cardClass = $isPending ? 'critical' : 'high';
                $badgeClass = $isPending ? 'badge-critical' : 'badge-high';
            @endphp
            <div class="sos-card" style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--surface);{{ $isPending ? 'border-left:4px solid var(--danger);' : 'border-left:4px solid #ea580c;' }}">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge {{ $badgeClass }}">
                            @if($isPending)
                                <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;animation:blink 1.6s infinite"></span>
                            @endif
                            {{ strtoupper($alert->status) }}
                        </span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:700;color:var(--text-muted)">SOS-{{ str_pad($alert->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <span style="font-size:12px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px">
                        <i data-lucide="clock" style="width:13px;height:13px"></i>
                        {{ $alert->created_at->diffForHumans() }} · {{ $alert->created_at->format('H:i') }}
                    </span>
                </div>

                <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-end;flex-wrap:wrap">
                    <div style="min-width:0;flex:1">
                        <h3 style="font-size:15px;font-weight:700;margin-bottom:6px">
                            {{ $alert->traveler->name ?? 'Unknown Traveler' }}
                            @if($alert->traveler && $alert->traveler->email)
                                <span style="font-size:12px;color:var(--text-muted);font-weight:500"> · {{ $alert->traveler->email }}</span>
                            @endif
                        </h3>
                        <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:12px;color:var(--text-muted);margin-bottom:8px">
                            <span style="display:inline-flex;align-items:center;gap:4px">
                                <i data-lucide="map-pin" style="width:12px;height:12px;color:var(--primary)"></i>
                                {{ number_format($alert->latitude, 6) }}, {{ number_format($alert->longitude, 6) }}
                            </span>
                            <a href="https://maps.google.com/?q={{ $alert->latitude }},{{ $alert->longitude }}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;font-weight:600">
                                Open Map →
                            </a>
                        </div>
                        @if($alert->message)
                        <div style="font-size:12.5px;color:var(--text);background:var(--surface-2);padding:8px 12px;border-radius:8px;border-left:3px solid var(--primary);margin-top:8px">
                            "{{ $alert->message }}"
                        </div>
                        @endif
                    </div>

                    <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap">
                        <a href="{{ route('provider.sos-alerts.show', $alert) }}" class="btn btn-primary btn-sm">
                            <i data-lucide="eye" style="width:13px;height:13px"></i> View & Respond
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="margin-top:20px">
        {{ $alerts->links() }}
    </div>
@else
    <div class="card">
        <div style="padding:80px 20px;text-align:center;color:var(--text-muted)">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--success-light);margin:0 auto 16px;display:grid;place-items:center">
                <i data-lucide="check-circle" style="width:32px;height:32px;color:var(--success)"></i>
            </div>
            <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;color:var(--text)">No active SOS alerts</h3>
            <p style="font-size:13px">All clear — you will be notified when a new SOS arrives.</p>
        </div>
    </div>
@endif

@endsection

@push('rescue-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
@endpush
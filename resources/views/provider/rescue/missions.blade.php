@extends('layouts.provider-rescue')

@section('title', 'Active Missions')
@section('page-title', 'Active Missions')
@section('page-sub', 'SOS & rescue lifecycles tracked independently')

@section('content')

<div style="margin-bottom:16px">
    <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Active Missions</h2>
    <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">SOS & rescue lifecycles tracked independently · completion requires safety confirmation</p>
</div>

@if($missions->count() > 0)
    <div class="card" style="padding:16px;background:transparent;border:none;box-shadow:none;">
        @foreach($missions as $mission)
            <div class="mission-card">
                <div class="mission-head">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span class="mission-id">{{ $mission->mission_number }}</span>
                        <span class="badge badge-blue">RESCUE: {{ strtoupper(str_replace('_', ' ', $mission->status)) }}</span>
                        @if($mission->sos)
                            <span class="badge badge-danger">SOS: {{ strtoupper(str_replace('_', ' ', $mission->sos->status)) }}</span>
                        @endif
                    </div>
                    <div style="font-size:12px;color:var(--text-muted)">
                        {{ $mission->created_at->format('M d, H:i') }}
                    </div>
                </div>

                <h3 style="font-size:15px;font-weight:700;margin-bottom:6px">
                    {{ $mission->sos && $mission->sos->traveler ? $mission->sos->traveler->name : 'Unknown Traveler' }}
                    @if($mission->fleet)
                        <span style="font-size:12px;color:var(--text-muted);font-weight:500"> · {{ $mission->fleet->registration_number }}</span>
                    @endif
                </h3>

                <div class="dual-tl">
                    {{-- Rescue Timeline --}}
                    <div class="tl-section">
                        <div class="tl-label">Rescue Lifecycle <span class="tl-label-badge rescue">Rescue system</span></div>
                        @php
                            $rescueStatuses = ['dispatched', 'accepted', 'en_route', 'on_scene', 'extracting', 'completed'];
                            $currentRescueIdx = array_search($mission->status, $rescueStatuses);
                        @endphp
                        <div class="tl-steps">
                            @foreach($rescueStatuses as $idx => $status)
                                <div class="tl-step {{ $idx < $currentRescueIdx ? 'done' : ($idx === $currentRescueIdx ? 'current' : '') }}">
                                    <div class="tl-dot"></div>
                                    <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
                    <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:11px 13px">
                        <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em">Cost Estimate</div>
                        <div style="font-size:13px;font-weight:700;margin-top:4px">NPR {{ number_format($mission->cost_estimate ?? 0, 2) }}</div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;align-items:center;gap:8px;flex-wrap:wrap">
                        @php
                            $nextStatus = [
                                'dispatched' => ['accepted', 'Accept Mission', 'btn-primary'],
                                'accepted'   => ['en_route', 'Mark En Route', 'btn-primary'],
                                'en_route'   => ['on_scene', 'Mark On Scene', 'btn-primary'],
                                'on_scene'   => ['extracting', 'Extracting', 'btn-primary'],
                                'extracting' => ['completed', 'Complete Mission', 'btn-warn'],
                            ][$mission->status] ?? null;
                        @endphp

                        @if($nextStatus)
                            <form action="{{ route('provider.rescue.missions.status', $mission) }}" method="POST" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $nextStatus[0] }}">
                                <button type="submit" class="btn {{ $nextStatus[2] }} btn-sm">
                                    {{ $nextStatus[1] }}
                                </button>
                            </form>
                        @elseif(in_array($mission->status, ['completed', 'cancelled']))
                            <span class="badge badge-green">✓ {{ strtoupper($mission->status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div style="padding:60px 20px;text-align:center;color:var(--text-muted)">
            <i data-lucide="plane" style="width:48px;height:48px;margin:0 auto 12px;opacity:.4"></i>
            <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;color:var(--text)">No active missions</h3>
            <p style="font-size:13px">Missions will appear here when you accept an SOS dispatch.</p>
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
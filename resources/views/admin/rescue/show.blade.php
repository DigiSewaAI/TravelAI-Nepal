@extends('layouts.admin')

@section('title', 'Manual Dispatch')
@section('page-title', 'Manual Dispatch')

@section('content')

<style>
    .md-wrap { max-width: 900px; margin: 0 auto; }
    .md-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; margin-bottom: 20px; }
    .md-card-head { padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .md-card-head h3 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
    .md-body { padding: 22px; }
    .md-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 640px) { .md-grid { grid-template-columns: 1fr; } }
    .md-label { font-size: 11px; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
    .md-value { font-size: 15px; font-weight: 700; color: #0f172a; }
    .md-sub { font-size: 13px; color: #64748b; margin-top: 4px; }
    .md-msg { font-size: 14px; font-style: italic; color: #0f172a; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border-left: 3px solid #0d9488; margin-top: 6px; }
    .md-badge { display: inline-flex; padding: 4px 12px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; background: #fee2e2; color: #991b1b; }
    .md-select { width: 100%; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; margin-top: 8px; margin-bottom: 20px; background: #fff; }
    .md-warning { background: #fef3c7; color: #b45309; padding: 14px 16px; border-radius: 10px; font-size: 13px; line-height: 1.5; margin-bottom: 20px; border-left: 4px solid #f59e0b; }
    .md-actions { display: flex; justify-content: flex-end; gap: 10px; padding-top: 18px; border-top: 1px solid #e2e8f0; }
    .md-btn { padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .md-btn.primary { background: #0d9488; color: #fff; }
    .md-btn.primary:hover { background: #0f766e; }
    .md-btn.ghost { background: #fff; border-color: #e2e8f0; color: #475569; }
    .md-btn.ghost:hover { background: #f8fafc; }
</style>

<div class="md-wrap">

    {{-- SOS Details Card --}}
    <div class="md-card">
        <div class="md-card-head">
            <h3>SOS-{{ str_pad($sos->id, 4, '0', STR_PAD_LEFT) }}</h3>
            <span class="md-badge">{{ $sos->status }}</span>
        </div>
        <div class="md-body">
            <div class="md-grid">
                <div>
                    <div class="md-label">Traveler</div>
                    <div class="md-value">{{ $sos->traveler->name ?? 'Unknown' }}</div>
                    <div class="md-sub">{{ $sos->traveler->email ?? '' }}</div>
                </div>
                <div>
                    <div class="md-label">Location</div>
                    <div class="md-value">Lat: {{ number_format($sos->latitude, 4) }}, Lng: {{ number_format($sos->longitude, 4) }}</div>
                    <a href="https://maps.google.com/?q={{ $sos->latitude }},{{ $sos->longitude }}" target="_blank" style="font-size: 12px; color: #0d9488; font-weight: 700; text-decoration: none; margin-top: 4px; display: inline-block;">
                        Open in Google Maps →
                    </a>
                </div>
                <div>
                    <div class="md-label">Time Requested</div>
                    <div class="md-value">{{ $sos->created_at->format('M d, Y H:i') }}</div>
                    <div class="md-sub">{{ $sos->created_at->diffForHumans() }}</div>
                </div>
                <div>
                    <div class="md-label">Assigned Provider</div>
                    <div class="md-value">{{ $sos->provider->name ?? 'Not assigned' }}</div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <div class="md-label">Message</div>
                <div class="md-msg">"{{ $sos->message ?? 'No message provided' }}"</div>
            </div>
        </div>
    </div>

    {{-- Manual Dispatch Form --}}
    <div class="md-card">
        <div class="md-card-head">
            <h3>Assign to Rescue Provider</h3>
        </div>
        <form action="{{ route('admin.rescue.sos.dispatch', $sos) }}" method="POST" class="md-body">
            @csrf
            <label style="font-size: 13px; font-weight: 800; color: #0f172a;">
                Select Available Provider <span style="color: #dc2626;">*</span>
            </label>
            <select name="provider_id" required class="md-select">
                <option value="">-- Choose Provider --</option>
                @foreach($rescueProviders as $rp)
                    <option value="{{ $rp->provider_id }}">
                        {{ $rp->provider->name }} ({{ $rp->fleet_size }} vehicles · {{ $rp->coverage_radius_km }}km radius)
                    </option>
                @endforeach
            </select>

            <div class="md-warning">
                <strong>⚠️ Warning:</strong> Manually dispatching will create a rescue mission and notify the provider immediately.
            </div>

            <div class="md-actions">
                <a href="{{ route('admin.rescue.index') }}" class="md-btn ghost">Cancel</a>
                <button type="submit" class="md-btn primary">Dispatch Now</button>
            </div>
        </form>
    </div>
</div>

@endsection
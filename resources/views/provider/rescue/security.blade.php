@extends('layouts.provider-rescue')

@section('title', 'Security & Permissions')
@section('page-title', 'Security & Permissions')
@section('page-sub', 'Access control and session security')

@section('content')

<div style="margin-bottom:16px">
    <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Security & Permissions</h2>
    <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Access control and session security</p>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid var(--border)">
        <div>
            <h4 style="font-size:13px;font-weight:700">Two-Factor Authentication</h4>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Required for all admin-level actions and SOS accept/confirm.</p>
        </div>
        <label class="duty-toggle" style="margin:0">
            <input type="checkbox" checked />
            <span class="slider"></span>
        </label>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid var(--border)">
        <div>
            <h4 style="font-size:13px;font-weight:700">Session Timeout</h4>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Auto-logout after 15 minutes of inactivity.</p>
        </div>
        <label class="duty-toggle" style="margin:0">
            <input type="checkbox" checked />
            <span class="slider"></span>
        </label>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid var(--border)">
        <div>
            <h4 style="font-size:13px;font-weight:700">IP Whitelist</h4>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Only allow operations from registered base IPs.</p>
        </div>
        <label class="duty-toggle" style="margin:0">
            <input type="checkbox" />
            <span class="slider"></span>
        </label>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid var(--border)">
        <div>
            <h4 style="font-size:13px;font-weight:700">SOS Siren</h4>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Audible alert for critical dispatch.</p>
        </div>
        <label class="duty-toggle" style="margin:0">
            <input type="checkbox" checked />
            <span class="slider"></span>
        </label>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px">
        <div>
            <h4 style="font-size:13px;font-weight:700">Auto-accept Critical</h4>
            <p style="font-size:11.5px;color:var(--text-muted);margin-top:2px">60s confirmation window still applies.</p>
        </div>
        <label class="duty-toggle" style="margin:0">
            <input type="checkbox" />
            <span class="slider"></span>
        </label>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
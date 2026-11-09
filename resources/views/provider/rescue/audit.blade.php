@extends('layouts.provider-rescue')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-sub', 'Immutable trail · who · what · when')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Audit Log</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Every action recorded — who, what, when. Immutable trail for compliance.</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="btn btn-ghost btn-sm"><i data-lucide="filter"></i> Filter</button>
        <button class="btn btn-ghost btn-sm"><i data-lucide="download"></i> Export</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="scroll-text"></i>
            <h2>Recent Events</h2>
            <span class="pill pill-gray">Today · 2 entries</span>
        </div>
    </div>
    <div>
        <div style="display:flex;gap:10px;padding:16px;border-bottom:1px solid var(--border)">
            <span style="font-size:11px;color:var(--text-light);font-family:'JetBrains Mono',monospace;font-weight:600;min-width:48px;padding-top:2px">14:32</span>
            <span style="width:7px;height:7px;border-radius:50%;margin-top:6px;background:var(--info)"></span>
            <div style="flex:1;font-size:12.5px;color:var(--text-muted);line-height:1.45">
                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border);margin-right:6px;text-transform:uppercase">mission</span>
                <span style="color:var(--text);font-weight:600">System</span> marked mission en route
            </div>
        </div>
        
        <div style="display:flex;gap:10px;padding:16px;border-bottom:1px solid var(--border)">
            <span style="font-size:11px;color:var(--text-light);font-family:'JetBrains Mono',monospace;font-weight:600;min-width:48px;padding-top:2px">14:28</span>
            <span style="width:7px;height:7px;border-radius:50%;margin-top:6px;background:var(--danger)"></span>
            <div style="flex:1;font-size:12.5px;color:var(--text-muted);line-height:1.45">
                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:var(--surface-3);color:var(--text-muted);border:1px solid var(--border);margin-right:6px;text-transform:uppercase">escalate</span>
                <span style="color:var(--text);font-weight:600">System</span> auto-escalated SOS alert
            </div>
        </div>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
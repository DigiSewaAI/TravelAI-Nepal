@extends('layouts.provider-rescue')

@section('title', 'Documents & Licenses')
@section('page-title', 'Documents & Licenses')
@section('page-sub', 'Compliance · admin verification required before going live')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Documents & Licenses</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Compliance · admin verification required before going live</p>
    </div>
    <button class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Upload Document</button>
</div>

<div class="card">
    <div style="display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid var(--border)">
        <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:var(--success-light);color:var(--success-dark)"><i data-lucide="file-text"></i></div>
        <div style="flex:1">
            <div style="font-size:13.5px;font-weight:700">Aviation Operating License</div>
            <div style="font-size:11.5px;color:var(--text-muted)">CAAN · Expires Dec 2026</div>
        </div>
        <span class="badge badge-green"><i data-lucide="check"></i> Verified</span>
    </div>
    
    <div style="display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid var(--border)">
        <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:var(--success-light);color:var(--success-dark)"><i data-lucide="file-text"></i></div>
        <div style="flex:1">
            <div style="font-size:13.5px;font-weight:700">Medical Certification</div>
            <div style="font-size:11.5px;color:var(--text-muted)">Ministry of Health · Valid</div>
        </div>
        <span class="badge badge-green"><i data-lucide="check"></i> Verified</span>
    </div>

    <div style="display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid var(--border)">
        <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:var(--warning-light);color:var(--warning-dark)"><i data-lucide="clock"></i></div>
        <div style="flex:1">
            <div style="font-size:13.5px;font-weight:700">Insurance Policy</div>
            <div style="font-size:11.5px;color:var(--text-muted)">Himalayan Assurance · Pending review</div>
        </div>
        <span class="badge badge-medium">Pending</span>
    </div>

    <div style="display:flex;align-items:center;gap:12px;padding:16px">
        <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:var(--danger-light);color:var(--danger)"><i data-lucide="alert-triangle"></i></div>
        <div style="flex:1">
            <div style="font-size:13.5px;font-weight:700">Maintenance Log — 9N-AMP</div>
            <div style="font-size:11.5px;color:var(--text-muted)">Overdue · Required before next flight</div>
        </div>
        <span class="badge badge-critical">Action needed</span>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
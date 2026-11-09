@extends('layouts.provider-rescue')

@section('title', 'Insurance Partners')
@section('page-title', 'Insurance Partners')
@section('page-sub', 'Accepted travel insurance providers')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Insurance Partners</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Accepted travel insurance providers</p>
    </div>
    <button class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Add Partner</button>
</div>

<div class="content-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
    {{-- Card 1 --}}
    <div class="card" style="padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div style="width:44px;height:44px;border-radius:12px;background:var(--primary-light);display:grid;place-items:center;color:var(--primary-dark)"><i data-lucide="shield-check"></i></div>
            <span class="badge badge-green">Active</span>
        </div>
        <div style="font-size:14px;font-weight:700">Himalayan Assurance</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Direct API · Auto-claim</div>
        <div style="margin-top:12px;border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px;font-size:11.5px;color:var(--text-muted)">
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="check" style="width:12px;height:12px"></i> 47 claims</span>
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="clock" style="width:12px;height:12px"></i> Avg 2.4 days</span>
        </div>
    </div>

    {{-- Card 2 --}}
    <div class="card" style="padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div style="width:44px;height:44px;border-radius:12px;background:var(--info-light);display:grid;place-items:center;color:var(--info-dark)"><i data-lucide="shield-check"></i></div>
            <span class="badge badge-green">Active</span>
        </div>
        <div style="font-size:14px;font-weight:700">Nepal Insurance Co.</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Manual claim submission</div>
        <div style="margin-top:12px;border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px;font-size:11.5px;color:var(--text-muted)">
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="check" style="width:12px;height:12px"></i> 21 claims</span>
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="clock" style="width:12px;height:12px"></i> Avg 4.1 days</span>
        </div>
    </div>

    {{-- Card 3 --}}
    <div class="card" style="padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div style="width:44px;height:44px;border-radius:12px;background:var(--warning-light);display:grid;place-items:center;color:var(--warning-dark)"><i data-lucide="shield-check"></i></div>
            <span class="badge badge-medium">Pending</span>
        </div>
        <div style="font-size:14px;font-weight:700">Global Rescue Intl.</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Integration in progress</div>
        <div style="margin-top:12px;border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px;font-size:11.5px;color:var(--text-muted)">
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="lock" style="width:12px;height:12px"></i> Awaiting contract</span>
            <span style="display:flex;align-items:center;gap:6px"><i data-lucide="clock" style="width:12px;height:12px"></i> ETA 2 weeks</span>
        </div>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
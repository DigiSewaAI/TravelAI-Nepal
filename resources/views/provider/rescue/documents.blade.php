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
    <button onclick="document.getElementById('docModal').classList.add('show')" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Upload Document
    </button>
</div>

<div class="card">
    @forelse(($rescueProvider->documents ?? []) as $doc)
        <div style="display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid var(--border)">
            <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;
                @if(($doc['status'] ?? '') === 'verified') background:var(--success-light);color:var(--success-dark); 
                @elseif(($doc['status'] ?? '') === 'pending') background:var(--warning-light);color:var(--warning-dark); 
                @else background:var(--danger-light);color:var(--danger); @endif">
                <i data-lucide="file-text"></i>
            </div>
            <div style="flex:1">
                <div style="font-size:13.5px;font-weight:700">{{ $doc['title'] ?? 'Untitled Document' }}</div>
                <div style="font-size:11.5px;color:var(--text-muted)">{{ $doc['issuer'] ?? '' }} · Expires {{ $doc['expiry'] ?? 'N/A' }}</div>
            </div>
            
            @if(($doc['status'] ?? '') === 'verified')
                <span class="badge badge-green"><i data-lucide="check"></i> Verified</span>
            @elseif(($doc['status'] ?? '') === 'pending')
                <span class="badge badge-medium"><i data-lucide="clock"></i> Pending</span>
            @else
                <span class="badge badge-critical"><i data-lucide="alert-triangle"></i> Action needed</span>
            @endif
        </div>
    @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted)">
            <i data-lucide="file-text" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No documents uploaded yet.</div>
        </div>
    @endforelse
</div>

{{-- Upload Document Modal --}}
<div class="modal-overlay" id="docModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="file-text"></i></div>
            <div>
                <div class="modal-title">Upload Document</div>
                <div class="modal-desc">Add a compliance or licensing document.</div>
            </div>
        </div>
        <form action="{{ route('provider.rescue.documents.store') }}" method="POST">
            @csrf
            <label>Document Title <span class="req">*</span></label>
            <input type="text" name="title" required placeholder="e.g. Aviation Operating License">

            <label>Issuer <span class="req">*</span></label>
            <input type="text" name="issuer" required placeholder="e.g. CAAN">

            <label>Expiry Date <span class="req">*</span></label>
            <input type="text" name="expiry" required placeholder="e.g. Dec 2026">

            <label>Status <span class="req">*</span></label>
            <select name="status" required>
                <option value="pending">Pending Review</option>
                <option value="verified">Verified</option>
                <option value="action_needed">Action Needed</option>
            </select>

            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('docModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Document</button>
            </div>
        </form>
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
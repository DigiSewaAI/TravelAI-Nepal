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
    <button onclick="document.getElementById('insModal').classList.add('show')" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Add Partner
    </button>
</div>

<div class="content-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
    @forelse(($rescueProvider->insurance_partners ?? []) as $partner)
        <div class="card" style="padding:16px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                <div style="width:44px;height:44px;border-radius:12px;background:var(--primary-light);display:grid;place-items:center;color:var(--primary-dark)">
                    <i data-lucide="shield-check"></i>
                </div>
                @if(($partner['status'] ?? '') === 'active')
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-medium">Pending</span>
                @endif
            </div>
            <div style="font-size:14px;font-weight:700">{{ $partner['name'] ?? 'Unknown Partner' }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">{{ $partner['type'] ?? '—' }}</div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted);">
            <i data-lucide="shield-check" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No insurance partners added yet.</div>
        </div>
    @endforelse
</div>

{{-- Add Insurance Modal --}}
<div class="modal-overlay" id="insModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="shield-check"></i></div>
            <div>
                <div class="modal-title">Add Insurance Partner</div>
                <div class="modal-desc">Connect a new insurance provider.</div>
            </div>
        </div>
        <form action="{{ route('provider.rescue.insurance.store') }}" method="POST">
            @csrf
            <label>Partner Name <span class="req">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Himalayan Assurance">

            <label>Integration Type <span class="req">*</span></label>
            <select name="type" required>
                <option value="Direct API">Direct API</option>
                <option value="Manual Submission">Manual Submission</option>
            </select>

            <label>Status <span class="req">*</span></label>
            <select name="status" required>
                <option value="active">Active</option>
                <option value="pending">Pending Integration</option>
            </select>

            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('insModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Partner</button>
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
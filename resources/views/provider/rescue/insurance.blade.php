@extends('layouts.provider-rescue')

@section('title', 'Insurance Partners')
@section('page-title', 'Insurance Partners')
@section('page-sub', 'Medical & evacuation insurance integrations')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Insurance Partners</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Medical & evacuation insurance integrations</p>
    </div>
    <button onclick="openAddIns()" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Add Partner
    </button>
</div>

<div class="content-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
    @forelse(($rescueProvider->insurance_partners ?? []) as $index => $partner)
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
            <div style="font-size:14px;font-weight:700">{{ $partner['name'] ?? '—' }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">{{ $partner['type'] ?? '—' }}</div>

            <div style="display:flex;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
                <button type="button" onclick='openEditIns({{ $index }}, @json($partner))' class="btn-link" style="color:var(--info)">
                    <i data-lucide="edit-2" style="width:12px;height:12px"></i> Edit
                </button>
                <form action="{{ route('provider.rescue.insurance.destroy', $index) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Remove {{ $partner['name'] ?? 'this partner' }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-link" style="color:var(--danger)">
                        <i data-lucide="trash-2" style="width:12px;height:12px"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--text-muted)">
            <i data-lucide="shield-check" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No insurance partners added yet.</div>
        </div>
    @endforelse
</div>

<div class="modal-overlay" id="insModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="shield-check"></i></div>
            <div><div class="modal-title" id="insModalTitle">Add Insurance Partner</div></div>
        </div>
        <form id="insForm" action="{{ route('provider.rescue.insurance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="insMethod" value="POST">
            <label>Partner Name <span class="req">*</span></label>
            <input type="text" name="name" id="insName" required>
            <label>Integration Type <span class="req">*</span></label>
            <select name="type" id="insType" required>
                <option value="Direct API">Direct API</option>
                <option value="Manual">Manual Submission</option>
            </select>
            <label>Status <span class="req">*</span></label>
            <select name="status" id="insStatus" required>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
            </select>
            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('insModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary" id="insSaveBtn">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
function openAddIns() {
    document.getElementById('insForm').reset();
    document.getElementById('insMethod').value = 'POST';
    document.getElementById('insForm').action = "{{ route('provider.rescue.insurance.store') }}";
    document.getElementById('insModalTitle').textContent = 'Add Insurance Partner';
    document.getElementById('insSaveBtn').textContent = 'Save';
    document.getElementById('insModal').classList.add('show');
}
function openEditIns(index, partner) {
    document.getElementById('insMethod').value = 'PUT';
    document.getElementById('insForm').action = '/provider/rescue/insurance/' + index;
    document.getElementById('insModalTitle').textContent = 'Edit Insurance Partner';
    document.getElementById('insSaveBtn').textContent = 'Update';
    document.getElementById('insName').value = partner.name || '';
    document.getElementById('insType').value = partner.type || 'Direct API';
    document.getElementById('insStatus').value = partner.status || 'active';
    document.getElementById('insModal').classList.add('show');
}
</script>
@endpush
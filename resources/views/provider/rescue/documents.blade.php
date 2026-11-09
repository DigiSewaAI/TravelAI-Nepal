@extends('layouts.provider-rescue')

@section('title', 'Documents & Licenses')
@section('page-title', 'Documents & Licenses')
@section('page-sub', 'Aviation licenses, certifications & regulatory documents')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Documents & Licenses</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Aviation licenses, certifications & regulatory documents</p>
    </div>
    <button onclick="openAddDoc()" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Upload Document
    </button>
</div>

{{-- Info banner: admin verification required --}}
<div style="background:var(--info-light);border:1px solid rgba(59,130,246,.3);color:var(--info-dark);padding:11px 14px;border-radius:10px;margin-bottom:14px;font-size:12.5px;font-weight:600;display:flex;align-items:center;gap:8px">
    <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0"></i>
    <span>All uploaded documents require <strong>admin verification</strong>. You can edit or replace files any time — status will reset to Pending.</span>
</div>

<div class="card">
    @forelse(($rescueProvider->documents ?? []) as $index => $doc)
        <div style="display:flex;align-items:center;gap:12px;padding:16px;border-bottom:1px solid var(--border);flex-wrap:wrap">
            <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;
                @if(($doc['status'] ?? '') === 'verified') background:var(--success-light);color:var(--success-dark);
                @elseif(($doc['status'] ?? '') === 'pending') background:var(--warning-light);color:var(--warning-dark);
                @elseif(($doc['status'] ?? '') === 'action_needed') background:var(--danger-light);color:var(--danger);
                @else background:var(--surface-3);color:var(--text-muted); @endif">
                <i data-lucide="file-text"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13.5px;font-weight:700">{{ $doc['title'] ?? '—' }}</div>
                <div style="font-size:11.5px;color:var(--text-muted)">{{ $doc['issuer'] ?? '—' }} · Expires {{ $doc['expiry'] ?? '—' }}</div>

                @if(!empty($doc['file_path']))
                    <a href="{{ route('provider.rescue.documents.download', $index) }}"
                       style="font-size:11.5px;color:var(--primary);font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-top:5px">
                        <i data-lucide="download" style="width:11px;height:11px"></i>
                        {{ $doc['original_name'] ?? 'Download' }}
                        @if(!empty($doc['file_size']))
                            · {{ number_format($doc['file_size'] / 1024, 1) }} KB
                        @endif
                    </a>
                @else
                    <div style="font-size:11px;color:var(--text-light);margin-top:5px;font-style:italic">No file attached</div>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                @if(($doc['status'] ?? '') === 'verified')
                    <span class="badge badge-green"><i data-lucide="check"></i> Verified</span>
                @elseif(($doc['status'] ?? '') === 'pending')
                    <span class="badge badge-medium"><i data-lucide="clock"></i> Pending Review</span>
                @elseif(($doc['status'] ?? '') === 'action_needed')
                    <span class="badge badge-critical">Action needed</span>
                @else
                    <span class="badge badge-gray">Unverified</span>
                @endif
                <button type="button" onclick='openEditDoc({{ $index }}, @json($doc))' class="btn-link" style="color:var(--info)">
                    <i data-lucide="edit-2" style="width:12px;height:12px"></i> Edit
                </button>
                <form action="{{ route('provider.rescue.documents.destroy', $index) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Delete {{ $doc['title'] ?? 'this document' }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-link" style="color:var(--danger)">
                        <i data-lucide="trash-2" style="width:12px;height:12px"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted)">
            <i data-lucide="file-text" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
            <div>No documents uploaded yet.</div>
        </div>
    @endforelse
</div>

{{-- Add/Edit Modal --}}
<div class="modal-overlay" id="docModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-icon info"><i data-lucide="file-text"></i></div>
            <div>
                <div class="modal-title" id="docModalTitle">Upload Document</div>
                <div class="modal-desc">After saving, admin will review and verify.</div>
            </div>
        </div>
        <form id="docForm" action="{{ route('provider.rescue.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="docMethod" value="POST">

            <label>Document Title <span class="req">*</span></label>
            <input type="text" name="title" id="docTitle" required placeholder="e.g. Aviation License">

            <label>Issuer <span class="req">*</span></label>
            <input type="text" name="issuer" id="docIssuer" required placeholder="e.g. CAAN">

            <label>Expiry Date <span class="req">*</span></label>
            <input type="text" name="expiry" id="docExpiry" required placeholder="e.g. Dec 2028">

            <label>Attach File <span style="font-size:10.5px;color:var(--text-muted);font-weight:500">(PDF, JPG, PNG — max 10MB)</span></label>
            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
            <div id="existingFileNote" style="display:none;font-size:11px;color:var(--text-muted);margin-top:6px"></div>

            <div style="background:var(--warning-light);color:var(--warning-dark);padding:10px 12px;border-radius:8px;font-size:11.5px;font-weight:600;margin-top:14px;display:flex;align-items:center;gap:6px">
                <i data-lucide="shield-alert" style="width:13px;height:13px;flex-shrink:0"></i>
                <span>Status is set by admin after review — cannot be self-set.</span>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="document.getElementById('docModal').classList.remove('show')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary" id="docSaveBtn">Save Document</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });

function openAddDoc() {
    document.getElementById('docForm').reset();
    document.getElementById('docMethod').value = 'POST';
    document.getElementById('docForm').action = "{{ route('provider.rescue.documents.store') }}";
    document.getElementById('docModalTitle').textContent = 'Upload Document';
    document.getElementById('docSaveBtn').textContent = 'Save Document';
    document.getElementById('existingFileNote').style.display = 'none';
    document.getElementById('docModal').classList.add('show');
}

function openEditDoc(index, doc) {
    document.getElementById('docMethod').value = 'PUT';
    document.getElementById('docForm').action = '/provider/rescue/documents/' + index;
    document.getElementById('docModalTitle').textContent = 'Edit Document';
    document.getElementById('docSaveBtn').textContent = 'Update Document';
    document.getElementById('docTitle').value = doc.title || '';
    document.getElementById('docIssuer').value = doc.issuer || '';
    document.getElementById('docExpiry').value = doc.expiry || '';

    const note = document.getElementById('existingFileNote');
    if (doc.original_name) {
        note.textContent = 'Current file: ' + doc.original_name + ' — leave blank to keep, or select new file to replace.';
        note.style.display = 'block';
    } else {
        note.textContent = 'No file attached yet.';
        note.style.display = 'block';
    }

    document.getElementById('docModal').classList.add('show');
}
</script>
@endpush
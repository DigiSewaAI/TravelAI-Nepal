@extends('layouts.admin')

@section('title', 'Document Verification Queue')
@section('page-title', 'Document Verification')

@section('content')

<style>
    .dv-wrap { max-width: 1200px; margin: 0 auto; }
    .dv-header { margin-bottom: 24px; }
    .dv-header h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
    .dv-header p { font-size: 13px; color: #64748b; margin: 4px 0 0; }
    .dv-tabs { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
    .dv-tab { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; color: #64748b; transition: 0.2s; }
    .dv-tab:hover { background: #f1f5f9; color: #0f172a; }
    .dv-tab.active { background: #0d9488; color: #fff; }
    .dv-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.06); overflow: hidden; }
    .dv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dv-table th { text-align: left; padding: 14px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .dv-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .dv-table tbody tr:last-child td { border-bottom: none; }
    .dv-table tbody tr:hover { background: #f8fafc; }
    .dv-badge { display: inline-flex; padding: 3px 9px; border-radius: 99px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; }
    .bg-pending { background: #fef3c7; color: #b45309; }
    .bg-verified { background: #d1fae5; color: #047857; }
    .bg-rejected { background: #fee2e2; color: #991b1b; }
    .dv-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: 0.2s; }
    .dv-btn.download { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .dv-btn.download:hover { background: #e2e8f0; }
    .dv-btn.approve { background: #0d9488; color: #fff; }
    .dv-btn.approve:hover { background: #0f766e; }
    .dv-btn.reject { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .dv-btn.reject:hover { background: #fca5a5; }
    .dv-empty { padding: 60px 20px; text-align: center; color: #94a3b8; font-size: 14px; }
    .dv-alert-success { background: #d1fae5; border: 1px solid #10b981; color: #047857; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
</style>

<div class="dv-wrap">
    
    <div class="dv-header">
        <h2>Document Verification Queue</h2>
        <p>Review and verify compliance documents uploaded by rescue providers.</p>
    </div>

    @if(session('success'))
        <div class="dv-alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="dv-tabs">
        <a href="?status=pending" class="dv-tab {{ $statusFilter === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="?status=verified" class="dv-tab {{ $statusFilter === 'verified' ? 'active' : '' }}">Verified</a>
        <a href="?status=action_needed" class="dv-tab {{ $statusFilter === 'action_needed' ? 'active' : '' }}">Rejected</a>
        <a href="?status=all" class="dv-tab {{ $statusFilter === 'all' ? 'active' : '' }}">All Documents</a>
    </div>

    <div class="dv-card">
        <div style="overflow-x: auto;">
            <table class="dv-table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Document Title</th>
                        <th>Issuer</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">{{ $doc['provider_name'] }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $doc['title'] }}</div>
                                @if($doc['uploaded_at'])
                                    <div style="font-size: 11px; color: #64748b;">Uploaded {{ \Carbon\Carbon::parse($doc['uploaded_at'])->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td style="color: #64748b;">{{ $doc['issuer'] }}</td>
                            <td style="color: #64748b;">{{ $doc['expiry'] }}</td>
                            <td>
                                @if($doc['status'] === 'pending')
                                    <span class="dv-badge bg-pending">Pending</span>
                                @elseif($doc['status'] === 'verified')
                                    <span class="dv-badge bg-verified">Verified</span>
                                @else
                                    <span class="dv-badge bg-rejected">Action Needed</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                    @if($doc['has_file'])
                                        <a href="{{ route('admin.rescue.documents.download', [$doc['provider_id'], $doc['doc_index']]) }}" class="dv-btn download">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                            Download
                                        </a>
                                    @endif

                                    @if($doc['status'] === 'pending')
                                        <form action="{{ route('admin.rescue.documents.verify', [$doc['provider_id'], $doc['doc_index']]) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="dv-btn approve" onclick="return confirm('Approve this document?')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Approve
                                            </button>
                                        </form>

                                        <button type="button" class="dv-btn reject" onclick="openRejectModal({{ $doc['provider_id'] }}, {{ $doc['doc_index'] }}, '{{ $doc['title'] }}')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="dv-empty">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin: 0 auto 12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                                <div>No documents found for this filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; padding:24px; width:100%; max-width:500px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0; font-size:18px; font-weight:800; color:#0f172a;">Reject Document</h3>
        <p style="font-size:13px; color:#64748b; margin-bottom:16px;" id="rejectDocTitle"></p>
        
        <form id="rejectForm" method="POST" action="">
            @csrf
            <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px; color:#0f172a;">Reason for Rejection <span style="color:red">*</span></label>
            <textarea name="rejection_reason" required maxlength="500" rows="3" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:13px; resize:vertical; margin-bottom:20px;" placeholder="e.g., License number is unclear, or document expired."></textarea>
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" style="padding:8px 16px; border-radius:8px; font-weight:700; font-size:13px; background:#f1f5f9; border:1px solid #e2e8f0; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px; border-radius:8px; font-weight:700; font-size:13px; background:#dc2626; color:#fff; border:none; cursor:pointer;">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(providerId, index, title) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const titleEl = document.getElementById('rejectDocTitle');
        
        // Action URL: /admin/rescue/documents/{providerId}/{index}/reject
        form.action = `/admin/rescue/documents/${providerId}/${index}/reject`;
        titleEl.innerText = `Document: "${title}"`;
        
        modal.style.display = 'flex';
    }
</script>

@endsection
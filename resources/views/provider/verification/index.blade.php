@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.verification_page_title'))
@section('header', __('messages.verification_header'))

@section('content')
<style>
  .vf-wrap{max-width:960px;margin:0 auto;padding:8px 0}
  .vf-card{
    background:var(--card,#fff);
    border:1px solid var(--line,#e5e7eb);
    border-radius:var(--radius,16px);
    padding:24px;margin-bottom:20px;
    transition:.25s;
  }
  .vf-card:hover{box-shadow:0 10px 24px rgba(15,23,42,.06)}
  .vf-card-head{display:flex;align-items:center;gap:12px;margin-bottom:18px}
  .vf-card-head .vf-ic{
    width:40px;height:40px;border-radius:12px;
    display:grid;place-items:center;font-size:18px;flex-shrink:0;
    background:rgba(59,130,246,.1);color:#3b82f6;
  }
  .vf-card-head h2{
    font-size:16px;font-weight:800;letter-spacing:-.3px;
    color:var(--text,#0f172a);line-height:1.3;margin:0;
  }
  .vf-card-head p{font-size:12px;color:var(--muted,#64748b);font-weight:600;margin-top:2px}

  /* Status display */
  .vf-status-row{
    display:flex;align-items:center;gap:16px;flex-wrap:wrap;
    padding:18px;border-radius:14px;
    background:linear-gradient(135deg,#eff6ff,#f0f9ff);
    border:1px solid #dbeafe;
  }
  .vf-status-row.verified{background:linear-gradient(135deg,#ecfdf5,#f0fdf4);border-color:#a7f3d0}
  .vf-status-row.rejected{background:linear-gradient(135deg,#fef2f2,#fff5f5);border-color:#fecaca}
  .vf-status-row.under_review{background:linear-gradient(135deg,#fffbeb,#fefce8);border-color:#fde68a}
  .vf-status-icon{
    width:56px;height:56px;border-radius:14px;flex-shrink:0;
    display:grid;place-items:center;font-size:26px;background:#fff;
    box-shadow:0 4px 12px rgba(15,23,42,.08);
  }
  .vf-status-info{flex:1;min-width:180px}
  .vf-status-label{
    font-size:10.5px;font-weight:800;color:var(--muted,#64748b);
    text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;
  }
  .vf-status-badge{
    display:inline-flex;align-items:center;gap:6px;
    font-size:12px;font-weight:800;padding:5px 12px;border-radius:20px;letter-spacing:.2px;
  }
  .vf-status-badge.verified{background:#d1fae5;color:#047857}
  .vf-status-badge.rejected{background:#fee2e2;color:#b91c1c}
  .vf-status-badge.under_review{background:#fef3c7;color:#b45309}
  .vf-status-badge.pending{background:#e5e7eb;color:#4b5563}
  .vf-status-msg{font-size:12.5px;font-weight:500;color:#475569;margin-top:8px;line-height:1.55}

  /* Upload form */
  .vf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  @media (max-width:640px){ .vf-grid{grid-template-columns:1fr} }
  .vf-field label{
    display:block;font-size:11px;font-weight:800;
    color:var(--muted,#64748b);text-transform:uppercase;
    letter-spacing:.6px;margin-bottom:7px;
  }
  .vf-field select, .vf-field input[type=file]{
    width:100%;padding:11px 13px;border-radius:11px;
    border:1px solid var(--line,#e5e7eb);
    background:#fbfcff;font-family:inherit;font-size:13px;
    font-weight:600;color:var(--text,#0f172a);outline:none;transition:.2s;
  }
  .vf-field select:focus, .vf-field input[type=file]:focus{
    border-color:#3b82f6;background:#fff;
    box-shadow:0 0 0 4px rgba(59,130,246,.12);
  }
  .vf-hint{font-size:11px;color:var(--muted,#64748b);font-weight:600;margin-top:6px}
  .vf-btn{
    display:inline-flex;align-items:center;gap:8px;
    padding:11px 20px;border:none;border-radius:11px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;
    font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;
    box-shadow:0 6px 16px rgba(59,130,246,.3);transition:.2s;
    margin-top:16px;
  }
  .vf-btn:hover{filter:brightness(1.08);transform:translateY(-1px)}

  /* Documents list */
  .vf-doc-list{display:flex;flex-direction:column;gap:10px}
  .vf-doc{
    display:flex;align-items:center;gap:14px;
    padding:14px;border-radius:13px;
    border:1px solid var(--line,#e5e7eb);background:#fbfcff;
    transition:.2s;
  }
  .vf-doc:hover{border-color:#bfdbfe;box-shadow:0 4px 14px rgba(59,130,246,.08)}
  .vf-doc-icon{
    width:44px;height:44px;border-radius:11px;flex-shrink:0;
    display:grid;place-items:center;font-size:20px;background:#eef2ff;
  }
  .vf-doc-info{flex:1;min-width:0}
  .vf-doc-title{
    font-size:13px;font-weight:800;color:var(--text,#0f172a);
    margin-bottom:5px;line-height:1.3;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  }
  .vf-doc-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .vf-doc-date{font-size:11px;color:var(--muted,#64748b);font-weight:600}
  .vf-doc-badge{
    font-size:10px;font-weight:800;padding:3px 9px;border-radius:20px;letter-spacing:.2px;
  }
  .vf-doc-badge.approved{background:#d1fae5;color:#047857}
  .vf-doc-badge.rejected{background:#fee2e2;color:#b91c1c}
  .vf-doc-badge.pending{background:#fef3c7;color:#b45309}
  .vf-doc-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
  .vf-doc-btn{
    width:34px;height:34px;border-radius:9px;border:none;
    background:#fff;border:1px solid var(--line,#e5e7eb);
    display:grid;place-items:center;cursor:pointer;transition:.18s;
    text-decoration:none;
  }
  .vf-doc-btn.view{color:#3b82f6}
  .vf-doc-btn.view:hover{background:#eff6ff;border-color:#bfdbfe}
  .vf-doc-btn.del{color:#dc2626}
  .vf-doc-btn.del:hover{background:#fef2f2;border-color:#fecaca}
  .vf-doc-btn svg{width:15px;height:15px;stroke-width:2.2}

  /* Empty state */
  .vf-empty{
    text-align:center;padding:44px 20px;
    color:var(--muted,#64748b);font-size:13px;font-weight:600;
  }
  .vf-empty .em-ic{font-size:44px;margin-bottom:10px;opacity:.5}
  .vf-empty p{margin-top:4px}
</style>

<div class="vf-wrap">

  {{-- ============ STATUS CARD ============ --}}
  <div class="vf-card">
    <div class="vf-card-head">
      <div class="vf-ic">🛡️</div>
      <div>
        <h2>{{ __('messages.verification_status') }}</h2>
        <p>{{ __('messages.verification_page_title') }}</p>
      </div>
    </div>

    @php
      $statusClass = in_array($provider->verification_status, ['verified','rejected','under_review']) 
                      ? $provider->verification_status 
                      : 'pending';
      $iconMap = [
        'verified'     => '✅',
        'rejected'     => '❌',
        'under_review' => '🔍',
        'pending'      => '⏳',
      ];
      $statusLabelMap = [
        'verified'     => __('messages.verified'),
        'rejected'     => __('messages.rejected'),
        'under_review' => __('messages.under_review'),
        'pending'      => __('messages.pending_verification'),
      ];
      $msgMap = [
        'verified'     => __('messages.verified_message'),
        'pending'      => __('messages.pending_message'),
        'under_review' => __('messages.under_review_message'),
        'rejected'     => __('messages.rejected_message'),
      ];
      $currentIcon  = $iconMap[$statusClass]  ?? '⏳';
      $currentLabel = $statusLabelMap[$statusClass] ?? '';
      $currentMsg   = $msgMap[$statusClass]   ?? '';
    @endphp

    <div class="vf-status-row {{ $statusClass }}">
      <div class="vf-status-icon">{{ $currentIcon }}</div>
      <div class="vf-status-info">
        <div class="vf-status-label">{{ __('messages.status_label') }}</div>
        <span class="vf-status-badge {{ $statusClass }}">{{ $currentLabel }}</span>
        @if($currentMsg)
          <p class="vf-status-msg">{{ $currentMsg }}</p>
        @endif
      </div>
    </div>
  </div>

  {{-- ============ UPLOAD DOCUMENTS ============ --}}
  <div class="vf-card">
    <div class="vf-card-head">
      <div class="vf-ic" style="background:#eff6ff;color:#2563eb">📤</div>
      <div>
        <h2>{{ __('messages.upload_documents') }}</h2>
        <p>{{ __('messages.file_hint') }}</p>
      </div>
    </div>

    <form method="POST" action="{{ route('provider.verification.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="vf-grid">
        <div class="vf-field">
          <label>{{ __('messages.document_type') }}</label>
          <select name="type">
            <option value="business_registration">{{ __('messages.business_registration') }}</option>
            <option value="tourism_license">{{ __('messages.tourism_license') }}</option>
            <option value="guide_license">{{ __('messages.guide_license') }}</option>
            <option value="id_card">{{ __('messages.id_card') }}</option>
            <option value="other">{{ __('messages.other_doc') }}</option>
          </select>
        </div>
        <div class="vf-field">
          <label>{{ __('messages.upload_file') }}</label>
          <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
          <p class="vf-hint">{{ __('messages.file_hint') }}</p>
        </div>
      </div>
      <button type="submit" class="vf-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;stroke-width:2.4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
        {{ __('messages.upload_document_btn') }}
      </button>
    </form>
  </div>

  {{-- ============ DOCUMENTS LIST ============ --}}
  <div class="vf-card">
    <div class="vf-card-head">
      <div class="vf-ic" style="background:#f0fdf4;color:#059669">📄</div>
      <div>
        <h2>{{ __('messages.uploaded_documents') }}</h2>
        <p>{{ $documents && $documents->count() > 0 ? $documents->count() . ' ' . __('messages.documents_uploaded') : __('messages.no_documents') }}</p>
      </div>
    </div>

    @if($documents && $documents->count() > 0)
      <div class="vf-doc-list">
        @foreach($documents as $doc)
          @php
            $docIcon = match($doc->type) {
              'business_registration' => '📋',
              'tourism_license'       => '🏔️',
              'guide_license'         => '🎫',
              'id_card'               => '🪪',
              default                 => '📄',
            };
            $docLabel = match($doc->type) {
              'business_registration' => __('messages.business_registration'),
              'tourism_license'       => __('messages.tourism_license'),
              'guide_license'         => __('messages.guide_license'),
              'id_card'               => __('messages.id_card'),
              default                 => __('messages.other_doc'),
            };
            $docStatus = in_array($doc->status, ['approved','rejected']) ? $doc->status : 'pending';
            $docStatusLabel = match($docStatus) {
              'approved' => __('messages.approved'),
              'rejected' => __('messages.rejected'),
              default    => __('messages.pending'),
            };
          @endphp
          <div class="vf-doc">
            <div class="vf-doc-icon">{{ $docIcon }}</div>
            <div class="vf-doc-info">
              <div class="vf-doc-title">{{ $docLabel }}</div>
              <div class="vf-doc-meta">
                <span class="vf-doc-badge {{ $docStatus }}">{{ $docStatusLabel }}</span>
                <span class="vf-doc-date">📅 {{ $doc->created_at->format('M d, Y') }}</span>
              </div>
            </div>
            <div class="vf-doc-actions">
              <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="vf-doc-btn view" title="{{ __('messages.view') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <form method="POST" action="{{ route('provider.verification.destroy', $doc) }}" style="margin:0">
                @csrf
                @method('DELETE')
                <button type="submit" class="vf-doc-btn del" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="vf-empty">
        <div class="em-ic">📂</div>
        <p>{{ __('messages.no_documents') }}</p>
      </div>
    @endif
  </div>

</div>
@endsection
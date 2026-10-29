@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.pm_page_title'))
@section('header', __('messages.pm_header'))
@section('content')
<style>
.pm-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:20px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3)}
.pm-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.pm-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px;color:#fff;position:relative;z-index:1}
.pm-hero p{font-size:13px;margin:0;opacity:.92;color:#fff;position:relative;z-index:1}
.pm-alert{display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:18px}
.pm-alert.success{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981);border-left:4px solid var(--green,#10b981)}
.pm-alert.error{background:var(--red-soft,#fdecec);color:var(--red,#ef4444);border-left:4px solid var(--red,#ef4444)}
.pm-alert.info{background:var(--blue-soft,#e9f1fe);color:var(--accent,#2563eb);border-left:4px solid var(--accent,#2563eb)}
.pm-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;overflow:hidden;box-shadow:0 4px 24px -8px rgba(15,23,42,.08);margin-bottom:20px}
.pm-card-head{padding:20px 24px;border-bottom:1px solid var(--line,#e8edf5);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:var(--bg,#f8fafc)}
.pm-card-head h2{font-size:16px;font-weight:800;color:var(--text,#0f172a);margin:0;display:flex;align-items:center;gap:10px}
.pm-card-head .flag{font-size:22px}
.pm-add{display:inline-flex;align-items:center;gap:7px;background:var(--accent,#2563eb);color:#fff;padding:10px 18px;border-radius:10px;font-size:12.5px;font-weight:800;border:none;cursor:pointer;font-family:inherit;box-shadow:0 6px 18px -6px var(--accent,#2563eb);transition:.2s}
.pm-add:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-1px)}
.pm-body{padding:20px 24px}
.pm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
.pm-item{background:var(--bg,#f8fafc);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:16px;transition:.2s}
.pm-item.inactive{opacity:.6}
.pm-item:hover{border-color:var(--accent,#2563eb);box-shadow:0 6px 20px -8px rgba(15,23,42,.12)}
.pm-item-head{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}
.pm-item-left{display:flex;gap:12px;align-items:flex-start;flex:1;min-width:0}
.pm-item-icon{width:44px;height:44px;border-radius:11px;background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb);display:grid;place-items:center;font-size:18px;flex-shrink:0}
.pm-item-text{flex:1;min-width:0}
.pm-item-name{font-size:14px;font-weight:800;color:var(--text,#0f172a);margin:0 0 4px;line-height:1.3}
.pm-item-sub{font-size:11.5px;color:var(--muted,#64748b);font-weight:600;margin:0;line-height:1.4;word-break:break-word}
.pm-item-qr{font-size:11px;color:var(--accent,#2563eb);text-decoration:none;font-weight:700;margin-top:4px;display:inline-flex;align-items:center;gap:4px}
.pm-item-actions{display:flex;gap:4px;flex-shrink:0}
.pm-icon-btn{background:none;border:1px solid var(--line,#e8edf5);width:34px;height:34px;border-radius:9px;cursor:pointer;color:var(--muted,#64748b);font-size:13px;transition:.2s;display:grid;place-items:center}
.pm-icon-btn:hover{border-color:var(--accent,#2563eb);color:var(--accent,#2563eb);background:var(--accent-soft,rgba(37,99,235,.05))}
.pm-icon-btn.toggle-on{color:var(--green,#10b981);border-color:var(--green-soft,#e7f8f1);background:var(--green-soft,#e7f8f1)}
.pm-icon-btn.toggle-on:hover{color:#fff;background:var(--green,#10b981);border-color:var(--green,#10b981)}
.pm-icon-btn.danger:hover{border-color:var(--red,#ef4444);color:var(--red,#ef4444);background:var(--red-soft,#fdecec)}
.pm-empty{padding:52px 20px;text-align:center;color:var(--muted,#64748b)}
.pm-empty-ic{width:72px;height:72px;border-radius:50%;background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb);display:inline-grid;place-items:center;font-size:28px;margin-bottom:16px}
.pm-empty p{font-size:13px;margin:0;font-weight:600}
.pm-modal{position:fixed;inset:0;background:rgba(15,23,42,.7);z-index:9999;display:none;align-items:flex-start;justify-content:center;overflow-y:auto;padding:20px;backdrop-filter:blur(4px)}
.pm-modal.open{display:flex}
.pm-modal-box{background:var(--card,#fff);border-radius:20px;width:100%;max-width:560px;box-shadow:0 30px 80px -20px rgba(0,0,0,.4);margin:auto}
.pm-modal-head{padding:22px 26px;border-bottom:1px solid var(--line,#e8edf5);display:flex;justify-content:space-between;align-items:center}
.pm-modal-head h3{font-size:18px;font-weight:800;color:var(--text,#0f172a);margin:0}
.pm-modal-close{background:none;border:none;font-size:26px;cursor:pointer;color:var(--muted,#64748b);line-height:1;font-family:inherit}
.pm-modal-close:hover{color:var(--text,#0f172a)}
.pm-form{padding:22px 26px;max-height:calc(90vh - 80px);overflow-y:auto}
.pm-field{margin-bottom:14px}
.pm-field label{display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);margin-bottom:6px}
.pm-field input,.pm-field select,.pm-field textarea{width:100%;padding:10px 14px;border:1px solid var(--line,#e8edf5);border-radius:10px;background:var(--card,#fff);color:var(--text,#0f172a);font-family:inherit;font-size:13px;transition:.2s}
.pm-field input:focus,.pm-field select:focus,.pm-field textarea:focus{outline:none;border-color:var(--accent,#2563eb);box-shadow:0 0 0 3px var(--accent-soft,rgba(37,99,235,.12))}
.pm-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.pm-modal-actions{padding:18px 26px;border-top:1px solid var(--line,#e8edf5);display:flex;justify-content:flex-end;gap:10px;background:var(--bg,#f8fafc)}
.pm-btn{padding:10px 22px;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit;transition:.2s;border:none}
.pm-btn.cancel{background:var(--bg,#f4f6fb);color:var(--muted,#64748b);border:1px solid var(--line,#e8edf5)}
.pm-btn.cancel:hover{background:var(--line,#e8edf5)}
.pm-btn.save{background:var(--accent,#2563eb);color:#fff;box-shadow:0 6px 18px -6px var(--accent,#2563eb)}
.pm-btn.save:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-1px)}
</style>

<div>
  <div class="pm-hero">
    <h1><i class="fas fa-wallet"></i> {{ __('messages.pm_page_title') }}</h1>
    <p>{{ __('messages.pm_header') }}</p>
  </div>

  @if(session('success'))
    <div class="pm-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="pm-alert error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
  @endif

  <div class="pm-alert info">
    <i class="fas fa-info-circle" style="font-size:18px"></i>
    <span>{{ __('messages.pm_disclaimer') }}</span>
  </div>

  @php
    $sections = [
      ['key' => 'domestic',      'flag' => '🇳🇵', 'title' => __('messages.pm_domestic_section'),      'add' => __('messages.pm_add_domestic'),      'items' => $domestic],
      ['key' => 'international', 'flag' => '🌍', 'title' => __('messages.pm_international_section'), 'add' => __('messages.pm_add_international'), 'items' => $international],
    ];
  @endphp

  @foreach($sections as $s)
    <div class="pm-card">
      <div class="pm-card-head">
        <h2><span class="flag">{{ $s['flag'] }}</span> {{ $s['title'] }}</h2>
        <button type="button" onclick="openPmModal('{{ $s['key'] }}')" class="pm-add">
          <i class="fas fa-plus"></i> {{ $s['add'] }}
        </button>
      </div>
      <div class="pm-body">
        @if($s['items']->isEmpty())
          <div class="pm-empty">
            <div class="pm-empty-ic"><i class="fas fa-inbox"></i></div>
            <p>{{ __('messages.pm_empty') }}</p>
          </div>
        @else
          <div class="pm-grid">
            @foreach($s['items'] as $m)
              @php
                $__icon = [
                  'bank' => 'university', 'esewa' => 'mobile-alt', 'khalti' => 'wallet',
                  'cash' => 'money-bill-wave', 'paypal' => 'paypal', 'wise' => 'exchange-alt',
                  'international_bank' => 'globe',
                ][$m->type] ?? 'credit-card';
              @endphp
              <div class="pm-item {{ $m->is_active ? '' : 'inactive' }}">
                <div class="pm-item-head">
                  <div class="pm-item-left">
                    <div class="pm-item-icon"><i class="fas fa-{{ $__icon }}"></i></div>
                    <div class="pm-item-text">
                      <p class="pm-item-name">{{ $m->label ?: __('messages.pm_type_' . $m->type) }}</p>
                      <p class="pm-item-sub">{{ $m->account_number ?: $m->identifier ?: $m->account_name ?: '—' }}</p>
                      @if($m->qr_image_path)
                        <a href="{{ Storage::url($m->qr_image_path) }}" target="_blank" class="pm-item-qr">
                          <i class="fas fa-qrcode"></i> QR
                        </a>
                      @endif
                    </div>
                  </div>
                  <div class="pm-item-actions">
                    <form method="POST" action="{{ route('provider.settings.payment-methods.toggle', $m->id) }}" style="display:inline">
                      @csrf
                      <button type="submit" class="pm-icon-btn {{ $m->is_active ? 'toggle-on' : '' }}" title="{{ __('messages.pm_toggle_active') }}">
                        <i class="fas fa-{{ $m->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                      </button>
                    </form>
                    <button type="button"
                      data-pm-edit="{{ base64_encode(json_encode($m->only(['id','type','label','account_name','account_number','identifier','bank_name','swift_code','currency','sort_order','instructions','qr_image_path']))) }}"
                      onclick="openPmEdit(this)"
                      class="pm-icon-btn" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('provider.settings.payment-methods.destroy', $m->id) }}"
                      onsubmit="return confirm('{{ __('messages.pm_confirm_delete') }}')" style="display:inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="pm-icon-btn danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>

<div id="pmModal" class="pm-modal">
  <div class="pm-modal-box">
    <div class="pm-modal-head">
      <h3 id="pmModalTitle">{{ __('messages.pm_add_method') }}</h3>
      <button type="button" onclick="closePmModal()" class="pm-modal-close">&times;</button>
    </div>
    <form id="pmForm" method="POST" enctype="multipart/form-data" action="{{ route('provider.settings.payment-methods.store') }}">
      @csrf
      <input type="hidden" name="_method" id="pmMethod" value="POST">
      <div class="pm-form">
        <div class="pm-field">
          <label>{{ __('messages.pm_type') }} *</label>
          <select name="type" id="pmType" required>
            <optgroup label="{{ __('messages.pm_domestic_section') }}">
              <option value="bank">{{ __('messages.pm_type_bank') }}</option>
              <option value="esewa">{{ __('messages.pm_type_esewa') }}</option>
              <option value="khalti">{{ __('messages.pm_type_khalti') }}</option>
              <option value="cash">{{ __('messages.pm_type_cash') }}</option>
            </optgroup>
            <optgroup label="{{ __('messages.pm_international_section') }}">
              <option value="paypal">{{ __('messages.pm_type_paypal') }}</option>
              <option value="wise">{{ __('messages.pm_type_wise') }}</option>
              <option value="international_bank">{{ __('messages.pm_type_international_bank') }}</option>
            </optgroup>
          </select>
        </div>
        <div class="pm-field">
          <label>{{ __('messages.pm_label') }}</label>
          <input type="text" name="label" id="pmLabel" maxlength="100" placeholder="e.g. NIC Asia Bank">
        </div>
        <div class="pm-row">
          <div class="pm-field">
            <label>{{ __('messages.pm_account_name') }}</label>
            <input type="text" name="account_name" id="pmAccountName" maxlength="150">
          </div>
          <div class="pm-field">
            <label>{{ __('messages.pm_account_number') }}</label>
            <input type="text" name="account_number" id="pmAccountNumber" maxlength="100">
          </div>
        </div>
        <div class="pm-field">
          <label>{{ __('messages.pm_identifier') }}</label>
          <input type="text" name="identifier" id="pmIdentifier" maxlength="150">
        </div>
        <div class="pm-row">
          <div class="pm-field">
            <label>{{ __('messages.pm_bank_name') }}</label>
            <input type="text" name="bank_name" id="pmBankName" maxlength="150">
          </div>
          <div class="pm-field">
            <label>{{ __('messages.pm_swift_code') }}</label>
            <input type="text" name="swift_code" id="pmSwiftCode" maxlength="30">
          </div>
        </div>
        <div class="pm-row">
          <div class="pm-field">
            <label>{{ __('messages.pm_currency') }}</label>
            <input type="text" name="currency" id="pmCurrency" maxlength="3" value="NPR">
          </div>
          <div class="pm-field">
            <label>{{ __('messages.pm_sort_order') }}</label>
            <input type="number" name="sort_order" id="pmSortOrder" min="0" max="999" value="0">
          </div>
        </div>
        <div class="pm-field">
          <label>{{ __('messages.pm_qr_image') }}</label>
          <input type="file" name="qr_image" accept="image/png,image/jpeg">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">PNG / JPG, max 2MB</p>
          <div id="pmQrPreview" style="margin-top:8px;display:none">
            <img id="pmQrPreviewImg" src="" alt="QR" style="width:96px;height:96px;border:1px solid var(--line);border-radius:8px;object-fit:contain">
          </div>
        </div>
        <div class="pm-field">
          <label>{{ __('messages.pm_instructions') }}</label>
          <textarea name="instructions" id="pmInstructions" rows="2" maxlength="1000"></textarea>
        </div>
      </div>
      <div class="pm-modal-actions">
        <button type="button" onclick="closePmModal()" class="pm-btn cancel">{{ __('messages.pm_cancel') }}</button>
        <button type="submit" class="pm-btn save">{{ __('messages.pm_save') }}</button>
      </div>
    </form>
  </div>
</div>

<script>
const PM_BASE  = '{{ route('provider.settings.payment-methods.store') }}';
const PM_INDEX = '{{ route('provider.settings.payment-methods.index') }}';

function openPmModal(context) {
    const f = document.getElementById('pmForm');
    f.reset();
    f.action = PM_BASE;
    document.getElementById('pmMethod').value = 'POST';
    document.getElementById('pmModalTitle').textContent = context === 'domestic'
        ? '{{ __('messages.pm_add_domestic') }}'
        : '{{ __('messages.pm_add_international') }}';
    document.getElementById('pmType').value = context === 'domestic' ? 'bank' : 'paypal';
    document.getElementById('pmCurrency').value = 'NPR';
    document.getElementById('pmSortOrder').value = 0;
    document.getElementById('pmQrPreview').style.display = 'none';
    document.getElementById('pmModal').classList.add('open');
}

function openPmEdit(btn) {
    const m = JSON.parse(atob(btn.dataset.pmEdit));
    const f = document.getElementById('pmForm');
    f.reset();
    f.action = PM_INDEX + '/' + m.id;
    document.getElementById('pmMethod').value = 'PUT';
    document.getElementById('pmModalTitle').textContent = '{{ __('messages.pm_edit_method') }}';
    document.getElementById('pmType').value          = m.type || 'bank';
    document.getElementById('pmLabel').value         = m.label || '';
    document.getElementById('pmAccountName').value   = m.account_name || '';
    document.getElementById('pmAccountNumber').value = m.account_number || '';
    document.getElementById('pmIdentifier').value    = m.identifier || '';
    document.getElementById('pmBankName').value      = m.bank_name || '';
    document.getElementById('pmSwiftCode').value     = m.swift_code || '';
    document.getElementById('pmCurrency').value      = m.currency || 'NPR';
    document.getElementById('pmSortOrder').value     = m.sort_order || 0;
    document.getElementById('pmInstructions').value  = m.instructions || '';
    if (m.qr_image_path) {
        document.getElementById('pmQrPreviewImg').src = '/storage/' + m.qr_image_path;
        document.getElementById('pmQrPreview').style.display = 'block';
    } else {
        document.getElementById('pmQrPreview').style.display = 'none';
    }
    document.getElementById('pmModal').classList.add('open');
}

function closePmModal() {
    document.getElementById('pmModal').classList.remove('open');
}
</script>
@endsection
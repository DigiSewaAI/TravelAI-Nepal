@extends($provider_layout ?? 'layouts.provider-money-changer')
@section('title', __('messages.nav_exchange_rates'))
@section('page-title', __('messages.nav_exchange_rates'))
@section('page-subtitle', __('messages.mc_rates_subtitle'))

@section('content')
<style>
  .mc-hero{background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .mc-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .mc-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .mc-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .mc-btn-add{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.96);color:var(--accent-d,#059669);padding:11px 18px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;position:relative;z-index:1;box-shadow:0 4px 14px rgba(0,0,0,.15);}
  .mc-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:768px){.mc-stats{grid-template-columns:1fr;}}
  .mc-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;}
  .mc-stat-ic{width:42px;height:42px;border-radius:12px;background:var(--green-soft,#e6f7f1);color:var(--accent,#10b981);display:grid;place-items:center;flex-shrink:0;}
  .mc-stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);font-weight:800;}
  .mc-stat-val{font-size:20px;font-weight:800;color:var(--text,#0f172a);margin-top:2px;}
  .mc-tbl-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;overflow:hidden;}
  .mc-tbl{width:100%;border-collapse:collapse;min-width:700px;}
  .mc-tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#64748b);font-weight:800;padding:14px 18px;background:var(--bg,#f4f6fb);border-bottom:1px solid var(--line,#e8edf5);}
  .mc-tbl td{padding:16px 18px;font-size:13px;font-weight:600;color:var(--text,#0f172a);border-bottom:1px solid var(--line,#e8edf5);vertical-align:middle;}
  .mc-tbl tbody tr:hover{background:var(--green-soft,#e6f7f1);}
  .mc-tbl tbody tr:last-child td{border-bottom:none;}
  .mc-flag{font-size:20px;margin-right:6px;}
  .mc-cc{font-weight:800;color:var(--accent-d,#059669);}
  .mc-pill{display:inline-flex;align-items:center;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;}
  .p-on{background:var(--green-soft,#e6f7f1);color:var(--green,#10b981);}
  .p-off{background:var(--red-soft,#fdeaea);color:var(--red,#ef4444);}
  .mc-del{background:var(--red-soft,#fdeaea);color:var(--red,#ef4444);border:none;width:34px;height:34px;border-radius:8px;cursor:pointer;transition:.15s;}
  .mc-del:hover{background:var(--red,#ef4444);color:#fff;}
  .mc-empty{text-align:center;padding:48px 20px;color:var(--muted,#64748b);}
</style>

<div class="mc-hero">
  <div>
    <h1>💱 {{ __('messages.nav_exchange_rates') }}</h1>
    <p>{{ __('messages.mc_rates_subtitle') }}</p>
  </div>
  <button type="button" onclick="openRateModal()" class="mc-btn-add" style="border:none;cursor:pointer;font-family:inherit;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
    {{ __('messages.mc_add_rate_btn') }}
  </button>
</div>

<div class="mc-stats">
  <div class="mc-stat"><div class="mc-stat-ic">💱</div><div><div class="mc-stat-lbl">{{ __('messages.total_rates') }}</div><div class="mc-stat-val">{{ $rates->count() }}</div></div></div>
  <div class="mc-stat"><div class="mc-stat-ic">✅</div><div><div class="mc-stat-lbl">{{ __('messages.active_rates') }}</div><div class="mc-stat-val">{{ $activeCount }}</div></div></div>
  <div class="mc-stat"><div class="mc-stat-ic">🌍</div><div><div class="mc-stat-lbl">{{ __('messages.currencies') }}</div><div class="mc-stat-val">{{ $currencyCount }}</div></div></div>
</div>

<div class="mc-tbl-card" style="overflow-x:auto;">
  @if($rates->count())
    <table class="mc-tbl">
      <thead>
        <tr>
          <th>{{ __('messages.currency') }}</th>
          <th>{{ __('messages.buy_rate_col') }}</th>
          <th>{{ __('messages.sell_rate_col') }}</th>
          <th>{{ __('messages.effective_date_col') }}</th>
          <th>{{ __('messages.status') }}</th>
          <th>{{ __('messages.action_col') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rates as $r)
          <tr>
            <td>
              <span class="mc-flag">{{ $r->flag_emoji ?? '💵' }}</span>
              <span class="mc-cc">{{ $r->currency_code }}</span>
              <span style="color:var(--muted,#64748b);font-size:12px;margin-left:8px;">{{ $r->currency_name }}</span>
            </td>
            <td>Rs {{ number_format($r->buy_rate, 2) }}</td>
            <td>Rs {{ number_format($r->sell_rate, 2) }}</td>
            <td>{{ $r->effective_date?->format('M d, Y') ?? '—' }}</td>
            <td>
              <span class="mc-pill {{ $r->is_active ? 'p-on' : 'p-off' }}">
                {{ $r->is_active ? __('messages.active') : __('messages.inactive') }}
              </span>
            </td>
            <td>
              <button type="button" onclick="openEditRateModal({{ $r->id }}, '{{ addslashes($r->currency_code) }}', '{{ addslashes($r->currency_name ?? '') }}', {{ (float) $r->buy_rate }}, {{ (float) $r->sell_rate }}, '{{ addslashes($r->flag_emoji ?? '') }}')" style="background:var(--blue-soft);color:#1d4ed8;border:none;width:34px;height:34px;border-radius:8px;cursor:pointer;margin-right:4px;"><i class="fas fa-edit"></i></button>
              <form method="POST" action="{{ route('provider.money-changer.rates.destroy', $r) }}" style="display:inline;margin:0;"
                    onsubmit="return confirm('{{ __('messages.delete_rate_confirm') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="mc-del"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="mc-empty">💱 {{ __('messages.no_rates_yet') }}</div>
  @endif
</div>

{{-- Add Rate Modal (copied from dashboard, theme-aware) --}}
<div id="rateModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text,#0f172a);margin:0">{{ __('messages.add_rate') }}</h3>
            <button type="button" onclick="closeRateModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted,#64748b);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.money-changer.rates.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.currency_code') }}</label>
                    <input type="text" name="currency_code" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="USD">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.currency_name') }}</label>
                    <input type="text" name="currency_name" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="US Dollar">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.buy_rate') }}</label>
                    <input type="number" step="0.01" name="buy_rate" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="137.20">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.sell_rate') }}</label>
                    <input type="number" step="0.01" name="sell_rate" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="137.90">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.flag_emoji') }}</label>
                    <input type="text" name="flag_emoji" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="🇺🇸">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#10b981);">{{ __('messages.create_rate') }}</button>
        </form>
    </div>
</div>

<script>
function openRateModal() { document.getElementById('rateModal').style.display = 'flex'; }
function closeRateModal() { document.getElementById('rateModal').style.display = 'none'; }
document.getElementById('rateModal').addEventListener('click', function(e) {
    if (e.target === this) closeRateModal();
});
</script>

{{-- Edit Rate Modal --}}
<div id="editRateModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text,#0f172a);margin:0">{{ __('messages.edit_rate') }}</h3>
            <button type="button" onclick="closeEditRateModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted,#64748b);line-height:1">&times;</button>
        </div>
        <form id="editRateForm" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.currency_code') }}</label>
                    <input type="text" name="currency_code" id="er_code" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.currency_name') }}</label>
                    <input type="text" name="currency_name" id="er_name" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.buy_rate') }}</label>
                    <input type="number" step="0.01" name="buy_rate" id="er_buy" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.sell_rate') }}</label>
                    <input type="number" step="0.01" name="sell_rate" id="er_sell" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.flag_emoji') }}</label>
                    <input type="text" name="flag_emoji" id="er_flag" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;">{{ __('messages.update_rate_btn') }}</button>
        </form>
    </div>
</div>

<script>
function openEditRateModal(id, code, name, buy, sell, flag) {
    document.getElementById('editRateForm').action = '/provider/money-changer/rates/' + id;
    document.getElementById('er_code').value = code;
    document.getElementById('er_name').value = name;
    document.getElementById('er_buy').value = buy;
    document.getElementById('er_sell').value = sell;
    document.getElementById('er_flag').value = flag;
    document.getElementById('editRateModal').style.display = 'flex';
}
function closeEditRateModal() { document.getElementById('editRateModal').style.display = 'none'; }
document.getElementById('editRateModal').addEventListener('click', function(e) { if (e.target === this) closeEditRateModal(); });
</script>
@endsection
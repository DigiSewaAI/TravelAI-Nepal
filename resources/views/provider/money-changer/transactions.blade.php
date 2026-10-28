@extends($provider_layout ?? 'layouts.provider-money-changer')
@section('title', __('messages.nav_transactions'))
@section('page-title', __('messages.nav_transactions'))
@section('page-subtitle', 'All your currency exchange transactions')

@section('content')
<style>
  .mc-hero{background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .mc-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .mc-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .mc-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .mc-btn-add{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.96);color:var(--accent-d,#059669);padding:11px 18px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;position:relative;z-index:1;box-shadow:0 4px 14px rgba(0,0,0,.15);}
  .mc-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:900px){.mc-stats{grid-template-columns:1fr 1fr;}}
  .mc-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:16px;}
  .mc-stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);font-weight:800;}
  .mc-stat-val{font-size:20px;font-weight:800;color:var(--text,#0f172a);margin-top:6px;}
  .mc-stat-val.accent{color:var(--accent,#10b981);}
  .mc-tbl-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;overflow:hidden;}
  .mc-tbl{width:100%;border-collapse:collapse;min-width:800px;}
  .mc-tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#64748b);font-weight:800;padding:14px 18px;background:var(--bg,#f4f6fb);border-bottom:1px solid var(--line,#e8edf5);}
  .mc-tbl td{padding:14px 18px;font-size:13px;font-weight:600;color:var(--text,#0f172a);border-bottom:1px solid var(--line,#e8edf5);}
  .mc-tbl tbody tr:hover{background:var(--green-soft,#e6f7f1);}
  .mc-tbl tbody tr:last-child td{border-bottom:none;}
  .mc-type{display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;}
  .t-buy{background:var(--blue-soft,#e8f1fe);color:var(--blue,#3b82f6);}
  .t-sell{background:var(--green-soft,#e6f7f1);color:var(--green,#10b981);}
  .mc-status{display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;}
  .s-done{background:var(--green-soft,#e6f7f1);color:var(--green,#10b981);}
  .s-pend{background:var(--amber-soft,#fef4e2);color:var(--amber,#f59e0b);}
  .s-canc{background:var(--red-soft,#fdeaea);color:var(--red,#ef4444);}
  .mc-del{background:var(--red-soft,#fdeaea);color:var(--red,#ef4444);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;}
  .mc-del:hover{background:var(--red,#ef4444);color:#fff;}
  .mc-empty{text-align:center;padding:48px 20px;color:var(--muted,#64748b);}
</style>

<div class="mc-hero">
  <div>
    <h1>📊 {{ __('messages.nav_transactions') }}</h1>
    <p>All your currency exchange transactions</p>
  </div>
  <button type="button" onclick="openTxModal()" class="mc-btn-add" style="border:none;cursor:pointer;font-family:inherit;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
    Add Transaction
  </a>
</div>

<div class="mc-stats">
  <div class="mc-stat"><div class="mc-stat-lbl">Total Volume</div><div class="mc-stat-val accent">Rs {{ number_format($totalVolume) }}</div></div>
  <div class="mc-stat"><div class="mc-stat-lbl">Total Profit</div><div class="mc-stat-val" style="color:var(--green,#10b981);">Rs {{ number_format($totalProfit) }}</div></div>
  <div class="mc-stat"><div class="mc-stat-lbl">Buy</div><div class="mc-stat-val">{{ $buyCount }}</div></div>
  <div class="mc-stat"><div class="mc-stat-lbl">Sell</div><div class="mc-stat-val">{{ $sellCount }}</div></div>
</div>

<div class="mc-tbl-card" style="overflow-x:auto;">
  @if($transactions->count())
    <table class="mc-tbl">
      <thead>
        <tr>
          <th>Transaction #</th>
          <th>Customer</th>
          <th>Type</th>
          <th>Currency</th>
          <th>Foreign Amount</th>
          <th>Rate</th>
          <th>NPR Amount</th>
          <th>Profit</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($transactions as $tx)
          <tr>
            <td><strong style="color:var(--accent-d,#059669);">{{ $tx->transaction_number }}</strong></td>
            <td>{{ $tx->customer_name }}</td>
            <td><span class="mc-type {{ $tx->type === 'buy' ? 't-buy' : 't-sell' }}">{{ ucfirst($tx->type) }}</span></td>
            <td>{{ $tx->currency_code }}</td>
            <td>{{ number_format($tx->amount_foreign, 2) }}</td>
            <td>Rs {{ number_format($tx->rate, 2) }}</td>
            <td style="font-weight:800;">Rs {{ number_format($tx->npr_amount) }}</td>
            <td style="color:var(--green,#10b981);font-weight:800;">Rs {{ number_format($tx->profit) }}</td>
            <td>
              @php $sClass = match($tx->status) { 'completed' => 's-done', 'pending' => 's-pend', 'cancelled' => 's-canc', default => 's-done' }; @endphp
              <span class="mc-status {{ $sClass }}">{{ ucfirst($tx->status) }}</span>
            </td>
            <td style="color:var(--muted,#64748b);">{{ $tx->created_at?->format('M d, Y') }}</td>
            <td>
              <form method="POST" action="{{ route('provider.money-changer.transactions.destroy', $tx) }}" style="display:inline;margin:0;"
                    onsubmit="return confirm('Delete this transaction?')">
                @csrf @method('DELETE')
                <button type="submit" class="mc-del"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <div class="mc-empty">📊 No transactions yet. Add from dashboard.</div>
  @endif
</div>

{{-- New Transaction Modal (copied from dashboard, theme-aware) --}}
<div id="transactionModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:var(--card,#fff);border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text,#0f172a);margin:0">{{ __('messages.new_transaction') }}</h3>
            <button type="button" onclick="closeTxModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted,#64748b);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.money-changer.transactions.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.customer_name') }}</label>
                    <input type="text" name="customer_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="John Doe">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.transaction_type') }}</label>
                    <select name="type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)">
                        <option value="buy">{{ __('messages.buy') }}</option>
                        <option value="sell">{{ __('messages.sell') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.currency_code') }}</label>
                    <input type="text" name="currency_code" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="USD">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.amount_foreign') }}</label>
                    <input type="number" step="0.01" name="amount_foreign" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="1000">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted,#64748b);text-transform:uppercase">{{ __('messages.rate') }} ({{ __('messages.npr_amount') }})</label>
                    <input type="number" step="0.01" name="rate" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line,#e8edf5);border-radius:10px;font-family:inherit;font-size:13px;background:var(--card,#fff);color:var(--text,#0f172a)" placeholder="137.50">
                </div>
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));color:#fff;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 20px -8px var(--accent,#10b981);">{{ __('messages.create_transaction') }}</button>
        </form>
    </div>
</div>

<script>
function openTxModal() { document.getElementById('transactionModal').style.display = 'flex'; }
function closeTxModal() { document.getElementById('transactionModal').style.display = 'none'; }
document.getElementById('transactionModal').addEventListener('click', function(e) {
    if (e.target === this) closeTxModal();
});
</script>
@endsection
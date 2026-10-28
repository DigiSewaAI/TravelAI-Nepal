@extends($provider_layout ?? 'layouts.provider-money-changer')
@section('title', __('messages.nav_reports'))
@section('page-title', __('messages.nav_reports'))
@section('page-subtitle', 'Volume, profit, and top performers')

@section('content')
<style>
  .mc-hero{background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .mc-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .mc-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .mc-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .mc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:900px){.mc-grid{grid-template-columns:1fr 1fr;}}
  .mc-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:18px;}
  .mc-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);font-weight:800;}
  .mc-val{font-size:22px;font-weight:800;color:var(--text,#0f172a);margin-top:8px;}
  .mc-val.accent{color:var(--accent,#10b981);}
  .mc-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;padding:22px;margin-bottom:20px;}
  .mc-card h3{font-size:16px;font-weight:800;color:var(--text,#0f172a);margin:0 0 16px;}
  .mc-bar{height:44px;border-radius:10px;background:var(--bg,#f4f6fb);display:flex;align-items:center;justify-content:space-between;padding:0 14px;margin-bottom:10px;font-weight:800;font-size:13px;}
  .mc-bar .rank{width:26px;height:26px;border-radius:8px;background:var(--green-soft,#e6f7f1);color:var(--accent,#10b981);display:grid;place-items:center;font-size:12px;font-weight:900;margin-right:12px;flex-shrink:0;}
  .mc-bar .lhs{display:flex;align-items:center;}
  .mc-bar .val{color:var(--accent,#10b981);}
  .chart{display:flex;gap:8px;align-items:flex-end;height:180px;padding:10px 0;}
  .chart-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
  .chart-bars{display:flex;gap:3px;align-items:flex-end;width:100%;height:140px;justify-content:center;}
  .cb-buy{width:45%;background:linear-gradient(180deg,#60a5fa,#3b82f6);border-radius:5px 5px 0 0;min-height:3px;}
  .cb-sell{width:45%;background:linear-gradient(180deg,#34d399,#10b981);border-radius:5px 5px 0 0;min-height:3px;}
  .chart-day{font-size:10px;color:var(--muted,#64748b);font-weight:800;text-transform:uppercase;}
  .chart-legend{display:flex;gap:18px;justify-content:center;margin-top:8px;font-size:12px;font-weight:700;}
  .chart-legend i{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:5px;}
  .mc-empty{text-align:center;color:var(--muted,#64748b);padding:20px;font-size:13px;}
</style>

<div class="mc-hero">
  <h1>📈 {{ __('messages.nav_reports') }}</h1>
  <p>Volume, profit, and top performers</p>
</div>

<div class="mc-grid">
  <div class="mc-stat"><div class="mc-lbl">All-Time Volume</div><div class="mc-val accent">Rs {{ number_format($allTimeVolume) }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">All-Time Profit</div><div class="mc-val" style="color:var(--green,#10b981);">Rs {{ number_format($allTimeProfit) }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">Transactions</div><div class="mc-val">{{ $totalTxCount }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">Today Volume</div><div class="mc-val">Rs {{ number_format($todayVolume) }}</div></div>
</div>

<div class="mc-card">
  <h3>📊 Buy vs Sell — Last 7 Days</h3>
  @php $maxV = collect($chartData)->map(fn($d) => max($d['buy'], $d['sell']))->max() ?: 1; @endphp
  <div class="chart">
    @foreach($chartData as $c)
      <div class="chart-col">
        <div class="chart-bars">
          <div class="cb-buy" style="height:{{ max(3, (int) (($c['buy'] / $maxV) * 130)) }}px;"></div>
          <div class="cb-sell" style="height:{{ max(3, (int) (($c['sell'] / $maxV) * 130)) }}px;"></div>
        </div>
        <div class="chart-day">{{ $c['day'] }}</div>
      </div>
    @endforeach
  </div>
  <div class="chart-legend">
    <span><i style="background:#3b82f6"></i>Buy</span>
    <span><i style="background:#10b981"></i>Sell</span>
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;margin-top:10px;">Total: <span style="color:var(--accent,#10b981);">Rs {{ number_format($chartTotal) }}</span></div>
</div>

<div class="mc-card">
  <h3>🏆 Top 5 Currencies by Volume</h3>
  @forelse($topCurrencies as $i => $c)
    <div class="mc-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $c->currency_code }} ({{ $c->tx_count }} tx)</span></div>
      <span class="val">Rs {{ number_format($c->total_volume) }}</span>
    </div>
  @empty
    <p class="mc-empty">No currency data yet.</p>
  @endforelse
</div>

<div class="mc-card">
  <h3>👥 Top 5 Customers by Volume</h3>
  @forelse($topCustomers as $i => $c)
    <div class="mc-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $c->customer_name }} ({{ $c->tx_count }} tx)</span></div>
      <span class="val">Rs {{ number_format($c->total_volume) }}</span>
    </div>
  @empty
    <p class="mc-empty">No customer data yet.</p>
  @endforelse
</div>
@endsection
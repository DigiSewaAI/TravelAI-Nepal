@extends($provider_layout ?? 'layouts.provider-inventory')
@section('title', __('messages.reports_management'))
@section('page-title', __('messages.reports_management'))
@section('page-subtitle', 'Stock value, product performance, and sales')

@section('content')
<style>
  .rep-hero{background:linear-gradient(135deg,var(--accent,#6366f1),var(--accent-d,#4f46e5));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .rep-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .rep-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .rep-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .rep-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:900px){.rep-grid{grid-template-columns:1fr 1fr;}}
  .rep-stat{background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:14px;padding:18px;}
  .rep-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280);font-weight:800;}
  .rep-val{font-size:22px;font-weight:800;color:var(--text,#111827);margin-top:8px;}
  .rep-val.accent{color:var(--accent,#6366f1);}
  .rep-card{background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:16px;padding:22px;margin-bottom:20px;}
  .rep-card h3{font-size:16px;font-weight:800;color:var(--text,#111827);margin:0 0 16px;}
  .rep-bar{height:44px;border-radius:10px;background:var(--bg,#f5f6fb);display:flex;align-items:center;justify-content:space-between;padding:0 14px;margin-bottom:10px;font-weight:800;font-size:13px;color:var(--text,#111827);}
  .rep-bar .rank{width:26px;height:26px;border-radius:8px;background:var(--accent-soft,#eef0fe);color:var(--accent,#6366f1);display:grid;place-items:center;font-size:12px;font-weight:900;margin-right:12px;flex-shrink:0;}
  .rep-bar .lhs{display:flex;align-items:center;}
  .rep-bar .val{color:var(--accent,#6366f1);}
  .rep-chart{display:flex;gap:8px;align-items:flex-end;height:160px;padding:10px 0;}
  .rep-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
  .rep-bar-el{width:100%;background:linear-gradient(180deg,var(--accent,#6366f1),var(--accent-d,#4f46e5));border-radius:6px 6px 0 0;min-height:4px;transition:height .3s;}
  .rep-day{font-size:10px;color:var(--muted,#6b7280);font-weight:800;text-transform:uppercase;}
</style>

<div class="rep-hero">
  <h1>📊 {{ __('messages.reports_management') }}</h1>
  <p>Stock value, product performance, and sales overview</p>
</div>

<div class="rep-grid">
  <div class="rep-stat"><div class="rep-lbl">Total Stock Value</div><div class="rep-val accent">Rs {{ number_format($stockValue) }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_products') }}</div><div class="rep-val">{{ $totalProducts }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">Low Stock</div><div class="rep-val" style="color:var(--amber,#f59e0b);">{{ $lowStockCount }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">Orders Today</div><div class="rep-val">{{ $ordersToday }}</div></div>
</div>

<div class="rep-card">
  <h3>📈 Sales — Last 7 Days</h3>
  @php $maxV = collect($chartData)->max('value') ?: 1; @endphp
  <div class="rep-chart">
    @foreach($chartData as $c)
      <div class="rep-col">
        <div class="rep-bar-el" style="height:{{ max(4, (int) (($c['value'] / $maxV) * 130)) }}px;"></div>
        <div class="rep-day">{{ $c['day'] }}</div>
      </div>
    @endforeach
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;color:var(--text,#111827);margin-top:10px;">Total: <span style="color:var(--accent,#6366f1);">Rs {{ number_format($chartTotal) }}</span></div>
</div>

<div class="rep-card">
  <h3>🏆 Top Products by Stock Value</h3>
  @forelse($topProducts as $i => $p)
    <div class="rep-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $p->name }}</span></div>
      <span class="val">Rs {{ number_format($p->total_value) }}</span>
    </div>
  @empty
    <p style="text-align:center;color:var(--muted,#6b7280);padding:20px;">No products yet.</p>
  @endforelse
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SH = {{ (int)($stockHealth ?? 0) }};
    const el = document.getElementById('scVal');
    const bar = document.getElementById('scBar');
    if (el) el.innerHTML = SH + '% <small>{{ __('messages.in_stock_label') }}</small>';
    if (bar) bar.style.width = SH + '%';
});
</script>
@endsection
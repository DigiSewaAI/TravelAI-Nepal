@extends($provider_layout ?? 'layouts.provider-inventory')
@section('title', __('messages.inventory_management'))
@section('page-title', __('messages.inventory_management'))
@section('page-subtitle', __('messages.manage_inventory_desc'))

@section('content')
<style>
  .inv-hero{background:linear-gradient(135deg,var(--accent,#6366f1),var(--accent-d,#4f46e5));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .inv-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .inv-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .inv-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .inv-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:768px){.inv-stats{grid-template-columns:1fr 1fr;}}
  .inv-stat{background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:14px;padding:16px;display:flex;align-items:center;gap:12px;}
  .inv-stat-ic{width:40px;height:40px;border-radius:11px;background:var(--accent-soft,#eef0fe);color:var(--accent,#6366f1);display:grid;place-items:center;flex-shrink:0;}
  .inv-stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280);font-weight:800;}
  .inv-stat-val{font-size:20px;font-weight:800;color:var(--text,#111827);margin-top:2px;}
  .inv-filter{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
  .inv-tab{padding:8px 16px;border-radius:9px;font-size:13px;font-weight:800;text-decoration:none;border:1.5px solid var(--line,#e7eaf3);color:var(--muted,#6b7280);background:var(--card,#fff);cursor:pointer;font-family:inherit;transition:.15s;}
  .inv-tab.active,.inv-tab:hover{border-color:var(--accent,#6366f1);color:var(--accent,#6366f1);background:var(--accent-soft,#eef0fe);}
  .inv-table{background:var(--card,#fff);border:1px solid var(--line,#e7eaf3);border-radius:16px;overflow:hidden;}
  .inv-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:12px;padding:14px 20px;border-bottom:1px solid var(--line,#e7eaf3);align-items:center;}
  .inv-row:last-child{border-bottom:none;}
  .inv-row.head{background:var(--bg,#f5f6fb);font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280);font-weight:800;padding:12px 20px;}
  .inv-name{font-weight:800;color:var(--text,#111827);font-size:14px;}
  .inv-sku{font-size:11px;color:var(--muted,#6b7280);font-weight:600;margin-top:2px;}
  .inv-pill{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;display:inline-block;}
  .p-healthy{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981);}
  .p-low{background:var(--amber-soft,#fef4e2);color:var(--amber,#f59e0b);}
  .p-out{background:var(--red-soft,#fdecec);color:var(--red,#ef4444);}
  .inv-empty{padding:48px 20px;text-align:center;color:var(--muted,#6b7280);}
</style>

<div class="inv-hero">
  <h1>📦 {{ __('messages.inventory_management') }}</h1>
  <p>{{ __('messages.manage_inventory_desc') }}</p>
</div>

<div class="inv-stats">
  <div class="inv-stat"><div class="inv-stat-ic">📦</div><div><div class="inv-stat-lbl">{{ __('messages.total_products') }}</div><div class="inv-stat-val">{{ $totalProducts }}</div></div></div>
  <div class="inv-stat"><div class="inv-stat-ic" style="background:var(--green-soft,#e7f8f1);color:var(--green,#10b981);">✅</div><div><div class="inv-stat-lbl">{{ __('messages.healthy_stock') }}</div><div class="inv-stat-val">{{ $healthy->count() }}</div></div></div>
  <div class="inv-stat"><div class="inv-stat-ic" style="background:var(--amber-soft,#fef4e2);color:var(--amber,#f59e0b);">⚠️</div><div><div class="inv-stat-lbl">{{ __('messages.low_stock') }}</div><div class="inv-stat-val">{{ $low->count() }}</div></div></div>
  <div class="inv-stat"><div class="inv-stat-ic" style="background:var(--red-soft,#fdecec);color:var(--red,#ef4444);">❌</div><div><div class="inv-stat-lbl">{{ __('messages.out_of_stock') }}</div><div class="inv-stat-val">{{ $out->count() }}</div></div></div>
</div>

<div class="inv-table">
  <div class="inv-row head">
    <div>{{ __('messages.product') }}</div>
    <div>{{ __('messages.stock') }}</div>
    <div>{{ __('messages.min') }}</div>
    <div>{{ __('messages.price') }}</div>
    <div>{{ __('messages.status') }}</div>
  </div>
  @forelse($products as $p)
    @php
      $status = $p->stock_quantity <= 0 ? 'out' : ($p->stock_quantity <= $p->low_stock_threshold ? 'low' : 'healthy');
      $pill = $status === 'out' ? 'p-out' : ($status === 'low' ? 'p-low' : 'p-healthy');
      $label = $status === 'out' ? __('messages.out_of_stock') : ($status === 'low' ? __('messages.low_stock') : __('messages.healthy_stock'));
    @endphp
    <div class="inv-row">
      <div><div class="inv-name">{{ $p->name }}</div><div class="inv-sku">{{ $p->sku }}</div></div>
      <div style="font-weight:800;color:{{ $p->stock_quantity <= 0 ? 'var(--red,#ef4444)' : 'var(--text,#111827)' }};">{{ $p->stock_quantity }}</div>
      <div style="color:var(--muted,#6b7280);font-weight:600;">{{ $p->low_stock_threshold }}</div>
      <div style="font-weight:700;">Rs {{ number_format($p->price) }}</div>
      <div><span class="inv-pill {{ $pill }}">{{ $label }}</span></div>
    </div>
  @empty
    <div class="inv-empty">📦 {{ __('messages.no_products_found') ?? 'No products yet.' }}</div>
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
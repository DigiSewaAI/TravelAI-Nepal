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
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_stock_value') }}</div><div class="rep-val accent">Rs {{ number_format($stockValue) }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_products') }}</div><div class="rep-val">{{ $totalProducts }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.low_stock') }}</div><div class="rep-val" style="color:var(--amber,#f59e0b);">{{ $lowStockCount }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.orders_today') }}</div><div class="rep-val">{{ $ordersToday }}</div></div>
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
    <p style="text-align:center;color:var(--muted,#6b7280);padding:20px;">{{ __('messages.no_products_yet') }}</p>
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

{{-- ═══ Inventory AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">📦 {{ __('messages.inventory_ai_stock_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.inventory_ai_stock_sub') }}</p>
  <button id="inventoryAiStockBtn" onclick="loadStockForecast()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.inventory_ai_run_stock') }}</button>
  <div id="inventoryAiStockOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">🔄 {{ __('messages.inventory_ai_reorder_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.inventory_ai_reorder_sub') }}</p>
  <button id="inventoryAiReorderBtn" onclick="loadReorderAssistant()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.inventory_ai_run_reorder') }}</button>
  <div id="inventoryAiReorderOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function invAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function invAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadStockForecast(){
  const btn=document.getElementById('inventoryAiStockBtn'), out=document.getElementById('inventoryAiStockOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.inventory_ai_loading") }}';
  try{
    const data=await invAiPost('{{ route("provider.inventory.ai.stock") }}');
    if(data.error){ invAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    (data.items||[]).forEach(i=>{
      var _st = i.risk || i.status || 'unknown';
      var _color = {out_of_stock:'#ef4444',low_stock:'#f59e0b',restock_soon:'#f59e0b',ok:'#10b981',healthy:'#10b981',critical:'#ef4444',high:'#f59e0b',medium:'#3b82f6'}[_st]||'#6b7a90';
      var _pred = i.predicted_units_next_7d !== undefined ? i.predicted_units_next_7d : (i.predicted_units_next_days||'—');
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;align-items:center;"><div><strong>'+i.name+'</strong><div style="font-size:10px;color:var(--muted)">'+(i.reason||('Stock: '+(i.stock||0)+', Sold: '+(i.units_sold_30d||0)))+'</div></div><div style="text-align:right;white-space:nowrap;"><span style="background:'+_color+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+_st+'</span><div style="font-size:10px;color:var(--muted);margin-top:2px;">Next 7d: '+_pred+' units</div></div></div>';
    });
    out.innerHTML=html;
  }catch(e){ invAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadReorderAssistant(){
  const btn=document.getElementById('inventoryAiReorderBtn'), out=document.getElementById('inventoryAiReorderOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.inventory_ai_loading") }}';
  try{
    const data=await invAiPost('{{ route("provider.inventory.ai.reorder") }}');
    if(data.error){ invAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#f0fdf4;border:1px solid #10b981;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">✅ '+data.data_warning+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    (data.items||[]).forEach(i=>{
      var _urg = i.urgency || 'ok';
      var _color = {critical:'#ef4444',high:'#f59e0b',medium:'#3b82f6',ok:'#10b981'}[_urg]||'#6b7a90';
      var _qty = i.suggested_reorder_qty || 0;
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;align-items:center;"><div><strong>'+i.name+'</strong><div style="font-size:10px;color:var(--muted)">'+(i.reason||'')+'</div></div><div style="text-align:right;white-space:nowrap;"><span style="background:'+_color+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+_urg+'</span><div style="font-size:10px;color:var(--muted);margin-top:2px;">Stock: '+(i.current_stock||0)+' · Reorder: <strong>'+_qty+'</strong></div></div></div>';
    });
    out.innerHTML=html;
  }catch(e){ invAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
@extends('layouts.provider-restaurant')
@section('title', __('messages.reports_management'))
@section('page-title', __('messages.reports_management'))
@section('page-subtitle', __('messages.manage_reports_desc'))

@push('restaurant-head')
<style>
  .stat-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px;}
  .stat-card{background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px;}
  .stat-card h4{font-size:12px; color:var(--muted); text-transform:uppercase; font-weight:800;}
  .stat-card .val{font-size:28px; font-weight:800; margin-top:10px; color:var(--text);}
</style>
@endpush

@section('content')
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800;">{{ __('messages.reports_management') }}</h3>
    <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="stat-grid">
    <div class="stat-card"><h4>{{ __('messages.total_sales') }}</h4><div class="val" style="color:var(--accent);">Rs {{ number_format($totalSales) }}</div></div>
    <div class="stat-card"><h4>{{ __('messages.total_orders') }}</h4><div class="val">{{ $totalOrders }}</div></div>
    <div class="stat-card"><h4>{{ __('messages.avg_order') }}</h4><div class="val">Rs {{ number_format($avgOrder) }}</div></div>
</div>

<div class="card" style="background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px;">
    <h3 style="font-size:16px; font-weight:800; margin-bottom:15px;">{{ __('messages.recent_orders') }}</h3>
    <table style="width:100%; border-collapse:collapse;">
        <thead><tr><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Order #</th><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Amount</th><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Status</th></tr></thead>
        <tbody>
            @forelse($orders as $o)
            <tr><td style="padding:10px; border-bottom:1px solid var(--line);">#{{ $o->order_number }}</td><td style="padding:10px; border-bottom:1px solid var(--line);">Rs {{ number_format($o->total) }}</td><td style="padding:10px; border-bottom:1px solid var(--line);">{{ ucfirst($o->status) }}</td></tr>
            @empty
            <tr><td colspan="3" style="padding:20px; text-align:center; color:var(--muted);">{{ __('messages.no_reports_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══ Restaurant AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">🍽️ {{ __('messages.restaurant_ai_menu_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.restaurant_ai_menu_sub') }}</p>
  <button id="restaurantAiMenuBtn" onclick="loadMenuIntelligence()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.restaurant_ai_run_menu') }}</button>
  <div id="restaurantAiMenuOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">📊 {{ __('messages.restaurant_ai_forecast_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.restaurant_ai_forecast_sub') }}</p>
  <button id="restaurantAiForecastBtn" onclick="loadRestaurantForecast()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.restaurant_ai_run_forecast') }}</button>
  <div id="restaurantAiForecastOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function restaurantAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function rAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadMenuIntelligence(){
  const btn=document.getElementById('restaurantAiMenuBtn'), out=document.getElementById('restaurantAiMenuOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.restaurant_ai_loading") }}';
  try{
    const data=await restaurantAiPost('{{ route("provider.restaurant.ai.menu") }}');
    if(data.error){ rAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    (data.items||[]).forEach(i=>{ html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;align-items:center;"><div><strong>'+i.name+'</strong><div style="font-size:10px;color:var(--muted)">'+i.reason+'</div></div><div style="text-align:right;white-space:nowrap;"><span style="background:'+({popular:'#10b981',steady:'#3b82f6',slow:'#f59e0b',dead:'#ef4444',insufficient_data:'#9ca3af'}[i.status]||'#6b7a90')+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+i.status+'</span><div style="font-size:10px;color:var(--muted);margin-top:2px;">'+i.suggestion+'</div></div></div>'; });
    out.innerHTML=html;
  }catch(e){ rAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadRestaurantForecast(){
  const btn=document.getElementById('restaurantAiForecastBtn'), out=document.getElementById('restaurantAiForecastOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.restaurant_ai_loading") }}';
  try{
    const data=await restaurantAiPost('{{ route("provider.restaurant.ai.forecast") }}');
    if(data.error){ rAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.avg_orders_per_day !== undefined) html+='<div style="font-size:11px;margin-bottom:8px;padding:8px;background:var(--bg,#f5f7fb);border-radius:8px;"><strong>Historical avg:</strong> '+data.avg_orders_per_day+' orders/day · Rs '+data.avg_revenue_per_day+'/day'+(data.avg_order_value?' · Rs '+data.avg_order_value+' avg order value':'')+(data.data_quality==='insufficient'?' <span style="background:#9ca3af;color:#fff;padding:1px 6px;border-radius:4px;font-size:10px;">INSUFFICIENT</span>':(data.data_quality==='sparse'?' <span style="background:#f59e0b;color:#fff;padding:1px 6px;border-radius:4px;font-size:10px;">SPARSE</span>':''))+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    (data.days||[]).forEach(d=>{ var _baseline = data.is_baseline?' <span style="background:#9ca3af;color:#fff;padding:1px 5px;border-radius:3px;font-size:9px;">BASELINE</span>':''; html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><span>'+d.date+_baseline+'</span><strong>'+d.predicted_orders+' orders · Rs '+d.predicted_revenue+' · '+({low:'Low',med:'Medium',high:'High'}[d.confidence]||d.confidence)+'</strong></div>'; });
    out.innerHTML=html;
  }catch(e){ rAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
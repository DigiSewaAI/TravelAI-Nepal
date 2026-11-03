@extends($provider_layout ?? 'layouts.provider-transport')
@section('title', __('messages.reports_management'))
@section('page-title', __('messages.reports_management'))
@section('page-subtitle', __('messages.transport_reports_subtitle'))

@section('content')
<style>
  .rep-hero{background:linear-gradient(135deg,var(--accent,#0ea5e9),var(--accent-d,#0284c7));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .rep-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .rep-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .rep-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .rep-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:900px){.rep-grid{grid-template-columns:1fr 1fr;}}
  .rep-stat{background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:14px;padding:18px;}
  .rep-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7f96);font-weight:800;}
  .rep-val{font-size:22px;font-weight:800;color:var(--text,#0c1e33);margin-top:8px;}
  .rep-val.accent{color:var(--accent,#0ea5e9);}
  .rep-card{background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:16px;padding:22px;margin-bottom:20px;}
  .rep-card h3{font-size:16px;font-weight:800;color:var(--text,#0c1e33);margin:0 0 16px;}
  .rep-bar{height:44px;border-radius:10px;background:var(--bg,#f3f7fc);display:flex;align-items:center;justify-content:space-between;padding:0 14px;margin-bottom:10px;font-weight:800;font-size:13px;color:var(--text,#0c1e33);}
  .rep-bar .rank{width:26px;height:26px;border-radius:8px;background:var(--accent-soft,#e0f2fe);color:var(--accent,#0ea5e9);display:grid;place-items:center;font-size:12px;font-weight:900;margin-right:12px;flex-shrink:0;}
  .rep-bar .lhs{display:flex;align-items:center;}
  .rep-bar .val{color:var(--accent,#0ea5e9);}
  .rep-chart{display:flex;gap:8px;align-items:flex-end;height:160px;padding:10px 0;}
  .rep-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
  .rep-bar-el{width:100%;background:linear-gradient(180deg,var(--accent,#0ea5e9),var(--accent-d,#0284c7));border-radius:6px 6px 0 0;min-height:4px;transition:height .3s;}
  .rep-day{font-size:10px;color:var(--muted,#6b7f96);font-weight:800;text-transform:uppercase;}
  .rep-empty{text-align:center;color:var(--muted,#6b7f96);padding:20px;font-size:13px;}
</style>

<div class="rep-hero">
  <h1>📊 {{ __('messages.reports_management') }}</h1>
  <p>{{ __('messages.transport_reports_subtitle') }}</p>
</div>

<div class="rep-grid">
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_revenue') }}</div><div class="rep-val accent">Rs {{ number_format($totalRevenue) }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_bookings') }}</div><div class="rep-val">{{ $totalBookings }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.active_fleet') }}</div><div class="rep-val" style="color:var(--green,#10b981);">{{ $activeFleet }} / {{ $totalFleet }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_vehicles') }}</div><div class="rep-val">{{ $totalFleet }}</div></div>
</div>

<div class="rep-card">
  <h3>📈 {{ __('messages.bookings_last_7_days') }}</h3>
  @php $maxV = collect($chartData)->max('trips') ?: 1; @endphp
  <div class="rep-chart">
    @foreach($chartData as $c)
      <div class="rep-col">
        <div class="rep-bar-el" style="height:{{ max(4, (int) (($c['trips'] / $maxV) * 130)) }}px;"></div>
        <div class="rep-day">{{ $c['day'] }}</div>
      </div>
    @endforeach
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;color:var(--text,#0c1e33);margin-top:10px;">{{ __('messages.total') }}: <span style="color:var(--accent,#0ea5e9);">{{ $chartTotal }} {{ __('messages.bookings_label') }}</span></div>
</div>

<div class="rep-card">
  <h3>🏆 {{ __('messages.top_routes_revenue') }}</h3>
  @forelse($topRoutes as $i => $r)
    <div class="rep-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $r->name }}</span></div>
      <span class="val">Rs {{ number_format($r->revenue) }}</span>
    </div>
  @empty
    <p class="rep-empty">{{ __('messages.no_route_revenue_data_yet') }}</p>
  @endforelse
</div>

{{-- ═══ Transport AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">🚐 {{ __('messages.transport_ai_vehicle_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.transport_ai_vehicle_sub') }}</p>
  <button id="transportAiVehicleBtn" onclick="loadVehicleAssignment()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.transport_ai_run_vehicle') }}</button>
  <div id="transportAiVehicleOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">💰 {{ __('messages.transport_ai_cost_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.transport_ai_cost_sub') }}</p>
  <button id="transportAiCostBtn" onclick="loadCostEstimator()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.transport_ai_run_cost') }}</button>
  <div id="transportAiCostOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function tAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function tAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadVehicleAssignment(){
  const btn=document.getElementById('transportAiVehicleBtn'), out=document.getElementById('transportAiVehicleOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.transport_ai_loading") }}';
  try{
    const data=await tAiPost('{{ route("provider.transport.ai.vehicle") }}');
    if(data.error){ tAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>';
    html+='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>'; if(data.assumptions&&data.assumptions.seat_assumption) html+='<div style="font-size:10px;color:var(--muted);margin-bottom:8px;">📋 '+data.assumptions.seat_assumption+' · '+data.assumptions.balance_rule+'</div>';
    html+='<div style="font-size:11px;margin:10px 0 6px;font-weight:700;">Fleet Utilization:</div>';
    (data.vehicles||[]).forEach(v=>{
      var _c={idle:'#9ca3af',underused:'#f59e0b',healthy:'#10b981',high_demand:'#3b82f6',over_capacity:'#ef4444'}[v.status]||'#6b7a90';
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;align-items:center;"><div><strong>'+v.vehicle_number+'</strong> <span style="font-size:10px;color:var(--muted)">'+v.vehicle_type+' · cap '+v.capacity+'</span></div><div style="text-align:right;white-space:nowrap;"><span style="background:'+_c+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+v.status+'</span><div style="font-size:10px;color:var(--muted);margin-top:2px;">'+v.booking_count+'/'+(v.capacity||v.seat_capacity||"?")+' seats · '+v.booking_allocation_pct+'% fill</div></div></div>';
    });
    if(data.suggestions && data.suggestions.length){
      html+='<div style="font-size:11px;margin:10px 0 6px;font-weight:700;">Upcoming Assignments:</div>';
      data.suggestions.forEach(s=>{
        html+='<div style="padding:6px 10px;background:#f0fdf4;border:1px solid #10b981;border-radius:8px;margin-bottom:6px;font-size:12px;"><strong>'+s.booking_ref+'</strong> ('+s.date+') → <strong>'+(s.suggested_vehicle||'—')+'</strong><div style="font-size:10px;color:var(--muted)">'+(s.reason||'')+'</div></div>';
      });
    }
    out.innerHTML=html;
  }catch(e){ tAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadCostEstimator(){
  const btn=document.getElementById('transportAiCostBtn'), out=document.getElementById('transportAiCostOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.transport_ai_loading") }}';
  try{
    const data=await tAiPost('{{ route("provider.transport.ai.cost") }}');
    if(data.error){ tAiErr(out, data.message||data.error); return; }
    let html='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    if(data.assumptions) html+='<div style="font-size:10px;color:var(--muted);margin-bottom:8px;">📋 '+data.assumptions.distance_km+'km '+(data.assumptions.trip_type||'')+' · Driver Rs '+data.assumptions.driver_cost+'/day · '+(data.assumptions.seats_assumption||'')+'</div>';
    (data.estimates||[]).forEach(e=>{
      var _c={healthy:'#10b981',thin_margin:'#f59e0b',loss:'#ef4444'}[e.cost_status]||'#6b7a90';
      html+='<div style="padding:8px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><div style="display:flex;justify-content:space-between;align-items:center;"><div><strong>'+e.vehicle_number+'</strong> <span style="font-size:10px;color:var(--muted)">'+e.vehicle_type+' · cap '+e.capacity+'</span></div><span style="background:'+_c+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+e.cost_status+'</span></div><div style="font-size:10px;color:var(--muted);margin-top:4px;">Cost: Rs '+e.total_trip_cost+' · Full rev: Rs '+e.full_trip_revenue+' · Profit: Rs '+e.margin_per_full_trip+' ('+e.margin_pct+'% margin, '+e.markup_pct+'% markup)</div></div>';
    });
    out.innerHTML=html;
  }catch(e){ tAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
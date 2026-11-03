@extends($provider_layout ?? 'layouts.provider-hotel')
@section('title', __('messages.reports_management'))
@section('page-title', __('messages.reports_management'))
@section('page-subtitle', __('messages.reports_subtitle'))

@section('content')
<style>
  .rep-hero{background:linear-gradient(135deg,var(--accent,#c9a227),var(--accent-d,#a8861c));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .rep-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .rep-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .rep-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .rep-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:900px){.rep-grid{grid-template-columns:1fr 1fr;}}
  .rep-stat{background:var(--card,#fff);border:1px solid var(--line,#e6ecf4);border-radius:14px;padding:18px;}
  .rep-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7a90);font-weight:800;}
  .rep-val{font-size:22px;font-weight:800;color:var(--text,#0e1726);margin-top:8px;}
  .rep-val.accent{color:var(--accent,#c9a227);}
  .rep-card{background:var(--card,#fff);border:1px solid var(--line,#e6ecf4);border-radius:16px;padding:22px;margin-bottom:20px;}
  .rep-card h3{font-size:16px;font-weight:800;color:var(--text,#0e1726);margin:0 0 16px;}
  .rep-bar{height:44px;border-radius:10px;background:var(--bg,#f5f7fb);display:flex;align-items:center;justify-content:space-between;padding:0 14px;margin-bottom:10px;font-weight:800;font-size:13px;color:var(--text,#0e1726);}
  .rep-bar .rank{width:26px;height:26px;border-radius:8px;background:var(--accent-soft,#fbf5e2);color:var(--accent,#c9a227);display:grid;place-items:center;font-size:12px;font-weight:900;margin-right:12px;flex-shrink:0;}
  .rep-bar .lhs{display:flex;align-items:center;}
  .rep-bar .val{color:var(--accent,#c9a227);}
  .rep-chart{display:flex;gap:8px;align-items:flex-end;height:160px;padding:10px 0;}
  .rep-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
  .rep-bar-el{width:100%;background:linear-gradient(180deg,var(--accent,#c9a227),var(--accent-d,#a8861c));border-radius:6px 6px 0 0;min-height:4px;transition:height .3s;}
  .rep-day{font-size:10px;color:var(--muted,#6b7a90);font-weight:800;text-transform:uppercase;}
  .rep-empty{text-align:center;color:var(--muted,#6b7a90);padding:20px;font-size:13px;}
</style>

<div class="rep-hero">
  <h1>📊 {{ __('messages.reports_management') }}</h1>
  <p>{{ __('messages.reports_subtitle') }}</p>
</div>

<div class="rep-grid">
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_revenue') }}</div><div class="rep-val accent">Rs {{ number_format($totalRevenue) }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.total_bookings') }}</div><div class="rep-val">{{ $totalBookings }}</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.occupancy_rate') }}</div><div class="rep-val" style="color:var(--green,#10b981);">{{ $occupancyRate }}%</div></div>
  <div class="rep-stat"><div class="rep-lbl">{{ __('messages.rooms_occupied_stat') }}</div><div class="rep-val">{{ $occupiedRooms }} / {{ $totalRooms }}</div></div>
</div>

<div class="rep-card">
  <h3>📈 {{ __('messages.bookings_last_7_days') }}</h3>
  @php $maxV = collect($chartData)->max('value') ?: 1; @endphp
  <div class="rep-chart">
    @foreach($chartData as $c)
      <div class="rep-col">
        <div class="rep-bar-el" style="height:{{ max(4, (int) (($c['value'] / $maxV) * 130)) }}px;"></div>
        <div class="rep-day">{{ $c['day'] }}</div>
      </div>
    @endforeach
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;color:var(--text,#0e1726);margin-top:10px;">{{ __('messages.total') }}: <span style="color:var(--accent,#c9a227);">{{ $chartTotal }} {{ __('messages.bookings_label') }}</span></div>
</div>

<div class="rep-card">
  <h3>🏆 {{ __('messages.top_rooms_by_revenue') }}</h3>
  @forelse($topRooms as $i => $r)
    <div class="rep-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>Room {{ $r->number }} — {{ $r->type }}</span></div>
      <span class="val">Rs {{ number_format($r->revenue) }}</span>
    </div>
  @empty
    <p class="rep-empty">No room revenue data yet.</p>
  @endforelse
</div>

@php
    $__occR = $occupiedRooms ?? null;
    $__totR = $totalRooms ?? null;
    if ($__occR === null || $__totR === null) {
        $__totR = \App\Models\HotelRoom::where('provider_id', $provider->id)->count();
        $__occR = \App\Models\HotelRoom::where('provider_id', $provider->id)->where('status', 'occupied')->count();
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const occ = {{ (int) $__occR }};
    const total = {{ (int) $__totR }};
    const pct = total > 0 ? Math.round((occ / total) * 100) : 0;
    const el = document.getElementById('occVal');
    const bar = document.getElementById('occBar');
    if (el) el.innerHTML = pct + '% <small>' + occ + ' / ' + total + '</small>';
    if (bar) bar.style.width = pct + '%';
});
</script>

{{-- ═══ AI Features ═══ --}}
<div class="rep-card" style="margin-top:20px;">
  <h3>🤖 {{ __('messages.hotel_ai_forecast_title') }}</h3>
  <p style="font-size:13px;color:var(--muted,#6b7a90);margin:0 0 14px;">{{ __('messages.hotel_ai_forecast_sub') }}</p>
  <button id="hotelAiForecastBtn" onclick="loadHotelAiForecast()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.hotel_ai_run_forecast') }}</button>
  <div id="hotelAiForecastOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="rep-card" style="margin-top:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
  <h3 style="margin:0;">💡 {{ __('messages.hotel_ai_pricing_title') }}</h3>
  <a href="{{ route('provider.hotel.price-history') }}" style="background:var(--accent,#c9a227);color:#fff;padding:6px 12px;border-radius:8px;text-decoration:none;font-size:11px;font-weight:800;">📋 History</a>
</div>
  <p style="font-size:13px;color:var(--muted,#6b7a90);margin:0 0 14px;">{{ __('messages.hotel_ai_pricing_sub') }}</p>
  <button id="hotelAiPricingBtn" onclick="loadHotelAiPricing()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.hotel_ai_run_pricing') }}</button>
  <div id="hotelAiPricingOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function hotelAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function hotelAiError(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadHotelAiForecast(){
  const btn=document.getElementById('hotelAiForecastBtn'), out=document.getElementById('hotelAiForecastOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.hotel_ai_loading") }}';
  try{
    const data=await hotelAiPost('{{ route("provider.hotel.ai.occupancy") }}');
    if(data.error){ hotelAiError(out, data.message||data.error); return; }
    let html='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>'; if(data.current_occupancy_pct!==undefined) html+='<div style="font-size:12px;margin-bottom:8px;">Current: <strong>'+data.current_occupancy_pct+'%</strong> ('+data.total_rooms+' rooms) <span style="font-size:10px;color:var(--muted,#6b7a90)">· 1 booking = 1 room</span></div>'; (data.days||[]).forEach(d=>{ var _tag=d.is_confirmed?'<span style="background:#10b981;color:#fff;padding:1px 6px;border-radius:4px;font-size:9px;margin-left:4px;">CONFIRMED</span>':'<span style="background:#f59e0b;color:#fff;padding:1px 6px;border-radius:4px;font-size:9px;margin-left:4px;">FORECAST</span>'; html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><span>'+d.date+_tag+'</span><strong>'+d.predicted_bookings+' booking'+(d.predicted_bookings>1?'s':'')+' · '+(d.predicted_occupancy_pct||0)+'% · '+({low:'Low',med:'Medium',high:'High'}[d.confidence]||d.confidence)+'</strong></div>'; });
    out.innerHTML=html;
  }catch(e){ hotelAiError(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadHotelAiPricing(){
  const btn=document.getElementById('hotelAiPricingBtn'), out=document.getElementById('hotelAiPricingOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.hotel_ai_loading") }}';
  try{
    const data=await hotelAiPost('{{ route("provider.hotel.ai.pricing") }}');
    if(data.error){ hotelAiError(out, data.message||data.error); return; }
    let html='<div style="font-weight:800;margin-bottom:8px;">'+(data.summary||'')+'</div>';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>'; var csrf='{{ csrf_token() }}'; (data.rooms||[]).forEach(r=>{ html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;align-items:center;"><div><span>Room '+r.room_number+'</span>'+(r.reason?'<div style="font-size:10px;color:var(--muted,#6b7a90)">'+r.reason+'</div>':'')+'</div><div style="text-align:right;white-space:nowrap;"><div style="font-size:10px;color:var(--muted,#6b7a90)">Current: Rs '+(r.current_rate||0).toLocaleString()+'</div><strong>Rs '+(r.suggested_rate||0).toLocaleString()+'</strong> /night <form method="POST" action="{{ route('provider.hotel.ai.apply-pricing') }}" style="display:inline;margin-left:6px;"><input type="hidden" name="_token" value="'+csrf+'"><input type="hidden" name="room_number" value="'+r.room_number+'"><input type="hidden" name="suggested_rate" value="'+r.suggested_rate+'"><button type="submit" style="font-size:10px;padding:2px 8px;background:#10b981;color:#fff;border:0;border-radius:4px;cursor:pointer;">Apply</button></form></div></div>'; });
    out.innerHTML=html;
  }catch(e){ hotelAiError(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
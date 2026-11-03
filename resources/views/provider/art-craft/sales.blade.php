@extends($provider_layout ?? 'layouts.provider-art-craft')
@section('title', __('messages.sales_title'))
@section('page-title', __('messages.sales_title'))
@section('page-subtitle', __('messages.sales_subtitle'))

@section('content')
<style>
  .sal-hero{background:linear-gradient(135deg,var(--accent,#c65d3b),var(--accent-d,#a8492b));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .sal-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .sal-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .sal-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .sal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;}
  @media(max-width:768px){.sal-grid{grid-template-columns:1fr;}}
  .sal-stat{background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:14px;padding:18px;}
  .sal-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#8a7565);font-weight:800;}
  .sal-val{font-size:22px;font-weight:800;color:var(--text,#2b1e16);margin-top:8px;}
  .sal-val.accent{color:var(--accent,#c65d3b);}
  .sal-card{background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:16px;padding:22px;margin-bottom:20px;}
  .sal-card h3{font-size:16px;font-weight:800;color:var(--text,#2b1e16);margin:0 0 16px;}
  .sal-chart{display:flex;gap:8px;align-items:flex-end;height:160px;padding:10px 0;}
  .sal-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
  .sal-bar{width:100%;background:linear-gradient(180deg,var(--accent,#c65d3b),var(--accent-d,#a8492b));border-radius:6px 6px 0 0;min-height:4px;transition:height .3s;}
  .sal-day{font-size:10px;color:var(--muted,#8a7565);font-weight:800;text-transform:uppercase;}
  .sal-table{width:100%;border-collapse:collapse;}
  .sal-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#8a7565);font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line,#ede1d3);}
  .sal-table td{padding:14px 12px;border-bottom:1px solid var(--line,#ede1d3);font-size:13px;font-weight:600;color:var(--text,#2b1e16);}
  .sal-table tr:last-child td{border-bottom:none;}
  .sal-name{font-weight:800;}
  .sal-emoji{display:inline-block;margin-right:8px;font-size:16px;}
  .sal-empty{text-align:center;padding:40px;color:var(--muted,#8a7565);font-size:13px;}
</style>

<div style="display:flex;justify-content:flex-end;margin-bottom:15px;">
  <a href="{{ route('provider.art-craft.dashboard') }}" style="background:var(--terracotta); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="sal-hero">
  <h1>💰 {{ __('messages.sales_title') }}</h1>
  <p>{{ __('messages.sales_subtitle') }}</p>
</div>

<div class="sal-grid">
  <div class="sal-stat"><div class="sal-lbl">{{ __('messages.total_revenue') }}</div><div class="sal-val accent">Rs {{ number_format($totalRevenue) }}</div></div>
  <div class="sal-stat"><div class="sal-lbl">{{ __('messages.artworks_sold') }}</div><div class="sal-val">{{ $soldCount }}</div></div>
  <div class="sal-stat"><div class="sal-lbl">{{ __('messages.average_price') }}</div><div class="sal-val">Rs {{ number_format($avgPrice) }}</div></div>
</div>

<div class="sal-card">
  <h3>📈 {{ __('messages.sales_last_7_days') }}</h3>
  @php $maxV = collect($chartData)->max('value') ?: 1; @endphp
  <div class="sal-chart">
    @foreach($chartData as $c)
      <div class="sal-col">
        <div class="sal-bar" style="height:{{ max(4, (int) (($c['value'] / $maxV) * 130)) }}px;"></div>
        <div class="sal-day">{{ $c['day'] }}</div>
      </div>
    @endforeach
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;color:var(--text,#2b1e16);margin-top:10px;">{{ __('messages.total_label') }}: <span style="color:var(--accent,#c65d3b);">Rs {{ number_format($chartTotal) }}</span></div>
</div>

<div class="sal-card">
  <h3>🖼️ {{ __('messages.sold_artworks') }} ({{ $soldCount }})</h3>
  @if($soldArtworks->count())
    <div style="overflow-x:auto;">
      <table class="sal-table">
        <thead>
          <tr>
            <th>{{ __('messages.artwork_header') }}</th>
            <th>{{ __('messages.artist_header') }}</th>
            <th>{{ __('messages.category_header') }}</th>
            <th>{{ __('messages.price_header') }}</th>
            <th>{{ __('messages.sold_date') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($soldArtworks as $a)
            <tr>
              <td><span class="sal-emoji">{{ $a->emoji ?? '🖼️' }}</span><span class="sal-name">{{ $a->name }}</span></td>
              <td>{{ $a->artist_name ?? '—' }}</td>
              <td><span style="text-transform:capitalize;">{{ __('messages.' . $a->category) ?? ucfirst($a->category) }}</span></td>
              <td style="font-weight:800;color:var(--accent,#c65d3b);">Rs {{ number_format($a->price) }}</td>
              <td style="color:var(--muted,#8a7565);">{{ $a->updated_at ? $a->updated_at->format('M d, Y') : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <p class="sal-empty">{{ __('messages.no_sales_yet') }}</p>
  @endif
</div>

{{-- ═══ ArtCraft AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">🎨 {{ __('messages.artcraft_ai_desc_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.artcraft_ai_desc_sub') }}</p>
  <button id="artcraftAiDescBtn" onclick="loadArtcraftDesc()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.artcraft_ai_run_desc') }}</button>
  <div id="artcraftAiDescOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">💰 {{ __('messages.artcraft_ai_pricing_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.artcraft_ai_pricing_sub') }}</p>
  <button id="artcraftAiPricingBtn" onclick="loadArtcraftPricing()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.artcraft_ai_run_pricing') }}</button>
  <div id="artcraftAiPricingOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function acAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function acAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadArtcraftDesc(){
  const btn=document.getElementById('artcraftAiDescBtn'), out=document.getElementById('artcraftAiDescOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.artcraft_ai_loading") }}';
  try{
    const data=await acAiPost('{{ route("provider.art-craft.ai.description") }}');
    if(data.error){ acAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.summary) html+='<div style="font-weight:800;margin-bottom:8px;">'+data.summary+'</div>';
    (data.items||[]).forEach(i=>{
      html+='<div style="padding:8px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><strong>'+i.name+'</strong><div style="font-size:12px;color:var(--muted);margin-top:4px;">'+(i.description||'')+'</div></div>';
    });
    out.innerHTML=html;
  }catch(e){ acAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadArtcraftPricing(){
  const btn=document.getElementById('artcraftAiPricingBtn'), out=document.getElementById('artcraftAiPricingOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.artcraft_ai_loading") }}';
  try{
    const data=await acAiPost('{{ route("provider.art-craft.ai.pricing") }}');
    if(data.error){ acAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#f0fdf4;border:1px solid #10b981;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">✅ '+data.data_warning+'</div>';
    if(data.summary) html+='<div style="font-weight:800;margin-bottom:8px;">'+data.summary+'</div>';
    (data.items||[]).forEach(i=>{
      var _c = {ok:'#10b981',underpriced:'#3b82f6',overpriced:'#f59e0b'}[i.status]||'#6b7a90';
      html+='<div style="padding:8px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><div style="display:flex;justify-content:space-between;align-items:center;"><div><strong>'+i.name+'</strong> <span style="font-size:10px;color:var(--muted)">'+i.category+' · '+i.craftsmanship+'</span></div><span style="background:'+_c+';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;">'+i.status+'</span></div><div style="font-size:11px;color:var(--muted);margin-top:4px;">Current: Rs '+i.current_price.toLocaleString()+' → Suggested: <strong>Rs '+i.suggested_price.toLocaleString()+'</strong> ('+i.delta_pct+'%)</div><div style="font-size:10px;color:var(--muted);margin-top:2px;">'+i.reason+'</div></div>';
    });
    if(data.assumptions) html+='<div style="margin-top:8px;padding:6px 10px;background:#fef3c7;border-radius:6px;font-size:10px;color:#92400e;">📋 Formula: '+data.assumptions.formula+' · '+data.assumptions.markup+'</div>';
    out.innerHTML=html;
  }catch(e){ acAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
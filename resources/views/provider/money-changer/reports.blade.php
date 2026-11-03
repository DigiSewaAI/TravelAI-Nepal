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
  <div class="mc-stat"><div class="mc-lbl">{{ __('messages.all_time_volume') }}</div><div class="mc-val accent">Rs {{ number_format($allTimeVolume) }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">{{ __('messages.all_time_profit') }}</div><div class="mc-val" style="color:var(--green,#10b981);">Rs {{ number_format($allTimeProfit) }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">{{ __('messages.transactions') }}</div><div class="mc-val">{{ $totalTxCount }}</div></div>
  <div class="mc-stat"><div class="mc-lbl">{{ __('messages.today_volume') }}</div><div class="mc-val">Rs {{ number_format($todayVolume) }}</div></div>
</div>

<div class="mc-card">
  <h3>📊 {{ __('messages.buy_vs_sell_7days') }}</h3>
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
    <span><i style="background:#3b82f6"></i>{{ __('messages.buy') }}</span>
    <span><i style="background:#10b981"></i>{{ __('messages.sell') }}</span>
  </div>
  <div style="text-align:right;font-size:14px;font-weight:800;margin-top:10px;">{{ __('messages.total') }}: <span style="color:var(--accent,#10b981);">Rs {{ number_format($chartTotal) }}</span></div>
</div>

<div class="mc-card">
  <h3>🏆 {{ __('messages.top_currencies_volume') }}</h3>
  @forelse($topCurrencies as $i => $c)
    <div class="mc-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $c->currency_code }} ({{ $c->tx_count }} tx)</span></div>
      <span class="val">Rs {{ number_format($c->total_volume) }}</span>
    </div>
  @empty
    <p class="mc-empty">{{ __('messages.no_currency_data_yet') }}</p>
  @endforelse
</div>

<div class="mc-card">
  <h3>👥 {{ __('messages.top_customers_volume') }}</h3>
  @forelse($topCustomers as $i => $c)
    <div class="mc-bar">
      <div class="lhs"><div class="rank">{{ $i + 1 }}</div><span>{{ $c->customer_name }} ({{ $c->tx_count }} tx)</span></div>
      <span class="val">Rs {{ number_format($c->total_volume) }}</span>
    </div>
  @empty
    <p class="mc-empty">{{ __('messages.no_customer_data_yet') }}</p>
  @endforelse
</div>

{{-- ═══ MoneyChanger AI ═══ --}}
<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">📊 {{ __('messages.moneychanger_ai_daily_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.moneychanger_ai_daily_sub') }}</p>
  <button id="moneychangerAiDailyBtn" onclick="loadMcDaily()" style="background:var(--accent,#c9a227);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.moneychanger_ai_run_daily') }}</button>
  <div id="moneychangerAiDailyOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<div class="card" style="background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;margin-top:20px;">
  <h3 style="font-size:16px;font-weight:800;margin:0 0 12px;">💡 {{ __('messages.moneychanger_ai_insights_title') }}</h3>
  <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">{{ __('messages.moneychanger_ai_insights_sub') }}</p>
  <button id="moneychangerAiInsightsBtn" onclick="loadMcInsights()" style="background:var(--accent,#9b6dff);color:#fff;border:0;border-radius:10px;padding:10px 20px;font-weight:800;cursor:pointer;font-size:13px;">{{ __('messages.moneychanger_ai_run_insights') }}</button>
  <div id="moneychangerAiInsightsOut" style="margin-top:14px;font-size:13px;"></div>
</div>

<script>
async function mcAiPost(url){
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:'{}' });
  return await r.json();
}
function mcAiErr(el,msg){ el.innerHTML='<div style="padding:10px;border-radius:8px;background:#fee;color:#900;font-weight:700;">'+msg+'</div>'; }
async function loadMcDaily(){
  const btn=document.getElementById('moneychangerAiDailyBtn'), out=document.getElementById('moneychangerAiDailyOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.moneychanger_ai_loading") }}';
  try{
    const data=await mcAiPost('{{ route("provider.money-changer.ai.daily") }}');
    if(data.error){ mcAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.data_warning) html+='<div style="background:#fef3c7;border:1px solid #f59e0b;padding:8px;border-radius:6px;margin-bottom:8px;font-size:11px;">⚠️ '+data.data_warning+'</div>';
    if(data.summary) html+='<div style="font-weight:800;margin-bottom:8px;">'+data.summary+'</div>';
    if(data.transactions_count!==undefined) html+='<div style="font-size:12px;margin-bottom:8px;">Transactions: <strong>'+data.transactions_count+'</strong> · Volume: Rs '+((data.total_npr||0).toLocaleString())+' · Profit: Rs '+((data.total_profit||0).toLocaleString())+'</div>';
    var byc = data.by_currency||{};
    Object.keys(byc).forEach(function(cur){
      var c = byc[cur];
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><span><strong>'+cur+'</strong> · '+c.count+' txns</span><strong>Rs '+((c.profit||0).toLocaleString())+'</strong></div>';
    });
    if(data.assumptions&&data.assumptions.nrb_note) html+='<div style="margin-top:8px;padding:6px 10px;background:#eff6ff;border-radius:6px;font-size:10px;color:#1e40af;">📋 '+data.assumptions.nrb_note+'</div>';
    out.innerHTML=html;
  }catch(e){ mcAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
async function loadMcInsights(){
  const btn=document.getElementById('moneychangerAiInsightsBtn'), out=document.getElementById('moneychangerAiInsightsOut');
  btn.disabled=true; btn.style.opacity=.6; out.innerHTML='⏳ {{ __("messages.moneychanger_ai_loading") }}';
  try{
    const data=await mcAiPost('{{ route("provider.money-changer.ai.insights") }}');
    if(data.error){ mcAiErr(out, data.message||data.error); return; }
    let html='';
    if(data.summary) html+='<div style="font-weight:800;margin-bottom:8px;">'+data.summary+'</div>';
    if(data.top_profit_currency) html+='<div style="font-size:12px;margin-bottom:8px;">🏆 Top profit: <strong>'+data.top_profit_currency+'</strong> · Top volume: <strong>'+data.top_volume_currency+'</strong></div>';
    var byc = data.by_currency||{};
    Object.keys(byc).forEach(function(cur){
      var c = byc[cur];
      html+='<div style="display:flex;justify-content:space-between;padding:6px 10px;background:var(--bg,#f5f7fb);border-radius:8px;margin-bottom:6px;"><span><strong>'+cur+'</strong> · Buy '+c.buy_count+' / Sell '+c.sell_count+'</span><strong>Rs '+((c.profit||0).toLocaleString())+'</strong></div>';
    });
    if(data.assumptions&&data.assumptions.nrb_note) html+='<div style="margin-top:8px;padding:6px 10px;background:#eff6ff;border-radius:6px;font-size:10px;color:#1e40af;">📋 '+data.assumptions.nrb_note+' · '+data.assumptions.period+'</div>';
    out.innerHTML=html;
  }catch(e){ mcAiErr(out,e.message); } finally{ btn.disabled=false; btn.style.opacity=1; }
}
</script>
@endsection
@extends($provider_layout ?? 'layouts.provider-money-changer')
@section('title', __('messages.nav_customers'))
@section('page-title', __('messages.nav_customers'))
@section('page-subtitle', 'Your regular customers and their volume')

@section('content')
<style>
  .mc-hero{background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .mc-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .mc-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .mc-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .mc-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:768px){.mc-stats{grid-template-columns:1fr;}}
  .mc-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:14px;padding:18px;}
  .mc-stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);font-weight:800;}
  .mc-stat-val{font-size:22px;font-weight:800;color:var(--text,#0f172a);margin-top:8px;}
  .mc-stat-val.accent{color:var(--accent,#10b981);}
  .cust-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
  .cust-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;padding:20px;transition:.2s;}
  .cust-card:hover{box-shadow:0 12px 28px -12px rgba(16,185,129,.25);border-color:var(--accent,#10b981);transform:translateY(-2px);}
  .cust-head{display:flex;align-items:center;gap:12px;margin-bottom:14px;}
  .cust-av{width:46px;height:46px;border-radius:13px;background:linear-gradient(135deg,var(--accent,#10b981),var(--accent-d,#059669));color:#fff;display:grid;place-items:center;font-size:16px;font-weight:900;flex-shrink:0;}
  .cust-name{font-size:15px;font-weight:800;color:var(--text,#0f172a);margin:0;}
  .cust-meta{font-size:11.5px;color:var(--muted,#64748b);font-weight:600;margin-top:2px;}
  .cust-rows{display:grid;gap:8px;padding-top:12px;border-top:1px dashed var(--line,#e8edf5);}
  .cust-row{display:flex;justify-content:space-between;font-size:13px;}
  .cust-row .k{color:var(--muted,#64748b);font-weight:600;}
  .cust-row .v{font-weight:800;color:var(--text,#0f172a);}
  .cust-row .v.accent{color:var(--accent,#10b981);}
  .mc-empty{text-align:center;padding:48px 20px;color:var(--muted,#64748b);background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;}
</style>

<div class="mc-hero">
  <h1>👥 {{ __('messages.nav_customers') }}</h1>
  <p>Your regular customers and their volume</p>
</div>

<div class="mc-stats">
  <div class="mc-stat"><div class="mc-stat-lbl">{{ __('messages.total_customers') }}</div><div class="mc-stat-val">{{ $totalCustomers }}</div></div>
  <div class="mc-stat"><div class="mc-stat-lbl">{{ __('messages.repeat_customers') }}</div><div class="mc-stat-val accent">{{ $repeatCustomers }}</div></div>
  <div class="mc-stat"><div class="mc-stat-lbl">{{ __('messages.total_volume') }}</div><div class="mc-stat-val">Rs {{ number_format($totalVolume) }}</div></div>
</div>

@if($customers->count())
  <div class="cust-grid">
    @foreach($customers as $c)
      <div class="cust-card">
        <div class="cust-head">
          <div class="cust-av">{{ strtoupper($c->customer_initials ?? substr($c->customer_name, 0, 2)) }}</div>
          <div>
            <h3 class="cust-name">{{ $c->customer_name }}</h3>
            <div class="cust-meta">Last: {{ $c->last_tx_at ? \Carbon\Carbon::parse($c->last_tx_at)->format('M d, Y') : '—' }}</div>
          </div>
        </div>
        <div class="cust-rows">
          <div class="cust-row"><span class="k">Transactions</span><span class="v">{{ $c->tx_count }}</span></div>
          <div class="cust-row"><span class="k">{{ __('messages.total_volume') }}</span><span class="v accent">Rs {{ number_format($c->total_volume) }}</span></div>
          <div class="cust-row"><span class="k">Profit</span><span class="v">Rs {{ number_format($c->total_profit) }}</span></div>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="mc-empty">👥 No customers yet. Add transactions from dashboard.</div>
@endif
@endsection
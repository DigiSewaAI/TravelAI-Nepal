@extends($provider_layout ?? 'layouts.provider-transport')
@section('title', __('messages.offers_management'))
@section('page-title', __('messages.offers_management'))
@section('page-subtitle', 'Manage transport promotions and special offers')

@section('content')
<style>
  .off-hero{background:linear-gradient(135deg,var(--accent,#0ea5e9),var(--accent-d,#0284c7));border-radius:18px;padding:26px 28px;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);}
  .off-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .off-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;position:relative;z-index:1;}
  .off-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;position:relative;z-index:1;}
  .off-table{background:var(--card,#fff);border:1px solid var(--line,#e4ecf6);border-radius:16px;overflow:hidden;}
  .off-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;padding:16px 22px;border-bottom:1px solid var(--line,#e4ecf6);align-items:center;}
  .off-row:last-child{border-bottom:none;}
  .off-row.head{background:var(--bg,#f3f7fc);font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7f96);font-weight:800;}
  .off-title{font-weight:800;color:var(--text,#0c1e33);font-size:14px;}
  .off-pill{padding:4px 11px;border-radius:20px;font-size:11px;font-weight:800;display:inline-block;}
  .p-on{background:var(--green-soft,#e6f8f1);color:var(--green,#10b981);}
  .p-off{background:var(--red-soft,#fdecec);color:var(--red,#ef4444);}
  .off-empty{text-align:center;color:var(--muted,#6b7f96);padding:40px;font-size:13px;}
</style>

<div class="off-hero">
  <h1>🎁 {{ __('messages.offers_management') }}</h1>
  <p>Manage transport promotions and special offers</p>
</div>

<div class="off-table">
  <div class="off-row head">
    <div>{{ __('messages.offer_title') }}</div>
    <div>{{ __('messages.discount') }}</div>
    <div>{{ __('messages.valid_until') }}</div>
    <div>{{ __('messages.status') }}</div>
  </div>
  @forelse($offers as $o)
    <div class="off-row">
      <div class="off-title">{{ $o->title }}</div>
      <div style="font-weight:800;color:var(--accent,#0ea5e9);">{{ $o->discount }}</div>
      <div style="color:var(--muted,#6b7f96);font-weight:600;">{{ $o->valid_until->format('M d, Y') }}</div>
      <div>
        @if($o->status === 'active')
          <span class="off-pill p-on">{{ __('messages.active') }}</span>
        @else
          <span class="off-pill p-off">{{ __('messages.inactive') }}</span>
        @endif
      </div>
    </div>
  @empty
    <p class="off-empty">{{ __('messages.no_offers_found') }}</p>
  @endforelse
</div>
@endsection
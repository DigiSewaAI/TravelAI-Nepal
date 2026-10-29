@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.ads_menu'))
@section('content')
<style>
.pa-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3);display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}
.pa-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.pa-hero>div,.pa-hero>a{position:relative;z-index:1}
.pa-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px;color:#fff}
.pa-hero p{font-size:13px;margin:0;opacity:.92;color:#fff}
.pa-hero-btn{display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--accent,#2563eb);padding:11px 20px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;box-shadow:0 6px 18px rgba(0,0,0,.15);transition:.2s}
.pa-hero-btn:hover{transform:translateY(-1px);box-shadow:0 10px 24px rgba(0,0,0,.2)}
.pa-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px}
.pa-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;padding:18px;position:relative;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.04)}
.pa-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:16px 16px 0 0}
.pa-stat.green::before{background:var(--green,#10b981)}
.pa-stat.amber::before{background:var(--amber,#f59e0b)}
.pa-stat.red::before{background:var(--red,#ef4444)}
.pa-stat.gray::before{background:var(--muted,#64748b)}
.pa-stat .ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;font-size:16px;margin-bottom:12px}
.pa-stat.green .ic{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pa-stat.amber .ic{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.pa-stat.red .ic{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.pa-stat.gray .ic{background:var(--bg,#f4f6fb);color:var(--muted,#64748b)}
.pa-stat .lbl{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#64748b);font-weight:800;margin-bottom:6px}
.pa-stat .val{font-size:26px;font-weight:800;color:var(--text,#0f172a);line-height:1}
.pa-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;overflow:hidden;box-shadow:0 4px 24px -8px rgba(15,23,42,.08)}
.pa-grid{padding:24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
.pa-tile{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;overflow:hidden;text-decoration:none;transition:.25s ease;display:block}
.pa-tile:hover{transform:translateY(-4px);box-shadow:0 16px 40px -14px rgba(15,23,42,.18);border-color:var(--accent,#2563eb)}
.pa-img{height:150px;background:var(--bg,#f4f6fb);overflow:hidden}
.pa-img img{width:100%;height:100%;object-fit:cover;display:block}
.pa-body{padding:18px}
.pa-body h3{font-size:15px;font-weight:800;color:var(--text,#0f172a);margin:0 0 10px;line-height:1.3}
.pa-meta{display:flex;flex-wrap:wrap;gap:12px;font-size:11.5px;color:var(--muted,#64748b);margin-bottom:14px;font-weight:600}
.pa-meta span{display:inline-flex;align-items:center;gap:4px}
.pa-mini{display:flex;gap:24px;padding:12px 0;border-top:1px solid var(--line,#e8edf5);margin-bottom:12px}
.pa-mini>div{display:flex;flex-direction:column}
.pa-mini .num{font-size:18px;font-weight:800;color:var(--accent,#2563eb);line-height:1}
.pa-mini .lbl{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#64748b);font-weight:700;margin-top:3px}
.pa-badge{display:inline-flex;padding:5px 12px;border-radius:20px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
.pa-badge.green{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.pa-badge.amber{background:var(--amber-soft,#fef5e3);color:var(--amber,#f59e0b)}
.pa-badge.blue{background:var(--blue-soft,#e9f1fe);color:var(--accent,#2563eb)}
.pa-badge.red{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.pa-badge.gray{background:var(--bg,#f4f6fb);color:var(--muted,#64748b)}
.pa-pagination{padding:18px 24px;border-top:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.pa-empty{padding:80px 24px;text-align:center;background:linear-gradient(180deg,var(--bg,#f8fafc) 0%,var(--card,#fff) 100%);border-radius:18px;border:1px solid var(--line,#e8edf5)}
.pa-empty-ic{width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,var(--accent-soft,rgba(37,99,235,.15)),var(--accent-soft,rgba(37,99,235,.08)));color:var(--accent,#2563eb);display:inline-grid;place-items:center;font-size:40px;margin-bottom:24px;box-shadow:0 16px 40px -12px var(--accent,#2563eb);border:2px solid rgba(255,255,255,.5)}
.pa-empty h2{font-size:24px;font-weight:800;color:var(--text,#0f172a);margin:0 0 10px;letter-spacing:-.5px}
.pa-empty p{font-size:14px;color:var(--muted,#64748b);max-width:440px;margin:0 auto 26px;line-height:1.7}
.pa-cta{display:inline-flex;align-items:center;gap:9px;background:var(--accent,#2563eb);color:#fff;padding:14px 28px;border-radius:12px;font-size:14px;font-weight:800;text-decoration:none;box-shadow:0 12px 30px -10px var(--accent,#2563eb);transition:.2s}
.pa-cta:hover{background:var(--accent-d,#1d4ed8);transform:translateY(-2px);box-shadow:0 18px 40px -12px var(--accent,#2563eb)}
</style>

<div>
  <div class="pa-hero">
    <div>
      <h1><i class="fas fa-bullhorn"></i> {{ __('messages.ads_menu') }}</h1>
      <p>{{ __('messages.ads_subtitle') }}</p>
    </div>
    <a href="{{ route('provider.ads.create') }}" class="pa-hero-btn">
      <i class="fas fa-plus"></i> {{ __('messages.ads_buy_new') }}
    </a>
  </div>

  <div class="pa-stats">
    <div class="pa-stat green">
      <div class="ic"><i class="fas fa-check"></i></div>
      <div class="lbl">{{ __('messages.ads_status_active') }}</div>
      <div class="val">{{ $stats['active'] }}</div>
    </div>
    <div class="pa-stat amber">
      <div class="ic"><i class="fas fa-clock"></i></div>
      <div class="lbl">{{ __('messages.ads_status_pending') }}</div>
      <div class="val">{{ $stats['pending'] }}</div>
    </div>
    <div class="pa-stat red">
      <div class="ic"><i class="fas fa-times"></i></div>
      <div class="lbl">{{ __('messages.ads_status_rejected') }}</div>
      <div class="val">{{ $stats['rejected'] }}</div>
    </div>
    <div class="pa-stat gray">
      <div class="ic"><i class="fas fa-hourglass-end"></i></div>
      <div class="lbl">{{ __('messages.ads_status_expired') }}</div>
      <div class="val">{{ $stats['expired'] }}</div>
    </div>
  </div>

  @if($ads->count() > 0)
    <div class="pa-card">
      <div class="pa-grid">
        @foreach($ads as $ad)
          <a href="{{ route('provider.ads.show', $ad) }}" class="pa-tile">
            <div class="pa-img">
              <img src="{{ Storage::url($ad->image_path) }}" alt="{{ $ad->title }}">
            </div>
            <div class="pa-body">
              <h3>{{ $ad->title }}</h3>
              <div class="pa-meta">
                <span><i class="fas fa-clock"></i> {{ $ad->duration_days }} {{ __('messages.ads_days') }}</span>
                <span><i class="fas fa-coins"></i> Rs. {{ number_format($ad->price_paid) }}</span>
              </div>
              <div class="pa-mini">
                <div><span class="num">{{ $ad->impressions }}</span><span class="lbl">views</span></div>
                <div><span class="num">{{ $ad->clicks }}</span><span class="lbl">clicks</span></div>
              </div>
              @php
                $__colors = [
                  'active'=>'green','pending_review'=>'amber','payment_pending'=>'blue',
                  'approved'=>'blue','rejected'=>'red','expired'=>'gray',
                ];
                $__c = $__colors[$ad->status] ?? 'gray';
              @endphp
              <span class="pa-badge {{ $__c }}">{{ ucfirst(str_replace('_', ' ', $ad->status)) }}</span>
            </div>
          </a>
        @endforeach
      </div>
      <div class="pa-pagination">{{ $ads->links() }}</div>
    </div>
  @else
    <div class="pa-empty">
      <div class="pa-empty-ic"><i class="fas fa-bullhorn"></i></div>
      <h2>{{ __('messages.ads_empty') }}</h2>
      <p>{{ __('messages.ads_empty_sub') }}</p>
      <a href="{{ route('provider.ads.create') }}" class="pa-cta">
        <i class="fas fa-plus"></i> {{ __('messages.ads_buy_new') }}
      </a>
    </div>
  @endif
</div>
@endsection
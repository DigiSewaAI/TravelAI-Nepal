@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.sos_alerts_title'))
@section('content')
<style>
.ps-hero{background:linear-gradient(135deg,var(--accent,#2563eb) 0%,var(--accent-d,#1d4ed8) 100%);border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden;box-shadow:0 18px 40px -14px rgba(0,0,0,.3)}
.ps-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:55%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 60%);pointer-events:none}
.ps-hero h1{font-size:23px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;color:#fff;position:relative;z-index:1}
.ps-hero p{font-size:13px;margin:0;opacity:.92;color:#fff;position:relative;z-index:1}
.ps-hero .badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.25);padding:5px 12px;border-radius:20px;font-size:12px;font-weight:800}
.ps-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:22px}
.ps-stat{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:16px;padding:18px;position:relative;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.04)}
.ps-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:16px 16px 0 0}
.ps-stat.total::before{background:var(--accent,#2563eb)}
.ps-stat.active::before{background:var(--red,#ef4444)}
.ps-stat.resolved::before{background:var(--green,#10b981)}
.ps-stat .ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;font-size:16px;margin-bottom:12px}
.ps-stat.total .ic{background:var(--accent-soft,rgba(37,99,235,.1));color:var(--accent,#2563eb)}
.ps-stat.active .ic{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.ps-stat.resolved .ic{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.ps-stat .lbl{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted,#64748b);font-weight:800;margin-bottom:6px}
.ps-stat .val{font-size:26px;font-weight:800;color:var(--text,#0f172a);line-height:1}
.ps-card{background:var(--card,#fff);border:1px solid var(--line,#e8edf5);border-radius:18px;overflow:hidden;box-shadow:0 4px 24px -8px rgba(15,23,42,.08)}
.ps-tabs{padding:18px 24px;border-bottom:1px solid var(--line,#e8edf5);display:flex;gap:8px;flex-wrap:wrap;background:var(--bg,#f8fafc)}
.ps-tab{padding:9px 18px;border-radius:10px;font-size:12.5px;font-weight:700;text-decoration:none;color:var(--muted,#64748b);background:var(--card,#fff);border:1px solid var(--line,#e8edf5);transition:.2s}
.ps-tab:hover{border-color:var(--accent,#2563eb);color:var(--accent,#2563eb)}
.ps-tab.active{background:var(--accent,#2563eb);color:#fff;border-color:var(--accent,#2563eb);box-shadow:0 4px 14px -4px var(--accent,#2563eb)}
.ps-item{display:block;padding:20px 24px;border-bottom:1px solid var(--line,#e8edf5);text-decoration:none;transition:.2s}
.ps-item:last-child{border-bottom:none}
.ps-item:hover{background:var(--accent-soft,rgba(37,99,235,.04))}
.ps-item-inner{display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap}
.ps-item-icon{width:52px;height:52px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;font-size:20px}
.ps-item-icon.danger{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.ps-item-icon.ok{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.ps-item-body{flex:1;min-width:0}
.ps-item-head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px}
.ps-item-head h3{font-size:15px;font-weight:800;color:var(--text,#0f172a);margin:0}
.ps-pill{display:inline-flex;padding:4px 10px;border-radius:20px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
.ps-pill.active{background:var(--red-soft,#fdecec);color:var(--red,#ef4444)}
.ps-pill.resolved{background:var(--green-soft,#e7f8f1);color:var(--green,#10b981)}
.ps-meta{display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:var(--muted,#64748b);font-weight:600;margin-top:8px}
.ps-meta span{display:inline-flex;align-items:center;gap:5px}
.ps-msg{font-size:13px;color:var(--text,#0f172a);margin:6px 0;line-height:1.5}
.ps-arrow{flex-shrink:0;color:var(--muted,#64748b);align-self:center;font-size:14px}
.ps-pagination{padding:18px 24px;border-top:1px solid var(--line,#e8edf5);background:var(--bg,#f8fafc)}
.ps-empty{padding:80px 24px;text-align:center;background:linear-gradient(180deg,var(--green-soft,#e7f8f1) 0%,var(--card,#fff) 100%);border-radius:18px;border:1px solid var(--line,#e8edf5)}
.ps-empty-ic{width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,var(--green-soft,#e7f8f1),rgba(16,185,129,.2));color:var(--green,#10b981);display:inline-grid;place-items:center;font-size:44px;margin-bottom:24px;box-shadow:0 16px 40px -12px var(--green,#10b981)}
.ps-empty h2{font-size:24px;font-weight:800;color:var(--text,#0f172a);margin:0 0 10px;letter-spacing:-.5px}
.ps-empty p{font-size:14px;color:var(--muted,#64748b);max-width:440px;margin:0 auto;line-height:1.7}
</style>

<div>
  <div class="ps-hero">
    <h1>
      <i class="fas fa-exclamation-triangle"></i>
      {{ __('messages.sos_alerts_title') }}
      @if($activeCount > 0)
        <span class="badge"><i class="fas fa-circle" style="font-size:7px"></i> {{ $activeCount }}</span>
      @endif
    </h1>
    <p>{{ __('messages.sos_alerts_subtitle') }}</p>
  </div>

  <div class="ps-stats">
    <div class="ps-stat total">
      <div class="ic"><i class="fas fa-bell"></i></div>
      <div class="lbl">Total</div>
      <div class="val">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="ps-stat active">
      <div class="ic"><i class="fas fa-exclamation-circle"></i></div>
      <div class="lbl">Active</div>
      <div class="val">{{ $stats['active'] ?? $activeCount }}</div>
    </div>
    <div class="ps-stat resolved">
      <div class="ic"><i class="fas fa-check-circle"></i></div>
      <div class="lbl">Resolved</div>
      <div class="val">{{ $stats['resolved'] ?? 0 }}</div>
    </div>
  </div>

  <div class="ps-card">
    <div class="ps-tabs">
      @foreach(['all' => __('messages.sos_alerts_tab_all'), 'active' => __('messages.sos_alerts_tab_active'), 'resolved' => __('messages.sos_alerts_tab_resolved')] as $key => $label)
        <a href="{{ route('provider.sos-alerts.index', ['status' => $key]) }}" class="ps-tab {{ $status === $key ? 'active' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    @if($alerts->count() > 0)
      <div>
        @foreach($alerts as $alert)
          @php $__isActive = in_array($alert->status, ['pending', 'sent']); @endphp
          <a href="{{ route('provider.sos-alerts.show', $alert) }}" class="ps-item">
            <div class="ps-item-inner">
              <div class="ps-item-icon {{ $__isActive ? 'danger' : 'ok' }}">
                <i class="fas {{ $__isActive ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
              </div>
              <div class="ps-item-body">
                <div class="ps-item-head">
                  <h3>{{ $alert->traveler->name ?? __('messages.sos_alerts_unknown_traveler') }}</h3>
                  @if($__isActive)
                    <span class="ps-pill active">{{ __('messages.sos_alerts_status_active') }}</span>
                  @else
                    <span class="ps-pill resolved">{{ __('messages.sos_alerts_status_resolved') }}</span>
                  @endif
                </div>
                @if($alert->booking && $alert->booking->service)
                  <p class="ps-meta"><span><i class="fas fa-hiking"></i> {{ $alert->booking->service->name }}</span></p>
                @endif
                @if($alert->message)
                  <p class="ps-msg">"{{ $alert->message }}"</p>
                @endif
                <div class="ps-meta">
                  <span><i class="fas fa-map-marker-alt" style="color:var(--red,#ef4444)"></i> {{ number_format($alert->latitude, 4) }}, {{ number_format($alert->longitude, 4) }}</span>
                  <span><i class="far fa-clock"></i> {{ $alert->created_at->diffForHumans() }}</span>
                </div>
              </div>
              <i class="fas fa-chevron-right ps-arrow"></i>
            </div>
          </a>
        @endforeach
      </div>
      <div class="ps-pagination">{{ $alerts->links() }}</div>
    @else
      <div class="ps-empty">
        <div class="ps-empty-ic"><i class="fas fa-check-circle"></i></div>
        <h2>{{ __('messages.sos_alerts_empty') }}</h2>
        <p>{{ __('messages.sos_alerts_empty_sub') }}</p>
      </div>
    @endif
  </div>
</div>
@endsection
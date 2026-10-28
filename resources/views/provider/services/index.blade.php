@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.services_page_title'))
@section('header', __('messages.manage_services'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $totalCount = $services ? $services->count() : 0;
    $activeCount = $services ? $services->where('status', 'active')->count() : 0;
    $categoryCount = $services ? $services->pluck('service_category_id')->filter()->unique()->count() : 0;
@endphp

<style>
  .svc-wrap{max-width:1200px;margin:0 auto;}
  .svc-hero{background:linear-gradient(135deg,var(--accent,#2563eb),var(--accent-d,#1d4ed8));border-radius:18px;padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden;box-shadow:0 18px 40px -18px rgba(0,0,0,.35);margin-bottom:22px;}
  .svc-hero::before{content:'';position:absolute;top:-60%;right:-10%;width:60%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 60%);pointer-events:none;}
  .svc-hero-text{position:relative;z-index:1;}
  .svc-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px;}
  .svc-hero p{font-size:13px;opacity:.92;margin:0;font-weight:500;}
  .svc-hero-actions{position:relative;z-index:1;}
  .svc-add-btn{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.96);color:var(--accent-d,#1d4ed8);padding:11px 18px;border-radius:11px;font-size:13px;font-weight:800;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.15);transition:.2s;}
  .svc-add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.22);}
  .svc-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
  @media(max-width:768px){.svc-stats{grid-template-columns:1fr;}}
  .svc-stat{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;transition:.2s;}
  .svc-stat:hover{box-shadow:0 8px 20px -10px rgba(0,0,0,.08);}
  .svc-stat-ic{width:42px;height:42px;border-radius:12px;background:var(--accent-soft,#eff6ff);color:var(--accent,#2563eb);display:grid;place-items:center;flex-shrink:0;}
  .svc-stat-ic svg{width:20px;height:20px;stroke-width:2.2;}
  .svc-stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted,#6b7280);font-weight:800;}
  .svc-stat-val{font-size:20px;font-weight:800;color:var(--text,#1f2937);margin-top:2px;}
  .svc-card{background:var(--card,#fff);border:1px solid var(--line,#e5e7eb);border-radius:16px;overflow:hidden;}
  .svc-card-head{padding:18px 22px;border-bottom:1px solid var(--line,#e5e7eb);display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;}
  .svc-card-head h2{font-size:15px;font-weight:800;color:var(--text,#1f2937);margin:0;}
  .svc-tbl-wrap{overflow-x:auto;}
  .svc-tbl{width:100%;border-collapse:collapse;min-width:700px;}
  .svc-tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--muted,#6b7280);font-weight:800;padding:14px 18px;background:var(--bg,#f9fafb);border-bottom:1px solid var(--line,#e5e7eb);}
  .svc-tbl td{padding:16px 18px;font-size:13px;font-weight:600;color:var(--text,#1f2937);border-bottom:1px solid var(--line,#e5e7eb);vertical-align:middle;}
  .svc-tbl tbody tr:hover{background:var(--accent-soft,#eff6ff);}
  .svc-tbl tbody tr:last-child td{border-bottom:none;}
  .svc-name{font-weight:800;color:var(--text,#1f2937);font-size:14px;}
  .svc-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;}
  .p-active{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);}
  .p-inactive{background:var(--line,#e5e7eb);color:var(--muted,#6b7280);}
  .p-green{background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);}
  .p-yellow{background:var(--amber-soft,#fef3c7);color:var(--amber,#f59e0b);}
  .p-gray{background:var(--line,#e5e7eb);color:var(--muted,#6b7280);}
  .svc-actions{display:flex;gap:8px;align-items:center;}
  .svc-icon-btn{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;transition:.15s;text-decoration:none;font-size:13px;}
  .svc-icon-edit{background:var(--accent-soft,#eff6ff);color:var(--accent-d,#1d4ed8);}
  .svc-icon-edit:hover{background:var(--accent,#2563eb);color:#fff;}
  .svc-icon-del{background:var(--red-soft,#fee2e2);color:var(--red,#dc2626);}
  .svc-icon-del:hover{background:var(--red,#dc2626);color:#fff;}
  .svc-itinerary-link{color:var(--accent,#2563eb);font-size:12px;font-weight:800;text-decoration:none;white-space:nowrap;}
  .svc-itinerary-link:hover{text-decoration:underline;}
  .svc-empty{padding:56px 24px;text-align:center;color:var(--muted,#6b7280);}
  .svc-empty svg{width:56px;height:56px;margin:0 auto 14px;opacity:.35;stroke-width:1.5;}
  .svc-empty a{color:var(--accent,#2563eb);font-weight:800;text-decoration:underline;}
</style>

<div class="svc-wrap">

  <div class="svc-hero">
    <div class="svc-hero-text">
      <h1>🎯 {{ __('messages.your_services') }}</h1>
      <p>{{ __('messages.manage_services') }} — {{ $totalCount }} {{ $totalCount === 1 ? 'service' : 'services' }}</p>
    </div>
    <div class="svc-hero-actions">
      <a href="{{ route('provider.services.create') }}" class="svc-add-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M12 5v14M5 12h14"/></svg>
        {{ __('messages.add_new_service') }}
      </a>
    </div>
  </div>

  <div class="svc-stats">
    <div class="svc-stat">
      <div class="svc-stat-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
      </div>
      <div><div class="svc-stat-lbl">{{ __('messages.your_services') }}</div><div class="svc-stat-val">{{ $totalCount }}</div></div>
    </div>
    <div class="svc-stat">
      <div class="svc-stat-ic" style="background:var(--green-soft,#dcfce7);color:var(--green,#16a34a);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div><div class="svc-stat-lbl">{{ __('messages.active') }}</div><div class="svc-stat-val">{{ $activeCount }}</div></div>
    </div>
    <div class="svc-stat">
      <div class="svc-stat-ic" style="background:var(--blue-soft,#dbeafe);color:var(--blue,#3b82f6);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      </div>
      <div><div class="svc-stat-lbl">{{ __('messages.category') }}</div><div class="svc-stat-val">{{ $categoryCount }}</div></div>
    </div>
  </div>

  <div class="svc-card">
    <div class="svc-card-head">
      <h2>{{ __('messages.your_services') }}</h2>
    </div>

    @if($services && $services->count() > 0)
      @php
        $itineraryCategories = ['trek', 'tour', 'activity', 'experience'];
        $showItineraryColumn = $services->contains(fn($s) => in_array($s->category->slug ?? '', $itineraryCategories));
      @endphp
      <div class="svc-tbl-wrap">
        <table class="svc-tbl">
          <thead>
            <tr>
              <th>{{ __('messages.name') }}</th>
              <th>{{ __('messages.category') }}</th>
              <th>{{ __('messages.price') }}</th>
              <th>{{ __('messages.status') }}</th>
              @if($showItineraryColumn)
                <th>{{ __('messages.itinerary') }}</th>
              @endif
              <th>{{ __('messages.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($services as $service)
              <tr>
                <td><div class="svc-name">{{ $service->name }}</div></td>
                <td>{{ $service->category->name ?? 'N/A' }}</td>
                <td>
                  @if($service->price !== null)
                    @php
                      $baseCurrency = $service->currency ?? 'USD';
                      $displayPrice = $currencyService->convert((float) $service->price, $baseCurrency, $displayCurrency);
                      $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
                    @endphp
                    <strong style="color:var(--accent-d,#1d4ed8);">{{ $formattedPrice }}</strong>
                  @else
                    <span style="color:var(--muted,#6b7280);font-style:italic;">N/A</span>
                  @endif
                </td>
                <td>
                  <span class="svc-pill {{ $service->status === 'active' ? 'p-active' : 'p-inactive' }}">
                    {{ $service->status === 'active' ? __('messages.active') : __('messages.inactive') }}
                  </span>
                </td>
                @if($showItineraryColumn)
                  <td>
                    @php $daysCount = $service->itinerary_days_count ?? 0; @endphp
                    <div style="display:flex;flex-direction:column;gap:4px;">
                      @if($daysCount === 0)
                        <span class="svc-pill p-gray">{{ __('messages.no_itinerary_yet') }}</span>
                      @elseif($service->itinerary_status === 'published')
                        <span class="svc-pill p-green">{{ __('messages.published') }}</span>
                      @else
                        <span class="svc-pill p-yellow">{{ __('messages.draft') }}</span>
                      @endif
                      <a href="{{ route('provider.services.itinerary.index', $service) }}" class="svc-itinerary-link">
                        {{ __('messages.manage_itinerary') }} →
                      </a>
                    </div>
                  </td>
                @endif
                <td>
                  <div class="svc-actions">
                    <a href="{{ route('provider.services.edit', $service) }}" class="svc-icon-btn svc-icon-edit" title="{{ __('messages.edit') }}">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('provider.services.destroy', $service) }}" style="display:inline;margin:0;"
                          onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="svc-icon-btn svc-icon-del" title="{{ __('messages.delete') }}">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="svc-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>
        <p style="font-size:14px;font-weight:600;margin:0;">{!! __('messages.no_services_yet', ['link' => route('provider.services.create')]) !!}</p>
      </div>
    @endif
  </div>
</div>
@endsection
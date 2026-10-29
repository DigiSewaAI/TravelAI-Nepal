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
              <td style="text-transform:capitalize;">{{ $a->category }}</td>
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
@endsection
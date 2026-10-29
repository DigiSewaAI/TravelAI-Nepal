@extends('layouts.provider-art-craft')

@php
    $greetingName = auth()->user()->name ?? __('messages.artist');
    $subtitle = __('messages.art_welcome_subtitle', ['name' => $greetingName, 'count' => $totalArtworks]);
@endphp

@section('title', __('messages.art_dashboard_title'))
@section('page-title', __('messages.welcome_back_name', ['name' => $greetingName]))
@section('page-subtitle', $subtitle)

@push('art-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(43,30,22,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:20px}
  .i-terra{background:var(--terracotta-soft)}.i-burg{background:var(--burgundy-soft)}.i-sand{background:var(--sand-soft)}.i-green{background:var(--green-soft)}
  .delta{font-size:11px;font-weight:800;padding:4px 9px;border-radius:20px}.up{background:var(--green-soft);color:var(--green)}.down{background:var(--red-soft);color:var(--red)}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:6px}
  .stat-value{font-family:'Playfair Display',serif;font-size:26px;font-weight:700;letter-spacing:-.6px;line-height:1.1}
  .stat-value small{font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px;min-width:0}
  .span-2{grid-column:span 2}.span-3{grid-column:span 3}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
  .card-head h3{font-family:'Playfair Display',serif;font-size:17px;font-weight:700}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:3px}
  .legend{display:flex;gap:14px;align-items:center}
  .legend span{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted)}
  .legend i{width:9px;height:9px;border-radius:3px;display:block}
  .chart-total{display:flex;align-items:baseline;gap:9px;margin-top:3px}
  .chart-total b{font-family:'Playfair Display',serif;font-size:26px;font-weight:700}
  .chart-total span{font-size:11.5px;font-weight:800;color:var(--green);background:var(--green-soft);padding:3px 9px;border-radius:20px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:8px}
  .gal-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
  .gtab{border:1px solid var(--line);background:#fff;border-radius:10px;padding:8px 15px;font-family:inherit;font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;transition:.18s}
  .gtab:hover{border-color:#f0cdb8;color:var(--terracotta-d)}.gtab.on{background:var(--charcoal);border-color:var(--charcoal);color:#fff}
  .gallery{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
  .art-card{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;transition:.25s;position:relative}
  .art-card:hover{transform:translateY(-4px);box-shadow:0 16px 32px rgba(43,30,22,.12);border-color:#f0cdb8}
  .art-img{aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;font-size:52px;position:relative;overflow:hidden}
  .art-img::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.4),transparent 60%);pointer-events:none}
  .art-cat{position:absolute;top:10px;left:10px;font-size:9px;font-weight:800;padding:3px 8px;border-radius:6px;background:rgba(255,255,255,.9);color:var(--text);letter-spacing:.5px;z-index:2}
  .art-body{padding:12px 13px 13px}
  .art-name{font-family:'Playfair Display',serif;font-size:14px;font-weight:700;line-height:1.25;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .art-artist{font-size:10.5px;color:var(--muted);font-weight:600;margin-bottom:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .art-foot{display:flex;align-items:center;justify-content:space-between;gap:6px}
  .art-price{font-size:14px;font-weight:800;letter-spacing:-.3px;color:var(--text)}
  .status-btn{border:none;border-radius:20px;padding:4px 10px;font-family:inherit;font-size:10px;font-weight:800;cursor:pointer;transition:.18s;white-space:nowrap}
  .status-btn:hover{filter:brightness(.95);transform:scale(1.05)}
  .s-available{background:var(--green-soft);color:var(--green)}.s-sold{background:var(--red-soft);color:var(--red)}.s-custom{background:var(--amber-soft);color:#a8741a}
  .art-delete{margin-top:8px;width:100%;background:var(--red-soft);color:var(--red);border:none;padding:6px;border-radius:6px;font-size:10.5px;font-weight:800;cursor:pointer;font-family:inherit;transition:.15s}
  .art-delete:hover{background:#fecaca;transform:scale(1.02)}
  .tbl-wrap{overflow-x:auto}.tbl{width:100%;border-collapse:collapse;min-width:420px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#b3a090;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f8f2ea;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}.tbl tbody tr:hover{background:#fdf9f5}
  .client{display:flex;align-items:center;gap:10px}
  .c-av{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:12px;flex-shrink:0}
  .c-name{font-weight:800;font-size:12.5px;line-height:1.25}.c-mail{font-size:10.5px;color:var(--muted);font-weight:600}
  .type-badge{font-size:10px;font-weight:800;padding:4px 9px;border-radius:6px;letter-spacing:.3px;display:inline-block}
  .t-painting{background:var(--terracotta-soft);color:var(--terracotta-d)}.t-sculpture{background:var(--burgundy-soft);color:var(--burgundy)}.t-handicraft{background:var(--sand-soft);color:#a8741a}.t-mural{background:var(--green-soft);color:var(--green)}
  .pill{font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;display:inline-block;border:none;cursor:pointer}
  .p-pending{background:var(--amber-soft);color:#a8741a}.p-progress{background:var(--burgundy-soft);color:var(--burgundy)}.p-done{background:var(--green-soft);color:var(--green)}
  .delete-order{background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;padding:2px 6px;border-radius:5px;transition:.15s}
  .delete-order:hover{background:var(--red-soft)}
  .empty{text-align:center;padding:32px 16px;color:var(--muted);font-size:13px;font-weight:600;grid-column:1/-1}
  .empty div{font-size:36px;margin-bottom:8px;opacity:.5}
  .action-bar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:18px;flex-wrap:wrap}
  .btn-secondary{background:var(--card);color:var(--text);border:1px solid var(--line);box-shadow:none}
  @media (max-width:1400px){.gallery{grid-template-columns:repeat(3,1fr)}}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2,.span-3{grid-column:span 1}.gallery{grid-template-columns:repeat(3,1fr)}}
  @media (max-width:720px){.gallery{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.gallery{grid-template-columns:1fr 1fr;gap:10px}.art-body{padding:10px}.art-name{font-size:12.5px}.art-price{font-size:12.5px}.action-bar{justify-content:stretch}.action-bar button{flex:1}}
</style>
@endpush

@section('content')
{{-- PHASE-1-WELCOME: Premium gradient banner (Trek parity) --}}
@php
    $__vp = auth()->user()->getCurrentProvider();
    $__isVerified = $__vp && $__vp->verification_status === 'verified';
@endphp
<div style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-d, var(--accent)) 100%); border-radius: 16px; padding: 22px 26px; margin-bottom: 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; box-shadow: 0 15px 35px -12px rgba(0,0,0,.25);">
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 22px; font-weight: 800; margin: 0 0 4px 0; letter-spacing: -0.3px;">
            {{ __('messages.welcome_back') }}, {{ $greetingName }}! &#128075;
            @if($__isVerified)
            <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,0.22);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;margin-left:10px;vertical-align:middle;">&#10003; {{ __('messages.verified') }}</span>
            @endif
        </h1>
        <p style="font-size: 13px; opacity: 0.92; margin: 0;">{{ $propertyName ?? auth()->user()->getCurrentProvider()?->name ?? '' }}</p>
    </div>
</div>

{{-- 🆕 Action Bar --}}
<div class="action-bar">
  <button class="btn-primary btn-secondary" id="newOrderBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;stroke-width:2.6">
      <path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>
    </svg>
    <span>{{ __('messages.new_custom_order') }}</span>
  </button>
  <button class="btn-primary" id="addArtworkBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    <span>{{ __('messages.new_artwork') }}</span>
  </button>
</div>

{{-- KYC Banner --}}
@php
    $vp = auth()->user()->getCurrentProvider();
    $needsVerification = $vp && $vp->verification_status !== 'verified';
@endphp
@if($needsVerification)
<div style="background:#fff7ed;border-left:4px solid #f59e0b;padding:16px 20px;margin-bottom:20px;border-radius:12px;display:flex;align-items:start;gap:14px">
    <div style="flex-shrink:0;width:38px;height:38px;background:#fef5e3;border-radius:10px;display:grid;place-items:center;font-size:18px">⚠️</div>
    <div style="flex:1">
        <h4 style="font-size:14px;font-weight:800;color:#92400e;margin-bottom:4px">{{ __('messages.account_unverified') }}</h4>
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_art_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-terra">🖼️</div><span class="delta up">▲ {{ __('messages.total_label') }}</span></div><div class="stat-label">{{ __('messages.total_artworks') }}</div><div class="stat-value">{{ $totalArtworks }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-burg">💎</div><span class="delta up">▲ {{ __('messages.sold_label') }}</span></div><div class="stat-label">{{ __('messages.artworks_sold_oct') }}</div><div class="stat-value">{{ $soldCount }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-sand">✍️</div><span class="delta down">{{ __('messages.needs_action') }}</span></div><div class="stat-label">{{ __('messages.custom_orders_pending') }}</div><div class="stat-value">{{ $pendingOrders }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">💰</div><span class="delta up">▲ {{ __('messages.revenue_label') }}</span></div><div class="stat-label">{{ __('messages.total_revenue_oct') }}</div><div class="stat-value"><small>Rs</small> {{ number_format($totalRevenue, 0) }}</div></div>
</section>

<section class="grid">
  <div class="card span-3">
    <div class="card-head">
      <div><h3>{{ __('messages.sales_custom_orders') }}</h3><div class="chart-total"><b>Rs {{ number_format($totalRevenue, 0) }}</b><span>{{ __('messages.this_month') }}</span></div></div>
      <div class="legend">
        <span><i style="background:#c65d3b"></i>{{ __('messages.sales') }}</span>
        <span><i style="background:#d9b382"></i>{{ __('messages.custom_orders_legend') }}</span>
      </div>
    </div>
    <div id="chart"></div>
  </div>
</section>

<section class="card" style="margin-bottom:16px">
  <div class="card-head">
    <div><h3>{{ __('messages.art_gallery') }}</h3><div class="sub">{{ __('messages.click_status_update') }}</div></div>
    <a href="#" class="link">{{ __('messages.manage_arrow') }}</a>
  </div>
  <div class="gal-tabs" id="galTabs">
    <button class="gtab on" data-filter="all">{{ __('messages.filter_all') }}</button>
    <button class="gtab" data-filter="painting">{{ __('messages.filter_paintings') }}</button>
    <button class="gtab" data-filter="sculpture">{{ __('messages.filter_sculptures') }}</button>
    <button class="gtab" data-filter="handicraft">{{ __('messages.filter_handicrafts') }}</button>
    <button class="gtab" data-filter="sold">{{ __('messages.filter_sold_out') }}</button>
  </div>
  <div class="gallery" id="gallery"></div>
</section>

<section class="card">
  <div class="card-head">
    <div><h3>{{ __('messages.recent_custom_orders') }}</h3><div class="sub">{{ __('messages.client_commissions') }}</div></div>
    <a href="#" class="link">{{ __('messages.view_all') }} →</a>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th>{{ __('messages.client') }}</th>
        <th>{{ __('messages.artwork_type') }}</th>
        <th>{{ __('messages.budget') }}</th>
        <th>{{ __('messages.deadline') }}</th>
        <th>{{ __('messages.status') }}</th>
        <th>{{ __('messages.action') }}</th>
      </tr></thead>
      <tbody id="ordersBody"></tbody>
    </table>
  </div>
</section>

{{-- 🆕 New Artwork Modal --}}
<div id="artworkModal" style="display:none;position:fixed;inset:0;background:rgba(30,22,18,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);font-family:'Playfair Display',serif">{{ __('messages.new_artwork') }}</h3>
            <button type="button" id="closeArtworkModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.art-craft.artworks.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.artwork_name') }}</label>
                    <input type="text" name="name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Himalayan Sunrise">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.artist_name') }}</label>
                    <input type="text" name="artist_name" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Anaya Shakya">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
                    <input type="number" name="price" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="45000">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.category') }}</label>
                    <select name="category" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="painting">{{ __('messages.painting') }}</option>
                        <option value="sculpture">{{ __('messages.sculpture') }}</option>
                        <option value="handicraft">{{ __('messages.handicraft') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.emoji') }}</label>
                    <input type="text" name="emoji" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="🖼️">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_artwork') }}</button>
        </form>
    </div>
</div>

{{-- 🆕 New Custom Order Modal --}}
<div id="orderModal" style="display:none;position:fixed;inset:0;background:rgba(30,22,18,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text);font-family:'Playfair Display',serif">{{ __('messages.new_custom_order') }}</h3>
            <button type="button" id="closeOrderModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.art-craft.orders.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="John Doe">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_email') }}</label>
                    <input type="email" name="client_email" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="john@example.com">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.artwork_type') }}</label>
                    <select name="type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="Painting">{{ __('messages.painting') }}</option>
                        <option value="Sculpture">{{ __('messages.sculpture') }}</option>
                        <option value="Handicraft">{{ __('messages.handicraft') }}</option>
                        <option value="Mural">{{ __('messages.mural') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.budget') }} (Rs)</label>
                    <input type="number" name="budget" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="55000">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.deadline') }}</label>
                    <input type="date" name="deadline" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_order') }}</button>
        </form>
    </div>
</div>
@endsection

@push('art-scripts')
<script>
const ARTWORKS = @json($artworks);
const ORDERS = @json($orders);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const STATUS_FLOW = ['available','sold','custom'];

/* Multilingual JS strings */
const T = {
  available:       @json(__('messages.available')),
  sold:            @json(__('messages.sold')),
  custom:          @json(__('messages.custom')),
  painting:        @json(__('messages.painting')),
  sculpture:       @json(__('messages.sculpture')),
  handicraft:      @json(__('messages.handicraft')),
  mural:           @json(__('messages.mural')),
  pending:         @json(__('messages.pending')),
  in_progress:     @json(__('messages.in_progress')),
  completed:       @json(__('messages.completed')),
  no_artworks:     @json(__('messages.no_artworks_filter')),
  no_orders:       @json(__('messages.no_custom_orders_yet')),
  delete_artwork:  @json(__('messages.delete_artwork')),
  delete_order:    @json(__('messages.delete_order')),
  confirm_art:     @json(__('messages.confirm_delete_artwork')),
  confirm_order:   @json(__('messages.confirm_delete_order'))
};
const STATUS_LABEL = { available: T.available, sold: T.sold, custom: T.custom };
const ORDER_STATUS_LABEL = { pending: T.pending, progress: T.in_progress, done: T.completed };
const TYPE_LABEL = { Painting: T.painting, Sculpture: T.sculpture, Handicraft: T.handicraft, Mural: T.mural };

function renderChart(){
  const CHART = @json($chartData);
  const W=900, H=280, padL=46, padR=12, padT=20, padB=34;
  const plotW=W-padL-padR, plotH=H-padT-padB;
  const max = Math.max(5, ...CHART.map(d=>d.sales), ...CHART.map(d=>d.custom)) * 1.2;
  const groupW=plotW/CHART.length, barW=26, gap=10;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg"><defs>
    <linearGradient id="gSales" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#e88a5a"/><stop offset="100%" stop-color="#c65d3b"/></linearGradient>
    <linearGradient id="gCustom" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#e5cba3"/><stop offset="100%" stop-color="#d9b382"/></linearGradient>
  </defs>`;
  for(let i=0;i<=5;i++){
    const val=(max/5)*i, y=padT+plotH-(val/max)*plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#f5ede2" stroke-width="1"/>`;
    svg += `<text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#b3a090">${Math.round(val)}</text>`;
  }
  CHART.forEach((d,i)=>{
    const cx=padL+i*groupW+groupW/2, baseY=padT+plotH;
    const hS=(d.sales/max)*plotH, hC=(d.custom/max)*plotH;
    svg += `<rect x="${cx-barW-gap/2}" y="${baseY-hS}" width="${barW}" height="${hS}" rx="6" fill="url(#gSales)"/>`;
    svg += `<rect x="${cx+gap/2}" y="${baseY-hC}" width="${barW}" height="${hC}" rx="6" fill="url(#gCustom)"/>`;
    svg += `<text x="${cx}" y="${H-11}" text-anchor="middle" font-size="11.5" font-weight="700" fill="#8a7565">${d.day}</text>`;
  });
  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

let galleryFilter = 'all';
function renderGallery(){
  const filtered = ARTWORKS.filter(a=>{
    if(galleryFilter === 'all') return true;
    if(galleryFilter === 'sold') return a.status === 'sold';
    return a.category === galleryFilter;
  });
  const box = document.getElementById('gallery');
  if(!filtered.length){ box.innerHTML = '<div class="empty"><div>🎨</div>'+T.no_artworks+'</div>'; return; }

  box.innerHTML = filtered.map(a=>{
    const catLabel = TYPE_LABEL[a.category.charAt(0).toUpperCase() + a.category.slice(1)] || a.category;
    return `<div class="art-card">
      <div class="art-img" style="background:${a.gradient || 'linear-gradient(135deg,#f9dad0,#c65d3b)'}">
        <span class="art-cat">${catLabel}</span>
        <span style="position:relative;z-index:1;filter:drop-shadow(0 4px 8px rgba(0,0,0,.15))">${a.emoji || '🖼️'}</span>
      </div>
      <div class="art-body">
        <div class="art-name" title="${a.name}">${a.name}</div>
        <div class="art-artist">${a.artist_name}</div>
        <div class="art-foot">
          <div class="art-price">Rs ${Number(a.price).toLocaleString()}</div>
          <button class="status-btn s-${a.status}" data-id="${a.id}" data-status="${a.status}">${STATUS_LABEL[a.status] || a.status}</button>
        </div>
        <button class="art-delete delete-artwork" data-id="${a.id}">${T.delete_artwork}</button>
      </div>
    </div>`;
  }).join('');

  box.querySelectorAll('.status-btn').forEach(btn=>{
    btn.addEventListener('click', async function(e){
      e.stopPropagation();
      const id = this.dataset.id;
      const cur = STATUS_FLOW.indexOf(this.dataset.status);
      const next = STATUS_FLOW[(cur+1) % STATUS_FLOW.length];
      try {
        const res = await fetch(`/provider/art-craft/artworks/${id}/status`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({status:next})
        });
        if(res.ok) window.location.reload();
      } catch(e){ console.error(e); }
    });
  });
}

document.querySelectorAll('.gtab').forEach(tab=>{
  tab.addEventListener('click', ()=>{
    document.querySelectorAll('.gtab').forEach(t=>t.classList.remove('on'));
    tab.classList.add('on');
    galleryFilter = tab.dataset.filter;
    renderGallery();
  });
});

function renderOrders(){
  const body = document.getElementById('ordersBody');
  if(!ORDERS.length){ body.innerHTML = '<tr><td colspan="6"><p class="empty">'+T.no_orders+'</p></td></tr>'; return; }
  const statusCls = { pending:'p-pending', progress:'p-progress', done:'p-done' };
  const typeCls = { Painting:'t-painting', Sculpture:'t-sculpture', Handicraft:'t-handicraft', Mural:'t-mural' };

  body.innerHTML = ORDERS.map(o=>{
    const inits = o.client_name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    const typeClass = typeCls[o.type] || 't-painting';
    return `<tr>
      <td><div class="client"><div class="c-av" style="background:var(--terracotta)">${inits}</div><div><div class="c-name">${o.client_name}</div><div class="c-mail">${o.client_email || ''}</div></div></div></td>
      <td><span class="type-badge ${typeClass}">${TYPE_LABEL[o.type] || o.type}</span></td>
      <td style="font-weight:800">Rs ${Number(o.budget).toLocaleString()}</td>
      <td>${new Date(o.deadline).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
      <td><button class="pill ${statusCls[o.status]}" data-id="${o.id}" data-status="${o.status}">${ORDER_STATUS_LABEL[o.status] || o.status}</button></td>
      <td><button class="delete-order" data-id="${o.id}" title="${T.delete_order}">✕</button></td>
    </tr>`;
  }).join('');

  document.querySelectorAll('#ordersBody .pill').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      let cur = this.dataset.status;
      let next = cur === 'pending' ? 'progress' : (cur === 'progress' ? 'done' : 'pending');
      try {
        const res = await fetch(`/provider/art-craft/orders/${id}/status`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({status:next})
        });
        if(res.ok) window.location.reload();
      } catch(e){ console.error(e); }
    });
  });
}

renderChart(); renderGallery(); renderOrders();

/* ========== Modals ========== */
const artworkModal = document.getElementById('artworkModal');
const orderModal = document.getElementById('orderModal');
document.getElementById('addArtworkBtn')?.addEventListener('click', () => { artworkModal.style.display = 'flex'; });
document.getElementById('newOrderBtn')?.addEventListener('click', () => { orderModal.style.display = 'flex'; });
document.getElementById('closeArtworkModal')?.addEventListener('click', () => { artworkModal.style.display = 'none'; });
document.getElementById('closeOrderModal')?.addEventListener('click', () => { orderModal.style.display = 'none'; });
artworkModal?.addEventListener('click', (e) => { if (e.target === artworkModal) artworkModal.style.display = 'none'; });
orderModal?.addEventListener('click', (e) => { if (e.target === orderModal) orderModal.style.display = 'none'; });

/* ========== Delete Artwork & Order (AJAX) ========== */
document.addEventListener('click', async function(e) {
  if (e.target.classList.contains('delete-artwork')) {
    if (!confirm(T.confirm_art)) return;
    const id = e.target.dataset.id;
    e.target.disabled = true;
    try {
      const res = await fetch(`/provider/art-craft/artworks/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      if (res.ok) window.location.reload();
      else e.target.disabled = false;
    } catch (err) { console.error(err); e.target.disabled = false; }
  }

  if (e.target.classList.contains('delete-order')) {
    if (!confirm(T.confirm_order)) return;
    const id = e.target.dataset.id;
    e.target.disabled = true;
    try {
      const res = await fetch(`/provider/art-craft/orders/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      if (res.ok) window.location.reload();
      else e.target.disabled = false;
    } catch (err) { console.error(err); e.target.disabled = false; }
  }
});
</script>
@endpush
@extends('layouts.provider-restaurant')

@php
    $greetingName = auth()->user()->name ?? __('messages.chef');
    $propertyName = $provider->name ?? __('messages.your_restaurant');
@endphp

@section('title', __('messages.restaurant_dashboard_title'))
@section('page-title', __('messages.dashboard'))
@section('page-subtitle', __('messages.restaurant_welcome_subtitle', ['name' => $greetingName, 'property' => $propertyName]))

@push('restaurant-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:19px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(28,25,23,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:19px}
  .i-orange{background:var(--accent-soft)}.i-green{background:var(--green-soft)}.i-blue{background:var(--blue-soft)}.i-purple{background:var(--purple-soft)}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}.up{background:var(--green-soft);color:var(--green)}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:23px;font-weight:800;letter-spacing:-.8px}.stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800}.card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px}
  .link{font-size:12px;font-weight:800;color:var(--accent-d);text-decoration:none}
  .chart-total{display:flex;align-items:baseline;gap:9px;margin-top:3px;flex-wrap:wrap}
  .chart-total b{font-size:25px;font-weight:800}.chart-total span{font-size:11.5px;font-weight:800;color:var(--green);background:var(--green-soft);padding:3px 9px;border-radius:20px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:6px}
  .orders{display:flex;flex-direction:column;gap:10px;max-height:352px;overflow-y:auto;padding-right:4px}
  .orders::-webkit-scrollbar{width:5px}.orders::-webkit-scrollbar-thumb{background:#e8ded2;border-radius:5px}
  .order{border:1px solid var(--line);border-radius:14px;padding:13px;background:#fffdfb}
  .order-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;gap:8px}
  .order-id{font-size:13px;font-weight:800}.order-id span{font-size:10.5px;color:var(--muted);font-weight:700;margin-left:6px}
  .order-time{font-size:10.5px;font-weight:700;color:#b3a89e;white-space:nowrap}
  .order-items{font-size:12px;color:var(--muted);font-weight:600;line-height:1.6;margin-bottom:10px}
  .order-bottom{display:flex;align-items:center;justify-content:space-between;gap:8px}
  .order-total{font-size:13.5px;font-weight:800}
  .st{font-size:10.5px;font-weight:800;padding:5px 10px;border-radius:20px;border:none;cursor:pointer;font-family:inherit}
  .st-pending{background:var(--amber-soft);color:#b45309}.st-preparing{background:var(--blue-soft);color:#1d4ed8}
  .st-ready{background:var(--purple-soft);color:#6d28d9}.st-served{background:var(--green-soft);color:#15803d}
  .tables{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  .table-tile{border-radius:14px;padding:14px 10px;text-align:center;cursor:pointer;border:1.5px solid var(--line);background:#fffdfb;transition:.2s}
  .table-tile:hover{transform:translateY(-3px);box-shadow:0 10px 22px rgba(28,25,23,.09)}
  .table-tile .t-num{font-size:16px;font-weight:800;margin-bottom:2px}
  .table-tile .t-seat{font-size:10.5px;font-weight:700;color:var(--muted)}
  .table-tile .t-dot{width:7px;height:7px;border-radius:50%;margin:8px auto 0}
  .t-free{border-color:#bbf7d0;background:#f4fdf7}.t-free .t-dot{background:#22c55e}.t-free .t-num{color:#15803d}
  .t-occupied{border-color:#fed7aa;background:#fffaf4}.t-occupied .t-dot{background:#f97316}.t-occupied .t-num{color:#c2410c}
  .t-reserved{border-color:#c7d2fe;background:#f6f8ff}.t-reserved .t-dot{background:#6366f1}.t-reserved .t-num{color:#4338ca}
  .t-cleaning{border-color:#e5e5e5;background:#fafafa}.t-cleaning .t-dot{background:#a8a29e}.t-cleaning .t-num{color:#78716c}
  .table-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid var(--line)}
  .table-legend span{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:var(--muted)}
  .table-legend i{width:8px;height:8px;border-radius:50%;display:block}
  .dish{display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid #f7f1ea}
  .dish:last-child{border-bottom:none}.dish:first-child{padding-top:0}
  .dish-emoji{width:40px;height:40px;border-radius:12px;background:#fff5ec;display:grid;place-items:center;font-size:19px;flex-shrink:0}
  .dish-info{flex:1;min-width:0}.dish-name{font-size:12.5px;font-weight:800;margin-bottom:5px}
  .dish-bar{height:5px;border-radius:5px;background:#f4ede4;overflow:hidden}
  .dish-bar div{height:100%;border-radius:5px;background:linear-gradient(90deg,#fb923c,#f97316);width:0;transition:width 1s cubic-bezier(.3,.9,.4,1)}
  .dish-right{text-align:right;flex-shrink:0}.dish-count{font-size:12px;font-weight:800}.dish-price{font-size:10.5px;color:var(--muted);font-weight:700}
  .reviews{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  .review{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px}
  .rev-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
  .rev-av{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:12.5px;flex-shrink:0}
  .rev-name{font-size:12.5px;font-weight:800}.rev-date{font-size:10.5px;color:var(--muted);font-weight:600}
  .stars{margin-left:auto;font-size:11px;letter-spacing:1px}.review p{font-size:12.5px;line-height:1.65;color:#57534e;font-weight:500}
  .empty{font-size:12.5px;color:var(--muted);text-align:center;padding:18px 0;font-weight:600}
  .action-bar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:18px;flex-wrap:wrap}
  .btn-secondary{background:var(--card);color:var(--text);border:1px solid var(--line);box-shadow:none}
  .delete-order{background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px;padding:2px 4px;border-radius:6px;transition:.15s}
  .delete-order:hover{background:var(--red-soft)}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.reviews{grid-template-columns:1fr}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.tables{grid-template-columns:repeat(3,1fr)}.action-bar{justify-content:stretch}.action-bar button{flex:1}}
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
  <button class="btn-primary btn-secondary" id="addMenuBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;stroke-width:2.6">
      <path d="M3 2v7c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2V2"/><path d="M5 11v11"/><path d="M19 2c-1.7 0-3 1.3-3 3v7h3v10"/>
    </svg>
    <span>{{ __('messages.add_menu_item') }}</span>
  </button>
  <button class="btn-primary" id="newOrderBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    <span>{{ __('messages.new_order') }}</span>
  </button>
</div>

{{-- KYC Banner --}}
@php
    $vp = auth()->user()->getCurrentProvider();
    $needsVerification = $vp && $vp->verification_status !== 'verified';
@endphp
@if($needsVerification)
<div style="background:#fff7ed;border-left:4px solid #f59e0b;padding:16px 20px;margin-bottom:20px;border-radius:12px;display:flex;align-items:start;gap:14px;box-shadow:0 2px 8px rgba(245,158,11,.08)">
    <div style="flex-shrink:0;width:38px;height:38px;background:#fef5e3;border-radius:10px;display:grid;place-items:center;font-size:18px">⚠️</div>
    <div style="flex:1">
        <h4 style="font-size:14px;font-weight:800;color:#92400e;margin-bottom:4px">{{ __('messages.account_unverified') }}</h4>
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_restaurant_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-orange">💰</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.today_revenue') }}</div><div class="stat-value"><small>{{ $currencySymbol }}</small> {{ number_format($stats['today_revenue'], 0) }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-blue">🧾</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.orders_today') }}</div><div class="stat-value">{{ $stats['today_orders'] }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">📈</div><span class="delta up">▲ Avg</span></div><div class="stat-label">{{ __('messages.avg_order_value') }}</div><div class="stat-value"><small>{{ $currencySymbol }}</small> {{ number_format($stats['avg_order'], 0) }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-purple">🪑</div><span class="delta up">{{ $stats['tables_total'] > 0 ? round(($stats['tables_occupied']/$stats['tables_total'])*100) : 0 }}%</span></div><div class="stat-label">{{ __('messages.tables_occupied') }}</div><div class="stat-value">{{ $stats['tables_occupied'] }}<small> / {{ $stats['tables_total'] }}</small></div></div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div><h3>{{ __('messages.revenue_by_hour') }}</h3><div class="chart-total"><b>{{ $currencySymbol }} {{ number_format($stats['today_revenue'], 0) }}</b><span>{{ __('messages.today') }}</span></div></div>
      <a href="#" class="link">{{ __('messages.full_report') }} →</a>
    </div>
    <div id="chart"></div>
  </div>
  <div class="card">
    <div class="card-head">
      <div><h3>{{ __('messages.live_orders') }}</h3><div class="sub" id="liveCount">{{ $liveOrders->count() }} {{ __('messages.active_now') }}</div></div>
      <a href="#" class="link">{{ __('messages.view_all') }} →</a>
    </div>
    <div class="orders" id="orders"></div>
  </div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div><h3>{{ __('messages.table_map') }}</h3><div class="sub">{{ __('messages.tap_table_hint') }}</div></div>
      <a href="#" class="link">{{ __('messages.manage_tables') }} →</a>
    </div>
    <div class="tables" id="tables"></div>
    <div class="table-legend">
      <span><i style="background:#22c55e"></i>{{ __('messages.free') }}</span>
      <span><i style="background:#f97316"></i>{{ __('messages.occupied') }}</span>
      <span><i style="background:#6366f1"></i>{{ __('messages.reserved') }}</span>
      <span><i style="background:#a8a29e"></i>{{ __('messages.cleaning') }}</span>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.popular_dishes') }}</h3><div class="sub">{{ __('messages.top_5_week') }}</div></div></div>
    <div id="dishes"></div>
  </div>
</section>

<section class="reviews" id="reviews"></section>

{{-- 🆕 Add Menu Item Modal --}}
<div id="menuModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:420px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.add_menu_item') }}</h3>
            <button type="button" id="closeMenuModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.menu.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.menu_item_name') }}</label>
                <input type="text" name="name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Chicken Momo">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
                <input type="number" name="price" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="250">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.category') }}</label>
                <input type="text" name="category" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Main Course">
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.emoji') }}</label>
                <input type="text" name="emoji" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="🥟">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.add_menu_item') }}</button>
        </form>
    </div>
</div>

{{-- 🆕 New Order Modal --}}
<div id="orderModal" style="display:none;position:fixed;inset:0;background:rgba(28,25,23,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:600px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_order') }}</h3>
            <button type="button" id="closeOrderModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.restaurant.orders.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:20px">
                <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.select_table') }}</label>
                <select name="table_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                    <option value="">{{ __('messages.select_table') }}...</option>
                    @foreach($tablesData as $t)
                        <option value="{{ $t['db_id'] }}">{{ $t['label'] ?? ('T-'.str_pad($t['n'],2,'0',STR_PAD_LEFT)) }} ({{ $t['seats'] }} {{ __('messages.seats') }})</option>
                    @endforeach
                </select>
            </div>

            <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:8px">{{ __('messages.select_menu_items') }}</label>
            <div style="max-height:250px;overflow-y:auto;border:1px solid var(--line);border-radius:12px;padding:10px;margin-bottom:20px" id="menuItemsList">
                @forelse($allProducts ?? [] as $item)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-bottom:1px solid #f0f0f0;gap:8px">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0">
                        <input type="checkbox" class="order-item-check" data-price="{{ $item['price'] ?? 0 }}" name="items[{{ $loop->index }}][id]" value="{{ $item['db_id'] ?? '' }}">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px">{{ $item['emoji'] ?? '🍽️' }} {{ $item['name'] ?? '' }} (Rs {{ number_format($item['price'] ?? 0) }})</span>
                    </div>
                    <input type="number" name="items[{{ $loop->index }}][qty]" class="item-qty" data-price="{{ $item['price'] ?? 0 }}" value="1" min="1" style="width:60px;padding:4px;text-align:center;border:1px solid var(--line);border-radius:6px;font-family:inherit;font-size:12px" disabled>
                </div>
                @empty
                <p class="empty">{{ __('messages.no_menu_items_yet') }}</p>
                @endforelse
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:15px;background:#f8fafc;border-radius:12px">
                <span style="font-size:14px;font-weight:700">{{ __('messages.total_amount') }}</span>
                <span style="font-size:18px;font-weight:800" id="orderTotalDisplay">Rs 0</span>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_order') }}</button>
        </form>
    </div>
</div>
@endsection

@push('restaurant-scripts')
<script>
const HOURLY = @json($hourlyRevenue);
const ORDERS = @json($liveOrders);
const TABLES = @json($tablesData);
const DISHES = @json($dishesData);
const REVIEWS = @json($reviewsData);
const CURRENCY = @json($currencySymbol);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const ORDER_FLOW = ['pending','preparing','ready','served'];
const TABLE_FLOW = ['free','occupied','reserved','cleaning'];

/* 🆕 Multilingual JS strings */
const T = {
  pending:      @json(__('messages.pending')),
  preparing:    @json(__('messages.preparing')),
  ready:        @json(__('messages.ready')),
  served:       @json(__('messages.served')),
  no_orders:    @json(__('messages.no_orders_yet')),
  no_tables:    @json(__('messages.no_tables_yet')),
  no_sales:     @json(__('messages.no_sales_data')),
  no_reviews:   @json(__('messages.no_reviews_yet')),
  active_now:   @json(__('messages.active_now')),
  seats:        @json(__('messages.seats')),
  confirm_del:  @json(__('messages.confirm_delete_order')),
  failed_del:   @json(__('messages.failed_to_delete')),
  delete_order: @json(__('messages.delete_order'))
};
const ORDER_LABEL = { pending: T.pending, preparing: T.preparing, ready: T.ready, served: T.served };

function renderChart(){
  const W=780,H=280,padL=48,padR=14,padT=20,padB=34;
  const plotW=W-padL-padR,plotH=H-padT-padB;
  const max=Math.max(1,...HOURLY.map(d=>d.v))*1.15;
  const step=HOURLY.length>1?plotW/(HOURLY.length-1):plotW;
  const pts=HOURLY.map((d,i)=>({x:padL+i*step,y:padT+plotH-(d.v/max)*plotH,...d}));
  let line=`M ${pts[0].x},${pts[0].y}`;
  for(let i=1;i<pts.length;i++){const p0=pts[i-1],p1=pts[i],mx=(p0.x+p1.x)/2;line+=` C ${mx},${p0.y} ${mx},${p1.y} ${p1.x},${p1.y}`;}
  const area=`${line} L ${pts[pts.length-1].x},${padT+plotH} L ${pts[0].x},${padT+plotH} Z`;
  let svg=`<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="areaG" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f97316" stop-opacity=".28"/><stop offset="100%" stop-color="#f97316" stop-opacity="0"/></linearGradient><linearGradient id="lineG" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#fb923c"/><stop offset="100%" stop-color="#f97316"/></linearGradient></defs>`;
  for(let i=0;i<=4;i++){const val=(max/4)*i,y=padT+plotH-(val/max)*plotH;svg+=`<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#f4ede4" stroke-width="1"/><text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#c4b8ab">${Math.round(val/1000)}k</text>`;}
  svg+=`<path d="${area}" fill="url(#areaG)"/><path d="${line}" fill="none" stroke="url(#lineG)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;
  const peakV=Math.max(...HOURLY.map(d=>d.v));
  pts.forEach(p=>{const isPeak=p.v===peakV&&peakV>0;svg+=`<circle cx="${p.x}" cy="${p.y}" r="${isPeak?6:4}" fill="#fff" stroke="#f97316" stroke-width="2.6"/><text x="${p.x}" y="${H-10}" text-anchor="middle" font-size="11" font-weight="700" fill="#a8a29e">${p.h}</text>`;});
  if(peakV>0){const peak=pts.find(p=>p.v===peakV);svg+=`<g><rect x="${peak.x-32}" y="${peak.y-38}" width="64" height="24" rx="8" fill="#1c1917"/><text x="${peak.x}" y="${peak.y-22}" text-anchor="middle" font-size="11" font-weight="800" fill="#fff">${CURRENCY} ${(peak.v/1000).toFixed(1)}k</text></g>`;}
  svg+=`</svg>`;document.getElementById('chart').innerHTML=svg;
}

function renderOrders(){
  const box=document.getElementById('orders');
  if(!ORDERS.length){box.innerHTML='<p class="empty">'+T.no_orders+'</p>';document.getElementById('liveCount').textContent='0 '+T.active_now;return;}
  box.innerHTML=ORDERS.map((o,idx)=>{
    const st=o.status;
    return `<div class="order">
      <div class="order-top">
        <div class="order-id">${o.id} <span>${o.table}</span></div>
        <div style="display:flex;align-items:center;gap:6px">
          <div class="order-time">${o.time}</div>
          <button class="delete-order" data-id="${o.db_id}" title="${T.delete_order}">✕</button>
        </div>
      </div>
      <div class="order-items">${o.items}</div>
      <div class="order-bottom">
        <div class="order-total">${CURRENCY} ${Number(o.total).toLocaleString('en-US',{maximumFractionDigits:0})}</div>
        <button class="st st-${st}" data-idx="${idx}" data-dbid="${o.db_id}">${ORDER_LABEL[st]||st}</button>
      </div>
    </div>`;
  }).join('');
  document.getElementById('liveCount').textContent=ORDERS.filter(o=>o.status!=='served').length+' '+T.active_now;
  box.querySelectorAll('.st').forEach(btn=>{btn.addEventListener('click',async()=>{const i=+btn.dataset.idx,dbId=btn.dataset.dbid,cur=ORDER_FLOW.indexOf(ORDERS[i].status);if(cur===-1||cur===ORDER_FLOW.length-1)return;const next=ORDER_FLOW[cur+1];btn.disabled=true;try{const res=await fetch(`/provider/restaurant/orders/${dbId}/status`,{method:'PATCH',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({status:next})});if(res.ok){ORDERS[i].status=next;renderOrders();}else{btn.disabled=false;}}catch(e){console.error(e);btn.disabled=false;}});});
}

function renderTables(){
  const box=document.getElementById('tables');
  if(!TABLES.length){box.innerHTML='<p class="empty" style="grid-column:1/-1">'+T.no_tables+'</p>';return;}
  box.innerHTML=TABLES.map((t,i)=>`<div class="table-tile t-${t.st}" data-idx="${i}" data-dbid="${t.db_id}"><div class="t-num">${t.label||('T-'+String(t.n).padStart(2,'0'))}</div><div class="t-seat">${t.seats} ${T.seats}</div><div class="t-dot"></div></div>`).join('');
  box.querySelectorAll('.table-tile').forEach(tile=>{tile.addEventListener('click',async()=>{const i=+tile.dataset.idx,dbId=tile.dataset.dbid,cur=TABLE_FLOW.indexOf(TABLES[i].st),next=TABLE_FLOW[(cur+1)%TABLE_FLOW.length];try{const res=await fetch(`/provider/restaurant/tables/${dbId}/status`,{method:'PATCH',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({status:next})});if(res.ok){TABLES[i].st=next;renderTables();}}catch(e){console.error(e);}});});
}

function renderDishes(){
  const box=document.getElementById('dishes');
  if(!DISHES.length){box.innerHTML='<p class="empty">'+T.no_sales+'</p>';return;}
  box.innerHTML=DISHES.map(d=>`<div class="dish"><div class="dish-emoji">${d.emoji}</div><div class="dish-info"><div class="dish-name">${d.name}</div><div class="dish-bar"><div data-pct="${d.pct}"></div></div></div><div class="dish-right"><div class="dish-count">${d.count}</div><div class="dish-price">${d.price}</div></div></div>`).join('');
  requestAnimationFrame(()=>{setTimeout(()=>{document.querySelectorAll('.dish-bar div').forEach(b=>{b.style.width=b.dataset.pct+'%';});},80);});
}

function renderReviews(){
  const box=document.getElementById('reviews');
  if(!REVIEWS.length){box.innerHTML='<p class="empty" style="grid-column:1/-1">'+T.no_reviews+'</p>';return;}
  box.innerHTML=REVIEWS.map(r=>{const stars='★'.repeat(r.stars)+'<span style="color:#e7e0d7">'+'★'.repeat(5-r.stars)+'</span>';return `<div class="review"><div class="rev-top"><div class="rev-av" style="background:${r.color}">${r.initials}</div><div><div class="rev-name">${r.name}</div><div class="rev-date">${r.date}</div></div><div class="stars" style="color:#f59e0b">${stars}</div></div><p>${r.text}</p></div>`;}).join('');
}

renderChart();renderOrders();renderTables();renderDishes();renderReviews();

/* ========== Modals ========== */
const menuModal = document.getElementById('menuModal');
const orderModal = document.getElementById('orderModal');
document.getElementById('addMenuBtn')?.addEventListener('click', () => { menuModal.style.display = 'flex'; });
document.getElementById('newOrderBtn')?.addEventListener('click', () => { orderModal.style.display = 'flex'; });
document.getElementById('closeMenuModal')?.addEventListener('click', () => { menuModal.style.display = 'none'; });
document.getElementById('closeOrderModal')?.addEventListener('click', () => { orderModal.style.display = 'none'; });
menuModal?.addEventListener('click', (e) => { if (e.target === menuModal) menuModal.style.display = 'none'; });
orderModal?.addEventListener('click', (e) => { if (e.target === orderModal) orderModal.style.display = 'none'; });

/* ========== Order Total Calculator ========== */
let orderTotal = 0;
function calculateTotal() {
  orderTotal = 0;
  document.querySelectorAll('.order-item-check').forEach(check => {
    const qtyInput = check.closest('div').querySelector('.item-qty');
    if (check.checked) {
      qtyInput.disabled = false;
      const price = parseFloat(check.dataset.price) || 0;
      const qty = parseInt(qtyInput.value) || 1;
      orderTotal += price * qty;
    } else {
      qtyInput.disabled = true;
      qtyInput.value = 1;
    }
  });
  document.getElementById('orderTotalDisplay').textContent = 'Rs ' + orderTotal.toLocaleString('en-US');
}
document.querySelectorAll('.order-item-check').forEach(c => c.addEventListener('change', calculateTotal));
document.querySelectorAll('.item-qty').forEach(q => q.addEventListener('input', calculateTotal));

/* ========== Delete Order (AJAX) ========== */
document.addEventListener('click', async function(e) {
  if (!e.target.classList.contains('delete-order')) return;
  if (!confirm(T.confirm_del)) return;
  const id = e.target.dataset.id;
  e.target.disabled = true;
  try {
    const res = await fetch(`/provider/restaurant/orders/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if (res.ok) window.location.reload();
    else { e.target.disabled = false; alert(T.failed_del); }
  } catch(err) { console.error(err); e.target.disabled = false; }
});
</script>
@endpush
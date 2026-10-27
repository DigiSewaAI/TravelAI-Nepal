@extends('layouts.provider-inventory')

@php
    $greetingName = auth()->user()->name ?? __('messages.manager');
    $subtitle = __('messages.inventory_welcome_subtitle');
@endphp

@section('title', __('messages.inventory_dashboard_title'))
@section('page-title', __('messages.dashboard'))
@section('page-subtitle', $subtitle)

@push('inv-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:19px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(17,24,39,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:19px}
  .i-indigo{background:var(--indigo-soft);color:var(--indigo-d)}.i-amber{background:var(--amber-soft);color:#b45309}.i-green{background:var(--green-soft);color:#059669}.i-purple{background:var(--purple-soft);color:#6d28d9}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}.up{background:var(--green-soft);color:#059669}.warn{background:var(--amber-soft);color:#b45309}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:23px;font-weight:800;letter-spacing:-.8px}
  .stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .card.flash{animation:flashCard 1.3s ease}
  @keyframes flashCard{0%{box-shadow:0 0 0 0 rgba(99,102,241,.5)}40%{box-shadow:0 0 0 8px rgba(99,102,241,.14)}100%{box-shadow:0 0 0 0 rgba(99,102,241,0)}}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:3px}
  .link{font-size:12px;font-weight:800;color:var(--indigo-d);text-decoration:none}
  .legend{display:flex;gap:14px;align-items:center}
  .legend span{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted)}
  .legend i{width:9px;height:9px;border-radius:3px;display:block}
  .chart-total{display:flex;align-items:baseline;gap:9px;margin-top:3px}
  .chart-total b{font-size:25px;font-weight:800}
  .chart-total span{font-size:11.5px;font-weight:800;color:#059669;background:var(--green-soft);padding:3px 9px;border-radius:20px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:8px}
  .alerts{display:flex;flex-direction:column;gap:9px;max-height:344px;overflow-y:auto;padding-right:4px}
  .alerts::-webkit-scrollbar{width:5px}.alerts::-webkit-scrollbar-thumb{background:#dfe3f2;border-radius:5px}
  .alert-row{display:flex;align-items:center;gap:11px;padding:10px;border-radius:13px;border:1px solid var(--line);background:#fbfcff;transition:.2s}
  .alert-row:hover{border-color:#d6d9fb;box-shadow:0 6px 18px rgba(99,102,241,.1);background:#fff}
  .alert-emoji{width:38px;height:38px;border-radius:11px;flex-shrink:0;background:#f3f4fb;display:grid;place-items:center;font-size:17px}
  .alert-info{flex:1;min-width:0}
  .alert-name{font-size:12px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .alert-name em{font-style:normal;font-size:9.5px;color:#9ca3af;font-weight:700;margin-left:5px}
  .alert-bar{height:5px;border-radius:5px;background:#eef1f9;margin:6px 0 5px;overflow:hidden}
  .alert-bar div{height:100%;border-radius:5px;transition:width .8s}
  .alert-meta{font-size:10px;font-weight:800;color:var(--muted)}
  .restock-btn{border:none;border-radius:9px;padding:7px 11px;cursor:pointer;font-family:inherit;font-size:10.5px;font-weight:800;background:var(--indigo-soft);color:var(--indigo-d);transition:.18s;white-space:nowrap}
  .restock-btn:hover{background:var(--indigo);color:#fff;transform:scale(1.04)}
  .all-good{text-align:center;padding:26px 10px;font-size:12.5px;font-weight:700;color:var(--muted)}
  .all-good div{font-size:26px;margin-bottom:8px}
  .qsu-label{display:block;font-size:10px;font-weight:800;color:var(--muted);letter-spacing:.7px;text-transform:uppercase;margin-bottom:7px}
  .qsu-select{width:100%;padding:11px 12px;border-radius:11px;border:1px solid var(--line);background:#fbfcff;font-family:inherit;font-size:12.5px;font-weight:700;color:var(--text);outline:none;cursor:pointer;transition:.2s;margin-bottom:14px}
  .qsu-select:focus{border-color:var(--indigo);box-shadow:0 0 0 4px rgba(99,102,241,.1);background:#fff}
  .qsu-preview{border:1px solid var(--line);border-radius:14px;padding:14px;background:#fbfcff;margin-bottom:16px}
  .qp-top{display:flex;align-items:center;gap:10px;margin-bottom:13px}
  .qp-emoji{width:40px;height:40px;border-radius:12px;flex-shrink:0;background:#f3f4fb;display:grid;place-items:center;font-size:19px}
  .qp-name{font-size:12.5px;font-weight:800}
  .qp-sku{font-size:10px;color:var(--muted);font-weight:700;margin-top:2px}
  .qp-status{margin-left:auto;font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;background:var(--green-soft);color:#059669}
  .qp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
  .qp-stats>div{background:#fff;border:1px solid var(--line);border-radius:10px;padding:8px;text-align:center}
  .qp-stats span{display:block;font-size:9px;font-weight:800;color:var(--muted);letter-spacing:.5px;text-transform:uppercase;margin-bottom:3px}
  .qp-stats b{font-size:14px;font-weight:800}
  .stepper{display:flex;align-items:center;gap:8px;margin-bottom:14px}
  .stepper button{width:42px;height:42px;flex-shrink:0;border-radius:11px;border:1px solid var(--line);background:#fff;cursor:pointer;font-size:19px;font-weight:800;color:var(--muted);display:grid;place-items:center;transition:.18s}
  .stepper button:hover{background:var(--indigo-soft);color:var(--indigo-d);border-color:#d6d9fb}
  .stepper input{flex:1;min-width:0;height:42px;border-radius:11px;border:1px solid var(--line);background:#fbfcff;text-align:center;font-family:inherit;font-size:16px;font-weight:800;color:var(--text);outline:none}
  .stepper input:focus{border-color:var(--indigo);box-shadow:0 0 0 4px rgba(99,102,241,.1);background:#fff}
  .qsu-actions{display:grid;grid-template-columns:1fr auto;gap:9px}
  .btn-add{border:none;border-radius:11px;padding:12px;cursor:pointer;font-family:inherit;font-size:12.5px;font-weight:800;color:#fff;background:linear-gradient(135deg,#34d399,#10b981);box-shadow:0 7px 18px rgba(16,185,129,.3);transition:.2s}
  .btn-add:hover{filter:brightness(1.06);transform:translateY(-1px)}
  .btn-remove{border:1px solid var(--line);border-radius:11px;padding:12px 14px;cursor:pointer;font-family:inherit;font-size:12.5px;font-weight:800;color:#b91c1c;background:#fff;transition:.2s}
  .btn-remove:hover{background:var(--red-soft);border-color:#fecaca}
  .delete-product-btn{margin-top:10px;width:100%;background:var(--red-soft);color:#b91c1c;border:none;padding:9px;border-radius:8px;font-weight:800;cursor:pointer;font-size:12px;font-family:inherit;transition:.18s}
  .delete-product-btn:hover{background:#fecaca;transform:scale(1.01)}
  .tbl-wrap{overflow-x:auto}
  .tbl{width:100%;border-collapse:collapse;min-width:360px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 10px 11px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:12px 10px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f5fb;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fafbff}
  .ord-id{font-weight:800;font-size:12.5px}
  .ord-items{font-size:10.5px;color:var(--muted);font-weight:600;margin-top:2px;max-width:220px;overflow:hidden;text-overflow:ellipsis}
  .cust{display:flex;align-items:center;gap:9px}
  .c-av{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:11px;flex-shrink:0;background:linear-gradient(135deg,#6366f1,#818cf8)}
  .c-name{font-weight:800;font-size:12.5px}
  .mode-badge{font-size:9.5px;font-weight:800;padding:4px 8px;border-radius:6px;letter-spacing:.4px;display:inline-block}
  .mode-sale{background:var(--indigo-soft);color:var(--indigo-d)}.mode-rental{background:var(--green-soft);color:#047857}.mode-wholesale{background:var(--purple-soft);color:#6d28d9}
  .st-btn{border:none;border-radius:20px;padding:5px 10px;font-family:inherit;font-size:10.5px;font-weight:800;white-space:nowrap;background:var(--amber-soft);color:#b45309}
  .empty{font-size:12.5px;color:var(--muted);text-align:center;padding:18px 0;font-weight:600}
  .toast{position:fixed;bottom:26px;right:26px;z-index:300;background:#111827;color:#fff;padding:13px 18px;border-radius:12px;font-size:12.5px;font-weight:700;box-shadow:0 14px 34px rgba(17,24,39,.3);opacity:0;transform:translateY(14px);pointer-events:none;transition:.3s;display:flex;align-items:center;gap:9px;max-width:320px}
  .toast.show{opacity:1;transform:translateY(0)}.toast.ok{border-left:3px solid #10b981}.toast.err{border-left:3px solid #ef4444}
  .action-bar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:18px;flex-wrap:wrap}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.toast{left:14px;right:14px;bottom:14px;max-width:none}.action-bar{justify-content:stretch}.action-bar button{flex:1}}
</style>
@endpush

@section('content')
{{-- 🆕 Welcome Banner --}}
<div style="background: linear-gradient(135deg, var(--indigo), var(--indigo-d)); border-radius: 16px; padding: 25px; margin-bottom: 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3);">
    <div>
        <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 5px;">{{ __('messages.welcome_back') }}, {{ $greetingName }}! 👋</h1>
        <p style="font-size: 13px; opacity: 0.9;">{{ $subtitle }}</p>
    </div>
    <div style="font-size: 45px; opacity: 0.4;">📦</div>
</div>

{{-- New Product Action Bar --}}
<div class="action-bar">
  <button class="btn-primary" id="newProductBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    <span>{{ __('messages.new_product') }}</span>
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_inventory_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

{{-- Stats --}}
<section class="stats">
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-indigo">📦</div><span class="delta up">▲ {{ __('messages.active') }}</span></div>
    <div class="stat-label">{{ __('messages.total_products') }}</div>
    <div class="stat-value">{{ $stats['total_products'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-amber">⚠️</div><span class="delta warn">{{ __('messages.needs_action') }}</span></div>
    <div class="stat-label">{{ __('messages.low_stock_alerts') }}</div>
    <div class="stat-value" id="lowStatVal">{{ $stats['low_stock_count'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-green">🧾</div><span class="delta up">▲ {{ __('messages.today') }}</span></div>
    <div class="stat-label">{{ __('messages.orders_today') }}</div>
    <div class="stat-value">{{ $stats['orders_today'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-purple">💰</div><span class="delta up">▲ {{ __('messages.value') }}</span></div>
    <div class="stat-label">{{ __('messages.stock_value') }}</div>
    <div class="stat-value"><small>Rs</small> {{ number_format($stats['stock_value'], 0) }}</div>
  </div>
</section>

{{-- Chart + Low Stock --}}
<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div>
        <h3>{{ __('messages.revenue_sales_rentals') }}</h3>
        <div class="chart-total">
          <b>Rs {{ number_format($chartTotal, 0) }}</b>
          <span>{{ __('messages.last_7_days') }}</span>
        </div>
      </div>
      <div class="legend">
        <span><i style="background:#6366f1"></i>{{ __('messages.sales') }}</span>
        <span><i style="background:#10b981"></i>{{ __('messages.rentals') }}</span>
      </div>
    </div>
    <div id="chart"></div>
  </div>

  <div class="card">
    <div class="card-head">
      <div><h3>{{ __('messages.low_stock_alerts') }}</h3><div class="sub" id="alertSub">{{ $stats['low_stock_count'] > 0 ? __('messages.items_at_or_below_min') : __('messages.everything_healthy') }}</div></div>
      <span style="font-size:9.5px;font-weight:800;padding:3px 8px;border-radius:6px;background:var(--red-soft);color:#b91c1c" id="alertTag">{{ $stats['low_stock_count'] }} {{ __('messages.items_upper') }}</span>
    </div>
    <div class="alerts" id="alerts"></div>
  </div>
</section>

{{-- QSU + Orders --}}
<section class="grid">
  <div class="card" id="qsuCard">
    <div class="card-head"><div><h3>{{ __('messages.quick_stock_update') }}</h3><div class="sub">{{ __('messages.add_remove_units') }}</div></div></div>
    <label class="qsu-label">{{ __('messages.select_product') }}</label>
    <select class="qsu-select" id="qsuSelect"></select>
    <div class="qsu-preview" id="qsuPreview"></div>
    <label class="qsu-label">{{ __('messages.quantity') }}</label>
    <div class="stepper">
      <button id="qtyMinus" type="button">−</button>
      <input id="qtyInput" type="number" value="10" min="1" max="9999">
      <button id="qtyPlus" type="button">+</button>
    </div>
    <div class="qsu-actions">
      <button class="btn-add" id="btnAddStock" type="button">＋ {{ __('messages.add_stock') }}</button>
      <button class="btn-remove" id="btnRemoveStock" type="button">− {{ __('messages.remove_stock') }}</button>
    </div>
  </div>

  <div class="card span-2">
    <div class="card-head">
      <div><h3>{{ __('messages.recent_orders') }}</h3><div class="sub">{{ __('messages.sales_rentals_wholesale') }}</div></div>
      <a href="#" class="link">{{ __('messages.view_all') }} →</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr>
          <th>{{ __('messages.order') }}</th>
          <th>{{ __('messages.customer') }}</th>
          <th>{{ __('messages.type') }}</th>
          <th>{{ __('messages.amount') }}</th>
          <th>{{ __('messages.status') }}</th>
        </tr></thead>
        <tbody id="ordersBody"></tbody>
      </table>
    </div>
  </div>
</section>

<div class="toast" id="toast"></div>

{{-- 🆕 New Product Modal --}}
<div id="productModal" style="display:none;position:fixed;inset:0;background:rgba(17,24,39,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_product') }}</h3>
            <button type="button" id="closeProductModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.inventory.products.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.product_name') }}</label>
                    <input type="text" name="name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Trekking Backpack 45L">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.price') }} (Rs)</label>
                    <input type="number" name="price" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="4500">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.product_type') }}</label>
                    <select name="product_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="shop">{{ __('messages.shop') }}</option>
                        <option value="rental">{{ __('messages.rental') }}</option>
                        <option value="wholesale">{{ __('messages.wholesale') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.stock_quantity') }}</label>
                    <input type="number" name="stock_quantity" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" value="10">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.low_stock_threshold') }}</label>
                    <input type="number" name="low_stock_threshold" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" value="5">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_product') }}</button>
        </form>
    </div>
</div>
@endsection

@push('inv-scripts')
<script>
const CHART = @json($chartData);
const LOW_ALERTS = @json($lowStockAlerts);
const PRODUCTS = @json($allProducts);
const ORDERS = @json($recentOrders);
const STOCK_HEALTH = @json($stats['stock_health_pct']);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* Multilingual JS strings */
const T = {
  out_of_stock:    @json(__('messages.out_of_stock')),
  low_stock:       @json(__('messages.low_stock')),
  in_stock:        @json(__('messages.in_stock')),
  current:         @json(__('messages.current')),
  min_label:       @json(__('messages.min')),
  price:           @json(__('messages.price')),
  in_stock_label:  @json(__('messages.in_stock_label')),
  restock:         @json(__('messages.restock')),
  all_stocked:     @json(__('messages.all_well_stocked')),
  no_orders:       @json(__('messages.no_orders_yet')),
  no_sku:          @json(__('messages.no_sku')),
  delete_product:  @json(__('messages.delete_product')),
  confirm_delete:  @json(__('messages.confirm_delete_product')),
  stock_zero:      @json(__('messages.stock_already_zero')),
  updated_ok:      @json(__('messages.stock_updated_ok')),
  updated_fail:    @json(__('messages.stock_updated_fail')),
  network_err:     @json(__('messages.network_error'))
};

function renderChart(){
  const W=780,H=260,padL=44,padR=10,padT=18,padB=32;
  const plotW=W-padL-padR,plotH=H-padT-padB;
  const allVals = CHART.flatMap(d=>[d.sales,d.rentals]);
  const max = Math.max(1, ...allVals) * 1.15;
  const groupW = CHART.length > 0 ? plotW/CHART.length : plotW;
  const barW=22, gap=9;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg"><defs>
    <linearGradient id="gSales" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#818cf8"/><stop offset="100%" stop-color="#6366f1"/></linearGradient>
    <linearGradient id="gRent" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#10b981"/></linearGradient>
  </defs>`;

  for(let i=0;i<=5;i++){
    const val=(max/5)*i,y=padT+plotH-(val/max)*plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#eef1f9" stroke-width="1"/>`;
    svg += `<text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#b0b7c9">${Math.round(val/1000)}k</text>`;
  }
  CHART.forEach((d,i)=>{
    const cx=padL+i*groupW+groupW/2, baseY=padT+plotH;
    const hS=(d.sales/max)*plotH, hR=(d.rentals/max)*plotH;
    svg += `<rect x="${cx-barW-gap/2}" y="${baseY-hS}" width="${barW}" height="${hS}" rx="5" fill="url(#gSales)"/>`;
    svg += `<rect x="${cx+gap/2}" y="${baseY-hR}" width="${barW}" height="${hR}" rx="5" fill="url(#gRent)"/>`;
    svg += `<text x="${cx}" y="${H-10}" text-anchor="middle" font-size="11.5" font-weight="700" fill="#9ca3af">${d.day}</text>`;
  });
  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

function renderAlerts(){
  const box = document.getElementById('alerts');
  if(!LOW_ALERTS.length){
    box.innerHTML = '<div class="all-good"><div>✅</div>'+T.all_stocked+'</div>';
  } else {
    box.innerHTML = LOW_ALERTS.map(p => {
      const ratio = Math.min(100, (p.stock / Math.max(p.min, 1)) * 100);
      const color = p.stock === 0 ? '#ef4444' : '#f59e0b';
      return `<div class="alert-row">
        <div class="alert-emoji">${p.emoji}</div>
        <div class="alert-info">
          <div class="alert-name">${p.name}<em>${p.sku || ''}</em></div>
          <div class="alert-bar"><div style="width:${ratio}%;background:${color}"></div></div>
          <div class="alert-meta" style="color:${color}">${p.stock} ${T.in_stock_label} · ${T.min_label} ${p.min}</div>
        </div>
        <button class="restock-btn" data-dbid="${p.db_id}" type="button">${T.restock}</button>
      </div>`;
    }).join('');
  }
  box.querySelectorAll('.restock-btn').forEach(btn=>{
    btn.addEventListener('click', ()=> focusRestock(btn.dataset.dbid));
  });
}

function renderHealth(){
  document.getElementById('scVal').innerHTML = STOCK_HEALTH + '% <small>'+T.in_stock_label+'</small>';
  document.getElementById('scBar').style.width = STOCK_HEALTH + '%';
}

function renderOrders(){
  const body = document.getElementById('ordersBody');
  if(!ORDERS.length){ body.innerHTML = '<tr><td colspan="5"><p class="empty">'+T.no_orders+'</p></td></tr>'; return; }
  body.innerHTML = ORDERS.map(o => {
    const modeCls = o.mode === 'Sale' ? 'mode-sale' : o.mode === 'Rental' ? 'mode-rental' : 'mode-wholesale';
    return `<tr>
      <td><div class="ord-id">${o.id}</div><div class="ord-items">${o.items || '—'}</div></td>
      <td><div class="cust"><div class="c-av">${o.initials}</div><div><div class="c-name">${o.name}</div></div></div></td>
      <td><span class="mode-badge ${modeCls}">${o.mode.toUpperCase()}</span></td>
      <td style="font-weight:800">Rs ${Number(o.amount).toLocaleString('en-US', { maximumFractionDigits: 0 })}</td>
      <td><span class="st-btn">${o.status}</span></td>
    </tr>`;
  }).join('');
}

const sel = document.getElementById('qsuSelect');
const previewEl = document.getElementById('qsuPreview');
const qtyInput = document.getElementById('qtyInput');

function buildSelect(){
  const prev = sel.value;
  sel.innerHTML = PRODUCTS.map(p => `<option value="${p.db_id}">${p.emoji} ${p.name} — ${p.sku || T.no_sku}</option>`).join('');
  if(prev && PRODUCTS.some(p => p.db_id == prev)) sel.value = prev;
}

function getSelected(){
  return PRODUCTS.find(p => p.db_id == sel.value) || PRODUCTS[0];
}

function renderPreview(){
  const p = getSelected();
  if(!p){ previewEl.innerHTML = ''; return; }
  const statusLabel = p.stock === 0 ? T.out_of_stock : (p.stock <= p.min ? T.low_stock : T.in_stock);
  const statusColor = p.stock === 0 ? '#ef4444' : (p.stock <= p.min ? '#f59e0b' : '#059669');
  const statusBg = p.stock === 0 ? 'var(--red-soft)' : (p.stock <= p.min ? 'var(--amber-soft)' : 'var(--green-soft)');
  previewEl.innerHTML = `
    <div class="qp-top">
      <div class="qp-emoji">${p.emoji}</div>
      <div><div class="qp-name">${p.name}</div><div class="qp-sku">${p.sku || T.no_sku}</div></div>
      <span class="qp-status" style="background:${statusBg};color:${statusColor}">${statusLabel}</span>
    </div>
    <div class="qp-stats">
      <div><span>${T.current}</span><b style="color:${statusColor}" id="qsuCurrent">${p.stock}</b></div>
      <div><span>${T.min_label}</span><b>${p.min}</b></div>
      <div><span>${T.price}</span><b>Rs ${Number(p.price).toLocaleString('en-US',{maximumFractionDigits:0})}</b></div>
    </div>
    <button class="delete-product-btn delete-product" data-id="${p.db_id}">${T.delete_product}</button>`;
}

function getQty(){
  let q = parseInt(qtyInput.value, 10);
  if(isNaN(q) || q < 1) q = 1;
  if(q > 9999) q = 9999;
  qtyInput.value = q;
  return q;
}

async function applyStock(sign){
  const p = getSelected();
  const qty = getQty();

  if(sign < 0 && p.stock === 0){
    toast(T.stock_zero + ' — ' + p.name, 'err');
    return;
  }

  const change = sign * qty;
  try {
    const res = await fetch(`/provider/inventory/products/${p.db_id}/stock`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ quantity: change })
    });
    const data = await res.json();
    if (res.ok && data.success) {
      p.stock = data.new_stock;
      renderPreview();
      const diff = change;
      toast(`${p.name}: ${diff > 0 ? '+' : ''}${diff} → ${p.stock} ${T.in_stock_label}`, diff > 0 ? 'ok' : 'err');
    } else {
      toast(T.updated_fail, 'err');
    }
  } catch(e) {
    console.error(e);
    toast(T.network_err, 'err');
  }
}

function focusRestock(dbId){
  sel.value = dbId;
  const p = PRODUCTS.find(x => x.db_id == dbId);
  if(p) qtyInput.value = Math.max(10, p.min * 2);
  renderPreview();
  const card = document.getElementById('qsuCard');
  card.classList.remove('flash');
  void card.offsetWidth;
  card.classList.add('flash');
  card.scrollIntoView({ behavior:'smooth', block:'center' });
}

let toastTimer;
function toast(msg, type){
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast show ' + (type || 'ok');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=> el.classList.remove('show'), 2600);
}

sel.addEventListener('change', renderPreview);
document.getElementById('qtyMinus').addEventListener('click', ()=> qtyInput.value = Math.max(1, getQty() - 1));
document.getElementById('qtyPlus').addEventListener('click', ()=> qtyInput.value = Math.min(9999, getQty() + 1));
qtyInput.addEventListener('change', getQty);
document.getElementById('btnAddStock').addEventListener('click', ()=> applyStock(1));
document.getElementById('btnRemoveStock').addEventListener('click', ()=> applyStock(-1));

/* ========== New Product Modal ========== */
const productModal = document.getElementById('productModal');
document.getElementById('newProductBtn')?.addEventListener('click', () => {
  productModal.style.display = 'flex';
});
document.getElementById('closeProductModal')?.addEventListener('click', () => {
  productModal.style.display = 'none';
});
productModal?.addEventListener('click', (e) => {
  if (e.target === productModal) productModal.style.display = 'none';
});

/* ========== Delete Product (AJAX) ========== */
document.addEventListener('click', async function(e) {
  if (!e.target.classList.contains('delete-product')) return;
  if (!confirm(T.confirm_delete)) return;
  const id = e.target.dataset.id;
  e.target.disabled = true;
  try {
    const res = await fetch(`/provider/inventory/products/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if (res.ok) window.location.reload();
    else e.target.disabled = false;
  } catch(err) {
    console.error(err);
    e.target.disabled = false;
  }
});

renderChart();
renderAlerts();
renderHealth();
renderOrders();
buildSelect();
renderPreview();
</script>
@endpush
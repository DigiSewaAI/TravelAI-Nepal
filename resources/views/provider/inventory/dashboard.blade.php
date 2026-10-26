@extends('layouts.provider-inventory')

@section('title', 'Inventory Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Namaste ' . (auth()->user()->name ?? 'Manager') . ' 👋 — Here\'s your inventory overview')

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
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.toast{left:14px;right:14px;bottom:14px;max-width:none}}
</style>
@endpush

@section('content')

{{-- KYC Banner --}}
@php
    $vp = auth()->user()->getCurrentProvider();
    $needsVerification = $vp && $vp->verification_status !== 'verified';
@endphp
@if($needsVerification)
<div style="background:#fff7ed;border-left:4px solid #f59e0b;padding:16px 20px;margin-bottom:20px;border-radius:12px;display:flex;align-items:start;gap:14px">
    <div style="flex-shrink:0;width:38px;height:38px;background:#fef5e3;border-radius:10px;display:grid;place-items:center;font-size:18px">⚠️</div>
    <div style="flex:1">
        <h4 style="font-size:14px;font-weight:800;color:#92400e;margin-bottom:4px">Account Unverified</h4>
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">Your inventory account is not verified yet. Upload your KYC documents to start selling products.</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">Verify Now →</a>
    </div>
</div>
@endif

{{-- Stats --}}
<section class="stats">
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-indigo">📦</div><span class="delta up">▲ Active</span></div>
    <div class="stat-label">Total Products</div>
    <div class="stat-value">{{ $stats['total_products'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-amber">⚠️</div><span class="delta warn">Needs action</span></div>
    <div class="stat-label">Low Stock Alerts</div>
    <div class="stat-value" id="lowStatVal">{{ $stats['low_stock_count'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-green">🧾</div><span class="delta up">▲ Today</span></div>
    <div class="stat-label">Orders Today</div>
    <div class="stat-value">{{ $stats['orders_today'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top"><div class="stat-icon i-purple">💰</div><span class="delta up">▲ Value</span></div>
    <div class="stat-label">Stock Value</div>
    <div class="stat-value"><small>Rs</small> {{ number_format($stats['stock_value'], 0) }}</div>
  </div>
</section>

{{-- Chart + Low Stock --}}
<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div>
        <h3>Revenue — Sales vs Rentals</h3>
        <div class="chart-total">
          <b>Rs {{ number_format($chartTotal, 0) }}</b>
          <span>Last 7 days</span>
        </div>
      </div>
      <div class="legend">
        <span><i style="background:#6366f1"></i>Sales</span>
        <span><i style="background:#10b981"></i>Rentals</span>
      </div>
    </div>
    <div id="chart"></div>
  </div>

  <div class="card">
    <div class="card-head">
      <div><h3>Low Stock Alerts</h3><div class="sub" id="alertSub">{{ $stats['low_stock_count'] > 0 ? 'Items at or below minimum' : 'Everything is healthy' }}</div></div>
      <span style="font-size:9.5px;font-weight:800;padding:3px 8px;border-radius:6px;background:var(--red-soft);color:#b91c1c" id="alertTag">{{ $stats['low_stock_count'] }} ITEMS</span>
    </div>
    <div class="alerts" id="alerts"></div>
  </div>
</section>

{{-- QSU + Orders --}}
<section class="grid">
  <div class="card" id="qsuCard">
    <div class="card-head"><div><h3>Quick Stock Update</h3><div class="sub">Add or remove units instantly</div></div></div>
    <label class="qsu-label">Select Product</label>
    <select class="qsu-select" id="qsuSelect"></select>
    <div class="qsu-preview" id="qsuPreview"></div>
    <label class="qsu-label">Quantity</label>
    <div class="stepper">
      <button id="qtyMinus" type="button">−</button>
      <input id="qtyInput" type="number" value="10" min="1" max="9999">
      <button id="qtyPlus" type="button">+</button>
    </div>
    <div class="qsu-actions">
      <button class="btn-add" id="btnAddStock" type="button">＋ Add Stock</button>
      <button class="btn-remove" id="btnRemoveStock" type="button">− Remove</button>
    </div>
  </div>

  <div class="card span-2">
    <div class="card-head">
      <div><h3>Recent Orders</h3><div class="sub">Sales, rentals & wholesale</div></div>
      <a href="#" class="link">View all →</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Order</th><th>Customer</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody id="ordersBody"></tbody>
      </table>
    </div>
  </div>
</section>

<div class="toast" id="toast"></div>
@endsection

@push('inv-scripts')
<script>
const CHART = @json($chartData);
const LOW_ALERTS = @json($lowStockAlerts);
const PRODUCTS = @json($allProducts);
const ORDERS = @json($recentOrders);
const STOCK_HEALTH = @json($stats['stock_health_pct']);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* Chart */
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

/* Low stock alerts */
function renderAlerts(){
  const box = document.getElementById('alerts');
  if(!LOW_ALERTS.length){
    box.innerHTML = '<div class="all-good"><div>✅</div>All products are well stocked!</div>';
  } else {
    box.innerHTML = LOW_ALERTS.map(p => {
      const ratio = Math.min(100, (p.stock / Math.max(p.min, 1)) * 100);
      const color = p.stock === 0 ? '#ef4444' : '#f59e0b';
      return `<div class="alert-row">
        <div class="alert-emoji">${p.emoji}</div>
        <div class="alert-info">
          <div class="alert-name">${p.name}<em>${p.sku || ''}</em></div>
          <div class="alert-bar"><div style="width:${ratio}%;background:${color}"></div></div>
          <div class="alert-meta" style="color:${color}">${p.stock} left · min ${p.min}</div>
        </div>
        <button class="restock-btn" data-dbid="${p.db_id}" type="button">Restock</button>
      </div>`;
    }).join('');
  }
  box.querySelectorAll('.restock-btn').forEach(btn=>{
    btn.addEventListener('click', ()=> focusRestock(btn.dataset.dbid));
  });
}

/* Health */
function renderHealth(){
  document.getElementById('scVal').innerHTML = STOCK_HEALTH + '% <small>in stock</small>';
  document.getElementById('scBar').style.width = STOCK_HEALTH + '%';
}

/* Orders */
function renderOrders(){
  const body = document.getElementById('ordersBody');
  if(!ORDERS.length){ body.innerHTML = '<tr><td colspan="5"><p class="empty">No orders yet</p></td></tr>'; return; }
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

/* Quick Stock Update */
const sel = document.getElementById('qsuSelect');
const previewEl = document.getElementById('qsuPreview');
const qtyInput = document.getElementById('qtyInput');

function buildSelect(){
  const prev = sel.value;
  sel.innerHTML = PRODUCTS.map(p => `<option value="${p.db_id}">${p.emoji} ${p.name} — ${p.sku || 'No SKU'}</option>`).join('');
  if(prev && PRODUCTS.some(p => p.db_id == prev)) sel.value = prev;
}

function getSelected(){
  return PRODUCTS.find(p => p.db_id == sel.value) || PRODUCTS[0];
}

function renderPreview(){
  const p = getSelected();
  if(!p){ previewEl.innerHTML = ''; return; }
  const statusLabel = p.stock === 0 ? 'Out of Stock' : (p.stock <= p.min ? 'Low Stock' : 'In Stock');
  const statusColor = p.stock === 0 ? '#ef4444' : (p.stock <= p.min ? '#f59e0b' : '#059669');
  const statusBg = p.stock === 0 ? 'var(--red-soft)' : (p.stock <= p.min ? 'var(--amber-soft)' : 'var(--green-soft)');
  previewEl.innerHTML = `
    <div class="qp-top">
      <div class="qp-emoji">${p.emoji}</div>
      <div><div class="qp-name">${p.name}</div><div class="qp-sku">${p.sku || 'No SKU'}</div></div>
      <span class="qp-status" style="background:${statusBg};color:${statusColor}">${statusLabel}</span>
    </div>
    <div class="qp-stats">
      <div><span>Current</span><b style="color:${statusColor}" id="qsuCurrent">${p.stock}</b></div>
      <div><span>Min</span><b>${p.min}</b></div>
      <div><span>Price</span><b>Rs ${Number(p.price).toLocaleString('en-US',{maximumFractionDigits:0})}</b></div>
    </div>`;
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
    toast('Stock is already 0 for ' + p.name, 'err');
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
      toast(`${p.name}: ${diff > 0 ? '+' : ''}${diff} → ${p.stock} in stock`, diff > 0 ? 'ok' : 'err');
    } else {
      toast('Failed to update stock', 'err');
    }
  } catch(e) {
    console.error(e);
    toast('Network error', 'err');
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

/* Toast */
let toastTimer;
function toast(msg, type){
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast show ' + (type || 'ok');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=> el.classList.remove('show'), 2600);
}

/* Events */
sel.addEventListener('change', renderPreview);
document.getElementById('qtyMinus').addEventListener('click', ()=> qtyInput.value = Math.max(1, getQty() - 1));
document.getElementById('qtyPlus').addEventListener('click', ()=> qtyInput.value = Math.min(9999, getQty() + 1));
qtyInput.addEventListener('change', getQty);
document.getElementById('btnAddStock').addEventListener('click', ()=> applyStock(1));
document.getElementById('btnRemoveStock').addEventListener('click', ()=> applyStock(-1));

renderChart();
renderAlerts();
renderHealth();
renderOrders();
buildSelect();
renderPreview();
</script>
@endpush
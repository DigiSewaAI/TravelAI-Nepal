@extends('layouts.provider-art-craft')

@section('title', 'Art Dashboard')
@section('page-title', 'Welcome back, ' . (auth()->user()->name ?? 'Artist'))
@section('page-subtitle', 'Your studio at a glance — ' . $totalArtworks . ' artworks live')

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
  .art-card{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;transition:.25s;cursor:pointer;position:relative}
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
  .empty{text-align:center;padding:32px 16px;color:var(--muted);font-size:13px;font-weight:600;grid-column:1/-1}
  .empty div{font-size:36px;margin-bottom:8px;opacity:.5}
  @media (max-width:1400px){.gallery{grid-template-columns:repeat(3,1fr)}}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2,.span-3{grid-column:span 1}.gallery{grid-template-columns:repeat(3,1fr)}}
  @media (max-width:720px){.gallery{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.gallery{grid-template-columns:1fr 1fr;gap:10px}.art-body{padding:10px}.art-name{font-size:12.5px}.art-price{font-size:12.5px}}
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">Your art studio is not verified yet. Upload your KYC documents to sell artworks and receive custom orders.</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">Verify Now →</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-terra">🖼️</div><span class="delta up">▲ Total</span></div><div class="stat-label">Total Artworks</div><div class="stat-value">{{ $totalArtworks }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-burg">💎</div><span class="delta up">▲ Sold</span></div><div class="stat-label">Artworks Sold (Oct)</div><div class="stat-value">{{ $soldCount }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-sand">✍️</div><span class="delta down">Needs action</span></div><div class="stat-label">Custom Orders Pending</div><div class="stat-value">{{ $pendingOrders }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">💰</div><span class="delta up">▲ Revenue</span></div><div class="stat-label">Total Revenue (Oct)</div><div class="stat-value"><small>Rs</small> {{ number_format($totalRevenue, 0) }}</div></div>
</section>

<section class="grid">
  <div class="card span-3">
    <div class="card-head">
      <div><h3>Sales & Custom Orders</h3><div class="chart-total"><b>Rs {{ number_format($totalRevenue, 0) }}</b><span>This Month</span></div></div>
      <div class="legend"><span><i style="background:#c65d3b"></i>Sales</span><span><i style="background:#d9b382"></i>Custom Orders</span></div>
    </div>
    <div id="chart"></div>
  </div>
</section>

<section class="card" style="margin-bottom:16px">
  <div class="card-head">
    <div><h3>Art Gallery</h3><div class="sub">Click any status badge to update</div></div>
    <a href="#" class="link">Manage →</a>
  </div>
  <div class="gal-tabs" id="galTabs">
    <button class="gtab on" data-filter="all">All</button>
    <button class="gtab" data-filter="painting">Paintings</button>
    <button class="gtab" data-filter="sculpture">Sculptures</button>
    <button class="gtab" data-filter="handicraft">Handicrafts</button>
    <button class="gtab" data-filter="sold">Sold Out</button>
  </div>
  <div class="gallery" id="gallery"></div>
</section>

<section class="card">
  <div class="card-head">
    <div><h3>Recent Custom Orders</h3><div class="sub">Client commissions & bespoke pieces</div></div>
    <a href="#" class="link">View all →</a>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>Client</th><th>Artwork Type</th><th>Budget</th><th>Deadline</th><th>Status</th></tr></thead>
      <tbody id="ordersBody"></tbody>
    </table>
  </div>
</section>
@endsection

@push('art-scripts')
<script>
const ARTWORKS = @json($artworks);
const ORDERS = @json($orders);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const STATUS_FLOW = ['available','sold','custom'];
const STATUS_LABEL = { available:'Available', sold:'Sold', custom:'Custom' };

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
  if(!filtered.length){ box.innerHTML = '<div class="empty"><div>🎨</div>No artworks in this filter</div>'; return; }

  box.innerHTML = filtered.map(a=>{
    const catLabel = a.category.charAt(0).toUpperCase() + a.category.slice(1);
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
          <button class="status-btn s-${a.status}" data-id="${a.id}" data-status="${a.status}">${STATUS_LABEL[a.status]}</button>
        </div>
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
  if(!ORDERS.length){ body.innerHTML = '<tr><td colspan="5"><p class="empty">No custom orders yet</p></td></tr>'; return; }
  const statusCls = { pending:'p-pending', progress:'p-progress', done:'p-done' };
  const statusLabel = { pending:'Pending', progress:'In Progress', done:'Completed' };
  const typeCls = { Painting:'t-painting', Sculpture:'t-sculpture', Handicraft:'t-handicraft', Mural:'t-mural' };

  body.innerHTML = ORDERS.map(o=>{
    const inits = o.client_name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    const typeClass = typeCls[o.type] || 't-painting';
    return `<tr>
      <td><div class="client"><div class="c-av" style="background:var(--terracotta)">${inits}</div><div><div class="c-name">${o.client_name}</div><div class="c-mail">${o.client_email || ''}</div></div></div></td>
      <td><span class="type-badge ${typeClass}">${o.type}</span></td>
      <td style="font-weight:800">Rs ${Number(o.budget).toLocaleString()}</td>
      <td>${new Date(o.deadline).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
      <td><button class="pill ${statusCls[o.status]}" data-id="${o.id}" data-status="${o.status}">${statusLabel[o.status]}</button></td>
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
</script>
@endpush
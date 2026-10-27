@extends('layouts.provider-transport')

@section('title', 'Transport Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Namaste ' . (auth()->user()->name ?? 'Manager') . ' 👋 — Here\'s your fleet status today')

@push('transport-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:19px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(12,30,51,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:19px}
  .i-sky{background:var(--sky-soft);color:var(--sky-d)}.i-green{background:var(--green-soft);color:#059669}.i-amber{background:var(--amber-soft);color:#b45309}.i-purple{background:var(--purple-soft);color:#6d28d9}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}.up{background:var(--green-soft);color:#059669}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:23px;font-weight:800;letter-spacing:-.8px}.stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:3px}
  .link{font-size:12px;font-weight:800;color:var(--sky-d);text-decoration:none}
  .legend{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
  .legend span{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted)}
  .legend i{width:9px;height:9px;border-radius:3px;display:block}
  .chart-total{display:flex;align-items:baseline;gap:9px;margin-top:3px;flex-wrap:wrap}
  .chart-total b{font-size:25px;font-weight:800}.chart-total span{font-size:11.5px;font-weight:800;color:#059669;background:var(--green-soft);padding:3px 9px;border-radius:20px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:8px}
  .fleet{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  .veh{border-radius:13px;padding:13px;cursor:pointer;transition:.2s;border:1.5px solid var(--line);background:#fff;position:relative}
  .veh:hover{transform:translateY(-3px);box-shadow:0 10px 22px rgba(12,30,51,.09)}
  .veh-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px}
  .veh-ic{font-size:20px;line-height:1}
  .veh-st{width:8px;height:8px;border-radius:50%;flex-shrink:0;box-shadow:0 0 0 3px rgba(0,0,0,.02)}
  .veh-id{font-size:11.5px;font-weight:800;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .veh-type{font-size:9.5px;color:var(--muted);font-weight:800;text-transform:uppercase;margin-bottom:8px}
  .veh-route{font-size:10.5px;font-weight:700;color:#4a5f78;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px}
  .veh-driver{font-size:9.5px;color:var(--muted);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .veh-available{border-color:#bbf7d0;background:#f4fdf8}.veh-available .veh-st{background:#22c55e}
  .veh-on-route{border-color:#bfe0f5;background:#f6fbff}.veh-on-route .veh-st{background:#0ea5e9;animation:pulse 1.6s infinite}
  .veh-maintenance{border-color:#fecaca;background:#fffafa}.veh-maintenance .veh-st{background:#ef4444}
  .veh-charging{border-color:#ddd0fb;background:#faf8ff}.veh-charging .veh-st{background:#8b5cf6}
  .fleet-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:15px;padding-top:15px;border-top:1px solid var(--line)}
  .fleet-legend span{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:var(--muted)}
  .fleet-legend i{width:8px;height:8px;border-radius:50%;display:block}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:340px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa9bc;font-weight:800;padding:0 10px 11px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:12px 10px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f1f6fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fafcff}
  .pax{display:flex;align-items:center;gap:9px}
  .p-av{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:11px;flex-shrink:0;background:linear-gradient(135deg,#0ea5e9,#6366f1)}
  .p-name{font-weight:800;font-size:12.5px;line-height:1.25}
  .p-mail{font-size:10.5px;color:var(--muted);font-weight:600}
  .pill{font-size:10.5px;font-weight:800;padding:4px 9px;border-radius:20px;display:inline-block}
  .p-confirm{background:var(--green-soft);color:#047857}.p-pending{background:var(--amber-soft);color:#b45309}.p-cancel{background:var(--red-soft);color:#b91c1c}
  .empty{font-size:12.5px;color:var(--muted);text-align:center;padding:18px 0;font-weight:600}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.fleet{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.fleet{grid-template-columns:1fr}}
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">Your transport account is not verified yet. Upload your KYC documents to accept ride bookings and receive payouts.</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">Verify Now →</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-sky">🛣️</div><span class="delta up">▲ Today</span></div><div class="stat-label">Total Trips Today</div><div class="stat-value">{{ $totalTripsToday }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">🚌</div><span class="delta up">▲ Live</span></div><div class="stat-label">Active Fleet</div><div class="stat-value">{{ $activeFleet }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-amber">🎫</div><span class="delta up">▲ Live</span></div><div class="stat-label">Pending Bookings</div><div class="stat-value">{{ $pendingBookings }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-purple">💰</div><span class="delta up">▲ Today</span></div><div class="stat-label">Today's Revenue</div><div class="stat-value"><small>Rs</small> {{ number_format($todayRevenue, 0) }}</div></div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div><h3>Trip Volume</h3><div class="chart-total"><b>Last 7 days</b><span>Land Transport</span></div></div>
      <div class="legend"><span><i style="background:#0ea5e9"></i>Trips</span></div>
    </div>
    <div id="chart"></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Ground Fleet</h3><div class="sub">Click a vehicle to cycle its status</div></div></div>
    <div class="fleet" id="fleet"></div>
    <div class="fleet-legend">
      <span><i style="background:#22c55e"></i>Available</span>
      <span><i style="background:#0ea5e9"></i>On Route</span>
      <span><i style="background:#8b5cf6"></i>Charging</span>
      <span><i style="background:#ef4444"></i>Maintenance</span>
    </div>
  </div>
</section>

<section class="grid">
  <div class="card" style="grid-column:span 3">
    <div class="card-head"><div><h3>Recent Bookings</h3><div class="sub">Latest reservations</div></div></div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Passenger</th><th>Vehicle</th><th>Route</th><th>Ref</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody id="bookingsBody"></tbody>
      </table>
    </div>
  </div>
</section>
@endsection

@push('transport-scripts')
<script>
const CHART = @json($chartData);
const VEHICLES = @json($vehicles);
const BOOKINGS = @json($recentBookings);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const VEH_STATUSES = ['available','on-route','charging','maintenance'];

function renderChart(){
  const W=780, H=260, padL=44, padR=10, padT=16, padB=32;
  const plotW=W-padL-padR, plotH=H-padT-padB;
  const max = Math.max(5, ...CHART.map(d=>d.trips)) * 1.2;
  const groupW = CHART.length > 0 ? plotW/CHART.length : plotW;
  const barW = 24;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg">
    <defs><linearGradient id="gTrip" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#0ea5e9"/></linearGradient></defs>`;
  
  for(let i=0;i<=5;i++){
    const val=(max/5)*i,y=padT+plotH-(val/max)*plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#eef4fa" stroke-width="1"/>`;
    svg += `<text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#a9b8cb">${Math.round(val)}</text>`;
  }
  CHART.forEach((d,i)=>{
    const cx=padL+i*groupW+groupW/2, baseY=padT+plotH;
    const h=(d.trips/max)*plotH;
    svg += `<rect x="${cx-barW/2}" y="${baseY-h}" width="${barW}" height="${h}" rx="5" fill="url(#gTrip)"/>`;
    svg += `<text x="${cx}" y="${H-9}" text-anchor="middle" font-size="11.5" font-weight="700" fill="#94a3b8">${d.day}</text>`;
  });
  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

function renderFleet(){
  const box = document.getElementById('fleet');
  if(!VEHICLES.length){ box.innerHTML='<p class="empty" style="grid-column:1/-1">No vehicles registered</p>'; return; }
  box.innerHTML = VEHICLES.map((v,i)=>`
    <div class="veh veh-${v.status}" data-id="${v.id}" data-status="${v.status}">
      <div class="veh-top"><div class="veh-ic">${v.vehicle_type.includes('Bus')?'🚌':(v.vehicle_type==='Van'||v.vehicle_type==='Hiace'?'🚐':(v.vehicle_type==='SUV'||v.vehicle_type==='Jeep'?'🚙':'🚗'))}</div><div class="veh-st"></div></div>
      <div class="veh-id">${v.vehicle_number}</div>
      <div class="veh-type">${v.vehicle_type}</div>
      <div class="veh-route">${v.route_name || '—'}</div>
      <div class="veh-driver">${v.driver_name || 'Unassigned'} • ${v.capacity} seats</div>
    </div>
  `).join('');

  document.querySelectorAll('.veh').forEach(el=>{
    el.addEventListener('click', async function(){
      const id = this.dataset.id;
      const cur = VEH_STATUSES.indexOf(this.dataset.status);
      const next = VEH_STATUSES[(cur+1) % VEH_STATUSES.length];
      try {
        const res = await fetch(`/provider/transport/vehicles/${id}/status`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({status:next})
        });
        if(res.ok) window.location.reload();
      } catch(e){ console.error(e); }
    });
  });
  
  const active = VEHICLES.filter(v=>v.status==='available'||v.status==='on-route').length;
  document.getElementById('fleetVal').innerHTML = active + ' <small>/ ' + VEHICLES.length + ' active</small>';
}

function renderBookings(){
  const body = document.getElementById('bookingsBody');
  if(!BOOKINGS.length){ body.innerHTML = '<tr><td colspan="7"><p class="empty">No bookings yet</p></td></tr>'; return; }
  body.innerHTML = BOOKINGS.map(b=>{
    const payCls = b.status==='confirmed'?'p-confirm':(b.status==='pending'?'p-pending':'p-cancel');
    const inits = b.passenger_name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    return `<tr>
      <td><div class="pax"><div class="p-av">${inits}</div><div><div class="p-name">${b.passenger_name}</div><div class="p-mail">${b.passenger_email || ''}</div></div></div></td>
      <td style="font-weight:800">${b.vehicle?.vehicle_number || '—'}</td>
      <td>${b.route_name}</td>
      <td style="color:#64748b">${b.booking_ref}</td>
      <td>${new Date(b.travel_date).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
      <td style="font-weight:800">Rs ${Number(b.amount).toLocaleString()}</td>
      <td><span class="pill ${payCls}">${b.status}</span></td>
    </tr>`;
  }).join('');
}

renderChart(); renderFleet(); renderBookings();
</script>
@endpush
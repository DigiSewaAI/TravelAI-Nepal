@extends('layouts.provider-transport')

@php
    $greetingName = auth()->user()->name ?? __('messages.manager');
    $subtitle = __('messages.transport_welcome_subtitle', ['name' => $greetingName]);
@endphp

@section('title', __('messages.transport_dashboard_title'))
@section('page-title', __('messages.dashboard'))
@section('page-subtitle', $subtitle)

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
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0;overflow:hidden}
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
  .fleet{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;min-width:0}
  .veh{border-radius:13px;padding:13px;cursor:pointer;transition:.2s;border:1.5px solid var(--line);background:#fff;position:relative;min-width:0;overflow:hidden}
  .veh:hover{transform:translateY(-3px);box-shadow:0 10px 22px rgba(12,30,51,.09)}
  .veh-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;padding-right:18px}
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
  .action-bar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:18px;flex-wrap:wrap}
  .btn-secondary{background:var(--card);color:var(--text);border:1px solid var(--line);box-shadow:none}
  .delete-vehicle{position:absolute;top:8px;right:8px;background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;padding:2px 5px;border-radius:5px;transition:.15s;z-index:2}
  .delete-vehicle:hover{background:var(--red-soft)}
  .delete-booking{background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;padding:2px 5px;border-radius:5px;transition:.15s}
  .delete-booking:hover{background:var(--red-soft)}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.fleet{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.fleet{grid-template-columns:1fr}.action-bar{justify-content:stretch}.action-bar button{flex:1}}
</style>
@endpush

@section('content')

{{-- Welcome Banner (Shiny) --}}
<div style="background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 20%, #0ea5e9 50%, #0284c7 80%, #0369a1 100%); border-radius: 16px; padding: 25px; margin-bottom: 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(14, 165, 233, 0.55), inset 0 1px 0 rgba(255,255,255,0.4), inset 0 -1px 0 rgba(0,0,0,0.1);">
    <div style="position: absolute; top: -60%; right: -15%; width: 70%; height: 220%; background: radial-gradient(circle, rgba(255,255,255,0.28) 0%, rgba(255,255,255,0.08) 30%, transparent 65%); pointer-events: none;"></div>
    <div style="position: absolute; bottom: -40%; left: 10%; width: 50%; height: 120%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); pointer-events: none;"></div>
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 5px; text-shadow: 0 2px 8px rgba(0,0,0,0.2); letter-spacing: -0.3px;">{{ __('messages.welcome_back') }}, {{ auth()->user()->name ?? 'Manager' }}! 👋 {{-- PHASE-1-BADGE --}}@if(auth()->user()->getCurrentProvider()?->verification_status === 'verified')<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,0.22);padding:3px 9px;border-radius:20px;font-size:11px;font-weight:800;margin-left:8px;vertical-align:middle;">&#10003; {{ __('messages.verified') }}</span>@endif</h1>
        <p style="font-size: 13px; opacity: 0.95; text-shadow: 0 1px 3px rgba(0,0,0,0.15);">{{ __('messages.transport_welcome_subtitle') }}</p>
    </div>
    <div style="font-size: 48px; opacity: 0.55; position: relative; z-index: 1; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">🚌</div>
</div>


{{-- 🆕 Action Bar --}}
<div class="action-bar">
  <button class="btn-primary btn-secondary" id="addVehicleBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;stroke-width:2.6">
      <path d="M4 17h16"/><path d="M6 17V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/>
    </svg>
    <span>{{ __('messages.add_vehicle') }}</span>
  </button>
  <button class="btn-primary" id="newTripBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    <span>{{ __('messages.new_trip') }}</span>
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_transport_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-sky">🛣️</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.total_trips_today') }}</div><div class="stat-value">{{ $totalTripsToday }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">🚌</div><span class="delta up">▲ {{ __('messages.live') }}</span></div><div class="stat-label">{{ __('messages.active_fleet') }}</div><div class="stat-value">{{ $activeFleet }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-amber">🎫</div><span class="delta up">▲ {{ __('messages.live') }}</span></div><div class="stat-label">{{ __('messages.pending_bookings') }}</div><div class="stat-value">{{ $pendingBookings }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-purple">💰</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.today_revenue') }}</div><div class="stat-value"><small>Rs</small> {{ number_format($todayRevenue, 0) }}</div></div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div><h3>{{ __('messages.trip_volume') }}</h3><div class="chart-total"><b>{{ __('messages.last_7_days') }}</b><span>{{ __('messages.land_transport') }}</span></div></div>
      <div class="legend"><span><i style="background:#0ea5e9"></i>{{ __('messages.trips') }}</span></div>
    </div>
    <div id="chart"></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.ground_fleet') }}</h3><div class="sub">{{ __('messages.click_vehicle_cycle') }}</div></div></div>
    <div class="fleet" id="fleet"></div>
    <div class="fleet-legend">
      <span><i style="background:#22c55e"></i>{{ __('messages.available') }}</span>
      <span><i style="background:#0ea5e9"></i>{{ __('messages.on_route') }}</span>
      <span><i style="background:#8b5cf6"></i>{{ __('messages.charging') }}</span>
      <span><i style="background:#ef4444"></i>{{ __('messages.maintenance') }}</span>
    </div>
  </div>
</section>

<section class="grid">
  <div class="card" style="grid-column:span 3">
    <div class="card-head"><div><h3>{{ __('messages.recent_bookings') }}</h3><div class="sub">{{ __('messages.latest_reservations') }}</div></div></div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr>
          <th>{{ __('messages.passenger') }}</th>
          <th>{{ __('messages.vehicle') }}</th>
          <th>{{ __('messages.route') }}</th>
          <th>{{ __('messages.ref') }}</th>
          <th>{{ __('messages.date') }}</th>
          <th>{{ __('messages.amount') }}</th>
          <th>{{ __('messages.status') }}</th>
          <th>{{ __('messages.action') }}</th>
        </tr></thead>
        <tbody id="bookingsBody"></tbody>
      </table>
    </div>
  </div>
</section>

{{-- 🆕 Add Vehicle Modal --}}
<div id="vehicleModal" style="display:none;position:fixed;inset:0;background:rgba(10,28,51,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.add_vehicle') }}</h3>
            <button type="button" id="closeVehicleModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.transport.vehicles.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.vehicle_number') }}</label>
                    <input type="text" name="vehicle_number" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="BA-1-KHA-1234">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.vehicle_type') }}</label>
                    <input type="text" name="vehicle_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Bus / Van / Car">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.capacity') }}</label>
                    <input type="number" name="capacity" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="42">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.driver_name') }}</label>
                    <input type="text" name="driver_name" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="Ram B.">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.route') }}</label>
                    <input type="text" name="route_name" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="KTM → Pokhara">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.add_vehicle') }}</button>
        </form>
    </div>
</div>

{{-- 🆕 New Trip Modal --}}
<div id="tripModal" style="display:none;position:fixed;inset:0;background:rgba(10,28,51,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_trip') }}</h3>
            <button type="button" id="closeTripModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.transport.bookings.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.passenger_name') }}</label>
                    <input type="text" name="passenger_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="John Doe">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.passenger_email') }}</label>
                    <input type="email" name="passenger_email" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="john@example.com">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.vehicle') }}</label>
                    <select name="vehicle_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="">—</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->vehicle_number }} ({{ $v->vehicle_type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.route') }}</label>
                    <input type="text" name="route_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="KTM → Pokhara">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.travel_date') }}</label>
                    <input type="date" name="travel_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="1500">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_booking') }}</button>
        </form>
    </div>
</div>
@endsection

@push('transport-scripts')
<script>
const CHART = @json($chartData);
const VEHICLES = @json($vehicles);
const BOOKINGS = @json($recentBookings);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const VEH_STATUSES = ['available','on-route','charging','maintenance'];

/* Multilingual JS strings */
const T = {
  no_vehicles:   @json(__('messages.no_vehicles_registered')),
  no_bookings:   @json(__('messages.no_bookings_yet')),
  unassigned:    @json(__('messages.unassigned')),
  seats:         @json(__('messages.seats')),
  pending:       @json(__('messages.pending')),
  confirmed:     @json(__('messages.confirmed')),
  cancelled:     @json(__('messages.cancelled')),
  confirm_del_v: @json(__('messages.confirm_delete_vehicle')),
  confirm_del_b: @json(__('messages.confirm_delete_booking')),
  delete_v:      @json(__('messages.delete_vehicle')),
  delete_b:      @json(__('messages.delete_booking'))
};
const STATUS_LABEL = { confirmed: T.confirmed, pending: T.pending, cancelled: T.cancelled };

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
  if(!VEHICLES.length){ box.innerHTML='<p class="empty" style="grid-column:1/-1">'+T.no_vehicles+'</p>'; return; }
  box.innerHTML = VEHICLES.map((v,i)=>`
    <div class="veh veh-${v.status}" data-id="${v.id}" data-status="${v.status}">
      <button class="delete-vehicle" data-id="${v.id}" title="${T.delete_v}">✕</button>
      <div class="veh-top"><div class="veh-ic">${v.vehicle_type.includes('Bus')?'🚌':(v.vehicle_type==='Van'||v.vehicle_type==='Hiace'?'🚐':(v.vehicle_type==='SUV'||v.vehicle_type==='Jeep'?'🚙':'🚗'))}</div><div class="veh-st"></div></div>
      <div class="veh-id">${v.vehicle_number}</div>
      <div class="veh-type">${v.vehicle_type}</div>
      <div class="veh-route">${v.route_name || '—'}</div>
      <div class="veh-driver">${v.driver_name || T.unassigned} • ${v.capacity} ${T.seats}</div>
    </div>
  `).join('');

  document.querySelectorAll('.veh').forEach(el=>{
    el.addEventListener('click', async function(e){
      if (e.target.classList.contains('delete-vehicle')) return; // delete handled separately
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
  document.getElementById('fleetVal').innerHTML = active + ' <small>/ ' + VEHICLES.length + ' ' + @json(\Illuminate\Support\Str::lower(__('messages.active'))) + '</small>';
}

function renderBookings(){
  const body = document.getElementById('bookingsBody');
  if(!BOOKINGS.length){ body.innerHTML = '<tr><td colspan="8"><p class="empty">'+T.no_bookings+'</p></td></tr>'; return; }
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
      <td><span class="pill ${payCls}">${STATUS_LABEL[b.status] || b.status}</span></td>
      <td><button class="delete-booking" data-id="${b.id}" title="${T.delete_b}">✕</button></td>
    </tr>`;
  }).join('');
}

renderChart(); renderFleet(); renderBookings();

/* ========== Modals ========== */
const vehicleModal = document.getElementById('vehicleModal');
const tripModal = document.getElementById('tripModal');
document.getElementById('addVehicleBtn')?.addEventListener('click', () => { vehicleModal.style.display = 'flex'; });
document.getElementById('newTripBtn')?.addEventListener('click', () => { tripModal.style.display = 'flex'; });
document.getElementById('closeVehicleModal')?.addEventListener('click', () => { vehicleModal.style.display = 'none'; });
document.getElementById('closeTripModal')?.addEventListener('click', () => { tripModal.style.display = 'none'; });
vehicleModal?.addEventListener('click', (e) => { if (e.target === vehicleModal) vehicleModal.style.display = 'none'; });
tripModal?.addEventListener('click', (e) => { if (e.target === tripModal) tripModal.style.display = 'none'; });

/* ========== Delete Handlers (Vehicle + Booking) ========== */
document.addEventListener('click', async function(e) {
  if (e.target.classList.contains('delete-vehicle')) {
    if (!confirm(T.confirm_del_v)) return;
    const id = e.target.dataset.id;
    e.target.disabled = true;
    try {
      const res = await fetch(`/provider/transport/vehicles/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      if (res.ok) window.location.reload();
      else e.target.disabled = false;
    } catch (err) { console.error(err); e.target.disabled = false; }
  }

  if (e.target.classList.contains('delete-booking')) {
    if (!confirm(T.confirm_del_b)) return;
    const id = e.target.dataset.id;
    e.target.disabled = true;
    try {
      const res = await fetch(`/provider/transport/bookings/${id}`, {
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
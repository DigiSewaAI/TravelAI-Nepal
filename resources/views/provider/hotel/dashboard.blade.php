@extends('layouts.provider-hotel')

@section('title', __('messages.hotel_dashboard_title'))

@php
    $greetingName  = auth()->user()->name ?? __('messages.manager');
    $propertyName  = $provider->name ?? __('messages.your_property');
    $subtitle      = __('messages.welcome_subtitle', ['name' => $greetingName, 'property' => $propertyName]);
@endphp

@section('page-title', __('messages.dashboard'))
@section('page-subtitle', $subtitle)

@push('hotel-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:19px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(14,23,38,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:19px}
  .i-gold{background:var(--gold-soft);color:var(--gold-d)}.i-green{background:var(--green-soft);color:#059669}.i-blue{background:var(--blue-soft);color:#1d4ed8}.i-purple{background:var(--purple-soft);color:#6d28d9}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}.up{background:var(--green-soft);color:#059669}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:23px;font-weight:800;letter-spacing:-.8px}.stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px}
  .link{font-size:12px;font-weight:800;color:var(--gold-d);text-decoration:none}
  .legend{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
  .legend span{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted)}
  .legend i{width:9px;height:9px;border-radius:3px;display:block}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:6px}
  .cio-list{display:flex;flex-direction:column;gap:10px;max-height:330px;overflow-y:auto;padding-right:4px}
  .cio{display:flex;align-items:center;gap:12px;padding:11px;border-radius:14px;border:1px solid var(--line);background:#fbfdff;transition:.2s}
  .cio:hover{border-color:#ecdfb4;box-shadow:0 6px 18px rgba(201,162,39,.1);background:#fff}
  .cio-av{width:38px;height:38px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;color:#fff;font-weight:800;font-size:12px;background:linear-gradient(135deg,#3b82f6,#6366f1)}
  .cio-info{flex:1;min-width:0}
  .cio-name{font-size:12.5px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .cio-meta{font-size:10.5px;color:var(--muted);font-weight:700;margin-top:2px}
  .cio-btn{border:none;border-radius:9px;padding:7px 12px;cursor:pointer;font-family:inherit;font-size:11px;font-weight:800;transition:.18s;white-space:nowrap;flex-shrink:0}
  .cio-btn:hover{filter:brightness(.95);transform:scale(1.04)}
  .btn-checkin{background:var(--green-soft);color:#047857}
  .btn-checkout{background:var(--amber-soft);color:#b45309}
  .cio-btn:disabled{opacity:.45;cursor:default;transform:none}
  .room-tabs{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px}
  .rtab{border:1px solid var(--line);background:#fff;border-radius:9px;padding:7px 13px;font-family:inherit;font-size:11.5px;font-weight:800;color:var(--muted);cursor:pointer;transition:.18s}
  .rtab:hover{border-color:#ecdfb4;color:var(--gold-d)}
  .rtab.on{background:var(--navy);border-color:var(--navy);color:#fff}
  .rooms{display:grid;grid-template-columns:repeat(6,1fr);gap:9px}
  .room{border-radius:12px;padding:12px 8px;text-align:center;cursor:pointer;border:1.5px solid var(--line);background:#fff;transition:.2s;position:relative}
  .room:hover{transform:translateY(-3px);box-shadow:0 10px 22px rgba(14,23,38,.1)}
  .room .r-num{font-size:13.5px;font-weight:800;letter-spacing:-.3px;line-height:1.1}
  .room .r-type{font-size:9px;font-weight:800;color:var(--muted);margin-top:3px;letter-spacing:.3px}
  .room .r-dot{width:6px;height:6px;border-radius:50%;margin:7px auto 0}
  .r-available{border-color:#bbf7d0;background:#f4fdf8}.r-available .r-dot{background:#22c55e}.r-available .r-num{color:#15803d}
  .r-occupied{border-color:#fecaca;background:#fffafa}.r-occupied .r-dot{background:#ef4444}.r-occupied .r-num{color:#b91c1c}
  .r-reserved{border-color:#c7d2fe;background:#f7f9ff}.r-reserved .r-dot{background:#6366f1}.r-reserved .r-num{color:#4338ca}
  .r-dirty{border-color:#fed7aa;background:#fffcf5}.r-dirty .r-dot{background:#f59e0b}.r-dirty .r-num{color:#b45309}
  .r-maintenance{border-color:#e2e8f0;background:#f8fafc}.r-maintenance .r-dot{background:#94a3b8}.r-maintenance .r-num{color:#64748b}
  .room-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:15px;padding-top:15px;border-top:1px solid var(--line)}
  .room-legend span{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:var(--muted)}
  .room-legend i{width:8px;height:8px;border-radius:50%;display:block}
  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:340px}
  .tbl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#9aa8bc;font-weight:800;padding:0 10px 11px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:12px 10px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fbfdff}
  .guest{display:flex;align-items:center;gap:9px}
  .g-av{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:11px;flex-shrink:0;background:linear-gradient(135deg,#3b82f6,#6366f1)}
  .g-name{font-weight:800;font-size:12.5px;line-height:1.25}
  .g-mail{font-size:10.5px;color:var(--muted);font-weight:600}
  .pill{font-size:10.5px;font-weight:800;padding:4px 9px;border-radius:20px;display:inline-block}
  .p-paid{background:var(--green-soft);color:#047857}.p-pending{background:var(--amber-soft);color:#b45309}.p-refund{background:var(--red-soft);color:#b91c1c}
  .hk{display:flex;align-items:center;gap:11px;padding:11px 0;border-bottom:1px solid #f3f7fc}
  .hk:last-child{border-bottom:none;padding-bottom:0}
  .hk:first-child{padding-top:0}
  .hk-ic{width:38px;height:38px;border-radius:11px;background:#f4f7fc;display:grid;place-items:center;font-size:17px;flex-shrink:0}
  .hk-info{flex:1;min-width:0}
  .hk-title{font-size:12.5px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .hk-sub{font-size:10.5px;color:var(--muted);font-weight:700;margin-top:2px}
  .hk-tag{font-size:10px;font-weight:800;padding:4px 9px;border-radius:20px;flex-shrink:0;white-space:nowrap}
  .hk-done{background:var(--green-soft);color:#047857}.hk-prog{background:var(--blue-soft);color:#1d4ed8}.hk-queue{background:#f1f5f9;color:#64748b}
  .st-btn{border:none;border-radius:8px;padding:5px 10px;font-family:inherit;font-size:11px;font-weight:800;cursor:pointer;transition:.18s}
  .st-btn:hover{filter:brightness(.95);transform:scale(1.04)}
  .st-refunded{background:var(--red-soft);color:#b91c1c}
  .empty{font-size:12.5px;color:var(--muted);text-align:center;padding:18px 0;font-weight:600}
  @media (max-width:1280px){ .rooms{grid-template-columns:repeat(5,1fr)} }
  @media (max-width:1250px){ .stats{grid-template-columns:repeat(2,1fr)} }
  @media (max-width:1080px){ .grid{grid-template-columns:1fr} .span-2{grid-column:span 1} }
  @media (max-width:640px){ .stats{grid-template-columns:1fr} .card{padding:16px} .rooms{grid-template-columns:repeat(3,1fr)} }
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
        <h4 style="font-size:14px;font-weight:800;color:#92400e;margin-bottom:4px">{{ __('messages.account_unverified') }}</h4>
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_hotel_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-gold">💰</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.today_revenue') }}</div><div class="stat-value"><small>Rs</small> {{ number_format($todayRevenue, 0) }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-blue">📅</div><span class="delta up">▲ {{ __('messages.today') }}</span></div><div class="stat-label">{{ __('messages.bookings_today') }}</div><div class="stat-value">{{ $bookingsToday }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-green">🏨</div><span class="delta up">▲ {{ __('messages.live') }}</span></div><div class="stat-label">{{ __('messages.rooms_occupied') }}</div><div class="stat-value">{{ $roomsOccupied }}<small> / {{ $totalRooms }}</small></div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-purple">👥</div><span class="delta up">▲ {{ __('messages.live') }}</span></div><div class="stat-label">{{ __('messages.guests_in_house') }}</div><div class="stat-value">{{ $guestsInHouse }}</div></div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div><h3>{{ __('messages.room_nights_sold') }}</h3><div class="sub">{{ __('messages.last_7_days_performance') }}</div></div>
      <div class="legend"><span><i style="background:#c9a227"></i>{{ __('messages.occupied') }}</span><span><i style="background:#dbe6f3"></i>{{ __('messages.available') }}</span></div>
    </div>
    <div id="chart"></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.front_desk') }}</h3><div class="sub" id="cioCount">{{ __('messages.checkins_checkouts') }}</div></div></div>
    <div class="cio-list" id="cioList"></div>
  </div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head"><div><h3>{{ __('messages.room_status') }}</h3><div class="sub">{{ __('messages.click_room_cycle') }}</div></div></div>
    <div class="room-tabs" id="roomTabs">
      <button class="rtab on" data-f="all">{{ __('messages.all_rooms') }}</button>
      <button class="rtab" data-f="1">{{ __('messages.floor') }} 1</button>
      <button class="rtab" data-f="2">{{ __('messages.floor') }} 2</button>
      <button class="rtab" data-f="3">{{ __('messages.floor') }} 3</button>
    </div>
    <div class="rooms" id="rooms"></div>
    <div class="room-legend">
      <span><i style="background:#22c55e"></i>{{ __('messages.available') }}</span>
      <span><i style="background:#ef4444"></i>{{ __('messages.occupied') }}</span>
      <span><i style="background:#6366f1"></i>{{ __('messages.reserved') }}</span>
      <span><i style="background:#f59e0b"></i>{{ __('messages.dirty') }}</span>
      <span><i style="background:#94a3b8"></i>{{ __('messages.maintenance') }}</span>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.housekeeping') }}</h3><div class="sub">{{ __('messages.todays_tasks') }}</div></div></div>
    <div id="housekeeping"></div>
  </div>
</section>

<section class="grid">
  <div class="card" style="grid-column:span 3">
    <div class="card-head"><div><h3>{{ __('messages.recent_bookings') }}</h3><div class="sub">{{ __('messages.latest_reservations') }}</div></div></div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr>
          <th>{{ __('messages.guest') }}</th>
          <th>{{ __('messages.room') }}</th>
          <th>{{ __('messages.checkin_col') }}</th>
          <th>{{ __('messages.checkout_col') }}</th>
          <th>{{ __('messages.nights') }}</th>
          <th>{{ __('messages.amount') }}</th>
          <th>{{ __('messages.payment') }}</th>
          <th>{{ __('messages.action') }}</th>
        </tr></thead>
        <tbody id="bookingsBody"></tbody>
      </table>
    </div>
  </div>
</section>

{{-- New Booking Modal --}}
<div id="bookingModal" style="display:none;position:fixed;inset:0;background:rgba(14,23,38,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:520px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_booking') }}</h3>
            <button type="button" id="closeBookingModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.hotel.bookings.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.guest_name') }}</label>
                    <input type="text" name="guest_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="{{ __('messages.guest_name_placeholder') }}">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.email_optional') }}</label>
                    <input type="email" name="guest_email" style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="john@example.com">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.room') }}</label>
                    <select name="room_id" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ __('messages.room') }} {{ $r->room_number }} ({{ $r->room_type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount_rs') }}</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="18000">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.checkin_col') }}</label>
                    <input type="date" name="check_in_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.checkout_col') }}</label>
                    <input type="date" name="check_out_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.payment_status') }}</label>
                    <select name="payment_status" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="paid">{{ __('messages.paid') }}</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_booking') }}</button>
        </form>
    </div>
</div>
@endsection

@push('hotel-scripts')
<script>
const CHART = @json($chartData);
const CIO = @json($frontDesk);
const ROOMS = @json($rooms);
const TASKS = @json($tasks);
const BOOKINGS = @json($recentBookings);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const ROOM_TYPES = {1:'DELUXE',2:'SUPERIOR',3:'SUITE',4:'DELUXE',5:'SUPERIOR',6:'PREMIUM'};
const ROOM_STATUSES = ['available','occupied','reserved','dirty','maintenance'];

/* Multilingual JS strings */
const T = {
  no_checkins:      @json(__('messages.no_checkins_today')),
  pending_actions:  @json(__('messages.pending_actions')),
  check_in:         @json(__('messages.check_in')),
  check_out:        @json(__('messages.check_out')),
  done:             @json(__('messages.done')),
  no_tasks:         @json(__('messages.no_tasks_pending')),
  in_progress:      @json(__('messages.in_progress')),
  queue:            @json(__('messages.queue')),
  no_bookings:      @json(__('messages.no_bookings_yet')),
  confirm_delete:   @json(__('messages.confirm_delete_booking')),
  failed_delete:    @json(__('messages.failed_to_delete')),
  delete:           @json(__('messages.delete')),
  room:             @json(__('messages.room'))
};

function renderChart(){
  const W=780, H=260, padL=44, padR=10, padT=16, padB=32;
  const plotW=W-padL-padR, plotH=H-padT-padB;
  const allVals = CHART.flatMap(d => [d.occ, d.avail]);
  const maxTotal = Math.max(5, ...allVals) * 1.15;
  const groupW = CHART.length > 0 ? plotW/CHART.length : plotW;
  const barW = 24, gap = 9;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="gOcc" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#e3c357"/><stop offset="100%" stop-color="#c9a227"/></linearGradient>
      <linearGradient id="gAv" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#e8eff8"/><stop offset="100%" stop-color="#dbe6f3"/></linearGradient>
    </defs>`;

  for(let i=0;i<=5;i++){
    const val=(maxTotal/5)*i, y=padT+plotH-(val/maxTotal)*plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#f1f5fb" stroke-width="1"/>`;
    svg += `<text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#b6c2d2">${Math.round(val)}</text>`;
  }
  CHART.forEach((d,i)=>{
    const cx=padL+i*groupW+groupW/2, baseY=padT+plotH;
    const hOcc=(d.occ/maxTotal)*plotH, hAv=(d.avail/maxTotal)*plotH;
    svg += `<rect x="${cx-barW-gap/2}" y="${baseY-hOcc}" width="${barW}" height="${hOcc}" rx="6" fill="url(#gOcc)"/>`;
    svg += `<rect x="${cx+gap/2}" y="${baseY-hAv}" width="${barW}" height="${hAv}" rx="6" fill="url(#gAv)"/>`;
    svg += `<text x="${cx}" y="${H-9}" text-anchor="middle" font-size="11.5" font-weight="700" fill="#9aa8bc">${d.day}</text>`;
  });
  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

function renderCIO(){
  const box = document.getElementById('cioList');
  if(!CIO.length){ box.innerHTML = '<p class="empty">' + T.no_checkins + '</p>'; document.getElementById('cioCount').textContent = '0 ' + T.pending_actions; return; }
  box.innerHTML = CIO.map((c,i)=>{
    const isPending = c.status === 'pending';
    const actionLabel = c.status === 'checked_in' ? T.check_out : (isPending ? T.check_in : T.done);
    const action = c.status === 'checked_in' ? 'check_out' : 'check_in';
    const isDisabled = c.status === 'checked_out';
    const cls = action === 'check_in' ? 'btn-checkin' : 'btn-checkout';
    const inits = c.guest_name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    return `<div class="cio">
      <div class="cio-av">${inits}</div>
      <div class="cio-info"><div class="cio-name">${c.guest_name}</div><div class="cio-meta">${T.room} ${c.room.room_number} • ${new Date(c.status === 'checked_in' ? c.check_out_date : c.check_in_date).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</div></div>
      <button class="cio-btn ${cls}" data-id="${c.id}" data-action="${action}" ${isDisabled?'disabled':''}>${actionLabel}</button>
    </div>`;
  }).join('');
  
  document.querySelectorAll('.cio-btn').forEach(btn=>{
    btn.addEventListener('click', async function(){
      this.disabled = true;
      try {
        const res = await fetch(`/provider/hotel/bookings/${this.dataset.id}/action`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({action:this.dataset.action})
        });
        if(res.ok) window.location.reload();
      } catch(e){ this.disabled=false; }
    });
  });
  document.getElementById('cioCount').textContent = CIO.filter(c=>c.status==='pending'||c.status==='checked_in').length + ' ' + T.pending_actions;
}

let roomFilter = 'all';
function renderRooms(){
  const filtered = roomFilter === 'all' ? ROOMS : ROOMS.filter(r => r.floor == roomFilter);
  document.getElementById('rooms').innerHTML = filtered.map(r=>{
    const type = r.room_type || ROOM_TYPES[+r.room_number[0]] || 'DELUXE';
    return `<div class="room r-${r.status}" data-id="${r.id}" data-status="${r.status}"><div class="r-num">${r.room_number}</div><div class="r-type">${type}</div><div class="r-dot"></div></div>`;
  }).join('');
  
  document.querySelectorAll('.room').forEach(el=>{
    el.addEventListener('click', async function(){
      const id = this.dataset.id;
      const cur = ROOM_STATUSES.indexOf(this.dataset.status);
      const next = ROOM_STATUSES[(cur+1) % ROOM_STATUSES.length];
      try {
        const res = await fetch(`/provider/hotel/rooms/${id}/status`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({status:next})
        });
        if(res.ok) window.location.reload();
      } catch(e){ console.error(e); }
    });
  });
  
  const occ = ROOMS.filter(r=>r.status==='occupied').length;
  const total = ROOMS.length;
  const pct = total > 0 ? Math.round((occ/total)*100) : 0;
  document.getElementById('occVal').innerHTML = `${pct}% <small>${occ} / ${total}</small>`;
  document.getElementById('occBar').style.width = pct + '%';
}

document.querySelectorAll('.rtab').forEach(tab=>{
  tab.addEventListener('click', ()=>{
    document.querySelectorAll('.rtab').forEach(t=>t.classList.remove('on'));
    tab.classList.add('on');
    roomFilter = tab.dataset.f;
    renderRooms();
  });
});

function renderHK(){
  const box = document.getElementById('housekeeping');
  if(!TASKS.length){ box.innerHTML = '<p class="empty">' + T.no_tasks + '</p>'; return; }
  box.innerHTML = TASKS.map(t=>{
    const cls = t.status==='done'?'hk-done':(t.status==='in_progress'?'hk-prog':'hk-queue');
    const label = t.status==='done'? T.done : (t.status==='in_progress'? T.in_progress : T.queue);
    return `<div class="hk">
      <div class="hk-ic">🧹</div>
      <div class="hk-info"><div class="hk-title">${t.task_name}</div><div class="hk-sub">${T.room} ${t.room?.room_number || 'N/A'} • ${t.assigned_to || '—'}</div></div>
      <span class="hk-tag ${cls}">${label}</span>
    </div>`;
  }).join('');
}

function renderBookings(){
  const body = document.getElementById('bookingsBody');
  if(!BOOKINGS.length){ body.innerHTML = '<tr><td colspan="8"><p class="empty">' + T.no_bookings + '</p></td></tr>'; return; }
  body.innerHTML = BOOKINGS.map(b=>{
    const payCls = b.payment_status==='paid'?'p-paid':(b.payment_status==='pending'?'p-pending':'p-refund');
    const inits = b.guest_name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    return `<tr>
      <td><div class="guest"><div class="g-av">${inits}</div><div><div class="g-name">${b.guest_name}</div><div class="g-mail">${b.guest_email || ''}</div></div></div></td>
      <td style="font-weight:800">${b.room?.room_number || 'N/A'}</td>
      <td>${new Date(b.check_in_date).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
      <td>${new Date(b.check_out_date).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
      <td style="font-weight:800">${b.nights}</td>
      <td style="font-weight:800">Rs ${Number(b.amount).toLocaleString()}</td>
      <td><span class="pill ${payCls}">${b.payment_status}</span></td>
      <td><button class="st-btn st-refunded delete-booking" data-id="${b.id}" title="${T.delete}">✕</button></td>
    </tr>`;
  }).join('');
}

renderChart(); renderCIO(); renderRooms(); renderHK(); renderBookings();

/* ========== New Booking Modal ========== */
const bookingModal = document.getElementById('bookingModal');
document.getElementById('newBookingBtn')?.addEventListener('click', () => {
    bookingModal.style.display = 'flex';
});
document.getElementById('closeBookingModal')?.addEventListener('click', () => {
    bookingModal.style.display = 'none';
});
bookingModal?.addEventListener('click', (e) => {
    if (e.target === bookingModal) bookingModal.style.display = 'none';
});

/* ========== Delete Booking (AJAX) ========== */
document.addEventListener('click', async function(e) {
    if (!e.target.classList.contains('delete-booking')) return;
    if (!confirm(T.confirm_delete)) return;

    const id = e.target.dataset.id;
    e.target.disabled = true;
    try {
        const res = await fetch(`/provider/hotel/bookings/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        if (res.ok) window.location.reload();
        else { e.target.disabled = false; alert(T.failed_delete); }
    } catch(err) {
        console.error(err);
        e.target.disabled = false;
    }
});
</script>
@endpush
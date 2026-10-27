@extends('layouts.provider-individual')

@php
    $greetingName = auth()->user()->name ?? __('messages.pro_fallback');
    $upcomingCount = count($assignments->where('dateKey', '>=', now()->format('Y-m-d')));
    $subtitle = __('messages.upcoming_assignments_count', ['name' => $greetingName, 'count' => $upcomingCount]);
@endphp

@section('title', __('messages.individual_dashboard_title'))
@section('page-title', __('messages.dashboard'))
@section('page-subtitle', $subtitle)

@push('ind-head')
<style>
  /* Topbar fix — grid layout to prevent wrap */
  .topbar{display:grid !important;grid-template-columns:minmax(0,1fr) auto !important;align-items:center !important;gap:18px !important;margin-bottom:24px !important}
  .topbar > div:first-child{min-width:0;overflow:hidden}
  .topbar > div:first-child > div{min-width:0;overflow:hidden}
  .topbar h2,.topbar p{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
  .topbar-right{flex-shrink:0;flex-wrap:nowrap}

  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:19px;transition:.25s;position:relative;overflow:hidden}
  .stat:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(13,31,23,.09)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:19px}
  .i-em{background:var(--emerald-soft)}.i-blue{background:var(--blue-soft)}.i-pur{background:var(--purple-soft)}.i-amb{background:var(--amber-soft)}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}.up{background:var(--emerald-soft);color:var(--emerald-d)}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:23px;font-weight:800;letter-spacing:-.8px}.stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .stat-value .star{color:#fbbf24;font-size:18px;margin-left:2px}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:3px}
  .link{font-size:12px;font-weight:800;color:var(--emerald-d);text-decoration:none}
  .chart-total{display:flex;align-items:baseline;gap:9px;margin-top:3px;flex-wrap:wrap}
  .chart-total b{font-size:25px;font-weight:800}.chart-total span{font-size:11.5px;font-weight:800;color:var(--emerald-d);background:var(--emerald-soft);padding:3px 9px;border-radius:20px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:8px}
  .profile-head{display:flex;align-items:center;gap:13px;margin-bottom:16px}
  .profile-av{width:56px;height:56px;border-radius:16px;flex-shrink:0;background:linear-gradient(135deg,#34d399,#059669);display:grid;place-items:center;color:#fff;font-weight:800;font-size:20px}
  .profile-name{font-size:15px;font-weight:800;line-height:1.25}
  .profile-role{font-size:11.5px;color:var(--muted);font-weight:700;margin-top:3px;display:flex;align-items:center;gap:5px}
  .verified{font-size:9px;font-weight:800;padding:2px 6px;border-radius:5px;background:var(--blue-soft);color:#1d4ed8;letter-spacing:.3px}
  .rating-big{text-align:center;padding:14px 0 16px;border-bottom:1px dashed var(--line);margin-bottom:16px}
  .rating-big .rb-num{font-size:36px;font-weight:800;letter-spacing:-1.5px;line-height:1;color:var(--text)}
  .rating-big .rb-num small{font-size:15px;color:var(--muted);font-weight:700}
  .rating-big .rb-stars{color:#fbbf24;font-size:16px;letter-spacing:2px;margin-top:5px;line-height:1}
  .rating-big .rb-meta{font-size:11px;color:var(--muted);font-weight:700;margin-top:6px}
  .profile-stats{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:16px;padding-top:16px;border-top:1px dashed var(--line)}
  .ps-item{background:#f6faf7;border:1px solid var(--line);border-radius:11px;padding:10px 12px}
  .ps-item span{display:block;font-size:9.5px;font-weight:800;color:var(--muted);text-transform:uppercase;margin-bottom:3px}
  .ps-item b{font-size:15px;font-weight:800}
  .cal-nav{display:flex;align-items:center;gap:10px;margin-bottom:14px}
  .cal-nav button{width:32px;height:32px;border-radius:9px;border:1px solid var(--line);background:#fff;cursor:pointer;display:grid;place-items:center;color:var(--muted);transition:.18s}
  .cal-nav button:hover{background:var(--emerald-soft);color:var(--emerald-d);border-color:#bfe6d2}
  .cal-month{font-size:14px;font-weight:800;flex:1}
  .cal-today-btn{border:1px solid var(--line);background:#fff;border-radius:9px;padding:6px 10px;font-family:inherit;font-size:10.5px;font-weight:800;color:var(--muted);cursor:pointer;transition:.18s}
  .cal-days{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:8px}
  .cal-days div{text-align:center;font-size:10px;font-weight:800;color:#a3b8ac;text-transform:uppercase;padding:4px 0}
  .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}
  .cal-day{aspect-ratio:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:10px;cursor:pointer;transition:.15s;font-size:12.5px;font-weight:700;color:var(--text);position:relative}
  .cal-day:hover:not(.empty){background:#f2f9f4}
  .cal-day.empty{pointer-events:none}
  .cal-day.has-bookings{font-weight:800}
  .cal-day.today{background:var(--emerald-soft);color:var(--emerald-d);font-weight:800}
  .cal-day.selected{background:var(--forest);color:#fff}
  .cal-day .cal-dots{display:flex;gap:2px;margin-top:2px;position:absolute;bottom:4px}
  .cal-dots i{width:4px;height:4px;border-radius:50%;display:block}
  .dot-guide{background:#10b981}.dot-porter{background:#f59e0b}.dot-photographer{background:#8b5cf6}
  .cal-legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid var(--line)}
  .cal-legend span{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:var(--muted)}
  .cal-legend i{width:7px;height:7px;border-radius:50%;display:block}
  .upc-list{display:flex;flex-direction:column;gap:9px;max-height:400px;overflow-y:auto;padding-right:4px}
  .assignment{display:flex;align-items:flex-start;gap:11px;padding:11px;border-radius:13px;border:1px solid var(--line);background:#fbfdfc;transition:.2s}
  .assignment:hover{border-color:#bfe6d2;box-shadow:0 6px 18px rgba(16,185,129,.09);background:#fff}
  .asm-ic{width:38px;height:38px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;font-size:17px}
  .ic-guide{background:var(--emerald-soft)}.ic-porter{background:var(--amber-soft)}.ic-photographer{background:var(--purple-soft)}
  .asm-info{flex:1;min-width:0}
  .asm-top{display:flex;align-items:center;gap:6px;margin-bottom:3px}
  .asm-type{font-size:9px;font-weight:800;padding:2px 6px;border-radius:5px;letter-spacing:.4px}
  .t-guide{background:var(--emerald-soft);color:var(--emerald-d)}.t-porter{background:var(--amber-soft);color:#b45309}.t-photographer{background:var(--purple-soft);color:#6d28d9}
  .asm-title{font-size:12.5px;font-weight:800;line-height:1.3;margin-bottom:2px}
  .asm-client{font-size:10.5px;color:var(--muted);font-weight:700}
  .asm-meta{display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap}
  .asm-date{font-size:10px;font-weight:800;color:var(--muted);display:inline-flex;align-items:center;gap:3px}
  .asm-amount{text-align:right;flex-shrink:0}
  .asm-amt{font-size:12.5px;font-weight:800}
  .asm-st{font-size:9px;font-weight:800;padding:2px 6px;border-radius:5px;margin-top:3px;display:inline-block;cursor:pointer;border:none}
  .s-confirm{background:var(--emerald-soft);color:var(--emerald-d)}.s-pend{background:var(--amber-soft);color:#b45309}.s-comp{background:var(--blue-soft);color:#1d4ed8}
  .delete-assignment{background:none;border:none;color:#ef4444;cursor:pointer;font-size:11px;font-weight:800;padding:2px 6px;border-radius:4px;transition:.15s}
  .delete-assignment:hover{background:var(--red-soft)}
  .asm-empty{text-align:center;padding:32px 16px;color:var(--muted);font-size:12.5px;font-weight:700}
  .action-bar{display:flex;justify-content:flex-end;gap:10px;margin-bottom:18px;flex-wrap:wrap}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}}
  @media (max-width:900px){.topbar{grid-template-columns:1fr !important}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.cal-day{font-size:11.5px}.action-bar{justify-content:stretch}.action-bar button{flex:1}}
</style>
@endpush

@section('content')

{{-- 🆕 New Assignment Action Bar --}}
<div class="action-bar">
  <button class="btn-primary" id="newAssignmentBtn" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    <span>{{ __('messages.new_assignment') }}</span>
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">{{ __('messages.kyc_individual_message') }}</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">{{ __('messages.verify_now') }}</a>
    </div>
</div>
@endif

<section class="stats">
  <div class="stat"><div class="stat-top"><div class="stat-icon i-em">💰</div><span class="delta up">▲ {{ __('messages.month_badge') }}</span></div><div class="stat-label">{{ __('messages.total_earnings') }}</div><div class="stat-value"><small>Rs</small> {{ number_format($totalEarnings, 0) }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-blue">📅</div><span class="delta up">▲ {{ __('messages.total_badge') }}</span></div><div class="stat-label">{{ __('messages.bookings_this_month') }}</div><div class="stat-value">{{ $bookingsThisMonth }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-pur">👁️</div><span class="delta up">▲ {{ __('messages.views_badge') }}</span></div><div class="stat-label">{{ __('messages.profile_views') }}</div><div class="stat-value">{{ number_format($profileViews) }}</div></div>
  <div class="stat"><div class="stat-top"><div class="stat-icon i-amb">⭐</div><span class="delta up">▲ {{ __('messages.avg_badge') }}</span></div><div class="stat-label">{{ __('messages.avg_rating') }}</div><div class="stat-value">{{ $avgRating }}<span class="star">★</span></div></div>
</section>

<section class="grid">
  <div class="card span-2">
    <div class="card-head"><div><h3>{{ __('messages.weekly_earnings') }}</h3><div class="chart-total"><b>Rs {{ number_format(collect($chartData)->sum('v'), 0) }}</b><span>{{ __('messages.last_7_days') }}</span></div></div></div>
    <div id="chart"></div>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.profile_stats') }}</h3><div class="sub">{{ __('messages.public_profile_overview') }}</div></div></div>
    <div class="profile-head">
      <div class="profile-av">{{ substr($provider->name ?? 'P', 0, 1) }}</div>
      <div style="min-width:0"><div class="profile-name">{{ $provider->name ?? __('messages.provider_fallback') }}</div><div class="profile-role">{{ __('messages.service_provider') }} <span class="verified">{{ __('messages.verified_badge') }}</span></div></div>
    </div>
    <div class="rating-big">
      <div class="rb-num">{{ $avgRating }}<small>/5</small></div>
      <div class="rb-stars">★★★★★</div>
      <div class="rb-meta">{{ __('messages.based_on_reviews', ['count' => $reviewCount]) }}</div>
    </div>
    <div class="profile-stats">
      <div class="ps-item"><span>{{ __('messages.completed') }}</span><b>{{ $provider->completed_jobs ?? 218 }}</b></div>
      <div class="ps-item"><span>{{ __('messages.repeat_clients') }}</span><b>{{ $provider->repeat_clients_pct ?? 68 }}%</b></div>
    </div>
  </div>
</section>

<section class="grid">
  <div class="card">
    <div class="card-head"><div><h3>{{ __('messages.booking_calendar') }}</h3><div class="sub">{{ __('messages.click_date_filter') }}</div></div></div>
    <div class="cal-nav">
      <button id="calPrev" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" style="width:14px;height:14px;stroke-width:2.5"><path d="m15 18-6-6 6-6"/></svg></button>
      <div class="cal-month" id="calMonth">—</div>
      <button id="calNext" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" style="width:14px;height:14px;stroke-width:2.5"><path d="m9 18 6-6-6-6"/></svg></button>
      <button class="cal-today-btn" id="calTodayBtn" type="button">{{ __('messages.today_btn') }}</button>
    </div>
    <div class="cal-days">
      <div>{{ __('messages.day_sun') }}</div><div>{{ __('messages.day_mon') }}</div><div>{{ __('messages.day_tue') }}</div>
      <div>{{ __('messages.day_wed') }}</div><div>{{ __('messages.day_thu') }}</div><div>{{ __('messages.day_fri') }}</div>
      <div>{{ __('messages.day_sat') }}</div>
    </div>
    <div class="cal-grid" id="calGrid"></div>
    <div class="cal-legend">
      <span><i class="dot-guide"></i>{{ __('messages.guide') }}</span>
      <span><i class="dot-porter"></i>{{ __('messages.porter') }}</span>
      <span><i class="dot-photographer"></i>{{ __('messages.photographer') }}</span>
    </div>
  </div>
  <div class="card span-2">
    <div class="card-head"><div><h3>{{ __('messages.upcoming_assignments') }}</h3><div class="sub" id="upcSub">{{ __('messages.your_next_bookings') }}</div></div></div>
    <div class="upc-list" id="upcList"></div>
  </div>
</section>

{{-- 🆕 New Assignment Modal --}}
<div id="assignmentModal" style="display:none;position:fixed;inset:0;background:rgba(15,36,25,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:500px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="font-size:18px;font-weight:800;color:var(--text)">{{ __('messages.new_assignment') }}</h3>
            <button type="button" id="closeAssignmentModal" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1">&times;</button>
        </div>
        <form action="{{ route('provider.individual.assignments.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.assignment_title') }}</label>
                    <input type="text" name="title" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="EBC Trek — 12 days">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.client_name') }}</label>
                    <input type="text" name="client_name" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="John Doe">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.service_type') }}</label>
                    <select name="service_type" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                        <option value="guide">{{ __('messages.guide') }}</option>
                        <option value="porter">{{ __('messages.porter') }}</option>
                        <option value="photographer">{{ __('messages.photographer') }}</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.duration') }}</label>
                    <input type="text" name="duration" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="10 days">
                </div>
                <div style="grid-column:span 2">
                    <label style="font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase">{{ __('messages.amount') }} (Rs)</label>
                    <input type="number" name="amount" required style="width:100%;padding:10px;margin-top:4px;border:1px solid var(--line);border-radius:10px;font-family:inherit;font-size:13px" placeholder="50000">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center">{{ __('messages.create_assignment') }}</button>
        </form>
    </div>
</div>
@endsection

@push('ind-scripts')
<script>
const ASSIGNMENTS = @json($assignments);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const MONTHS = [
  @json(__('messages.month_january')),
  @json(__('messages.month_february')),
  @json(__('messages.month_march')),
  @json(__('messages.month_april')),
  @json(__('messages.month_may')),
  @json(__('messages.month_june')),
  @json(__('messages.month_july')),
  @json(__('messages.month_august')),
  @json(__('messages.month_september')),
  @json(__('messages.month_october')),
  @json(__('messages.month_november')),
  @json(__('messages.month_december'))
];

/* Multilingual JS strings */
const T = {
  guide_upper:        @json(__('messages.guide_upper')),
  porter_upper:       @json(__('messages.porter_upper')),
  photo_upper:        @json(__('messages.photo_upper')),
  pending_upper:      @json(__('messages.pending_upper')),
  confirmed_upper:    @json(__('messages.confirmed_upper')),
  completed_upper:    @json(__('messages.completed_upper')),
  no_assignments_date: @json(__('messages.no_assignments_date')),
  no_upcoming:        @json(__('messages.no_upcoming_assignments')),
  nothing_scheduled:  @json(__('messages.nothing_scheduled_day')),
  showing_selected:   @json(__('messages.showing_selected_date')),
  your_next:          @json(__('messages.your_next_bookings')),
  pax:                @json(__('messages.pax_label')),
  delete_assignment:  @json(__('messages.delete_assignment')),
  confirm_delete:     @json(__('messages.confirm_delete_assignment'))
};
const TYPE_LABEL = { guide: T.guide_upper, porter: T.porter_upper, photographer: T.photo_upper };
const TYPE_ICON  = { guide: '🥾', porter: '🎒', photographer: '📷' };
const STATUS_LABEL = { pending: T.pending_upper, confirmed: T.confirmed_upper, completed: T.completed_upper };

let calDate = new Date();
let selectedDate = null;

function dateKey(d){ return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

function renderChart(){
  const CHART = @json($chartData);
  const W=780, H=260, padL=48, padR=14, padT=20, padB=34;
  const plotW=W-padL-padR, plotH=H-padT-padB;
  const max = Math.max(1000, ...CHART.map(d=>d.v)) * 1.18;
  const step = CHART.length > 1 ? plotW/(CHART.length-1) : plotW;

  const pts = CHART.map((d,i)=>({ x: padL + i*step, y: padT + plotH - (d.v/max)*plotH, ...d }));
  let line = `M ${pts[0].x},${pts[0].y}`;
  for(let i=1;i<pts.length;i++){ const p0=pts[i-1], p1=pts[i], mx=(p0.x+p1.x)/2; line += ` C ${mx},${p0.y} ${mx},${p1.y} ${p1.x},${p1.y}`; }
  const area = `${line} L ${pts[pts.length-1].x},${padT+plotH} L ${pts[0].x},${padT+plotH} Z`;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="areaE" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity=".28"/><stop offset="100%" stop-color="#10b981" stop-opacity="0"/></linearGradient><linearGradient id="lineE" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#059669"/></linearGradient></defs>`;
  for(let i=0;i<=4;i++){ const val=(max/4)*i, y=padT+plotH-(val/max)*plotH; svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#eef5f1" stroke-width="1"/><text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#b3c4ba">${Math.round(val/1000)}k</text>`; }
  svg += `<path d="${area}" fill="url(#areaE)"/><path d="${line}" fill="none" stroke="url(#lineE)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;
  const peakV = Math.max(...CHART.map(d=>d.v));
  pts.forEach(p=>{ const isPeak = p.v === peakV && peakV > 0; svg += `<circle cx="${p.x}" cy="${p.y}" r="${isPeak?6:4}" fill="#fff" stroke="#10b981" stroke-width="2.6"/><text x="${p.x}" y="${H-10}" text-anchor="middle" font-size="11" font-weight="700" fill="#9cb0a4">${p.day}</text>`; });
  if (peakV > 0) { const peak = pts.find(p=>p.v===peakV); svg += `<g><rect x="${peak.x-36}" y="${peak.y-40}" width="72" height="24" rx="8" fill="#0d1f17"/><text x="${peak.x}" y="${peak.y-24}" text-anchor="middle" font-size="11" font-weight="800" fill="#fff">Rs ${(peak.v/1000).toFixed(1)}k</text></g>`; }
  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

function renderCalendar(){
  const year = calDate.getFullYear(), month = calDate.getMonth();
  document.getElementById('calMonth').textContent = MONTHS[month] + ' ' + year;
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month+1, 0).getDate();
  const todayKey = dateKey(new Date());
  const selectedKey = selectedDate ? dateKey(selectedDate) : null;

  let html = '';
  for(let i=0;i<firstDay;i++) html += `<div class="cal-day empty"></div>`;
  for(let d=1;d<=daysInMonth;d++){
    const key = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const dayBookings = ASSIGNMENTS.filter(a => String(a.dateKey).trim() === String(key).trim());
    const types = [...new Set(dayBookings.map(b => b.type))];
    const cls = ['cal-day'];
    if(key === todayKey) cls.push('today');
    if(key === selectedKey) cls.push('selected');
    if(types.length) cls.push('has-bookings');
    const dots = types.map(t => `<i class="dot-${t}"></i>`).join('');
    html += `<div class="${cls.join(' ')}" data-date="${key}">${d}${types.length?`<div class="cal-dots">${dots}</div>`:''}</div>`;
  }
  document.getElementById('calGrid').innerHTML = html;

  document.querySelectorAll('.cal-day:not(.empty)').forEach(el=>{
    el.addEventListener('click', ()=>{
      const [y,m,d] = el.dataset.date.split('-').map(Number);
      const clicked = new Date(y, m-1, d);
      if(selectedDate && dateKey(selectedDate) === el.dataset.date) selectedDate = null;
      else selectedDate = clicked;
      renderCalendar();
      renderUpcoming();
    });
  });
}

function renderUpcoming(){
  const today = new Date(); today.setHours(0,0,0,0);
  const todayKey = dateKey(today);
  let list = selectedDate ? ASSIGNMENTS.filter(a => a.dateKey === dateKey(selectedDate)) : ASSIGNMENTS.filter(a => a.dateKey >= todayKey);
  list = [...list].sort((a,b)=> a.dateKey.localeCompare(b.dateKey));

  const listEl = document.getElementById('upcList');
  const subEl = document.getElementById('upcSub');

  if(!list.length){
    listEl.innerHTML = `<div class="asm-empty">📭 ${selectedDate ? T.no_assignments_date : T.no_upcoming}</div>`;
    subEl.textContent = selectedDate ? T.nothing_scheduled : T.your_next;
    return;
  }
  subEl.textContent = selectedDate ? T.showing_selected : T.your_next;

  listEl.innerHTML = list.map(a=>{
    const stCls = a.status==='confirmed'?'s-confirm':(a.status==='completed'?'s-comp':'s-pend');
    return `<div class="assignment">
      <div class="asm-ic ic-${a.type}">${TYPE_ICON[a.type]}</div>
      <div class="asm-info">
        <div class="asm-top"><span class="asm-type t-${a.type}">${TYPE_LABEL[a.type]}</span></div>
        <div class="asm-title">${a.title}</div>
        <div class="asm-client">${a.client}</div>
        <div class="asm-meta">
          <span class="asm-date">📅 ${a.dateStr}</span>
          <span class="asm-date">👥 ${a.pax} ${T.pax} · ${a.duration}</span>
        </div>
      </div>
      <div class="asm-amount">
        <div class="asm-amt">Rs ${(a.amount/1000).toFixed(0)}k</div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;margin-top:5px">
          <button class="asm-st ${stCls}" data-id="${a.id}" data-status="${a.status}">${STATUS_LABEL[a.status] || a.status.toUpperCase()}</button>
          <button class="delete-assignment" data-id="${a.id}" title="${T.delete_assignment}">✕</button>
        </div>
      </div>
    </div>`;
  }).join('');

  document.querySelectorAll('.asm-st').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      let cur = this.dataset.status;
      let next = cur === 'pending' ? 'confirmed' : (cur === 'confirmed' ? 'completed' : 'pending');
      this.disabled = true;
      try {
        const res = await fetch(`/provider/individual/bookings/${id}/status`, {
          method:'PATCH', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({status:next})
        });
        if(res.ok) window.location.reload();
      } catch(e){ console.error(e); this.disabled=false; }
    });
  });
}

document.getElementById('calPrev').addEventListener('click', ()=>{ calDate = new Date(calDate.getFullYear(), calDate.getMonth()-1, 1); renderCalendar(); });
document.getElementById('calNext').addEventListener('click', ()=>{ calDate = new Date(calDate.getFullYear(), calDate.getMonth()+1, 1); renderCalendar(); });
document.getElementById('calTodayBtn').addEventListener('click', ()=>{ calDate = new Date(); selectedDate = null; renderCalendar(); renderUpcoming(); });

renderChart(); renderCalendar(); renderUpcoming();

/* ========== New Assignment Modal ========== */
const assignmentModal = document.getElementById('assignmentModal');
document.getElementById('newAssignmentBtn')?.addEventListener('click', () => { assignmentModal.style.display = 'flex'; });
document.getElementById('closeAssignmentModal')?.addEventListener('click', () => { assignmentModal.style.display = 'none'; });
assignmentModal?.addEventListener('click', (e) => { if (e.target === assignmentModal) assignmentModal.style.display = 'none'; });

/* ========== Delete Assignment (AJAX) ========== */
document.addEventListener('click', async function(e) {
  if (!e.target.classList.contains('delete-assignment')) return;
  if (!confirm(T.confirm_delete)) return;
  const id = e.target.dataset.id;
  e.target.disabled = true;
  try {
    const res = await fetch(`/provider/individual/assignments/${id}`, {
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
</script>
@endpush
@extends('layouts.provider-individual')
@section('title', __('messages.earnings_overview'))
@section('page-title', __('messages.earnings_overview'))
@section('page-subtitle', __('messages.earnings_subtitle'))

@push('ind-head')
<style>
  .stat-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:20px}
  .stat-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:24px;position:relative;overflow:hidden;transition:.25s}
  .stat-card:hover{transform:translateY(-3px);box-shadow:0 16px 36px rgba(13,31,23,.09)}
  .stat-card::after{content:'';position:absolute;right:-30px;top:-30px;width:100px;height:100px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,.1),transparent 70%)}
  .stat-card .sc-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:20px;margin-bottom:14px}
  .stat-card h4{font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:800;letter-spacing:.6px}
  .stat-card .val{font-size:32px;font-weight:800;margin-top:8px;color:var(--text);letter-spacing:-1px}

  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px;margin-bottom:16px}
  .card-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px}
  .card-head h3{font-size:15px;font-weight:800}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:3px}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible;margin-top:8px}

  .btn-back{background:var(--emerald);color:#fff;padding:8px 15px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;transition:.18s;flex-shrink:0}
  .btn-back:hover{filter:brightness(1.08)}

  .tbl-wrap{overflow-x:auto;margin:0 -4px}
  .tbl{width:100%;border-collapse:collapse;min-width:500px}
  .tbl th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.7px;color:#a3b8ac;font-weight:800;padding:0 12px 12px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:13px 12px;font-size:12.5px;font-weight:600;border-bottom:1px solid #f3f8f5;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fafdfc}
  .job-icon{width:34px;height:34px;border-radius:10px;display:inline-grid;place-items:center;font-size:15px;background:var(--emerald-soft);margin-right:8px;vertical-align:middle}
  .amt-col{font-weight:800;color:var(--emerald-d)}
  .pill{font-size:10px;font-weight:800;padding:4px 9px;border-radius:20px;display:inline-block}
  .p-confirm{background:var(--emerald-soft);color:var(--emerald-d)}
  .p-comp{background:var(--blue-soft);color:#1d4ed8}
  .empty{text-align:center;padding:44px 20px;font-size:13px;font-weight:600;color:var(--muted)}
  .empty div{font-size:36px;margin-bottom:10px;opacity:.5}
  @media (max-width:640px){.stat-grid{grid-template-columns:1fr}.stat-card .val{font-size:26px}}
</style>
@endpush

@section('content')

<div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
        <h3 style="font-size:16px;font-weight:800">{{ __('messages.earnings_overview') }}</h3>
        <p style="font-size:12px;color:var(--muted);margin-top:3px">{{ __('messages.earnings_subtitle') }}</p>
    </div>
    <a href="{{ route('provider.individual.dashboard') }}" class="btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;stroke-width:2.5"><path d="m15 18-6-6 6-6"/></svg>
        {{ __('messages.back_to_dashboard') }}
    </a>
</div>

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="sc-icon" style="background:var(--emerald-soft);color:var(--emerald-d)">💰</div>
        <h4>{{ __('messages.total_earnings') }}</h4>
        <div class="val" style="color:var(--emerald-d)">Rs {{ number_format($totalEarnings, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="sc-icon" style="background:var(--blue-soft);color:#1d4ed8">✅</div>
        <h4>{{ __('messages.completed_jobs') }}</h4>
        <div class="val">{{ $completedJobs }}</div>
    </div>
</div>

{{-- Chart --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>{{ __('messages.weekly_earnings') }}</h3>
            <div class="sub">{{ __('messages.last_7_days') }} — Rs {{ number_format(collect($chartData)->sum('v'), 0) }}</div>
        </div>
    </div>
    <div id="earningsChart"></div>
</div>

{{-- Recent Earning Jobs --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>{{ __('messages.recent_earnings') }}</h3>
            <div class="sub">{{ __('messages.completed_jobs') }} · {{ $completedJobs }} {{ __('messages.total_badge') }}</div>
        </div>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.assignment_title') }}</th>
                    <th>{{ __('messages.client_name') }}</th>
                    <th>{{ __('messages.start_date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentJobs as $job)
                    @php
                        $icon = $job->service_type === 'guide' ? '🥾' : ($job->service_type === 'porter' ? '🎒' : '📷');
                        $statusCls = $job->status === 'completed' ? 'p-comp' : 'p-confirm';
                    @endphp
                    <tr>
                        <td>
                            <span class="job-icon">{{ $icon }}</span>
                            <strong>{{ $job->title }}</strong>
                        </td>
                        <td>{{ $job->client_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($job->start_date)->format('M d, Y') }}</td>
                        <td class="amt-col">Rs {{ number_format($job->amount, 0) }}</td>
                        <td><span class="pill {{ $statusCls }}">{{ ucfirst($job->status) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty"><div>💰</div>{{ __('messages.no_bookings_yet') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('ind-scripts')
<script>
const EARNINGS = @json($chartData);

function renderEarningsChart(){
  const W = 780, H = 260, padL = 48, padR = 14, padT = 20, padB = 34;
  const plotW = W - padL - padR, plotH = H - padT - padB;
  const max = Math.max(1000, ...EARNINGS.map(d => d.v)) * 1.18;
  const step = EARNINGS.length > 1 ? plotW / (EARNINGS.length - 1) : plotW;

  const pts = EARNINGS.map((d, i) => ({
    x: padL + i * step,
    y: padT + plotH - (d.v / max) * plotH,
    ...d
  }));

  let line = `M ${pts[0].x},${pts[0].y}`;
  for (let i = 1; i < pts.length; i++) {
    const p0 = pts[i-1], p1 = pts[i], mx = (p0.x + p1.x) / 2;
    line += ` C ${mx},${p0.y} ${mx},${p1.y} ${p1.x},${p1.y}`;
  }
  const area = `${line} L ${pts[pts.length-1].x},${padT+plotH} L ${pts[0].x},${padT+plotH} Z`;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="areaE" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#10b981" stop-opacity=".28"/>
        <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
      </linearGradient>
      <linearGradient id="lineE" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#059669"/>
      </linearGradient>
    </defs>`;

  for (let i = 0; i <= 4; i++) {
    const val = (max / 4) * i, y = padT + plotH - (val / max) * plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W - padR}" y2="${y}" stroke="#eef5f1" stroke-width="1"/>`;
    svg += `<text x="${padL - 12}" y="${y + 4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#b3c4ba">${Math.round(val / 1000)}k</text>`;
  }

  svg += `<path d="${area}" fill="url(#areaE)"/>`;
  svg += `<path d="${line}" fill="none" stroke="url(#lineE)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;

  const peakV = Math.max(...EARNINGS.map(d => d.v));
  pts.forEach(p => {
    const isPeak = p.v === peakV && peakV > 0;
    svg += `<circle cx="${p.x}" cy="${p.y}" r="${isPeak ? 6 : 4}" fill="#fff" stroke="#10b981" stroke-width="2.6"/>`;
    svg += `<text x="${p.x}" y="${H - 10}" text-anchor="middle" font-size="11" font-weight="700" fill="#9cb0a4">${p.day}</text>`;
  });

  if (peakV > 0) {
    const peak = pts.find(p => p.v === peakV);
    svg += `<g>
      <rect x="${peak.x - 36}" y="${peak.y - 40}" width="72" height="24" rx="8" fill="#0d1f17"/>
      <text x="${peak.x}" y="${peak.y - 24}" text-anchor="middle" font-size="11" font-weight="800" fill="#fff">Rs ${(peak.v / 1000).toFixed(1)}k</text>
    </g>`;
  }

  svg += `</svg>`;
  document.getElementById('earningsChart').innerHTML = svg;
}

renderEarningsChart();
</script>
@endpush
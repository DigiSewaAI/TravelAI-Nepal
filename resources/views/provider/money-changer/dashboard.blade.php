@extends('layouts.provider-money-changer')

@section('title', 'Money Changer Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Namaste ' . (auth()->user()->name ?? 'Manager') . ' 👋 — Here\'s today\'s overview')

@push('mc-head')
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;transition:.25s}
  .stat:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(15,23,42,.08)}
  .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
  .stat-icon{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;font-size:19px}
  .i-green{background:var(--green-soft);color:var(--green-d)}.i-blue{background:var(--blue-soft);color:var(--blue)}.i-amber{background:var(--amber-soft);color:var(--amber)}.i-purple{background:var(--purple-soft);color:var(--purple)}
  .delta{font-size:11px;font-weight:800;padding:4px 8px;border-radius:20px}
  .up{background:var(--green-soft);color:var(--green-d)}.down{background:var(--red-soft);color:var(--red)}
  .stat-label{font-size:12px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .stat-value{font-size:22px;font-weight:800;letter-spacing:-.7px}
  .stat-value small{font-size:13px;font-weight:700;color:var(--muted)}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;min-width:0}
  .span-2{grid-column:span 2}
  .card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
  .card-head h3{font-size:15px;font-weight:800}
  .card-head .sub{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px}
  .link{font-size:12px;font-weight:700;color:var(--green-d);text-decoration:none}
  .legend{display:flex;gap:14px;align-items:center}
  .legend span{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted)}
  .legend i{width:9px;height:9px;border-radius:3px;display:block}
  .chart-svg{width:100%;height:auto;display:block;overflow:visible}
  .chart-total{display:flex;align-items:baseline;gap:8px;margin-bottom:2px}
  .chart-total b{font-size:24px;font-weight:800}
  .chart-total span{font-size:12px;font-weight:700;color:var(--green-d);background:var(--green-soft);padding:3px 8px;border-radius:20px}
  .conv{display:flex;flex-direction:column;gap:11px}
  .conv-field label{display:block;font-size:11px;font-weight:800;color:var(--muted);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px}
  .conv-input{display:flex;align-items:center;gap:10px;border:1px solid var(--line);border-radius:12px;padding:10px 13px;background:#fbfcfe}
  .conv-input:focus-within{border-color:var(--green);background:#fff;box-shadow:0 0 0 4px rgba(16,185,129,.1)}
  .conv-input select{border:none;background:transparent;font-family:inherit;font-weight:800;font-size:13.5px;color:var(--text);outline:none;cursor:pointer}
  .conv-input input{border:none;background:transparent;outline:none;font-family:inherit;font-size:17px;font-weight:800;color:var(--text);width:100%;text-align:right;min-width:0}
  .conv-input input[readonly]{color:var(--green-d)}
  .swap-btn{width:38px;height:38px;border-radius:50%;align-self:center;background:#fff;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;color:var(--muted);margin:-4px 0;box-shadow:0 3px 10px rgba(15,23,42,.07)}
  .swap-btn:hover{background:var(--green);border-color:var(--green);color:#fff;transform:rotate(180deg);transition:.3s}
  .conv-note{margin-top:12px;padding:11px 13px;border-radius:11px;background:#f8fafc;border:1px dashed #dbe4f0;font-size:12px;font-weight:700;color:var(--muted);text-align:center}
  .conv-note b{color:var(--text)}
  .tbl-wrap{overflow-x:auto}
  .tbl{width:100%;border-collapse:collapse;min-width:320px}
  .tbl th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.7px;color:#94a3b8;font-weight:800;padding:0 10px 11px;border-bottom:1px solid var(--line);white-space:nowrap}
  .tbl td{padding:12px 10px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f7fc;white-space:nowrap}
  .tbl tbody tr:last-child td{border-bottom:none}
  .tbl tbody tr:hover{background:#fafcff}
  .tbl .num{font-weight:800;font-variant-numeric:tabular-nums}
  .buy-col{color:var(--green-d)}.sell-col{color:#e11d48}
  .cur{display:flex;align-items:center;gap:10px}
  .flag{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;font-size:15px;background:#f4f7fc;flex-shrink:0}
  .cur-code{font-weight:800;font-size:12.5px;line-height:1.25}
  .cur-name{font-size:10.5px;color:var(--muted);font-weight:600}
  .pill{font-size:10.5px;font-weight:800;padding:4px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px}
  .pill-buy{background:var(--blue-soft);color:#1d4ed8}
  .pill-sell{background:var(--amber-soft);color:#b45309}
  .pill-ok{background:var(--green-soft);color:var(--green-d)}
  .pill-pend{background:#fef4e2;color:#b45309}
  .empty{font-size:12.5px;color:var(--muted);text-align:center;padding:18px 0;font-weight:600}
  @media (max-width:1250px){.stats{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:1080px){.grid{grid-template-columns:1fr}.span-2{grid-column:span 1}}
  @media (max-width:640px){.stats{grid-template-columns:1fr}.card{padding:16px}.stat-value{font-size:20px}}
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
        <p style="font-size:12.5px;color:#b45309;font-weight:500;line-height:1.5">Your money changer account is not verified yet. Upload your KYC documents to accept exchange transactions.</p>
        <a href="{{ route('provider.verification.index') }}" style="display:inline-block;margin-top:10px;background:#f59e0b;color:#fff;padding:7px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:800">Verify Now →</a>
    </div>
</div>
@endif

{{-- Stats --}}
<section class="stats">
  <div class="stat">
    <div class="stat-top">
      <div class="stat-icon i-green">🔄</div>
      <span class="delta up">▲ Today</span>
    </div>
    <div class="stat-label">Today's Transactions</div>
    <div class="stat-value">{{ $stats['today_tx'] }}</div>
  </div>
  <div class="stat">
    <div class="stat-top">
      <div class="stat-icon i-blue">⬇️</div>
      <span class="delta up">▲ Today</span>
    </div>
    <div class="stat-label">Buy Volume (NPR)</div>
    <div class="stat-value"><small>Rs</small> {{ number_format($stats['buy_volume'], 0) }}</div>
  </div>
  <div class="stat">
    <div class="stat-top">
      <div class="stat-icon i-amber">⬆️</div>
      <span class="delta up">▲ Today</span>
    </div>
    <div class="stat-label">Sell Volume (NPR)</div>
    <div class="stat-value"><small>Rs</small> {{ number_format($stats['sell_volume'], 0) }}</div>
  </div>
  <div class="stat">
    <div class="stat-top">
      <div class="stat-icon i-purple">📈</div>
      <span class="delta up">▲ Today</span>
    </div>
    <div class="stat-label">Net Profit (NPR)</div>
    <div class="stat-value"><small>Rs</small> {{ number_format($stats['net_profit'], 0) }}</div>
  </div>
</section>

{{-- Chart + Converter --}}
<section class="grid">
  <div class="card span-2">
    <div class="card-head">
      <div>
        <h3>Weekly Volume</h3>
        <div class="chart-total">
          <b>Rs {{ number_format($weeklyTotal, 0) }}</b>
          <span>Last 7 days</span>
        </div>
      </div>
      <div class="legend">
        <span><i style="background:#10b981"></i>Buy</span>
        <span><i style="background:#c7d7ea"></i>Sell</span>
      </div>
    </div>
    <div id="chart"></div>
  </div>

  <div class="card">
    <div class="card-head"><div><h3>Currency Converter</h3><div class="sub">Live counter rates</div></div></div>
    <div class="conv">
      <div class="conv-field">
        <label>You Send</label>
        <div class="conv-input">
          <select id="fromCur"></select>
          <input id="amount" type="number" value="100" min="0" step="any">
        </div>
      </div>
      <button class="swap-btn" id="swapBtn" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;stroke-width:2.3"><path d="M7 16V4"/><path d="m3 8 4-4 4 4"/><path d="M17 8v12"/><path d="m21 16-4 4-4-4"/></svg>
      </button>
      <div class="conv-field">
        <label>You Receive</label>
        <div class="conv-input">
          <select id="toCur"></select>
          <input id="result" type="text" readonly value="0.00">
        </div>
      </div>
    </div>
    <div class="conv-note" id="convNote">—</div>
  </div>
</section>

{{-- Rates + Transactions --}}
<section class="grid">
  <div class="card">
    <div class="card-head">
      <div><h3>Today's Rates</h3><div class="sub">NPR per 1 unit</div></div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Currency</th><th>Buy</th><th>Sell</th></tr></thead>
        <tbody id="ratesBody"></tbody>
      </table>
    </div>
  </div>

  <div class="card span-2">
    <div class="card-head">
      <div><h3>Recent Transactions</h3><div class="sub">Latest 6 entries</div></div>
      <a href="#" class="link">View all →</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Customer</th><th>Type</th><th>Amount</th><th>Rate</th><th>Total (NPR)</th><th>Status</th></tr></thead>
        <tbody id="txBody"></tbody>
      </table>
    </div>
  </div>
</section>
@endsection

@push('mc-scripts')
<script>
const RATES = @json($rates);
const RATE_MAP = {};
RATES.forEach(r => RATE_MAP[r.code] = { name: r.name, flag: r.flag, buy: r.buy, sell: r.sell });

const RECENT = @json($recentTransactions);
const WEEKLY = @json($weeklyData);

/* Chart */
function renderChart(){
  const W=760, H=250, padL=44, padR=8, padT=14, padB=30;
  const plotW=W-padL-padR, plotH=H-padT-padB;
  const allVals = WEEKLY.flatMap(d => [d.buy, d.sell]);
  const max = Math.max(1, ...allVals) * 1.15;
  const groupW = WEEKLY.length > 0 ? plotW/WEEKLY.length : plotW;
  const barW = 20, gap = 8;

  let svg = `<svg viewBox="0 0 ${W} ${H}" class="chart-svg" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="gBuy" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#10b981"/></linearGradient>
      <linearGradient id="gSell" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#dbe7f5"/><stop offset="100%" stop-color="#c7d7ea"/></linearGradient>
    </defs>`;

  for(let i=0;i<=4;i++){
    const val=(max/4)*i, y=padT+plotH-(val/max)*plotH;
    svg += `<line x1="${padL}" y1="${y}" x2="${W-padR}" y2="${y}" stroke="#eef2f9" stroke-width="1"/>`;
    svg += `<text x="${padL-12}" y="${y+4}" text-anchor="end" font-size="10.5" font-weight="700" fill="#a8b4c6">${Math.round(val/1000)}k</text>`;
  }

  WEEKLY.forEach((d,i)=>{
    const gx = padL + i*groupW;
    const cx = gx + groupW/2;
    const h1 = (d.buy/max)*plotH;
    const h2 = (d.sell/max)*plotH;
    const x1 = cx - barW - gap/2;
    const x2 = cx + gap/2;
    const baseY = padT + plotH;
    svg += `<rect x="${x1}" y="${baseY-h1}" width="${barW}" height="${h1}" rx="5" fill="url(#gBuy)"/>`;
    svg += `<rect x="${x2}" y="${baseY-h2}" width="${barW}" height="${h2}" rx="5" fill="url(#gSell)"/>`;
    svg += `<text x="${cx}" y="${H-9}" text-anchor="middle" font-size="11.5" font-weight="700" fill="#94a3b8">${d.day}</text>`;
  });

  svg += `</svg>`;
  document.getElementById('chart').innerHTML = svg;
}

/* Rates */
function renderRates(){
  const body = document.getElementById('ratesBody');
  if (!RATES.length){ body.innerHTML='<tr><td colspan="3"><p class="empty">No rates yet</p></td></tr>'; return; }
  body.innerHTML = RATES.map(r => `
    <tr>
      <td>
        <div class="cur">
          <div class="flag">${r.flag || '💱'}</div>
          <div>
            <div class="cur-code">${r.code}</div>
            <div class="cur-name">${r.name || ''}</div>
          </div>
        </div>
      </td>
      <td class="num buy-col">${Number(r.buy).toFixed(2)}</td>
      <td class="num sell-col">${Number(r.sell).toFixed(2)}</td>
    </tr>
  `).join('');
}

/* Transactions */
function renderTx(){
  const body = document.getElementById('txBody');
  if (!RECENT.length){ body.innerHTML='<tr><td colspan="6"><p class="empty">No transactions yet</p></td></tr>'; return; }
  body.innerHTML = RECENT.map(t => `
    <tr>
      <td><div class="cur"><div class="flag" style="font-size:11px;font-weight:800;color:#475569">${t.initials || '??'}</div><div class="cur-code">${t.name}</div></div></td>
      <td><span class="pill ${t.type==='Buy'?'pill-buy':'pill-sell'}">${t.type}</span></td>
      <td class="num">${t.amt} <span style="color:#94a3b8;font-size:11px">${t.cur}</span></td>
      <td class="num" style="color:#64748b">${t.rate}</td>
      <td class="num">Rs ${t.total}</td>
      <td><span class="pill ${t.status==='Completed'?'pill-ok':'pill-pend'}">${t.status}</span></td>
    </tr>
  `).join('');
}

/* Converter */
const fromSel=document.getElementById('fromCur'), toSel=document.getElementById('toCur');
const amountEl=document.getElementById('amount'), resultEl=document.getElementById('result'), noteEl=document.getElementById('convNote');

function buildSelects(){
  const opts = ['NPR', ...Object.keys(RATE_MAP)];
  const html = opts.map(c => {
    const label = c === 'NPR' ? '🇳🇵 NPR' : `${RATE_MAP[c].flag || '💱'} ${c}`;
    return `<option value="${c}">${label}</option>`;
  }).join('');
  fromSel.innerHTML = html; toSel.innerHTML = html;
  fromSel.value = 'USD'; toSel.value = 'NPR';
}

function toNPR(amt, cur){
  if (cur === 'NPR') return amt;
  const r = RATE_MAP[cur]; if (!r) return amt;
  return amt * r.buy;
}
function fromNPR(npr, cur){
  if (cur === 'NPR') return npr;
  const r = RATE_MAP[cur]; if (!r) return npr;
  return npr / r.sell;
}

function convert(){
  const amt = parseFloat(amountEl.value) || 0;
  const from = fromSel.value, to = toSel.value;
  const npr = toNPR(amt, from);
  const out = fromNPR(npr, to);
  resultEl.value = out.toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
  const unitRate = fromNPR(toNPR(1, from), to);
  noteEl.innerHTML = `1 <b>${from}</b> = <b>${unitRate.toLocaleString('en-US',{minimumFractionDigits:2, maximumFractionDigits:4})}</b> ${to}`;
}

amountEl.addEventListener('input', convert);
fromSel.addEventListener('change', convert);
toSel.addEventListener('change', convert);
document.getElementById('swapBtn').addEventListener('click', () => {
  const a = fromSel.value; fromSel.value = toSel.value; toSel.value = a; convert();
});

renderChart();
renderRates();
renderTx();
buildSelects();
convert();
</script>
@endpush
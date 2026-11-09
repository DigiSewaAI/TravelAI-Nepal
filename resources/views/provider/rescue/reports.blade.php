@extends('layouts.provider-rescue')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-sub', 'Performance, revenue & response metrics')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Reports & Analytics</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Performance, revenue & response metrics</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-teal">
        <div class="stat-icon"><i data-lucide="ambulance"></i></div>
        <div class="stat-body"><span class="stat-label">Missions Completed</span><div class="stat-value">0</div><span class="stat-sub">All time</span></div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon"><i data-lucide="timer"></i></div>
        <div class="stat-body"><span class="stat-label">Avg Response</span><div class="stat-value">—</div><span class="stat-sub">Target < 5min</span></div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon"><i data-lucide="wallet"></i></div>
        <div class="stat-body"><span class="stat-label">Total Revenue</span><div class="stat-value">NPR 0</div><span class="stat-sub">Avg NPR 0</span></div>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon"><i data-lucide="award"></i></div>
        <div class="stat-body"><span class="stat-label">Success Rate</span><div class="stat-value">—</div><span class="stat-sub">0 cancellations</span></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title"><i data-lucide="trending-up"></i><h2>Missions per Month</h2></div></div>
        <div class="card-body">
            <svg class="chart-svg" viewBox="0 0 360 190">
                <g stroke="#e5eaf1" stroke-width="1"><line x1="30" y1="30" x2="350" y2="30"/><line x1="30" y1="75" x2="350" y2="75"/><line x1="30" y1="120" x2="350" y2="120"/><line x1="30" y1="170" x2="350" y2="170"/></g>
                <g fill="#0d9488">
                    <rect x="45" y="170" width="26" height="0" rx="6"/><rect x="95" y="170" width="26" height="0" rx="6"/><rect x="145" y="170" width="26" height="0" rx="6"/>
                    <rect x="195" y="170" width="26" height="0" rx="6"/><rect x="245" y="170" width="26" height="0" rx="6"/><rect x="295" y="170" width="26" height="0" rx="6"/>
                </g>
                <g fill="#94a3b8" font-size="9" font-family="Inter" text-anchor="middle">
                    <text x="58" y="185">Sep</text><text x="108" y="185">Oct</text><text x="158" y="185">Nov</text><text x="208" y="185">Dec</text><text x="258" y="185">Jan</text><text x="308" y="185">Feb</text>
                </g>
            </svg>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i data-lucide="activity"></i><h2>Response Time Trend</h2></div></div>
        <div class="card-body">
            <svg class="chart-svg" viewBox="0 0 360 190">
                <g stroke="#e5eaf1" stroke-width="1"><line x1="30" y1="30" x2="350" y2="30"/><line x1="30" y1="75" x2="350" y2="75"/><line x1="30" y1="120" x2="350" y2="120"/><line x1="30" y1="170" x2="350" y2="170"/></g>
                <polyline points="45,170 105,170 165,170 225,170 285,170 335,170" fill="none" stroke="#0d9488" stroke-width="2.5"/>
                <g fill="#94a3b8" font-size="9" font-family="Inter" text-anchor="middle">
                    <text x="45" y="185">Sep</text><text x="105" y="185">Oct</text><text x="165" y="185">Nov</text><text x="225" y="185">Dec</text><text x="285" y="185">Jan</text><text x="335" y="185">Feb</text>
                </g>
            </svg>
        </div>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
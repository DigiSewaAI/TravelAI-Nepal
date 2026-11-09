@extends('layouts.provider-rescue')

@section('title', 'Staff & Crew')
@section('page-title', 'Staff & Crew')
@section('page-sub', 'Pilots, medics & coordinators')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Staff & Crew</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Pilots, medics & coordinators</p>
    </div>
    <button class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Add Staff</button>
</div>

<div class="grid-3">
    <div class="person-card">
        <div class="person-top">
            <div class="person-avatar" style="background:linear-gradient(135deg,#0d9488,#2dd4bf)">RS</div>
            <div>
                <div class="person-name">Ramesh Sherpa</div>
                <div class="person-role">Chief Pilot</div>
            </div>
        </div>
        <div class="person-meta">
            <span><i data-lucide="award"></i> CAAN #PL-4821</span>
            <span><i data-lucide="clock"></i> 4,200 hours</span>
        </div>
    </div>

    <div class="person-card">
        <div class="person-top">
            <div class="person-avatar" style="background:linear-gradient(135deg,#3b82f6,#60a5fa)">SG</div>
            <div>
                <div class="person-name">Suman Gurung</div>
                <div class="person-role">Pilot</div>
            </div>
        </div>
        <div class="person-meta">
            <span><i data-lucide="award"></i> CAAN #PL-5119</span>
            <span><i data-lucide="clock"></i> 2,850 hours</span>
        </div>
    </div>

    <div class="person-card">
        <div class="person-top">
            <div class="person-avatar" style="background:linear-gradient(135deg,#ef4444,#f87171)">MT</div>
            <div>
                <div class="person-name">Dr. Maya Thapa</div>
                <div class="person-role">Lead Medic</div>
            </div>
        </div>
        <div class="person-meta">
            <span><i data-lucide="heart"></i> Emergency medicine</span>
            <span><i data-lucide="award"></i> NMC #MD-8821</span>
        </div>
    </div>

    <div class="person-card">
        <div class="person-top">
            <div class="person-avatar" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)">NK</div>
            <div>
                <div class="person-name">Nabin Karki</div>
                <div class="person-role">Flight Medic</div>
            </div>
        </div>
        <div class="person-meta">
            <span><i data-lucide="heart"></i> Paramedic</span>
            <span><i data-lucide="mountain"></i> High-altitude</span>
        </div>
    </div>

    <div class="person-card">
        <div class="person-top">
            <div class="person-avatar" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)">PK</div>
            <div>
                <div class="person-name">Pooja Khadka</div>
                <div class="person-role">Dispatch Coordinator</div>
            </div>
        </div>
        <div class="person-meta">
            <span><i data-lucide="headset"></i> Ops control</span>
            <span><i data-lucide="radio"></i> Radio certified</span>
        </div>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
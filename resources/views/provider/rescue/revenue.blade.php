@extends('layouts.provider-rescue')

@section('title', 'Mission Revenue')
@section('page-title', 'Mission Revenue')
@section('page-sub', 'Completed missions only · excludes estimates & pending')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <div>
        <h2 style="font-size:19px;font-weight:800;letter-spacing:-.02em">Mission Revenue</h2>
        <p style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Completed missions only · excludes estimates & pending</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-purple">
        <div class="stat-icon"><i data-lucide="banknote"></i></div>
        <div class="stat-body">
            <span class="stat-label">Total Revenue</span>
            <div class="stat-value">NPR 0</div>
            <span class="stat-sub">0 completed missions</span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon"><i data-lucide="trending-up"></i></div>
        <div class="stat-body">
            <span class="stat-label">Avg / Mission</span>
            <div class="stat-value">NPR 0</div>
            <span class="stat-sub">Median NPR 0</span>
        </div>
    </div>
    <div class="stat-card stat-teal">
        <div class="stat-icon"><i data-lucide="shield-check"></i></div>
        <div class="stat-body">
            <span class="stat-label">Insurance Covered</span>
            <div class="stat-value">0%</div>
            <span class="stat-sub">0 / 0 missions</span>
        </div>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon"><i data-lucide="clock"></i></div>
        <div class="stat-body">
            <span class="stat-label">Pending Claims</span>
            <div class="stat-value">NPR 0</div>
            <span class="stat-sub">0 claims in process</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i data-lucide="banknote"></i><h2>Recent Completed Missions</h2></div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Mission</th><th>Traveler</th><th>Completed</th><th>Final Cost</th><th>Insurance</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">
                        <i data-lucide="banknote" style="width:32px;height:32px;margin:0 auto 8px;opacity:.4"></i>
                        <div>No completed missions yet.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('rescue-scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
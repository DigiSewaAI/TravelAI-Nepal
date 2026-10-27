@extends('layouts.provider-restaurant')
@section('title', __('messages.reports_management'))
@section('page-title', __('messages.reports_management'))
@section('page-subtitle', __('messages.manage_reports_desc'))

@push('restaurant-head')
<style>
  .stat-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px;}
  .stat-card{background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px;}
  .stat-card h4{font-size:12px; color:var(--muted); text-transform:uppercase; font-weight:800;}
  .stat-card .val{font-size:28px; font-weight:800; margin-top:10px; color:var(--text);}
</style>
@endpush

@section('content')
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800;">{{ __('messages.reports_management') }}</h3>
    <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="stat-grid">
    <div class="stat-card"><h4>{{ __('messages.total_sales') }}</h4><div class="val" style="color:var(--accent);">Rs {{ number_format($totalSales) }}</div></div>
    <div class="stat-card"><h4>{{ __('messages.total_orders') }}</h4><div class="val">{{ $totalOrders }}</div></div>
    <div class="stat-card"><h4>{{ __('messages.avg_order') }}</h4><div class="val">Rs {{ number_format($avgOrder) }}</div></div>
</div>

<div class="card" style="background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:20px;">
    <h3 style="font-size:16px; font-weight:800; margin-bottom:15px;">{{ __('messages.recent_orders') }}</h3>
    <table style="width:100%; border-collapse:collapse;">
        <thead><tr><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Order #</th><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Amount</th><th style="text-align:left; padding:10px; border-bottom:1px solid var(--line);">Status</th></tr></thead>
        <tbody>
            @forelse($orders as $o)
            <tr><td style="padding:10px; border-bottom:1px solid var(--line);">#{{ $o->order_number }}</td><td style="padding:10px; border-bottom:1px solid var(--line);">Rs {{ number_format($o->total) }}</td><td style="padding:10px; border-bottom:1px solid var(--line);">{{ ucfirst($o->status) }}</td></tr>
            @empty
            <tr><td colspan="3" style="padding:20px; text-align:center; color:var(--muted);">{{ __('messages.no_reports_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
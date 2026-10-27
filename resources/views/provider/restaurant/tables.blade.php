@extends('layouts.provider-restaurant')
@section('title', __('messages.table_management'))
@section('page-title', __('messages.table_management'))
@section('page-subtitle', __('messages.manage_tables_desc'))

@push('restaurant-head')
<style>
  .table-grid{display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:15px;}
  .table-card{background:var(--card); border:1px solid var(--line); border-radius:14px; padding:20px; text-align:center; transition:0.2s;}
  .table-card:hover{transform:translateY(-3px); box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);}
  .t-num{font-size:24px; font-weight:800; color:var(--text); margin-bottom:5px;}
  .t-seats{font-size:12px; color:var(--muted); font-weight:600; margin-bottom:10px;}
</style>
@endpush

@section('content')
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="font-size:16px; font-weight:800;">{{ __('messages.table_management') }}</h3>
    <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
</div>

<div class="table-grid">
    @forelse($tables as $t)
    <div class="table-card">
        <div class="t-num">{{ $t->table_number }}</div>
        <div class="t-seats">{{ $t->seats }} {{ __('messages.seats') }}</div>
        @php
            $c = match($t->status) {
                'available' => 'var(--green-soft); color:#047857',
                'occupied' => 'var(--amber-soft); color:#b45309',
                'reserved' => 'var(--blue-soft); color:#1d4ed8',
                'cleaning' => 'var(--line); color:var(--muted)',
                default => 'var(--line); color:var(--muted)',
            };
        @endphp
        <span class="pill" style="background:{{ $c }};">{{ ucfirst($t->status) }}</span>
    </div>
    @empty
    <p style="color:var(--muted);">{{ __('messages.no_tables_found') }}</p>
    @endforelse
</div>
@endsection
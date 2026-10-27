@extends('layouts.provider-inventory')
@section('title', __('messages.order_history'))
@section('page-title', __('messages.order_history'))
@section('page-subtitle', __('messages.manage_orders_subtitle'))

@push('inv-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto; margin-top:10px;}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff;}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
  .customer-cell{display:flex; align-items:center; gap:10px;}
  .c-avatar{width:32px; height:32px; border-radius:50%; background:var(--indigo-soft); color:var(--indigo-d); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div>
            <h3 style="font-size:16px; font-weight:800;">{{ __('messages.order_history') }}</h3>
            <p style="font-size:12px; color:var(--muted); margin-top:4px;">{{ __('messages.manage_orders_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.inventory.dashboard') }}" class="btn-primary" style="background:var(--indigo); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">Back to Dashboard</a>
    </div>
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.order_id') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><span style="font-weight:800; font-family:monospace; color:var(--indigo-d);">{{ $o->order_number }}</span></td>
                    <td>
                        <div class="customer-cell">
                            @php
                                $name = $o->user->name ?? 'Guest';
                                $inits = strtoupper(substr($name, 0, 1));
                            @endphp
                            <div class="c-avatar">{{ $inits }}</div>
                            <span>{{ $name }}</span>
                        </div>
                    </td>
                    <td style="color:var(--muted);">{{ $o->created_at->format('M d, Y') }}</td>
                    <td style="font-weight:800;">Rs {{ number_format($o->total) }}</td>
                    <td>
                        @if($o->status === 'pending')
                            <span class="pill" style="background:var(--amber-soft); color:#b45309;">{{ __('messages.pending') ?? 'Pending' }}</span>
                        @elseif($o->status === 'completed')
                            <span class="pill" style="background:var(--emerald-soft); color:var(--emerald);">{{ __('messages.completed') ?? 'Completed' }}</span>
                        @else
                            <span class="pill" style="background:var(--indigo-soft); color:var(--indigo-d);">{{ ucfirst($o->status) }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted);">{{ __('messages.no_orders_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
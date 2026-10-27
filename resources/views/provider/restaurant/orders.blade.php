@extends('layouts.provider-restaurant')
@section('title', __('messages.order_history'))
@section('page-title', __('messages.order_history'))
@section('page-subtitle', __('messages.manage_orders_desc'))

@push('restaurant-head')
<style>
  .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;}
  .tbl-wrap{overflow-x:auto;margin-top:10px}
  .tbl{width:100%;border-collapse:collapse;}
  .tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;font-weight:800;padding:0 15px 15px;border-bottom:1px solid var(--line);}
  .tbl td{padding:15px;font-size:13px;font-weight:600;border-bottom:1px solid #f3f5fb;vertical-align:middle;}
  .tbl tbody tr:hover{background:#fafbff}
  .pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;display:inline-block;}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <div><h3 style="font-size:16px; font-weight:800;">{{ __('messages.order_history') }}</h3></div>
        <a href="{{ route('provider.restaurant.dashboard') }}" class="btn-primary" style="background:var(--accent); color:#fff; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:700;">{{ __('messages.back_to_dashboard') }}</a>
    </div>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.order_id') }}</th>
                    <th>{{ __('messages.table_col') }}</th>
                    <th>{{ __('messages.items_col') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><strong style="color:var(--accent-d);">#{{ $o->order_number }}</strong></td>
                    <td>{{ $o->table ? $o->table->table_number : 'Takeaway' }}</td>
                    <td>
                        @foreach($o->items as $item)
                            <div style="font-size:12px;color:var(--muted);">{{ $item->item_name }} ×{{ $item->quantity }}</div>
                        @endforeach
                    </td>
                    <td style="font-weight:800;">Rs {{ number_format($o->total) }}</td>
                    <td>
                        @php
                            $colors = [
                                'pending' => 'var(--amber-soft); color:#b45309',
                                'preparing' => 'var(--blue-soft); color:#1d4ed8',
                                'ready' => 'var(--purple-soft); color:#6d28d9',
                                'served' => 'var(--green-soft); color:#047857',
                            ];
                            $c = $colors[$o->status] ?? 'var(--line); color:var(--muted)';
                        @endphp
                        <span class="pill" style="background:{{ $c }};">{{ ucfirst($o->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">{{ __('messages.no_orders_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
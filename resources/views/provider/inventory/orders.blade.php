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
<div style="background:linear-gradient(135deg,var(--indigo) 0%,var(--indigo-d) 100%);border-radius:var(--radius);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;margin-bottom:20px;box-shadow:0 10px 30px rgba(99,102,241,.25)">
    <div style="min-width:0">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <h2 style="font-size:22px;font-weight:800;letter-spacing:-.5px;margin:0;color:#fff">{{ __('messages.order_history') }}</h2>
            <span style="background:rgba(255,255,255,.22);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase">{{ $orders->count() }} {{ __('orders') }}</span>
        </div>
        <p style="font-size:13px;opacity:.88;margin-top:6px;font-weight:500;margin-bottom:0">{{ __('messages.manage_orders_subtitle') }}</p>
    </div>
    <a href="{{ route('provider.inventory.dashboard') }}" style="background:rgba(255,255,255,.2);color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:800;border:1px solid rgba(255,255,255,.3);display:inline-flex;align-items:center;gap:6px;white-space:nowrap">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        {{ __('Back to Dashboard') }}
    </a>
</div>

<div class="card">
    
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('messages.order_id') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th style="text-align:right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                                @forelse($orders as $o)
                <tr onclick="window.location='{{ route('provider.inventory.orders.show', $o) }}'" style="cursor:pointer" onmouseover="this.style.background='var(--indigo-soft)'" onmouseout="this.style.background=''">
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
                    <td style="text-align:right" onclick="event.stopPropagation()">
                        <a href="{{ route('provider.inventory.orders.show', $o) }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;background:var(--indigo-soft);color:var(--indigo-d);font-size:12px;font-weight:800;text-decoration:none">
                            {{ __('View') }}
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--muted);">{{ __('messages.no_orders_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{-- Stock Health widget sync --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const SH = {{ (int)($stockHealth ?? 0) }};
    const el = document.getElementById('scVal');
    const bar = document.getElementById('scBar');
    if (el) el.innerHTML = SH + '% <small>{{ __('messages.in_stock_label') }}</small>';
    if (bar) bar.style.width = SH + '%';
});
</script>
@endsection
@extends($provider_layout ?? 'layouts.provider-restaurant')
@section('title', $order->order_number)
@section('page-title', $order->order_number)
@section('page-subtitle', $order->created_at->format('M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fef4e2','#b45309'],'preparing'=>['#fef4e2','#b45309'],'served'=>['#e7f8f1','#059669'],'completed'=>['#e7f8f1','#059669'],'cancelled'=>['#fdecec','#dc2626']];
    [$sBg,$sFg] = $sc[$order->status] ?? ['#f3f4f6','#6b7280'];
    $pc = ['paid'=>['#e7f8f1','#059669'],'pending'=>['#fef4e2','#b45309']];
    [$pBg,$pFg] = $pc[$order->payment_status] ?? ['#f3f4f6','#6b7280'];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <a href="{{ route('provider.restaurant.orders.list') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#f97316);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        All Orders
    </a>
    <div style="background:linear-gradient(135deg,var(--accent,#f97316) 0%,var(--accent-d,#ea580c) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800">{{ $order->order_number }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ $order->created_at->format('M d, Y H:i') }}</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $order->status }}</span>
            <span style="padding:7px 14px;border-radius:10px;background:{{ $pBg }};color:{{ $pFg }};font-size:12px;font-weight:800;text-transform:uppercase">Payment: {{ $order->payment_status }}</span>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#efe7de);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#1c1917)">{{ __('messages.customer') }}</h3>
            <div style="font-size:14px;font-weight:700">{{ $order->customer_name ?? 'Walk-in Customer' }}</div>
            @if($order->customer_phone)<div style="font-size:13px;color:var(--muted,#78716c);margin-top:4px">{{ $order->customer_phone }}</div>@endif
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#efe7de);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#1c1917)">{{ __('messages.order_details') }}</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#78716c)">{{ __('messages.subtotal') }}</span><span style="font-weight:700">NPR {{ number_format($order->subtotal, 2) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#78716c)">Tax</span><span style="font-weight:700">NPR {{ number_format($order->tax, 2) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#efe7de)"><span>{{ __('messages.total') }}</span><span style="color:var(--accent,#f97316)">NPR {{ number_format($order->total, 2) }}</span></div>
        </div>
    </div>
    <div style="background:var(--card,#fff);border:1px solid var(--line,#efe7de);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:14px">{{ __('messages.items') }}</h3>
        @foreach($order->items as $item)
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line,#efe7de)">
                <div>
                    <div style="font-weight:700;font-size:13px">{{ $item->item_name }}</div>
                    <div style="font-size:12px;color:var(--muted,#78716c);margin-top:2px">Qty {{ $item->quantity }} × NPR {{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div style="font-weight:800">NPR {{ number_format($item->line_total, 2) }}</div>
            </div>
        @endforeach
    </div>
    @if($order->notes)
    <div style="background:var(--card,#fff);border:1px solid var(--line,#efe7de);border-radius:var(--radius,18px);padding:22px">
        <h3 style="font-size:14px;font-weight:800;margin-bottom:10px">{{ __('messages.notes') }}</h3>
        <p style="font-size:13px;color:var(--text,#1c1917);white-space:pre-line">{{ $order->notes }}</p>
    </div>
    @endif
</div>
@endsection
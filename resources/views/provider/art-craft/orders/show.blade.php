@extends($provider_layout ?? 'layouts.provider-art-craft')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)
@section('page-subtitle', $order->created_at->format('M d, Y H:i'))
@section('content')
@php
    $sc = ['pending'=>['#fbf1dc','#b45309'],'in_progress'=>['#fbf1dc','#b45309'],'completed'=>['#e8f0e2','#4d6b3f'],'cancelled'=>['#fbe3df','#b54434']];
    [$sBg,$sFg] = $sc[$order->status] ?? ['#f3f4f6','#6b7280'];
@endphp
<div style="display:flex;flex-direction:column;gap:18px">
    <a href="{{ route('provider.art-craft.orders') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent,#c65d3b);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        All Orders
    </a>
    <div style="background:linear-gradient(135deg,var(--accent,#c65d3b) 0%,var(--accent-d,#a8492b) 100%);border-radius:var(--radius,18px);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(0,0,0,.15)">
        <div>
            <div style="font-size:22px;font-weight:800">Order #{{ $order->id }}</div>
            <div style="font-size:13px;opacity:.88;margin-top:4px">{{ ucfirst($order->type) }} · {{ $order->created_at->format('M d, Y H:i') }}</div>
        </div>
        <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $order->status }}</span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        <div style="background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#2b1e16)">{{ __('messages.client') }}</h3>
            <div style="font-size:14px;font-weight:700">{{ $order->client_name }}</div>
            @if($order->client_email)<div style="font-size:13px;color:var(--muted,#8a7565);margin-top:4px">{{ $order->client_email }}</div>@endif
        </div>
        <div style="background:var(--card,#fff);border:1px solid var(--line,#ede1d3);border-radius:var(--radius,18px);padding:22px">
            <h3 style="font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text,#2b1e16)">{{ __('messages.details') }}</h3>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#8a7565)">{{ __('messages.type') }}</span><span style="font-weight:700">{{ ucfirst($order->type) }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted,#8a7565)">{{ __('messages.deadline') }}</span><span style="font-weight:700">{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('M d, Y') : 'N/A' }}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;margin-top:10px;padding-top:10px;border-top:1px solid var(--line,#ede1d3)"><span>{{ __('messages.budget') }}</span><span style="color:var(--accent,#c65d3b)">NPR {{ number_format($order->budget, 2) }}</span></div>
        </div>
    </div>
</div>
@endsection
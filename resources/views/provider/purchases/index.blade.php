@extends($provider_layout ?? 'layouts.provider-inventory')

@section('title', __('messages.my_purchases'))
@section('page-title', __('messages.my_purchases'))
@section('page-subtitle', 'Orders you placed as a customer')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px">

  <div style="background:linear-gradient(135deg,var(--indigo) 0%,var(--indigo-d) 100%);border-radius:var(--radius);padding:26px 28px;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;box-shadow:0 10px 30px rgba(99,102,241,.25)">
    <div style="min-width:0">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <h2 style="font-size:22px;font-weight:800;letter-spacing:-.5px;margin:0;color:#fff">{{ __('messages.my_purchases') }}</h2>
        <span style="background:rgba(255,255,255,.22);padding:4px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.4px">{{ $orders->total() }} TOTAL</span>
      </div>
      <p style="font-size:13px;opacity:.88;margin-top:6px;font-weight:500">Orders you placed as a customer</p>
    </div>
  </div>

  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    @if($orders->count() > 0)
      <div style="display:flex;flex-direction:column;gap:10px">
        @foreach($orders as $order)
          @php
            $sc = match($order->status) {
              'pending'   => ['var(--amber-soft)', 'var(--amber)'],
              'confirmed' => ['var(--blue-soft)', 'var(--blue)'],
              'shipped'   => ['var(--purple-soft)', 'var(--purple)'],
              'delivered' => ['var(--green-soft)', 'var(--green)'],
              'returned'  => ['#f3f4f6', '#6b7280'],
              'cancelled' => ['var(--red-soft)', 'var(--red)'],
              default     => ['#f3f4f6', '#6b7280'],
            };
          @endphp
          <a href="{{ route('orders.show', $order) }}" style="display:flex;justify-content:space-between;align-items:center;gap:14px;padding:16px 18px;border:1px solid var(--line);border-radius:12px;text-decoration:none;color:inherit;transition:.2s" onmouseover="this.style.background='var(--indigo-soft)';this.style.borderColor='var(--indigo)'" onmouseout="this.style.background='';this.style.borderColor='var(--line)'">
            <div style="min-width:0">
              <div style="font-weight:800;font-size:14px;color:var(--indigo-d);font-family:monospace">{{ $order->order_number }}</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">{{ $order->created_at->format('M d, Y H:i') }} · {{ $order->items->count() }} {{ __('messages.items_count') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0">
              <span style="padding:6px 12px;border-radius:8px;background:{{ $sc[0] }};color:{{ $sc[1] }};font-size:11px;font-weight:800;text-transform:uppercase">{{ $order->status }}</span>
              <span style="font-weight:800;font-size:14px">Rs {{ number_format($order->total, 2) }}</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
            </div>
          </a>
        @endforeach
      </div>
      <div style="margin-top:20px">{{ $orders->links() }}</div>
    @else
      <div style="text-align:center;padding:60px 20px;color:var(--muted)">
        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.35;margin:0 auto 14px;display:block"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <h3 style="font-size:16px;font-weight:800;color:var(--text);margin:0 0 6px">{{ __('messages.no_orders_found') ?? 'No orders yet' }}</h3>
        <p style="font-size:13px;margin:0 0 20px">You have not placed any orders as a customer yet.</p>
        <div style="display:inline-flex;gap:10px;flex-wrap:wrap;justify-content:center">
          <a href="{{ route('public.rental.index') }}" style="display:inline-flex;align-items:center;gap:7px;padding:11px 20px;border-radius:10px;background:var(--indigo);color:#fff;font-weight:800;font-size:13px;text-decoration:none;box-shadow:0 6px 16px rgba(99,102,241,.3)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
            {{ __('messages.nav_rental') }}
          </a>
          <a href="{{ route('public.shop.index') }}" style="display:inline-flex;align-items:center;gap:7px;padding:11px 20px;border-radius:10px;background:#fff;color:var(--indigo-d);font-weight:800;font-size:13px;text-decoration:none;border:1px solid var(--indigo)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            {{ __('messages.nav_shop') }}
          </a>
          <a href="{{ route('public.wholesale.index') }}" style="display:inline-flex;align-items:center;gap:7px;padding:11px 20px;border-radius:10px;background:#fff;color:var(--indigo-d);font-weight:800;font-size:13px;text-decoration:none;border:1px solid var(--indigo)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            {{ __('messages.nav_wholesale') }}
          </a>
        </div>
      </div>
    @endif
  </div>

</div>
@endsection
@extends('layouts.provider-inventory')

@section('title', $order->order_number)
@section('page-title', __('Order Details'))
@section('page-subtitle', '')

@section('content')
@php
    $statusColors = [
        'pending'    => ['var(--amber-soft)',  'var(--amber)'],
        'confirmed'  => ['var(--blue-soft)',   'var(--blue)'],
        'processing' => ['var(--purple-soft)', 'var(--purple)'],
        'completed'  => ['var(--green-soft)',  'var(--green)'],
        'cancelled'  => ['var(--red-soft)',    'var(--red)'],
    ];
    [$sBg, $sFg] = $statusColors[$order->status] ?? ['var(--indigo-soft)', 'var(--indigo)'];
    $pKey = $order->payment_status === 'paid' ? 'completed' : ($order->payment_status === 'pending' ? 'pending' : 'processing');
    [$pBg, $pFg] = $statusColors[$pKey] ?? ['var(--indigo-soft)', 'var(--indigo)'];
@endphp

<div style="display:flex;flex-direction:column;gap:18px">

  <a href="{{ route('provider.inventory.orders') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--indigo);font-weight:700;text-decoration:none;font-size:13px;width:max-content">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
    {{ __('Back to Orders') }}
  </a>

  {{-- Hero --}}
  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px;display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap">
    <div>
      <div style="font-size:22px;font-weight:800;letter-spacing:-.5px">{{ $order->order_number }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:4px">{{ $order->created_at->format('M d, Y H:i') }}</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <span style="padding:7px 14px;border-radius:10px;background:{{ $sBg }};color:{{ $sFg }};font-size:12px;font-weight:800;text-transform:uppercase">{{ $order->status }}</span>
      <span style="padding:7px 14px;border-radius:10px;background:{{ $pBg }};color:{{ $pFg }};font-size:12px;font-weight:800;text-transform:uppercase">Payment: {{ $order->payment_status }}</span>
    </div>
  </div>

  {{-- Customer + Deposit --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">

    <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
      <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        {{ __('Customer') }}
      </h3>
      <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $order->contact_name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:8px;display:flex;align-items:center;gap:6px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        {{ $order->contact_email }}
      </div>
      @if($order->contact_phone)
        <div style="font-size:13px;color:var(--muted);margin-top:4px;display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          {{ $order->contact_phone }}
        </div>
      @endif
      <div style="font-size:13px;color:var(--muted);margin-top:8px;display:flex;align-items:flex-start;gap:6px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>{{ $order->shipping_address }}, {{ $order->shipping_city }}</span>
      </div>
    </div>

    <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
      <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        {{ __('Deposit') }}
      </h3>
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted)">{{ __('Charged') }}</span><span style="font-weight:700">Rs {{ number_format($order->deposit_total, 2) }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--muted)">{{ __('Held') }}</span><span style="font-weight:700">Rs {{ number_format($order->deposit_total - $order->deposit_refunded_amount, 2) }}</span></div>
      @if($order->deposit_refunded_amount > 0)
        <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--muted)">{{ __('Refunded') }}</span><span style="font-weight:700;color:var(--green)">Rs {{ number_format($order->deposit_refunded_amount, 2) }}</span></div>
      @endif
    </div>
  </div>

  {{-- Items --}}
  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
      {{ __('Items') }}
    </h3>
    @foreach($order->items as $item)
      <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line)">
        <div>
          <div style="font-weight:700;font-size:14px;color:var(--text)">{{ $item->product_name }}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">
            {{ ucfirst($item->product_type) }} · Qty {{ $item->quantity }}
            @if($item->product_type === 'rental' && $item->rental_start_date)
              · {{ \Carbon\Carbon::parse($item->rental_start_date)->format('M d') }} → {{ \Carbon\Carbon::parse($item->rental_end_date)->format('M d, Y') }} ({{ $item->rental_days }} days)
            @endif
          </div>
          <div style="font-size:11px;color:var(--muted);margin-top:4px">
            {{ __('Provider status') }}:
            <strong style="color:{{ $item->provider_status === 'confirmed' ? 'var(--green)' : ($item->provider_status === 'pending' ? 'var(--amber)' : 'var(--muted)') }}">{{ ucfirst($item->provider_status) }}</strong>
          </div>
        </div>
        <div style="font-weight:800;white-space:nowrap">Rs {{ number_format($item->line_total, 2) }}</div>
      </div>
    @endforeach
    <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:15px;font-weight:800">
      <span>{{ __('Total') }}</span><span>Rs {{ number_format($order->total, 2) }}</span>
    </div>
  </div>

  {{-- Timeline --}}
  @if($order->statusHistory && $order->statusHistory->count())
  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:16px;color:var(--text)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      {{ __('Order Timeline') }}
    </h3>
    <div style="display:flex;flex-direction:column">
      @foreach($order->statusHistory as $h)
        @php $hc = $statusColors[$h->to_status] ?? ['var(--indigo-soft)', 'var(--indigo)']; @endphp
        <div style="display:flex;gap:14px;position:relative;padding-bottom:18px">
          <div style="flex-shrink:0;width:14px;height:14px;border-radius:50%;background:{{ $hc[1] }};margin-top:4px;box-shadow:0 0 0 3px {{ $hc[0] }}"></div>
          @if(!$loop->last)
            <div style="position:absolute;left:6px;top:20px;bottom:0;width:2px;background:var(--line)"></div>
          @endif
          <div style="flex:1">
            <div style="font-size:13px;font-weight:700;color:var(--text)">
              {{ ucfirst($h->to_status) }}
              @if($h->from_status && $h->from_status !== $h->to_status)
                <span style="color:var(--muted);font-weight:500">(from {{ $h->from_status }})</span>
              @endif
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">
              {{ $h->created_at?->diffForHumans() ?? '' }} · {{ $h->changed_by_role ?? 'system' }}
              @if($h->note) — {{ $h->note }} @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Return Confirmation (Rental) --}}
  @php
    $pendingReturns = $order->items->filter(fn($i) => $i->isRental() && $i->hasReturnRequest() && !$i->isReturnConfirmed());
  @endphp
  @if($pendingReturns->count() > 0)
  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
      {{ __('Return Confirmation') }}
    </h3>
    @foreach($pendingReturns as $item)
      <div style="padding:14px;border:1px solid var(--line);border-radius:10px;margin-bottom:10px;background:var(--amber-soft)">
        <div style="font-weight:700;font-size:14px;color:var(--text)">{{ $item->product_name }}</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ __('Traveler requested return') }} · {{ $item->return_requested_at?->diffForHumans() }}</div>
      </div>
      <form method="POST" action="{{ route('provider.inventory.orders.items.confirm-return', [$order, $item]) }}" style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
        @csrf @method('PATCH')
        <div>
          <label style="font-size:12px;font-weight:700;color:var(--muted);display:block;margin-bottom:4px">{{ __('Condition') }}</label>
          <select name="return_condition" required style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:10px;font-size:13px;font-weight:700;background:#fff;color:var(--text)">
            <option value="good">{{ __('Good — full refund') }}</option>
            <option value="damaged">{{ __('Damaged — partial refund') }}</option>
            <option value="lost">{{ __('Lost — no refund') }}</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:var(--muted);display:block;margin-bottom:4px">{{ __('Notes (optional)') }}</label>
          <textarea name="return_notes" maxlength="1000" rows="2" style="width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:10px;font-size:13px;background:#fff;color:var(--text);font-family:inherit"></textarea>
        </div>
        <button type="submit" class="btn-primary" style="align-self:flex-start">{{ __('Confirm Return') }}</button>
      </form>
    @endforeach
  </div>
  @endif
  {{-- Actions --}}
  <div style="background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:22px">
    <h3 style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;margin-bottom:14px;color:var(--text)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      {{ __('Actions') }}
    </h3>

    @if($order->items->contains(fn($i) => $i->return_requested_at !== null))
      <div style="background:var(--amber-soft,#fef4e2);border:1px solid var(--amber,#f59e0b);color:#b45309;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:700">
        {{ __('Status locked — return flow in progress') }}
      </div>
    @else
        <form method="POST" action="{{ route('provider.inventory.orders.updateStatus', $order) }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px">
      @csrf @method('PATCH')
      <select name="status" style="padding:10px 14px;border:1px solid var(--line);border-radius:10px;font-size:13px;font-weight:700;background:#fff;color:var(--text)">
        @foreach(['confirmed','shipped','delivered','cancelled'] as $s)
          <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn-primary">{{ __('Update Status') }}</button>
    </form>
    @endif

    @if($order->payment_status === 'pending' && $order->payment_reference)
      <div style="padding-top:14px;border-top:1px solid var(--line)">
        <div style="font-size:13px;color:var(--muted);margin-bottom:10px">
          {{ __('Traveler says paid') }} — {{ __('Ref') }}: <strong style="color:var(--text)">{{ $order->payment_reference }}</strong>
        </div>
        <form method="POST" action="{{ route('provider.inventory.orders.verifyPayment', $order) }}" style="display:flex;gap:10px;align-items:center">
          @csrf @method('PATCH')
          <button type="submit" class="btn-primary">{{ __('Verify Payment') }}</button>
        </form>
      </div>
    @endif
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
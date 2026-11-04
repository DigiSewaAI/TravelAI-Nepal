@php
    $treks = $treks ?? null;
    $allOrders = $allOrders ?? null;
    $shopOrders = $shopOrders ?? null;
    $rentalOrders = $rentalOrders ?? null;
    $rfqs = $rfqs ?? null;
    $counts = $counts ?? [];
@endphp

<div class="tv2-mytrips" style="background: #fff; border-radius: 18px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 22px; font-weight: 800; margin: 0; color: #111827;">
            🧳 {{ __('messages.my_trips') ?? 'My Trips' }}
        </h2>
        <a href="{{ route('traveler.dashboard-v2') }}" style="color: #2563eb; font-weight: 600; font-size: 14px; text-decoration: none;">{{ __('messages.view_all') ?? 'View All' }} →</a>
    </div>

    {{-- Tab counts --}}
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
        <span style="background: #2563eb; color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.tab_all') ?? 'All' }} ({{ $counts['all_orders'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.tab_treks') ?? 'Trekking' }} ({{ $counts['trekking'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_shop') ?? 'Shop' }} ({{ $counts['shop'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_rental') ?? 'Rental' }} ({{ $counts['rental'] ?? 0 }})</span>
        <span style="background: #f3f4f6; color: #374151; padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;">{{ __('messages.nav_wholesale') ?? 'Wholesale' }} ({{ $counts['wholesale'] ?? 0 }})</span>
    </div>

    {{-- Trekking cards --}}
    @if($treks && $treks->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @foreach($treks as $trek)
                <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: #fff;">
                    <div style="font-size: 12px; color: #2563eb; font-weight: 700; margin-bottom: 4px;">🏔️ {{ __('messages.tab_treks') ?? 'Trekking' }}</div>
                    <div style="font-weight: 700; color: #111827; margin-bottom: 6px;">{{ $trek->service->name ?? 'Trek' }}</div>
                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">{{ $trek->created_at->translatedFormat('M d, Y') }}</div>
                    <span style="background: #ecfdf5; color: #059669; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">{{ __('messages.st_confirmed') ?? 'Confirmed' }}</span>
                    <div style="margin-top: 12px;">
                        <a href="{{ route('traveler.bookings.show', $trek) }}" style="color: #2563eb; font-size: 13px; font-weight: 600; text-decoration: none;">{{ __('messages.view') ?? 'View' }} →</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent orders --}}
    @if($allOrders && $allOrders->count() > 0)
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 12px;">{{ __('messages.my_orders') ?? 'My Orders' }}</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($allOrders->take(5) as $order)
                    @php
                        $orderTypes = $order->items->pluck('product_type')->unique();
                        $isMixed = $orderTypes->count() > 1 || $orderTypes->contains(null);
                        $typeLabel = $isMixed ? 'Mixed' : ucfirst($orderTypes->first() ?? 'order');
                    @endphp
                    <a href="{{ route('orders.show', $order) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; text-decoration: none; color: inherit;">
                        <div>
                            <div style="font-weight: 700; color: #111827; font-size: 14px;">{{ $order->order_number ?? 'Order' }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $order->created_at->translatedFormat('M d, Y') }} · {{ $order->items->count() }} {{ __('messages.items_count') ?? 'items' }} · {{ $typeLabel }}</div>
                        </div>
                        <span style="color: #2563eb; font-size: 13px; font-weight: 600;">{{ __('messages.view') ?? 'View' }} →</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if((!$treks || $treks->count() === 0) && (!$allOrders || $allOrders->count() === 0) && (!$rfqs || $rfqs->count() === 0))
        <div style="text-align: center; padding: 40px 20px; color: #6b7280;">
            <div style="font-size: 48px; margin-bottom: 8px;">🧳</div>
            <p>{{ __('messages.no_trips_yet') ?? 'No trips yet. Start exploring Nepal!' }}</p>
        </div>
    @endif
</div>
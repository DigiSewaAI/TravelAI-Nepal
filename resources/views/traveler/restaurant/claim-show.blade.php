@extends('layouts.traveler-v2')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    
    {{-- Back Button --}}
    <a href="{{ route('traveler.dashboard-v2') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 600; text-decoration: none; margin-bottom: 20px; font-size: 14px;">
        ← {{ __('messages.back') ?? 'Back to Dashboard' }}
    </a>

    {{-- Header Card --}}
    <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 18px; padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px;">🍽️ {{ __('messages.restaurant_order') ?? 'Restaurant Order' }}</div>
                <h1 style="font-size: 24px; font-weight: 800; color: #111827; margin: 6px 0;">{{ $order->order_number }}</h1>
                <p style="color: #78350f; font-size: 14px; margin: 0;">
                    {{ $order->created_at ? $order->created_at->translatedFormat('M d, Y · h:i A') : '' }}
                </p>
            </div>
            <span style="background: rgba(255,255,255,0.7); color: #92400e; padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 700; border: 1px solid rgba(146, 64, 14, 0.2);">
                {{ ucfirst($order->status) }}
            </span>
        </div>
    </div>

    {{-- Provider & Service Info --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">📋 {{ __('messages.details') ?? 'Details' }}</h2>
        <div style="display: grid; gap: 12px;">
            @if($order->provider)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.provider') ?? 'Provider' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $order->provider->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($order->service)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.service') ?? 'Service' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $order->service->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($order->table)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.table') ?? 'Table' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $order->table->name ?? ('#' . $order->table_id) }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.customer') ?? 'Customer' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $order->customer_name ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- Items --}}
    @if($order->items && $order->items->count() > 0)
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">🍴 {{ __('messages.items') ?? 'Items' }} ({{ $order->items->count() }})</h2>
        <div style="display: grid; gap: 10px;">
            @foreach($order->items as $item)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f9fafb; border-radius: 10px;">
                <div>
                    <div style="font-weight: 700; color: #111827; font-size: 14px;">{{ $item->name ?? 'Item' }}</div>
                    @if($item->quantity)
                    <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Qty: {{ $item->quantity }}</div>
                    @endif
                </div>
                @if($item->price)
                <span style="font-weight: 700; color: #111827; font-size: 14px;">{{ $order->currency ?? 'NPR' }} {{ number_format($item->price * ($item->quantity ?? 1), 2) }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Payment Summary --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">💰 {{ __('messages.payment') ?? 'Payment' }}</h2>
        <div style="display: grid; gap: 10px;">
            <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                <span style="font-size: 14px; color: #6b7280;">Subtotal</span>
                <span style="font-size: 14px; color: #111827;">{{ $order->currency ?? 'NPR' }} {{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                <span style="font-size: 14px; color: #6b7280;">Tax</span>
                <span style="font-size: 14px; color: #111827;">{{ $order->currency ?? 'NPR' }} {{ number_format($order->tax ?? 0, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #e5e7eb; margin-top: 6px;">
                <span style="font-size: 16px; font-weight: 800; color: #111827;">Total</span>
                <span style="font-size: 18px; font-weight: 800; color: #059669;">{{ $order->currency ?? 'NPR' }} {{ number_format($order->total ?? 0, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0; margin-top: 6px;">
                <span style="font-size: 13px; color: #6b7280;">Payment Status</span>
                <span style="font-size: 13px; font-weight: 700; color: {{ $order->payment_status === 'paid' ? '#059669' : '#d97706' }};">
                    {{ ucfirst($order->payment_status ?? 'pending') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Claim Info --}}
    <div style="background: #ecfdf5; border-radius: 16px; padding: 16px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
        <p style="font-size: 13px; color: #065f46; margin: 0; font-weight: 600;">
            ✅ {{ __('messages.claimed_on') ?? 'Claimed on' }}: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y · h:i A') : 'N/A' }}
        </p>
    </div>

    {{-- Actions --}}
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('traveler.dashboard-v2') }}" style="flex: 1; min-width: 140px; text-align: center; background: #2563eb; color: #fff; padding: 12px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px;">
            {{ __('messages.back_to_dashboard') ?? 'Back to Dashboard' }}
        </a>
    </div>

</div>
@endsection
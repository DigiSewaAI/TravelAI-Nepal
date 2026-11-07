@extends('layouts.traveler-v2')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    
    <a href="{{ route('traveler.dashboard-v2') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 600; text-decoration: none; margin-bottom: 20px; font-size: 14px;">
        ← {{ __('messages.back') ?? 'Back to Dashboard' }}
    </a>

    {{-- Header Card --}}
    <div style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 18px; padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">🚐 {{ __('messages.transport_booking') ?? 'Transport Booking' }}</div>
                <h1 style="font-size: 24px; font-weight: 800; color: #111827; margin: 6px 0;">{{ $booking->booking_ref }}</h1>
                @if($booking->travel_date)
                <p style="color: #1e40af; font-size: 14px; margin: 0;">
                    📅 {{ \Carbon\Carbon::parse($booking->travel_date)->translatedFormat('M d, Y') }}
                </p>
                @endif
            </div>
            <span style="background: rgba(255,255,255,0.7); color: #1e40af; padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 700; border: 1px solid rgba(30, 64, 175, 0.2);">
                {{ ucfirst($booking->status) }}
            </span>
        </div>
    </div>

    {{-- Trip Details --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">🎫 {{ __('messages.trip_details') ?? 'Trip Details' }}</h2>
        <div style="display: grid; gap: 12px;">
            @if($booking->provider)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.provider') ?? 'Provider' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->provider->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->route_name)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.route') ?? 'Route' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->route_name }}</span>
            </div>
            @endif
            @if($booking->vehicle)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.vehicle') ?? 'Vehicle' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->vehicle->name ?? $booking->vehicle->type ?? 'N/A' }}</span>
            </div>
            @endif
            @if($booking->passenger_name)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                <span style="font-size: 13px; color: #6b7280; font-weight: 600;">{{ __('messages.passenger') ?? 'Passenger' }}</span>
                <span style="font-size: 14px; color: #111827; font-weight: 700;">{{ $booking->passenger_name }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment --}}
    <div style="background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 14px;">💰 {{ __('messages.payment') ?? 'Payment' }}</h2>
        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #e5e7eb; margin-top: 6px;">
            <span style="font-size: 16px; font-weight: 800; color: #111827;">{{ __('messages.total') ?? 'Total Amount' }}</span>
            <span style="font-size: 18px; font-weight: 800; color: #059669;">NPR {{ number_format($booking->amount ?? 0, 2) }}</span>
        </div>
    </div>

    {{-- Claim Info --}}
    <div style="background: #ecfdf5; border-radius: 16px; padding: 16px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
        <p style="font-size: 13px; color: #065f46; margin: 0; font-weight: 600;">
            ✅ {{ __('messages.claimed_on') ?? 'Claimed on' }}: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y · h:i A') : 'N/A' }}
        </p>
    </div>

    {{-- Back Button --}}
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('traveler.dashboard-v2') }}" style="flex: 1; min-width: 140px; text-align: center; background: #2563eb; color: #fff; padding: 12px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px;">
            {{ __('messages.back_to_dashboard') ?? 'Back to Dashboard' }}
        </a>
    </div>

</div>
@endsection
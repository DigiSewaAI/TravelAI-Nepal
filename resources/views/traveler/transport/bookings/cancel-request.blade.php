@extends('layouts.traveler-v2')
@section('title', 'Cancel Request — ' . $booking->booking_ref)
@section('content')
<div style="max-width:700px;margin:0 auto;padding:20px">
    <a href="{{ route('traveler.transport.bookings.show', $booking->id) }}" style="color:#0284c7;font-weight:700;font-size:13px;text-decoration:none">← Back to Booking</a>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;margin-top:16px">
        <h1 style="font-size:20px;font-weight:800;margin:0 0 4px">🚫 Request Cancellation</h1>
        <p style="font-size:13px;color:#64748b;margin:0 0 20px">Booking #{{ $booking->booking_ref }} — {{ $booking->route_name }}</p>

        <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:12px;padding:14px 18px;margin-bottom:18px">
            <div style="font-size:11px;font-weight:800;color:#92400e;text-transform:uppercase;margin-bottom:6px">⏰ Cancellation Policy</div>
            <div style="font-size:13px;color:#78350f;white-space:pre-line;line-height:1.7">{{ $policy }}</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
            <div style="background:#f8fafc;padding:14px;border-radius:10px">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:800;margin-bottom:4px">{{ __('messages.cancel_hours_until') }}</div>
                <div style="font-size:20px;font-weight:800;color:#0f172a">
                    @if($hoursUntil >= 1)
                        {{ (int) $hoursUntil }}h {{ (int) (($hoursUntil - (int)$hoursUntil) * 60) }}m
                    @else
                        {{ (int) ($hoursUntil * 60) }}m
                    @endif
                </div>
            </div>
            <div style="background:#f0f9ff;padding:14px;border-radius:10px">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:800;margin-bottom:4px">{{ __('messages.cancel_estimated_refund') }}</div>
                <div style="font-size:20px;font-weight:800;color:#0284c7">Rs {{ number_format($estimatedRefund, 2) }}</div>
            </div>
        </div>

        @if(($booking->payment_status ?? 'pending') !== 'paid')
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:12px;font-size:12px;color:#0369a1;margin-bottom:18px">
                [i] This booking has not been paid yet. No refund will be issued.
            </div>
        @elseif($estimatedRefund == 0)
            <div style="background:#fef4e2;border:1px solid #fbbf24;border-radius:10px;padding:12px;font-size:12px;color:#92400e;margin-bottom:18px">
                ⚠️ Based on the policy, no refund will be issued for this cancellation (departure is less than 12 hours away).
            </div>
        @endif

        <form method="POST" action="{{ route('traveler.transport.bookings.cancel-request.store', $booking->id) }}">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:6px">Reason for cancellation *</label>
                <textarea name="reason" required maxlength="500" rows="4" placeholder="Please tell us why you need to cancel..." style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;font-family:inherit;font-size:13px"></textarea>
            </div>

            <div style="display:flex;gap:10px">
                <a href="{{ route('traveler.transport.bookings.show', $booking->id) }}" style="flex:1;padding:12px;background:#f1f5f9;color:#0f172a;border-radius:10px;font-weight:700;text-align:center;text-decoration:none;font-size:13px">{{ __('messages.cancel_keep_booking') }}</a>
                <button type="submit" onclick="return confirm('{{ __(\'messages.cancel_confirm_prompt\') }}')" style="flex:2;padding:12px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer;font-size:13px">{{ __('messages.cancel_submit') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
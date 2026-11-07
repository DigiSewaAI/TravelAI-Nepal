@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:linear-gradient(135deg,#ede9fe,#ddd6fe); border-radius:18px; padding:24px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#5b21b6; text-transform:uppercase;">🏨 Hotel Booking</div>
                <h1 style="font-size:24px; font-weight:800; color:#111827; margin:6px 0;">{{ $booking->guest_name ?? 'Guest' }}</h1>
                <div style="font-size: 14px; color: #6b7280; margin-top: 6px; font-weight: 600;">📋 ID: <span style="color:#5b21b6; font-weight: 800;">{{ $booking->booking_number ?? ('#' . $booking->id) }}</span></div>
                <div style="font-size:14px; color:#6b7280; margin-top:4px;">ID: <strong style="color:#5b21b6;">{{ $booking->booking_number ?? ('#' . $booking->id) }}</strong></div>
                @if($booking->check_in_date)
                <p style="color:#5b21b6; font-size:14px; margin:0;">📅 {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}{{ $booking->check_out_date ? ' → ' . \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') : '' }}</p>
                @endif
            </div>
            <span style="background:rgba(255,255,255,0.7); color:#5b21b6; padding:6px 16px; border-radius:999px; font-size:13px; font-weight:700;">{{ ucfirst($booking->status) }}</span>
        </div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">🏨 Booking Details</h2>
        @if($booking->provider)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Provider</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->provider->name ?? 'N/A' }}</span></div>@endif
        @if($booking->room)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Room</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->room->name ?? $booking->room_id }}</span></div>@endif
        @if($booking->nights)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Nights</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->nights }}</span></div>@endif
        @if($booking->guest_email)<div style="display:flex; justify-content:space-between; padding:8px 0;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Email</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $booking->guest_email }}</span></div>@endif
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💰 Payment</h2>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:2px solid #e5e7eb;"><span style="font-size:16px; font-weight:800;">Total</span><span style="font-size:18px; font-weight:800; color:#059669;">NPR {{ number_format($booking->amount ?? 0, 2) }}</span></div>
    </div>
    <div style="background:#ecfdf5; border-radius:16px; padding:16px; margin-bottom:20px; border:1px solid #a7f3d0;">
        <p style="font-size:13px; color:#065f46; margin:0; font-weight:600;">✅ Claimed on: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y') : 'N/A' }}</p>
    </div>
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:block; text-align:center; background:#2563eb; color:#fff; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Back to Dashboard</a>
</div>
@endsection
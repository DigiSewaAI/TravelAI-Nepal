@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:linear-gradient(135deg,#fce7f3,#fbcfe8); border-radius:18px; padding:24px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#9f1239; text-transform:uppercase;">🎨 ArtCraft Order</div>
                <h1 style="font-size:24px; font-weight:800; color:#111827; margin:6px 0;">{{ ucfirst($order->type ?? 'ArtCraft') }} · {{ $order->client_name ?? 'Client' }}</h1>
                @if($order->deadline)<p style="color:#9f1239; font-size:14px; margin:0;">📅 Due: {{ \Carbon\Carbon::parse($order->deadline)->format('M d, Y') }}</p>@endif
            </div>
            <span style="background:rgba(255,255,255,0.7); color:#9f1239; padding:6px 16px; border-radius:999px; font-size:13px; font-weight:700;">{{ ucfirst($order->status) }}</span>
        </div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">🎨 Order Details</h2>
        @if($order->client_name)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Client</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $order->client_name }}</span></div>@endif
        @if($order->client_email)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Email</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $order->client_email }}</span></div>@endif
        @if($order->type)<div style="display:flex; justify-content:space-between; padding:8px 0;"><span style="font-size:13px; color:#6b7280; font-weight:600;">Type</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ ucfirst($order->type) }}</span></div>@endif
    </div>
    @if($order->description)
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 10px;">📝 Description</h2>
        <p style="font-size:14px; color:#374151; line-height:1.6; margin:0;">{{ $order->description }}</p>
    </div>
    @endif
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💰 Budget</h2>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:2px solid #e5e7eb;"><span style="font-size:16px; font-weight:800;">Total</span><span style="font-size:18px; font-weight:800; color:#059669;">NPR {{ number_format($order->budget ?? 0, 2) }}</span></div>
    </div>
    <div style="background:#ecfdf5; border-radius:16px; padding:16px; margin-bottom:20px; border:1px solid #a7f3d0;">
        <p style="font-size:13px; color:#065f46; margin:0; font-weight:600;">✅ Claimed on: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y') : 'N/A' }}</p>
    </div>
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:block; text-align:center; background:#2563eb; color:#fff; padding:12px 20px; border-radius:12px; font-weight:700; text-decoration:none;">Back to Dashboard</a>
</div>
@endsection
@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:linear-gradient(135deg,#d1fae5,#a7f3d0); border-radius:18px; padding:24px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; font-weight:700; color:#065f46; text-transform:uppercase;">💱 Money Changer Transaction</div>
                <h1 style="font-size:24px; font-weight:800; color:#111827; margin:6px 0;">{{ $tx->transaction_number }}</h1>
                @if($tx->created_at)<p style="color:#065f46; font-size:14px; margin:0;">📅 {{ $tx->created_at->translatedFormat('M d, Y') }}</p>@endif
            </div>
            <span style="background:rgba(255,255,255,0.7); color:#065f46; padding:6px 16px; border-radius:999px; font-size:13px; font-weight:700;">{{ ucfirst($tx->status) }}</span>
        </div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💱 Transaction Details</h2>
        @if($tx->provider)<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280;">Provider</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $tx->provider->name ?? 'N/A' }}</span></div>@endif
        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280;">Type</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ strtoupper($tx->type) }}</span></div>
        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;"><span style="font-size:13px; color:#6b7280;">Currency</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $tx->currency_code }}</span></div>
        <div style="display:flex; justify-content:space-between; padding:8px 0;"><span style="font-size:13px; color:#6b7280;">Customer</span><span style="font-size:14px; color:#111827; font-weight:700;">{{ $tx->customer_name }}</span></div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:20px; margin-bottom:16px; border:1px solid #e5e7eb;">
        <h2 style="font-size:16px; font-weight:800; color:#111827; margin:0 0 14px;">💰 Amount</h2>
        <div style="display:flex; justify-content:space-between; padding:6px 0;"><span style="font-size:14px; color:#6b7280;">Foreign Amount</span><span style="font-size:14px; font-weight:700;">{{ number_format($tx->amount_foreign, 2) }} {{ $tx->currency_code }}</span></div>
        <div style="display:flex; justify-content:space-between; padding:6px 0;"><span style="font-size:14px; color:#6b7280;">Rate</span><span style="font-size:14px; font-weight:700;">{{ number_format($tx->rate, 4) }}</span></div>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:2px solid #e5e7eb; margin-top:6px;"><span style="font-size:16px; font-weight:800;">NPR Total</span><span style="font-size:18px; font-weight:800; color:#059669;">NPR {{ number_format($tx->npr_amount, 2) }}</span></div>
    </div>
    <div style="background:#ecfdf5; border-radius:16px; padding:16px; margin-bottom:20px; border:1px solid #a7f3d0;">
        <p style="font-size:13px; color:#065f46; margin:0; font-weight:600;">✅ Claimed on: {{ $claim->claimed_at ? $claim->claimed_at->translatedFormat('M d, Y') : 'N/A' }}</p>
    </div>
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:block; text-align:center; background:#2563eb; color:#fff; padding:12px; border-radius:12px; font-weight:700; text-decoration:none;">Back to Dashboard</a>
</div>
@endsection
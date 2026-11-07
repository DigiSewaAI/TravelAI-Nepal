@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 500px; margin: 0 auto; padding: 20px;">
    <a href="{{ route('traveler.dashboard-v2') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back to Dashboard</a>
    <div style="background:#fff; border-radius:18px; padding:24px; border:1px solid #e5e7eb;">
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0 0 8px;">💱 Claim Money Changer Transaction</h1>
        <p style="font-size:14px; color:#6b7280; margin:0 0 20px;">Enter your transaction number and email to claim it.</p>
        @if($errors->any())<div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:16px; font-size:14px;">{{ $errors->first() }}</div>@endif
        <form action="{{ route('traveler.moneychanger.claim.initiate') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">Transaction Number *</label>
                <input type="text" name="transaction_number" required placeholder="TXN-XXXXXX" style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:700; color:#374151; display:block; margin-bottom:6px;">Email Address *</label>
                <input type="email" name="email" required placeholder="your@email.com" style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px;">
            </div>
            <button type="submit" style="width:100%; background:#2563eb; color:#fff; padding:12px; border:0; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer;">Send OTP</button>
        </form>
    </div>
</div>
@endsection
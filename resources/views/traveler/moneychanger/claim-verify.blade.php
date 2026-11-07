@extends('layouts.traveler-v2')
@section('content')
<div style="max-width: 500px; margin: 0 auto; padding: 20px;">
    @if(app()->environment('local') && session('debug_otp'))
    <div style="background:#fef3c7;border:1px solid #fbbf24;padding:12px;border-radius:10px;margin-bottom:16px;font-size:13px;">
        <strong>🔧 DEBUG OTP:</strong> <code style="background:#fff;padding:2px 6px;border-radius:4px;font-weight:bold;font-size:18px;">{{ session('debug_otp') }}</code>
    </div>
    @endif
    <a href="{{ route('traveler.moneychanger.claim.form') }}" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; text-decoration:none; margin-bottom:20px; font-size:14px;">← Back</a>
    <div style="background:#fff; border-radius:18px; padding:24px; border:1px solid #e5e7eb;">
        <h1 style="font-size:22px; font-weight:800; color:#111827; margin:0 0 8px;">🔐 Verify OTP</h1>
        <p style="font-size:14px; color:#6b7280; margin:0 0 20px;">Enter the 6-digit code sent to your email.</p>
        @if(session('info'))<div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:12px; border-radius:10px; margin-bottom:16px; font-size:14px;">{{ session('info') }}</div>@endif
        @if($errors->any())<div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:16px; font-size:14px;">{{ $errors->first() }}</div>@endif
        <form action="{{ route('traveler.moneychanger.claim.verify', $attempt->id) }}" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" required placeholder="000000" style="width:100%; padding:14px; border:1px solid #e5e7eb; border-radius:10px; font-size:24px; text-align:center; letter-spacing:8px; font-weight:700;">
            </div>
            <button type="submit" style="width:100%; background:#2563eb; color:#fff; padding:12px; border:0; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer;">Verify & Claim</button>
        </form>
    </div>
</div>
@endsection
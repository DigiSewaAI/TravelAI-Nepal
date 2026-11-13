@extends('layouts.provider-rescue')

@section('title', ($notification->data['title'] ?? 'Announcement') . ' — Rescue')

@section('content')

@php
    $rv       = 'var(--primary, #dc2626)';
    $rvD      = 'var(--danger, #ef4444)';
    $rvCard   = 'var(--surface-2, #ffffff)';
    $rvLine   = 'var(--border, #e5e7eb)';
    $rvText   = 'var(--text, #111827)';
    $rvMuted  = 'var(--text-muted, #6b7280)';
    $rvSoft   = 'rgba(220, 38, 38, 0.1)';
@endphp

<div style="max-width: 800px; margin: 40px auto; padding: 20px;">
    <div style="background: {{ $rvCard }}; border-radius: 14px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid {{ $rvLine }};">

        <a href="{{ route('provider.rescue.dashboard') }}"
           style="display: inline-block; margin-bottom: 24px; color: {{ $rv }}; text-decoration: none; font-size: 14px; font-weight: 600;">
            &larr; Back to Rescue Dashboard
        </a>

        <div style="display: inline-block; background: {{ $rvSoft }}; color: {{ $rv }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">
            &#128226; {{ ucfirst($notification->data['category'] ?? 'Announcement') }}
        </div>

        <h1 style="font-size: 26px; font-weight: 700; color: {{ $rvText }}; margin: 0 0 12px 0; line-height: 1.3;">
            {{ $notification->data['title'] ?? 'Notification' }}
        </h1>

        <p style="font-size: 13px; color: {{ $rvMuted }}; margin: 0 0 24px 0;">
            {{ $notification->created_at->format('F d, Y \a\t h:i A') }}
            &middot; {{ $notification->created_at->diffForHumans() }}
        </p>

        <hr style="border: none; border-top: 1px solid {{ $rvLine }}; margin: 24px 0;">

        <div style="font-size: 15px; color: {{ $rvText }}; line-height: 1.8; white-space: pre-wrap;">{{ $notification->data['message'] ?? 'No message content.' }}</div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid {{ $rvLine }}; text-align: center;">
            <a href="{{ route('provider.rescue.dashboard') }}"
               style="display: inline-block; background: {{ $rv }}; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px;">
                Go to Rescue Dashboard
            </a>
        </div>

    </div>

    <p style="text-align: center; font-size: 12px; color: {{ $rvMuted }}; margin-top: 24px;">
        TravelAI Nepal &middot; Admin Announcement
    </p>
</div>
@endsection
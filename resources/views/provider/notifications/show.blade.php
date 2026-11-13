@extends($provider_layout ?? 'layouts.provider')

@section('content')

@php
    $cv      = 'var(--accent, #2563eb)';
    $cvSoft  = 'var(--accent-soft, #eff6ff)';
    $cvCard  = 'var(--card, #ffffff)';
    $cvLine  = 'var(--line, #e5e7eb)';
    $cvText  = 'var(--text, #1f2937)';
    $cvMuted = 'var(--muted, #6b7280)';
    $cvR     = 'var(--radius, 12px)';
@endphp

<div style="max-width: 800px; margin: 40px auto; padding: 20px;">
    <div style="background: {{ $cvCard }}; border-radius: {{ $cvR }}; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid {{ $cvLine }};">

        <a href="{{ route('provider.dashboard') }}"
           style="display: inline-block; margin-bottom: 24px; color: {{ $cv }}; text-decoration: none; font-size: 14px; font-weight: 600;">
            &larr; Back to Dashboard
        </a>

        <div style="display: inline-block; background: {{ $cvSoft }}; color: {{ $cv }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">
            &#128226; {{ ucfirst($notification->data['category'] ?? 'Announcement') }}
        </div>

        <h1 style="font-size: 26px; font-weight: 700; color: {{ $cvText }}; margin: 0 0 12px 0; line-height: 1.3;">
            {{ $notification->data['title'] ?? 'Notification' }}
        </h1>

        <p style="font-size: 13px; color: {{ $cvMuted }}; margin: 0 0 24px 0;">
            {{ $notification->created_at->format('F d, Y \a\t h:i A') }}
            &middot; {{ $notification->created_at->diffForHumans() }}
        </p>

        <hr style="border: none; border-top: 1px solid {{ $cvLine }}; margin: 24px 0;">

        <div style="font-size: 15px; color: {{ $cvText }}; line-height: 1.8; white-space: pre-wrap;">{{ $notification->data['message'] ?? 'No message content.' }}</div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid {{ $cvLine }}; text-align: center;">
            <a href="{{ route('provider.dashboard') }}"
               style="display: inline-block; background: {{ $cv }}; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px;">
                Go to Dashboard
            </a>
        </div>

    </div>

    <p style="text-align: center; font-size: 12px; color: {{ $cvMuted }}; margin-top: 24px;">
        TravelAI Nepal &middot; Admin Announcement
    </p>
</div>
@endsection
@extends('layouts.traveler-v2')

@section('title', ($notification->data['title'] ?? 'Notification') . ' | TravelAI Nepal')

@section('traveler-v2-content')
<div style="max-width: 800px; margin: 40px auto; padding: 20px;">
    <div style="background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e5e7eb;">

        {{-- Back button --}}
        <a href="{{ route('traveler.dashboard-v2') }}"
           style="display: inline-block; margin-bottom: 24px; color: #6366f1; text-decoration: none; font-size: 14px; font-weight: 600;">
            ← Back to Dashboard
        </a>

        {{-- Badge --}}
        <div style="display: inline-block; background: #e0e7ff; color: #4f46e5; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">
            📢 {{ ucfirst($notification->data['category'] ?? 'Announcement') }}
        </div>

        {{-- Title --}}
        <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0 0 12px 0; line-height: 1.3;">
            {{ $notification->data['title'] ?? 'Notification' }}
        </h1>

        {{-- Date --}}
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 24px 0;">
            {{ $notification->created_at->format('F d, Y \a\t h:i A') }}
            · {{ $notification->created_at->diffForHumans() }}
        </p>

        {{-- Divider --}}
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

        {{-- Message Body --}}
        <div style="font-size: 15px; color: #374151; line-height: 1.8; white-space: pre-wrap;">{{ $notification->data['message'] ?? 'No message content.' }}</div>

        {{-- Footer --}}
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center;">
            <a href="{{ route('traveler.dashboard-v2') }}"
               style="display: inline-block; background: #6366f1; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 2px 8px rgba(99,102,241,0.3);">
                Go to Dashboard
            </a>
        </div>

    </div>

    {{-- Footer note --}}
    <p style="text-align: center; font-size: 12px; color: #9ca3af; margin-top: 24px;">
        TravelAI Nepal · Admin Announcement
    </p>
</div>
@endsection
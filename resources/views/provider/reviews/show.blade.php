@extends($provider_layout)

@section('content')

@php
    $cv      = 'var(--accent, #2563eb)';
    $cvSoft  = 'var(--accent-soft, #eff6ff)';
    $cvCard  = 'var(--card, #ffffff)';
    $cvLine  = 'var(--line, #e5e7eb)';
    $cvText  = 'var(--text, #1f2937)';
    $cvMuted = 'var(--muted, #6b7280)';
    $cvR     = 'var(--radius, 12px)';
    $cvGrS   = 'var(--green-soft, #d1fae5)';
    $cvGr    = 'var(--green, #065f46)';
    $cvRdS   = 'var(--red-soft, #fee2e2)';
    $cvRd    = 'var(--red, #991b1b)';
@endphp

<div style="padding: 24px 20px; max-width: 900px; margin: 0 auto;">

    <div style="margin-bottom:20px;">
        <a href="{{ route('provider.reviews.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:{{ $cv }};font-size:13px;font-weight:700;text-decoration:none;">&larr; Back to Reviews</a>
    </div>

    @if(session('success'))
        <div style="background:{{ $cvGrS }};color:{{ $cvGr }};border-left:4px solid {{ $cvGr }};border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="background:{{ $cvRdS }};color:{{ $cvRd }};border-left:4px solid {{ $cvRd }};border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;">
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:28px;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px;flex-wrap:wrap;">
            <div style="flex:1;min-width:240px;">
                <h1 style="font-size:20px;font-weight:800;color:{{ $cvText }};margin:0 0 8px;line-height:1.3;">{{ $review->title ?? 'Review' }}</h1>
                <p style="font-size:13px;color:{{ $cvMuted }};margin:0;">
                    <span style="font-weight:700;color:{{ $cvText }};">{{ $review->user->name ?? 'Traveler' }}</span>
                    <span style="margin:0 6px;">&middot;</span>
                    <span>{{ $review->service->name ?? '-' }}</span>
                    <span style="margin:0 6px;">&middot;</span>
                    <span>{{ $review->created_at->format('M d, Y') }}</span>
                </p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;">
                <div style="color:{{ $cv }};font-size:22px;letter-spacing:2px;line-height:1;">
                    @for($i = 0; $i < $review->rating; $i++)&#9733;@endfor
                    @for($i = $review->rating; $i < 5; $i++)<span style="color:{{ $cvLine }};">&#9733;</span>@endfor
                </div>
                <div style="font-size:11px;color:{{ $cvMuted }};font-weight:700;margin-top:4px;">{{ $review->rating }}.0 / 5.0</div>
            </div>
        </div>

        @if($review->comment)
            <div style="background:{{ $cvSoft }};border-left:3px solid {{ $cv }};border-radius:6px;padding:14px 16px;margin-bottom:16px;">
                <p style="font-size:14px;color:{{ $cvText }};line-height:1.6;margin:0;">{{ $review->comment }}</p>
            </div>
        @endif

        @if($review->verified_purchase)
            <span style="display:inline-flex;align-items:center;gap:6px;background:{{ $cvGrS }};color:{{ $cvGr }};padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;">&#10003; Verified Purchase</span>
        @endif
    </div>

    @if($review->provider_response)
        <div style="background:{{ $cvSoft }};border:1px solid {{ $cv }};border-radius:{{ $cvR }};padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cv }};"></span>
                <h3 style="font-size:15px;font-weight:800;color:{{ $cv }};margin:0;letter-spacing:0.3px;">Your Response</h3>
            </div>
            <p style="font-size:14px;color:{{ $cvText }};line-height:1.6;margin:0 0 10px;">{{ $review->provider_response }}</p>
            <p style="font-size:11px;color:{{ $cvMuted }};margin:0;font-weight:600;">Responded {{ $review->provider_responded_at?->format('M d, Y H:i') }}</p>
        </div>
    @else
        <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};padding:28px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <h3 style="font-size:16px;font-weight:800;color:{{ $cvText }};margin:0 0 8px;">Respond to this Review</h3>
            <p style="font-size:12px;color:{{ $cvMuted }};margin:0 0 18px;line-height:1.5;">
                <strong>One-time public response.</strong> Your reply will be visible publicly below the traveler's review.
            </p>
            <form action="{{ route('provider.reviews.respond', $review) }}" method="POST">
                @csrf
                <textarea name="response" rows="5" required maxlength="2000"
                    placeholder="Thank you for your feedback..."
                    style="width:100%;padding:14px;border:1px solid {{ $cvLine }};border-radius:10px;font-family:inherit;font-size:14px;color:{{ $cvText }};resize:vertical;box-sizing:border-box;outline:none;">{{ old('response') }}</textarea>
                <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                    <button type="submit"
                        style="padding:11px 24px;background:{{ $cv }};color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;letter-spacing:0.3px;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                        Submit Response
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
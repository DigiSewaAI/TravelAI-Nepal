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
    $cvAmS   = 'var(--amber-soft, #fef3c7)';
    $cvAm    = 'var(--amber, #92400e)';
@endphp

<div style="padding: 24px 20px; max-width: 1280px; margin: 0 auto;">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
        <div>
            <h1 style="font-size:26px;font-weight:800;color:{{ $cvText }};margin:0 0 4px;">Reviews</h1>
            <p style="font-size:13px;color:{{ $cvMuted }};margin:0;">Approved customer reviews for your services</p>
        </div>
        <div style="display:flex;align-items:stretch;background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="padding:14px 20px;text-align:center;border-right:1px solid {{ $cvLine }};">
                <div style="font-size:22px;font-weight:800;color:{{ $cvText }};line-height:1;">{{ $provider->reviews_count ?? 0 }}</div>
                <div style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;margin-top:4px;font-weight:600;">Total</div>
            </div>
            <div style="padding:14px 20px;text-align:center;">
                <div style="font-size:22px;font-weight:800;color:{{ $cv }};line-height:1;">&#9733; {{ number_format($provider->average_rating ?? 0, 2) }}</div>
                <div style="font-size:11px;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.6px;margin-top:4px;font-weight:600;">Average</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="background:{{ $cvGrS }};color:{{ $cvGr }};border-left:4px solid {{ $cvGr }};border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    @if($reviews->isEmpty())
        <div style="background:{{ $cvCard }};border:1px dashed {{ $cvLine }};border-radius:{{ $cvR }};padding:60px 20px;text-align:center;">
            <div style="font-size:52px;color:{{ $cv }};opacity:0.25;line-height:1;margin-bottom:16px;">&#9733;</div>
            <h3 style="font-size:18px;font-weight:700;color:{{ $cvText }};margin:0 0 8px;">No Reviews Yet</h3>
            <p style="font-size:13px;color:{{ $cvMuted }};margin:0;">Approved reviews from your customers will appear here.</p>
        </div>
    @else
        <div style="background:{{ $cvCard }};border:1px solid {{ $cvLine }};border-radius:{{ $cvR }};overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:900px;">
                    <thead>
                        <tr style="background:{{ $cvSoft }};border-bottom:2px solid {{ $cvLine }};">
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Traveler</th>
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Service</th>
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Rating</th>
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Title</th>
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Response</th>
                            <th style="text-align:left;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Date</th>
                            <th style="text-align:right;padding:14px 18px;font-size:11px;font-weight:800;color:{{ $cvMuted }};text-transform:uppercase;letter-spacing:0.7px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr style="border-bottom:1px solid {{ $cvLine }};">
                                <td style="padding:16px 18px;font-size:13px;color:{{ $cvText }};font-weight:600;">{{ $review->user->name ?? 'Unknown' }}</td>
                                <td style="padding:16px 18px;font-size:13px;color:{{ $cvMuted }};">{{ $review->service->name ?? '-' }}</td>
                                <td style="padding:16px 18px;">
                                    <span style="color:{{ $cv }};font-size:14px;letter-spacing:1px;">
                                        @for($i = 0; $i < $review->rating; $i++)&#9733;@endfor
                                    </span>
                                    <span style="color:{{ $cvMuted }};font-size:11px;margin-left:6px;font-weight:600;">{{ $review->rating }}.0</span>
                                </td>
                                <td style="padding:16px 18px;font-size:13px;color:{{ $cvText }};">{{ \Illuminate\Support\Str::limit($review->title, 40) ?? '-' }}</td>
                                <td style="padding:16px 18px;">
                                    @if($review->provider_response)
                                        <span style="display:inline-block;background:{{ $cvGrS }};color:{{ $cvGr }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">Replied</span>
                                    @else
                                        <span style="display:inline-block;background:{{ $cvAmS }};color:{{ $cvAm }};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">Pending</span>
                                    @endif
                                </td>
                                <td style="padding:16px 18px;font-size:12px;color:{{ $cvMuted }};">{{ $review->created_at->format('M d, Y') }}</td>
                                <td style="padding:16px 18px;text-align:right;">
                                    <a href="{{ route('provider.reviews.show', $review) }}" style="display:inline-block;padding:8px 16px;background:{{ $cv }};color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($reviews->hasPages())
            <div style="margin-top:24px;">{{ $reviews->links() }}</div>
        @endif
    @endif
</div>
@endsection
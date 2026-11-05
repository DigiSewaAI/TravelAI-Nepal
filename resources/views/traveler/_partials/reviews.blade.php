<div style="background: #fff; border-radius: 18px; padding: 20px; border: 1px solid #e5e7eb;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <span style="font-size: 20px;">⭐</span>
        <h3 style="font-size: 15px; font-weight: 800; color: #111827; margin: 0;">{{ __('messages.my_reviews') ?? 'My Reviews' }}</h3>
    </div>
    @if(($reviewsCount ?? 0) > 0)
        <p style="font-size: 13px; color: #374151;">{{ $reviewsCount }} reviews</p>
    @else
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px;">{{ __('messages.no_reviews_yet') ?? 'No reviews yet' }}</p>
        <a href="{{ route('traveler.reviews.index') }}" style="display: block; text-align: center; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; color: #2563eb; font-weight: 600; font-size: 13px; text-decoration: none;">
            {{ __('messages.share_experience') ?? 'Share Your Experience' }}
        </a>
    @endif
</div>
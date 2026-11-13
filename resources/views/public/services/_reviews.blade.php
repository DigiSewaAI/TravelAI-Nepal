{{-- PROVIDER-ITINERARY-09A: Public Reviews List --}}
@if($reviews->count() > 0)
<section class="mt-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">{{ __('messages.reviews') ?? 'Reviews' }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $service->ratingsCount() }} {{ $service->ratingsCount() === 1 ? 'review' : 'reviews' }}
            </p>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($reviews as $review)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-yellow-400 {{ $i <= $review->rating ? 'fas fa-star' : 'far fa-star' }}"></span>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-800 text-sm">
                            {{ $review->user->display_name ?? __('messages.traveler') ?? 'Traveler' }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap">
                        {{ $review->created_at->format('M Y') }}
                    </span>
                </div>

                @if($review->comment)
                    <p class="text-sm text-gray-600 leading-relaxed mt-2">{{ $review->comment }}</p>
                @endif

                @if($review->verified_purchase)
                    <span class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                        <i class="fas fa-check-circle"></i> Verified Purchase
                    </span>
                @endif

                @if($review->provider_response)
                    <div class="mt-4 pl-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-lg p-3">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <i class="fas fa-reply text-blue-600 text-xs"></i>
                            <span class="text-xs font-bold text-blue-700">
                                {{ $review->provider->name ?? 'Provider' }} responded
                            </span>
                            @if($review->provider_responded_at)
                                <span class="text-xs text-gray-500">· {{ $review->provider_responded_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed mt-1">{{ $review->provider_response }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if($reviews->hasPages())
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @endif
</section>
@endif
@extends('layouts.public')

@section('title', __('messages.traveler_my_reviews'))

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('traveler.dashboard') }}" class="hover:text-blue-600">{{ __('messages.traveler_dashboard') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('messages.traveler_my_reviews') }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-star text-amber-500"></i>
                {{ __('messages.traveler_my_reviews') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('messages.traveler_reviews_showing', ['count' => $reviews->total()]) }}
            </p>
        </div>
    </div>

    {{-- Reviews List --}}
    @if($reviews->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <i class="fas fa-star text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800">{{ __('messages.traveler_no_reviews_yet') }}</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">{{ __('messages.traveler_reviews_empty_desc') }}</p>
            <a href="{{ route('public.services.index') }}"
               class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 hover:brightness-110 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                {{ __('messages.traveler_explore_services') }} →
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="bg-white rounded-xl shadow-sm border border-l-4
                    @if($review->status === 'approved') border-l-emerald-500
                    @elseif($review->status === 'pending') border-l-yellow-400
                    @else border-l-red-400 @endif
                    p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">

                    {{-- Status Badge --}}
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($review->status === 'approved') bg-emerald-100 text-emerald-800
                            @elseif($review->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            @if($review->status === 'approved') {{ __('messages.traveler_reviews_status_approved') }}
                            @elseif($review->status === 'pending') {{ __('messages.traveler_reviews_status_pending') }}
                            @else {{ __('messages.traveler_reviews_status_rejected') }} @endif
                        </span>
                    </div>

                    {{-- Rating + Service --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <h3 class="font-semibold text-gray-900 text-base truncate">
                            {{ $review->service->name ?? __('messages.na') }}
                        </h3>
                        <span class="text-amber-500 text-lg">{{ str_repeat('⭐', $review->rating) }}</span>
                    </div>

                    {{-- Comment --}}
                    @if($review->comment)
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">{{ $review->comment }}</p>
                    @else
                        <p class="text-xs text-gray-400 italic mt-2">{{ __('messages.traveler_no_comment') }}</p>
                    @endif

                    {{-- Footer: Date + View Booking --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 mt-4 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <i class="far fa-clock"></i>
                            {{ __('messages.traveler_reviews_reviewed_on') }}
                            {{ $review->created_at->diffForHumans() }}
                        </p>
                        @if($review->booking)
                            <a href="{{ route('traveler.bookings.show', $review->booking_id) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 flex items-center gap-1">
                                {{ __('messages.traveler_reviews_view_booking') }}
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @endif

    {{-- Back to Dashboard --}}
    <div class="mt-8 text-center">
        <a href="{{ route('traveler.dashboard') }}"
           class="inline-block text-sm text-gray-600 hover:text-blue-600 font-medium transition-colors duration-200">
            ← {{ __('messages.traveler_back_dashboard') }}
        </a>
    </div>
</div>
@endsection
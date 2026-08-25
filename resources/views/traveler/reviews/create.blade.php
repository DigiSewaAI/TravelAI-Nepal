@extends('layouts.public')

@section('title', __('messages.traveler_review_create_title'))
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md border p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.traveler_review_create_heading') }}</h1>
        <p class="text-gray-600 mb-4">{{ __('messages.traveler_review_create_booking') }} <span class="font-medium">{{ $booking->service->name ?? __('messages.na') }}</span></p>

        <form method="POST" action="{{ route('traveler.reviews.store', $booking) }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.traveler_review_rating_label') }} *</label>
                <div class="flex gap-2" id="rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-value="{{ $i }}" class="text-3xl focus:outline-none hover:scale-110 transition star-btn">☆</button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-input" required>
                <p class="text-sm text-gray-500 mt-1" id="rating-label">{{ __('messages.traveler_review_click_to_rate') }}</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.traveler_review_comment_label') }} ({{ __('messages.optional') }})</label>
                <textarea name="comment" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="{{ __('messages.traveler_review_comment_placeholder') }}"></textarea>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                {{ __('messages.traveler_review_submit') }}
            </button>
            <a href="{{ route('traveler.dashboard') }}" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>

<script>
    const stars = document.querySelectorAll('#rating-stars button');
    const ratingInput = document.getElementById('rating-input');
    const ratingLabel = document.getElementById('rating-label');
    let selected = 0;

    stars.forEach(btn => {
        btn.addEventListener('click', function() {
            selected = parseInt(this.dataset.value);
            ratingInput.value = selected;
            stars.forEach((b, i) => {
                b.textContent = i < selected ? '★' : '☆';
                b.style.color = i < selected ? '#f59e0b' : '#d1d5db';
            });
            ratingLabel.textContent = '{{ __('messages.traveler_review_rating_selected') }}' + selected + ' / 5';
        });
    });
</script>
@endsection
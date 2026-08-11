@extends('layouts.public')

@section('title', 'My Dashboard | TravelAI Nepal')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Dashboard</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Bookings -->
        <div class="bg-white rounded-xl shadow-md border p-6">
            <h2 class="text-xl font-semibold mb-4">My Bookings</h2>
            @if($bookings->count() > 0)
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        <div class="border-b pb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">{{ $booking->service->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">Start: {{ $booking->start_date->format('Y-m-d') }}</p>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                <div>
                                    @if($booking->status === 'completed' && !$booking->review)
                                        <a href="{{ route('traveler.reviews.create', $booking) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-star"></i> Review
                                        </a>
                                    @endif
                                    @if($booking->review)
                                        <span class="text-sm text-green-600">✅ Reviewed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $bookings->links() }}
            @else
                <p class="text-gray-500">No bookings yet.</p>
            @endif
        </div>

        <!-- Reviews -->
        <div class="bg-white rounded-xl shadow-md border p-6">
            <h2 class="text-xl font-semibold mb-4">My Reviews</h2>
            @if($reviews->count() > 0)
                <div class="space-y-3">
                    @foreach($reviews as $review)
                        <div class="border-b pb-2">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ $review->service->name ?? 'N/A' }}</span>
                                <span class="text-yellow-500">{{ str_repeat('⭐', $review->rating) }}</span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $review->comment ?: 'No comment' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No reviews yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
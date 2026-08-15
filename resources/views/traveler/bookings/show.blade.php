@extends('layouts.public')

@section('title', 'Booking #' . $booking->id . ' | TravelAI Nepal')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('traveler.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
        <span class="mx-2">/</span>
        <span>Booking #{{ $booking->id }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-md border p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Booking #{{ $booking->id }}</h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $booking->service->name ?? 'N/A' }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">Start Date</p>
                <p class="font-medium">{{ $booking->start_date ? $booking->start_date->format('M d, Y') : 'TBD' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Booking Date</p>
                <p class="font-medium">{{ $booking->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">Service Provider</p>
                <p class="font-medium">{{ $booking->service->provider->name ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- Review Section --}}
        @if($booking->review)
            <div class="mt-6 border-t pt-4">
                <h3 class="font-semibold text-gray-700">Your Review</h3>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-yellow-500">{{ str_repeat('⭐', $booking->review->rating) }}</span>
                    <span class="text-sm text-gray-500">({{ $booking->review->rating }}/5)</span>
                </div>
                @if($booking->review->comment)
                    <p class="text-gray-600 mt-1">{{ $booking->review->comment }}</p>
                @endif
            </div>
        @elseif($booking->status === 'completed')
            <div class="mt-6 border-t pt-4">
                <a href="{{ route('traveler.reviews.create', $booking) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-star mr-1"></i> Write a Review
                </a>
            </div>
        @endif

        <div class="mt-6 border-t pt-4">
            <a href="{{ route('traveler.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
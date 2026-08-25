@extends('layouts.public')

@section('title', __('messages.traveler_booking_detail_title', ['id' => $booking->id]))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('traveler.dashboard') }}" class="hover:text-blue-600">{{ __('messages.traveler_dashboard') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('messages.traveler_booking_hash', ['id' => $booking->id]) }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-md border p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.traveler_booking_hash', ['id' => $booking->id]) }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $booking->service->name ?? __('messages.na') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                @if($booking->status === 'pending') {{ __('messages.pending') }}
                @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                @else {{ __('messages.cancelled') }} @endif
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_start_date') }}</p>
                <p class="font-medium">{{ $booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.tbd') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_date') }}</p>
                <p class="font-medium">{{ $booking->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_provider') }}</p>
                <p class="font-medium">{{ $booking->service->provider->name ?? __('messages.na') }}</p>
            </div>
        </div>

        {{-- 🔥 QR Code Section --}}
        <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-gray-700">📱 {{ __('messages.traveler_booking_qr_heading') }}</h3>
            <div class="mt-2">
                <img src="{{ route('booking.qr', $booking->id) }}" 
                     alt="{{ __('messages.traveler_booking_qr_alt') }}" 
                     class="w-32 h-32 border rounded-lg">
                <p class="text-xs text-gray-400 mt-1">{{ __('messages.traveler_booking_qr_instruction') }}</p>
            </div>
        </div>

        {{-- Review Section --}}
        @if($booking->review)
            <div class="mt-6 border-t pt-4">
                <h3 class="font-semibold text-gray-700">{{ __('messages.traveler_booking_your_review') }}</h3>
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
                    <i class="fas fa-star mr-1"></i> {{ __('messages.traveler_booking_write_review') }}
                </a>
            </div>
        @endif

        <div class="mt-6 border-t pt-4">
            <a href="{{ route('traveler.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                ← {{ __('messages.traveler_booking_back_to_dashboard') }}
            </a>
        </div>
    </div>
</div>
@endsection
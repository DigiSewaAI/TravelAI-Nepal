@extends('layouts.provider')

@section('title', __('messages.booking_detail_title', ['id' => $booking->id]))
@section('header', __('messages.booking_detail_header'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('provider.bookings.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_bookings') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-start">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.booking_hash', ['id' => $booking->id]) }}</h2>
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

        <div class="grid md:grid-cols-2 gap-4 mt-4 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.traveler') }}</p>
                <p class="font-medium">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                <p class="text-sm text-gray-600">{{ $booking->traveler->email ?? __('messages.na') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.service') }}</p>
                <p class="font-medium">{{ $booking->service->name ?? __('messages.na') }}</p>
                <p class="text-sm text-gray-600">{{ $booking->service->provider->name ?? __('messages.na') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.start_date') }}</p>
                <p class="font-medium">{{ $booking->start_date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.booking_date') }}</p>
                <p class="font-medium">{{ $booking->booking_date->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <div class="mt-4 border-t pt-4">
            <h3 class="font-semibold text-gray-700">{{ __('messages.qr_code') }}</h3>
            <div class="mt-2">
                <img src="{{ route('booking.qr', $booking) }}" alt="{{ __('messages.qr_code_alt') }}" class="w-32 h-32">
            </div>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('provider.bookings.updateStatus', $booking) }}" class="flex gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>{{ __('messages.confirmed') }}</option>
                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    {{ __('messages.update_status') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
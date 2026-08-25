@extends('layouts.public')

@section('title', __('messages.booking_confirmation_page_title'))
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <i class="fas fa-check-circle text-4xl text-green-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.booking_confirmed_title') }}</h1>
        <p class="text-gray-500 mt-2">{{ __('messages.booking_confirmed_subtitle') }}</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- QR Code -->
        <div class="bg-white rounded-2xl shadow-md border p-6 text-center">
            <h3 class="font-semibold text-gray-700 mb-2">{{ __('messages.booking_qr_code_heading') }}</h3>
            <div class="bg-gray-50 p-4 rounded-xl inline-block">
                <img src="{{ route('booking.qr', $booking) }}" alt="QR Code" class="mx-auto w-48 h-48">
            </div>
            <p class="text-xs text-gray-400 mt-3">{{ __('messages.booking_qr_instruction') }}</p>
            <div class="mt-4">
                <a href="{{ route('booking.qr', $booking) }}" download="qr-{{ $booking->id }}.svg" 
                   class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition inline-block">
                    <i class="fas fa-download mr-1"></i> {{ __('messages.booking_download_qr') }}
                </a>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="bg-white rounded-2xl shadow-md border p-6">
            <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.booking_details_heading') }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.booking_id_label') }}</span><span class="font-medium">#{{ $booking->id }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.service') }}</span><span class="font-medium">{{ $booking->service->name ?? __('messages.na') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.provider') }}</span><span class="font-medium">{{ $booking->service->provider->name ?? __('messages.na') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.traveler') }}</span><span class="font-medium">{{ $booking->traveler->name ?? $booking->trekker->name ?? __('messages.guest') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.start_date') }}</span><span class="font-medium">{{ $booking->start_date->format('Y-m-d') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('messages.status') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs 
                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($booking->status == 'completed') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        @if($booking->status == 'pending') {{ __('messages.pending') }}
                        @elseif($booking->status == 'confirmed') {{ __('messages.confirmed') }}
                        @elseif($booking->status == 'completed') {{ __('messages.completed') }}
                        @else {{ __('messages.cancelled') }} @endif
                    </span>
                </div>
            </div>
            <hr class="my-4">
            <p class="text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i> {{ __('messages.booking_confirmation_email_notice') }}</p>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('home') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition inline-block mr-3">
            <i class="fas fa-home mr-1"></i> {{ __('messages.back_to_home') }}
        </a>
        <a href="{{ route('public.services.show', $booking->service->slug) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition inline-block">
            <i class="fas fa-eye mr-1"></i> {{ __('messages.view_service') }}
        </a>
    </div>
</div>
@endsection
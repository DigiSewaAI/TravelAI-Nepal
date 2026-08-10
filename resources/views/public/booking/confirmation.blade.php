@extends('layouts.public')

@section('title', 'Booking Confirmation | TravelAI Nepal')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <i class="fas fa-check-circle text-4xl text-green-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Booking Confirmed! 🎉</h1>
        <p class="text-gray-500 mt-2">Your booking has been successfully created.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- QR Code -->
        <div class="bg-white rounded-2xl shadow-md border p-6 text-center">
            <h3 class="font-semibold text-gray-700 mb-2">Your QR Check‑in Code</h3>
            <div class="bg-gray-50 p-4 rounded-xl inline-block">
                <img src="{{ route('booking.qr', $booking) }}" alt="QR Code" class="mx-auto w-48 h-48">
            </div>
            <p class="text-xs text-gray-400 mt-3">Scan this at checkpoints to record your presence.</p>
            <div class="mt-4">
                <a href="{{ route('booking.qr', $booking) }}" download="qr-{{ $booking->id }}.svg" 
                   class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition inline-block">
                    <i class="fas fa-download mr-1"></i> Download QR
                </a>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="bg-white rounded-2xl shadow-md border p-6">
            <h3 class="font-semibold text-gray-700 mb-3">Booking Details</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Booking ID</span><span class="font-medium">#{{ $booking->id }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Service</span><span class="font-medium">{{ $booking->service->name ?? 'N/A' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Provider</span><span class="font-medium">{{ $booking->service->provider->name ?? 'N/A' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Traveler</span><span class="font-medium">{{ $booking->traveler->name ?? $booking->trekker->name ?? 'Guest' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Start Date</span><span class="font-medium">{{ $booking->start_date->format('Y-m-d') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-xs 
                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($booking->status == 'completed') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
            </div>
            <hr class="my-4">
            <p class="text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i> You will receive a confirmation email shortly.</p>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('home') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition inline-block mr-3">
            <i class="fas fa-home mr-1"></i> Back to Home
        </a>
        <a href="{{ route('public.services.show', $booking->service->slug) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition inline-block">
            <i class="fas fa-eye mr-1"></i> View Service
        </a>
    </div>
</div>
@endsection
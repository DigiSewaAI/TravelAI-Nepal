@extends('layouts.provider')

@section('title', 'Check-in Detail | TravelAI Nepal')
@section('header', '📍 Check-in Detail')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('provider.checkins.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Check-ins
        </a>
    </div>

    <div class="flex justify-between items-start mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Check-in Detail</h2>
        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
            <i class="fas fa-check-circle mr-1"></i> Checked In
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">Traveler</p>
            <p class="font-medium">{{ $scan->booking->traveler->name ?? 'Guest' }}</p>
            <p class="text-sm text-gray-600">{{ $scan->booking->traveler->email ?? '' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Service</p>
            <p class="font-medium">{{ $scan->booking->service->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Provider</p>
            <p class="font-medium">{{ $scan->booking->service->provider->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Checkpoint</p>
            <p class="font-medium"><i class="fas fa-map-pin text-blue-500 mr-1"></i> {{ $scan->checkpoint_name ?? 'Check-in' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Checked In At</p>
            <p class="font-medium">{{ $scan->scanned_at->format('M d, Y H:i') }}</p>
        </div>
        @if($scan->latitude && $scan->longitude)
        <div class="col-span-2">
            <p class="text-sm text-gray-500">Location</p>
            <p class="font-medium">Lat: {{ $scan->latitude }}, Lng: {{ $scan->longitude }}</p>
        </div>
        @endif
    </div>

    <div class="mt-6 border-t pt-4">
        <a href="{{ route('provider.bookings.show', $scan->booking_id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-calendar-check mr-1"></i> View Full Booking
        </a>
    </div>
</div>
@endsection
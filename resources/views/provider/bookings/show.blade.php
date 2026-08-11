@extends('layouts.provider')

@section('title', 'Booking #' . $booking->id . ' | TravelAI Nepal')
@section('header', 'Booking Detail')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('provider.bookings.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-start">
            <h2 class="text-2xl font-bold text-gray-900">Booking #{{ $booking->id }}</h2>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mt-4 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">Traveler</p>
                <p class="font-medium">{{ $booking->traveler->name ?? 'Guest' }}</p>
                <p class="text-sm text-gray-600">{{ $booking->traveler->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Service</p>
                <p class="font-medium">{{ $booking->service->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">{{ $booking->service->provider->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Start Date</p>
                <p class="font-medium">{{ $booking->start_date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Booking Date</p>
                <p class="font-medium">{{ $booking->booking_date->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <div class="mt-4 border-t pt-4">
            <h3 class="font-semibold text-gray-700">QR Code</h3>
            <div class="mt-2">
                <img src="{{ route('booking.qr', $booking) }}" alt="QR Code" class="w-32 h-32">
            </div>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('provider.bookings.updateStatus', $booking) }}" class="flex gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    Update Status
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
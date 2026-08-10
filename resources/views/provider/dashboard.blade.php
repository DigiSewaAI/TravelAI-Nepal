@extends('layouts.provider')

@section('title', 'Dashboard | TravelAI Nepal')
@section('header', 'Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase">Total Services</p>
            <p class="text-2xl font-bold">{{ $totalServices ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase">Total Bookings</p>
            <p class="text-2xl font-bold">{{ $totalBookings ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs uppercase">Pending</p>
            <p class="text-2xl font-bold">{{ $pendingBookings ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs uppercase">Provider Status</p>
            <p class="text-lg font-bold">
                @if($provider->verification_status === 'verified')
                    <span class="text-green-600">Verified ✅</span>
                @else
                    <span class="text-yellow-600">Pending ⏳</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-4">📋 Recent Bookings</h2>
        @if($recentBookings && $recentBookings->count() > 0)
            <div class="space-y-3">
                @foreach($recentBookings as $booking)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-medium">{{ $booking->traveler->name ?? 'Guest' }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->service->name ?? 'Unknown Service' }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->start_date->format('Y-m-d') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                            @elseif($booking->status == 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No bookings yet.</p>
        @endif
    </div>
@endsection
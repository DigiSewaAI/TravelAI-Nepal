@extends('agency.layouts.app')

@section('header', 'Agency Details')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Back Button --}}
    <div class="mb-4">
        <a href="{{ route('agency.agencies.index') }}" class="text-blue-600 hover:underline">
            <i class="fas fa-arrow-left"></i> Back to Agencies
        </a>
    </div>

    {{-- Agency Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex items-start space-x-6">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                @if($agency->logo_url)
                    <img src="{{ asset('storage/' . $agency->logo_url) }}" alt="{{ $agency->name }}" class="w-24 h-24 rounded-full object-cover border">
                @else
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-building text-4xl text-blue-500"></i>
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $agency->name }}</h2>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($agency->role == 'super_admin') bg-purple-100 text-purple-800
                                @elseif($agency->role == 'admin') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $agency->role ?? 'agency' }}
                            </span>
                            <span class="text-sm text-gray-500">• ID: #{{ $agency->id }}</span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('agency.agencies.edit', $agency->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 text-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-800">{{ $agency->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="text-gray-800">{{ $agency->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="text-gray-800">{{ $agency->address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Registered</p>
                        <p class="text-gray-800">{{ $agency->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Last Updated</p>
                        <p class="text-gray-800">{{ $agency->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase">Total Treks</p>
            <p class="text-2xl font-bold">{{ $agency->treks_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase">Total Bookings</p>
            <p class="text-2xl font-bold">{{ $agency->bookings_count ?? 0 }}</p>
        </div>
    </div>

    {{-- Treks List --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
        <h3 class="text-lg font-semibold mb-4">🗺️ All Treks ({{ $agency->treks->count() }})</h3>
        @if($agency->treks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Name</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Price</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Duration</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Difficulty</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agency->treks as $trek)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 text-sm">{{ $trek->name }}</td>
                            <td class="py-2 text-sm">${{ number_format($trek->price, 0) }}</td>
                            <td class="py-2 text-sm">{{ $trek->duration_days }} days</td>
                            <td class="py-2 text-sm">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if(strtolower($trek->difficulty) == 'easy') bg-green-100 text-green-800
                                    @elseif(strtolower($trek->difficulty) == 'moderate') bg-yellow-100 text-yellow-800
                                    @elseif(strtolower($trek->difficulty) == 'difficult') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($trek->difficulty) }}
                                </span>
                            </td>
                            <td class="py-2 text-sm">{{ $trek->bookings_count ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No treks created yet.</p>
        @endif
    </div>

    {{-- Recent Bookings List --}}
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-lg font-semibold mb-4">📋 Recent Bookings ({{ $agency->bookings->count() }})</h3>
        @if($agency->bookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Trekker</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Trek</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Start Date</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agency->bookings->take(10) as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 text-sm">{{ $booking->trekker->name ?? 'N/A' }}</td>
                            <td class="py-2 text-sm">{{ $booking->trek->name }}</td>
                            <td class="py-2 text-sm">{{ $booking->start_date->format('Y-m-d') }}</td>
                            <td class="py-2 text-sm">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($booking->status == 'completed') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($agency->bookings->count() > 10)
                    <p class="text-sm text-gray-500 mt-2">Showing 10 of {{ $agency->bookings->count() }} bookings.</p>
                @endif
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No bookings yet.</p>
        @endif
    </div>
</div>
@endsection
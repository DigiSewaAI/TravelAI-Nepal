@extends('layouts.admin')

@section('title', 'Bookings | TravelAI Nepal')
@section('header', 'Manage Bookings')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Bookings</h2>

    @if($bookings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Traveler</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Service</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Start Date</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm">#{{ $booking->id }}</td>
                        <td class="py-3 text-sm">{{ $booking->traveler->name ?? 'Guest' }}</td>
                        <td class="py-3 text-sm">{{ $booking->service->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $booking->service->provider->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $booking->start_date->format('Y-m-d') }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" class="inline"
                                  onsubmit="return confirm('Delete this booking?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No bookings found.</p>
    @endif
</div>
@endsection
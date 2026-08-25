@extends('layouts.provider')

@section('title', __('messages.bookings_page_title'))
@section('header', __('messages.manage_bookings'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.all_bookings') }}</h2>

    @if($bookings && $bookings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.traveler') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.service') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.start_date') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.status') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm">#{{ $booking->id }}</td>
                        <td class="py-3 text-sm">{{ $booking->traveler->name ?? __('messages.guest') }}</td>
                        <td class="py-3 text-sm">{{ $booking->service->name ?? __('messages.na') }}</td>
                        <td class="py-3 text-sm">{{ $booking->start_date->format('Y-m-d') }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs
                                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($booking->status === 'pending') {{ __('messages.pending') }}
                                @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                                @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                                @else {{ __('messages.cancelled') }} @endif
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('provider.bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-center py-8">{{ __('messages.no_bookings_yet') }}</p>
    @endif
</div>
@endsection
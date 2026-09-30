@extends('layouts.public')

@section('title', __('messages.traveler_my_bookings'))

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('traveler.dashboard') }}" class="hover:text-blue-600">{{ __('messages.traveler_dashboard') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('messages.traveler_my_bookings') }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-calendar-check text-blue-600"></i>
                {{ __('messages.traveler_my_bookings') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('messages.traveler_bookings_showing', ['count' => $counts['all']]) }}
            </p>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @php
            $filters = [
                'all'       => ['label' => __('messages.traveler_bookings_filter_all'),       'count' => $counts['all']],
                'upcoming'  => ['label' => __('messages.traveler_bookings_filter_upcoming'),  'count' => $counts['upcoming']],
                'active'    => ['label' => __('messages.traveler_bookings_filter_active'),    'count' => $counts['active']],
                'completed' => ['label' => __('messages.traveler_bookings_filter_completed'), 'count' => $counts['completed']],
            ];
        @endphp
        @foreach($filters as $key => $f)
            <a href="{{ route('traveler.bookings.index', ['filter' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2
                    {{ $filter === $key
                        ? 'bg-blue-600 text-white shadow-sm hover:brightness-110'
                        : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:shadow-sm' }}">
                {{ $f['label'] }}
                <span class="text-xs px-2 py-0.5 rounded-full {{ $filter === $key ? 'bg-white/20' : 'bg-gray-100 text-gray-600' }}">
                    {{ $f['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Booking List --}}
    @if($bookings->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
            <i class="fas fa-calendar-check text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800">{{ __('messages.traveler_no_bookings_yet') }}</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">{{ __('messages.traveler_bookings_empty_desc') }}</p>
            <a href="{{ route('public.services.index') }}"
               class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 hover:brightness-110 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                {{ __('messages.traveler_explore_services') }} →
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $booking)
                <div class="bg-white rounded-xl shadow-sm border border-l-4
                    @if($booking->status === 'pending') border-l-yellow-400
                    @elseif($booking->status === 'confirmed') border-l-blue-500
                    @elseif($booking->status === 'completed') border-l-emerald-500
                    @else border-l-red-400 @endif
                    p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        {{-- Booking Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($booking->status === 'completed') bg-emerald-100 text-emerald-800
                                    @else bg-red-100 text-red-800 @endif">
                                    @if($booking->status === 'pending') {{ __('messages.pending') }}
                                    @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                                    @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                                    @else {{ __('messages.cancelled') }} @endif
                                </span>
                                @if($booking->review)
                                    <span class="text-xs text-emerald-600 flex items-center gap-1">
                                        <i class="fas fa-check-circle"></i> {{ __('messages.traveler_reviewed') }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-semibold text-gray-900 text-base truncate">
                                {{ $booking->service->name ?? __('messages.na') }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-3">
                                <span>
                                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                    {{ $booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.tbd') }}
                                </span>
                                @if($booking->service->provider)
                                    <span>
                                        <i class="fas fa-building mr-1 text-gray-400"></i>
                                        {{ $booking->service->provider->name }}
                                    </span>
                                @endif
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <a href="{{ route('traveler.bookings.show', $booking->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 hover:brightness-110 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                                <i class="fas fa-eye"></i> {{ __('messages.view') }}
                            </a>
                            @if($booking->status === 'completed' && !$booking->review)
                                <a href="{{ route('traveler.reviews.create', $booking) }}"
                                   class="bg-amber-500 hover:bg-amber-600 hover:brightness-110 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                                    <i class="fas fa-star"></i> {{ __('messages.traveler_write_review') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @endif

    {{-- Back to Dashboard --}}
    <div class="mt-8 text-center">
        <a href="{{ route('traveler.dashboard') }}"
           class="inline-block text-sm text-gray-600 hover:text-blue-600 font-medium transition-colors duration-200">
            ← {{ __('messages.traveler_back_dashboard') }}
        </a>
    </div>
</div>
@endsection
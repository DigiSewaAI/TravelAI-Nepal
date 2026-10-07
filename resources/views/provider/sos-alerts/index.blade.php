@extends('layouts.provider')

@section('title', __('messages.sos_alerts_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                {{ __('messages.sos_alerts_title') }}
                @if($activeCount > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                        {{ $activeCount }}
                    </span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.sos_alerts_subtitle') }}</p>
        </div>
    </div>

    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(['all' => __('messages.sos_alerts_tab_all'), 'active' => __('messages.sos_alerts_tab_active'), 'resolved' => __('messages.sos_alerts_tab_resolved')] as $key => $label)
            <a href="{{ route('provider.sos-alerts.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $status === $key ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:border-red-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Alerts List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($alerts->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($alerts as $alert)
                    @php
                        $isActive = in_array($alert->status, ['pending', 'sent']);
                    @endphp
                    <a href="{{ route('provider.sos-alerts.show', $alert) }}"
                       class="block hover:bg-gray-50 transition">
                        <div class="p-5 flex flex-wrap items-start gap-4">

                            {{-- Status indicator --}}
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $isActive ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                <i class="fas {{ $isActive ? 'fa-exclamation-triangle' : 'fa-check-circle' }} text-xl"></i>
                            </div>

                            {{-- Main content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-900">
                                        {{ $alert->traveler->name ?? __('messages.sos_alerts_unknown_traveler') }}
                                    </h3>
                                    @if($isActive)
                                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                            {{ __('messages.sos_alerts_status_active') }}
                                        </span>
                                    @else
                                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                            {{ __('messages.sos_alerts_status_resolved') }}
                                        </span>
                                    @endif
                                </div>

                                @if($alert->booking && $alert->booking->service)
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-hiking mr-1"></i>
                                        {{ $alert->booking->service->name }}
                                    </p>
                                @endif

                                @if($alert->message)
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        "{{ $alert->message }}"
                                    </p>
                                @endif

                                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-gray-400">
                                    <span>
                                        <i class="fas fa-map-marker-alt mr-1 text-red-500"></i>
                                        {{ number_format($alert->latitude, 4) }}, {{ number_format($alert->longitude, 4) }}
                                    </span>
                                    <span>
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $alert->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <div class="flex-shrink-0 self-center">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $alerts->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800">{{ __('messages.sos_alerts_empty') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('messages.sos_alerts_empty_sub') }}</p>
            </div>
        @endif
    </div>

</div>
@endsection
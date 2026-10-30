@extends('layouts.public')

@section('title', __('messages.traveler_dashboard_title'))

@section('content')

{{-- 🔥 Notif flash animation (Tailwind-independent) --}}
<style>
@keyframes notifFlashRed {
    0%, 100% {
        color: #ffffff;
        text-shadow:
            0 0 6px #ffffff,
            0 0 12px #ffffff,
            0 0 20px rgba(255, 255, 255, 0.7),
            0 0 30px rgba(255, 255, 255, 0.5);
        transform: scale(1);
    }
    50% {
        color: #ffffff;
        text-shadow:
            0 0 10px #ffffff,
            0 0 20px #ffffff,
            0 0 30px rgba(255, 255, 255, 0.9),
            0 0 45px rgba(255, 255, 255, 0.7),
            0 0 60px rgba(255, 255, 255, 0.5);
        transform: scale(1.25);
    }
}
.notif-flash-red {
    animation: notifFlashRed 1.1s ease-in-out infinite !important;
    font-weight: 900 !important;
    display: inline-block !important;
}
</style>

{{-- ========== HERO / WELCOME SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-6 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl md:text-3xl font-bold">
<span id="dynamic-greeting">{{ __('messages.greeting_morning') }}</span>, {{ Auth::user()->name ?? 'Traveler' }} 👋
                </h1>
                <p class="text-blue-100 text-sm mt-1">{{ __('messages.traveler_ready_for_adventure') }}</p>

                {{-- Quick stats chips --}}
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="text-xs bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                        <i class="fas fa-hiking mr-1"></i>{{ $bookingStats['completed'] ?? 0 }} {{ __('messages.traveler_quick_treks') }}
                    </span>
                    <span class="text-xs bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                        <i class="fas fa-calendar-check mr-1"></i>{{ $bookings->count() }} {{ __('messages.traveler_quick_bookings') }}
                    </span>
                    <span class="text-xs bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                        <i class="fas fa-star mr-1"></i>{{ $reviews->count() }} {{ __('messages.traveler_quick_reviews') }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                {{-- Notification Bell Widget (left of weather) --}}
<div class="relative" id="dashNotifWrapper">
    <button type="button" id="dashNotifToggle" 
            class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-2.5 flex items-center gap-3 hover:bg-white/20 transition-all duration-200 cursor-pointer">
        <i class="fas fa-bell text-2xl text-yellow-300"></i>
        <div class="leading-tight text-left">
            <p class="text-[10px] uppercase tracking-wider text-blue-100/80">{{ __('messages.notifications_widget_title') }}</p>
            <p class="text-lg font-bold">
    <span id="dashNotifBadge" class="text-white">0</span> 
    <span id="dashNotifLabel" class="text-blue-100/80">{{ __("messages.notif_unread") }}</span>
</p>
        </div>
    </button>
    <div id="dashNotifPanel" 
         class="hidden absolute left-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
        <div class="px-4 py-3 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">{{ __('messages.notifications_widget_title') }}</h3>
            <a href="#" id="dashNotifMarkAll" class="text-xs text-blue-600 hover:underline">{{ __('messages.notifications_mark_all_read') }}</a>
        </div>
        <div id="dashNotifList">
            <p class="p-4 text-sm text-gray-500 text-center">{{ __('messages.notifications_empty') }}</p>
        </div>
    </div>
</div>

{{-- Weather Widget --}}
@if(!empty($weatherData) && isset($weatherData['temperature']))
                    @php
                        $wmo = \App\Services\OpenMeteoService::describeWmoCode($weatherData['weather_code'] ?? null);
                        $iconMap = [
                            'clear' => 'fa-sun',
                            'cloud' => 'fa-cloud',
                            'rain'  => 'fa-cloud-rain',
                            'snow'  => 'fa-snowflake',
                            'fog'   => 'fa-smog',
                            'storm' => 'fa-bolt',
                            'unknown' => 'fa-cloud-sun',
                        ];
                        $wIcon = $iconMap[$wmo['icon']] ?? 'fa-cloud-sun';
                    @endphp
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-2.5 flex items-center gap-3 hover:bg-white/20 transition-all duration-200">
                        <i class="fas {{ $wIcon }} text-2xl text-yellow-300"></i>
                        <div class="leading-tight">
                            <p class="text-[10px] uppercase tracking-wider text-blue-100/80">{{ $weatherCity }}</p>
                            <p class="text-lg font-bold">{{ round($weatherData['temperature']) }}°C</p>
                        </div>
                    </div>
                @endif

                <a href="{{ route('home') }}#ai-planner" class="bg-white/20 hover:bg-white/30 hover:brightness-110 backdrop-blur-sm text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                    <i class="fas fa-robot"></i> {{ __('messages.plan_with_ai') }}
                </a>
                <a href="{{ route('public.services.index') }}" class="bg-white text-blue-600 hover:bg-gray-100 hover:brightness-110 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                    <i class="fas fa-compass"></i> {{ __('messages.explore_nepal_btn') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ========== STATS CARDS ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingStats['upcoming'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('messages.traveler_stat_upcoming') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-hiking"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingStats['active'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('messages.traveler_stat_active') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900">{{ $bookingStats['completed'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('messages.traveler_stat_completed') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-star"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-3xl font-bold text-gray-900">{{ $reviews->count() }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('messages.traveler_stat_reviews') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- &#9989; PASSPORT QUICK ACCESS CARD WITH SHARE TOGGLE --}}
<div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 rounded-2xl p-6 mb-6 text-white shadow-xl relative overflow-hidden group">
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="flex items-center gap-4">
            <div class="text-4xl">&#127890;</div>
            <div>
                <h3 class="text-xl font-bold">{{ __('messages.passport_card_title') }}</h3>
                <p class="text-blue-100 text-sm">
                    {{ Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_public') : __('messages.passport_card_private') }}
                </p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3 flex-wrap">
            {{-- Share Toggle --}}
            <form action="{{ route('traveler.passport.toggle') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                            {{ Auth::user()->passport_privacy === 'public'
                                ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                : 'bg-white/20 hover:bg-white/30 text-white' }}">
                    {{ Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_make_private') : __('messages.passport_card_share_public') }}
                </button>
            </form>
            <a href="{{ route('traveler.passport') }}"
               class="bg-white text-blue-600 px-6 py-2.5 rounded-xl font-bold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl flex items-center gap-2 group-hover:scale-105 transform duration-200">
                {{ __('messages.passport_card_view_full') }}
            </a>
        </div>
    </div>
    @if(Auth::user()->passport_privacy === 'public')
        <div class="relative z-10 mt-3 text-sm text-blue-100">
            {{ __('messages.passport_card_share_label') }}
            <span class="font-mono text-xs bg-black/20 px-2 py-1 rounded">{{ url('/passport/' . Auth::user()->passport_public_id) }}</span>
            <button onclick="navigator.clipboard?.writeText('{{ url('/passport/' . Auth::user()->passport_public_id) }}')"
                    class="ml-2 text-xs bg-white/20 px-2 py-1 rounded hover:bg-white/30 transition">
                {{ __('messages.passport_card_copy') }}
            </button>
        </div>
    @endif
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ========== LEFT COLUMN: Active Trip + Bookings ========== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Group: My Travels --}}
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                <i class="fas fa-suitcase-rolling mr-1"></i> {{ __('messages.traveler_group_travels') }}
            </h2>

            {{-- Active Trip --}}
            @if($activeTrip)
                <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-blue-500 p-6 hover:shadow-lg transition-all duration-300">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-hiking text-blue-600"></i> {{ __('messages.traveler_active_trip_title') }}
                    </h3>
                    <div class="mt-3">
                        <h4 class="text-xl font-semibold text-gray-900">{{ $activeTrip->service->name ?? __('messages.na') }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i>
                            {{ $activeTrip->start_date ? $activeTrip->start_date->translatedFormat('M d, Y') : __('messages.tbd') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> {{ __('messages.traveler_active') }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ __('messages.traveler_status_label') }}: <span class="font-medium text-gray-700">{{ __('messages.' . $activeTrip->status) }}</span>
                            </span>
                            <a href="{{ route('traveler.passport') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium ml-auto">
                                {{ __('messages.traveler_view_trek_passport') }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
                    <i class="fas fa-hiking text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.traveler_no_active_trip') }}</h3>
                    <p class="text-sm text-gray-400">{{ __('messages.traveler_no_active_trip_sub') }}</p>
                    <a href="{{ route('home') }}#ai-planner" class="inline-block mt-3 text-blue-600 hover:underline text-sm">{{ __('messages.traveler_start_planning') }} &rarr;</a>
                </div>
            @endif

            {{-- My Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-blue-500 p-6 hover:shadow-lg transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-blue-600"></i> {{ __('messages.traveler_my_bookings') }}
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400">{{ $bookings->count() }} {{ __('messages.traveler_total') }}</span>
                        @if($bookings->count() > 0)
                            <a href="{{ route('traveler.bookings.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors flex items-center gap-1">
                                {{ __('messages.traveler_view_all_bookings_short') }} <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        @endif
                    </div>
                </div>

                @if($bookings->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($bookings->take(5) as $booking)
                            <div class="py-3 flex flex-wrap justify-between items-center gap-2 hover:bg-gray-50/60 -mx-2 px-2 rounded-lg transition-colors duration-200">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $booking->service->name ?? __('messages.na') }}</p>
                                    <p class="text-xs text-gray-400">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ $booking->start_date ? $booking->start_date->translatedFormat('M d, Y') : __('messages.tbd') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        @if($booking->status === 'pending') {{ __('messages.pending') }}
                                        @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                                        @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                                        @else {{ __('messages.cancelled') }} @endif
                                    </span>
                                    @if($booking->status === 'completed' && !$booking->review)
                                        <a href="{{ route('traveler.reviews.create', $booking) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-star"></i> {{ __('messages.traveler_write_review') }}
                                        </a>
                                    @endif
                                    @if($booking->review)
                                        <span class="text-sm text-green-600">&#9989; {{ __('messages.traveler_reviewed') }}</span>
                                    @endif
                                    <a href="{{ route('traveler.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        {{ __('messages.view') }} <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    <p class="text-gray-500 text-center py-6">{{ __('messages.traveler_no_bookings_yet') }}</p>
                    <div class="text-center">
                        <a href="{{ route('public.services.index') }}" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_explore_services') }} &rarr;</a>
                    </div>
                @endif
            </div>

            {{-- Ads: Featured for You (Phase 1 MVP - left column, below bookings) --}}
            @include('partials.ad-carousel', ['featuredAds' => $featuredAds ?? collect()])

        </div>

        {{-- ========== RIGHT COLUMN: Reviews + Quick Actions ========== --}}
        <div class="space-y-6">

                        <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-amber-500 p-6 hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-star text-yellow-500"></i> {{ __('messages.traveler_my_reviews') }}
                    </h3>
                    @if($reviews->count() > 0)
                        <a href="{{ route('traveler.reviews.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors flex items-center gap-1">
                            {{ __('messages.traveler_view_all_reviews_short') }} <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
                @if($reviews->count() > 0)
                    <div class="space-y-3">
                        @foreach($reviews->take(3) as $review)
                            <div class="border-b pb-2 last:border-0">
                                <div class="flex justify-between items-start">
                                    <span class="font-medium text-sm text-gray-800">{{ $review->service->name ?? __('messages.na') }}</span>
                                    <span class="text-yellow-500 text-sm">{!! str_repeat('&#11088;', $review->rating) !!}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $review->comment ?: __('messages.traveler_no_comment') }}</p>
                            </div>
                        @endforeach
                    </div>

                @else
                    <p class="text-gray-400 text-sm text-center py-4">{{ __('messages.traveler_no_reviews_yet') }}</p>
                @endif
            </div>

            {{-- My Trek History (QR Check-ins) --}}
            <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-emerald-500 p-6 hover:shadow-lg transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-route text-green-600"></i> {{ __('messages.traveler_trek_history') }}
                </h3>
                @if($qrScans->count() > 0)
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                        @foreach($qrScans as $scan)
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0">
                                <div>
                                    <p class="font-medium text-sm text-gray-800">{{ $scan->booking->service->name ?? __('messages.na') }}</p>
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-map-pin mr-1 text-blue-500"></i>
                                        {{ $scan->checkpoint_name ?? __('messages.traveler_checkin_default') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-medium text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i> {{ __('messages.traveler_checked_in') }}
                                    </span>
                                    <p class="text-[10px] text-gray-400">{{ $scan->scanned_at ? $scan->scanned_at->translatedFormat('M d, Y H:i') : __('messages.na') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($qrScans->count() > 10)
                        <div class="mt-3 text-center">
                            <a href="{{ route('traveler.journey-replay') }}" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_view_all_history') }} &rarr;</a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-route text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-400 text-sm">{{ __('messages.traveler_no_trek_history') }}</p>
                        <p class="text-xs text-gray-400">{{ __('messages.traveler_no_trek_history_sub') }}</p>
                    </div>
                @endif
            </div>
            {{-- Group: Tools & Shortcuts --}}
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider pt-2">
                <i class="fas fa-toolbox mr-1"></i> {{ __('messages.traveler_group_tools') }}
            </h2>

            {{-- &#128293; AI Travel Planner (Prominent Card) --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">{{ __('messages.traveler_ai_planner_card_title') }}</h3>
                        <p class="text-blue-100 text-sm mt-1 max-w-md">
                            {{ __('messages.traveler_ai_planner_card_desc') }}
                        </p>
                        <a href="{{ route('home') }}#ai-planner" class="inline-block mt-4 bg-white text-blue-600 hover:bg-gray-100 px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-md hover:shadow-lg">
                            {{ __('messages.traveler_create_ai_itinerary') }} <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 💱 Forex Widget (Phase 1) --}}
            @if(!empty($forexRates) && count($forexRates) > 0)
                <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-emerald-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ __('messages.forex_widget_title') }}</h4>
                            <p class="text-xs text-gray-500">{{ __('messages.forex_widget_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        @foreach($forexRates as $currency => $rate)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">1 {{ $currency }}</span>
                                <span class="font-medium text-gray-800">
                                    NPR {{ number_format($rate, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if($forexUpdatedAt)
                        <p class="text-[10px] text-gray-400 mt-2 text-right">
                            {{ __('messages.forex_updated') }}: {{ \Carbon\Carbon::parse($forexUpdatedAt)->diffForHumans() }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- &#128248; My Travel Memories --}}
<div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-images text-green-500 text-xl"></i>
        </div>
                <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-gray-800">&#128248; {{ __('messages.traveler_memories_title') }}</h4>
            <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.traveler_memories_subtitle') }}</p>

            {{-- &#9989; Upload Form --}}
            <form id="uploadForm" class="mt-3" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-col sm:flex-row gap-2">
                    <select name="checkpoint" id="checkpointSelect" class="w-full sm:flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">{{ __('messages.traveler_checkpoint_select') }}</option>
                        @foreach($userWaypoints as $wp)
                            <option value="{{ $wp->name }}">{{ $wp->name }}</option>
                        @endforeach
                    </select>
                    <input type="file" name="media" id="fileInput" accept="image/*,video/*" class="hidden" required>
                    <label for="fileInput" class="w-full sm:flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50 flex items-center gap-2">
                        <i class="fas fa-folder-open text-blue-600"></i>
                        <span id="fileInputLabel">{{ __('messages.choose_file') }}</span>
                    </label>
                    <button type="submit" id="uploadBtn" class="w-full sm:w-auto whitespace-nowrap bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition">
                        <i class="fas fa-upload"></i> {{ __('messages.traveler_upload_btn') }}
                    </button>
                </div>
                <div id="uploadMessage" class="mt-2 text-sm hidden"></div>
            </form>

            {{-- Session messages --}}
            @if(session('upload_success'))
                <div class="mt-2 text-sm text-green-600">{{ session('upload_success') }}</div>
            @endif
            @if(session('upload_error'))
                <div class="mt-2 text-sm text-red-600">{{ session('upload_error') }}</div>
            @endif

            {{-- Existing Memories --}}
            @if($userMedia->count() > 0)
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach($userMedia->take(6) as $media)
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square bg-gray-100">
                            @if($media->media_type === 'image')
                                <img src="{{ Storage::url($media->optimized_path) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover">
                            @endif
                            <form action="{{ route('traveler.memory.delete') }}" method="POST" class="absolute top-1 right-1">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $media->id }}">
                                <button type="submit" onclick="return confirm(__('messages.traveler_delete_memory_confirm'))" class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">&times;</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                @if($userMedia->count() > 6)
                    <p class="text-xs text-gray-400 mt-2">+{{ $userMedia->count() - 6 }} more</p>
                @endif
            @else
                <p class="text-xs text-gray-400 mt-3">{{ __('messages.traveler_no_memories') }}</p>
            @endif
        </div>
    </div>
</div>

            {{-- &#127916; My Journey Replay (Always Visible) --}}
<div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-5 text-white shadow-lg hover:shadow-xl transition group">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <i class="fas fa-film text-2xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-lg">&#127916; {{ __('messages.traveler_replay_title') }}</h4>
            <p class="text-sm text-purple-100 mt-0.5">{{ __('messages.traveler_replay_subtitle') }}</p>
            <a href="{{ route('traveler.journey-replay') }}"
               class="inline-block mt-2 bg-white text-purple-600 hover:bg-gray-100 px-4 py-1.5 rounded-lg text-sm font-semibold transition shadow group-hover:scale-105 transform duration-200">
                {{ __('messages.traveler_replay_cta') }} &rarr;
            </a>
        </div>
    </div>
</div>

            {{-- &#128293; Safety Center --}}
<div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-red-500 p-5 hover:shadow-lg transition-all duration-300">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-alt text-red-500 text-xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-800">{{ __('messages.traveler_safety_center_title') }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.traveler_safety_center_desc') }}</p>
                </div>
                @if(isset($unreadAlerts) && count($unreadAlerts) > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ count($unreadAlerts) }}</span>
                @endif
            </div>

            {{-- Alerts List --}}
            @if(isset($unreadAlerts) && count($unreadAlerts) > 0)
                <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                    @foreach($unreadAlerts as $alert)
                        <div class="border-l-4
                            @if($alert['severity'] === 'critical') border-red-600
                            @elseif($alert['severity'] === 'high') border-orange-500
                            @elseif($alert['severity'] === 'moderate') border-yellow-500
                            @else border-green-500 @endif
                            bg-gray-50 p-3 rounded-r-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-sm">
                                        @if($alert['severity'] === 'critical') &#128308;
                                        @elseif($alert['severity'] === 'high') &#128992;
                                        @elseif($alert['severity'] === 'moderate') &#128993;
                                        @else &#128994; @endif
                                        {{ $alert['incident']['title'] ?? $alert['message'] ?? 'Safety Alert' }}
                                    </p>
                                    <p class="text-xs text-gray-600">{{ $alert['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
    {{ isset($alert['sent_at']) ? \Carbon\Carbon::parse($alert['sent_at'])->diffForHumans() : 'Just now' }}
</p>
                                </div>
                                <form method="POST" action="{{ route('traveler.alert.read', $alert['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">{{ __('messages.traveler_mark_read_btn') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3">
                    <p class="text-gray-500 text-sm">&#9989; {{ __('messages.traveler_no_safety_alerts') }}</p>
                </div>
            @endif

            <div class="mt-3 flex flex-col gap-2">
                <a href="{{ route('safety.index') }}" class="text-sm text-blue-600 hover:underline">
                    {{ __('messages.traveler_view_safety_map') }} &rarr;
                </a>

                {{-- 🆘 SOS EMERGENCY BUTTON --}}
                <button type="button" onclick="openSosModal()"
                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition shadow-md mt-1">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ __('messages.sos_button_label') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
</div>
</div>

{{-- 🆘 SOS EMERGENCY MODAL + JS --}}
<div id="sosModal" class="fixed inset-0 bg-black/60 hidden z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-lg text-gray-900">{{ __('messages.sos_modal_title') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.sos_modal_subtitle') }}</p>
            </div>
        </div>

        <div id="sosStatus" class="text-sm mb-3 hidden"></div>

        <div class="mb-4">
            <label for="sosMessage" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('messages.sos_message_label') }}
            </label>
            <textarea id="sosMessage" rows="3" maxlength="500"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                      placeholder="{{ __('messages.sos_message_placeholder') }}"></textarea>
        </div>

        <div class="flex gap-2">
            <button type="button" onclick="closeSosModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-medium transition">
                {{ __('messages.cancel') ?? 'Cancel' }}
            </button>
            <button type="button" id="sosSendBtn" onclick="sendSos()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-bold transition shadow-md disabled:opacity-50">
                <i class="fas fa-paper-plane mr-1"></i>
                <span>{{ __('messages.sos_send_now') }}</span>
            </button>
        </div>
    </div>
</div>

<script>
function openSosModal() {
    const m = document.getElementById('sosModal');
    m.classList.remove('hidden');
    document.getElementById('sosStatus').classList.add('hidden');
    document.getElementById('sosMessage').value = '';
}

function closeSosModal() {
    document.getElementById('sosModal').classList.add('hidden');
}

function sendSos() {
    const statusEl = document.getElementById('sosStatus');
    const btn = document.getElementById('sosSendBtn');

    statusEl.classList.remove('hidden');
    statusEl.className = 'text-sm mb-3 text-blue-600';
    statusEl.textContent = '{{ __("messages.sos_getting_location") }}';
    btn.disabled = true;

    if (!navigator.geolocation) {
        statusEl.className = 'text-sm mb-3 text-red-600';
        statusEl.textContent = '{{ __("messages.sos_geo_unsupported") }}';
        btn.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const response = await fetch('{{ url("/api/sos") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        message: document.getElementById('sosMessage').value || null,
                    }),
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    statusEl.className = 'text-sm mb-3 text-green-600 font-semibold';
                    statusEl.textContent = '✅ ' + (data.message || '{{ __("messages.sos_sent_success") }}');
                    setTimeout(() => closeSosModal(), 3000);
                } else {
                    statusEl.className = 'text-sm mb-3 text-red-600';
                    statusEl.textContent = '❌ ' + (data.message || '{{ __("messages.sos_send_failed") }}');
                }
            } catch (err) {
                statusEl.className = 'text-sm mb-3 text-red-600';
                statusEl.textContent = '❌ {{ __("messages.sos_network_error") }}';
            }
            btn.disabled = false;
        },
        (error) => {
            statusEl.className = 'text-sm mb-3 text-red-600';
            statusEl.textContent = '❌ {{ __("messages.sos_location_denied") }}';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const btn = document.getElementById('uploadBtn');
    const msg = document.getElementById('uploadMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        msg.classList.add('hidden');

        fetch('{{ route("traveler.checkpoint.upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('&#9989; ' + data.message, 'green');
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage('&#10060; ' + data.message, 'red');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
            }
        })
        .catch(error => {
            showMessage('&#10060; Network error', 'red');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
        });
    });

    function showMessage(text, color) {
        msg.textContent = text;
        msg.className = 'mt-2 text-sm ' + (color === 'red' ? 'text-red-600' : 'text-green-600');
        msg.classList.remove('hidden');
    }
});
</script>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const wrapper = document.getElementById('dashNotifWrapper');
    const toggle = document.getElementById('dashNotifToggle');
    const panel = document.getElementById('dashNotifPanel');
const badge = document.getElementById('dashNotifBadge');
const label = document.getElementById('dashNotifLabel');   // ← NAYA
const list = document.getElementById('dashNotifList');
    const markAll = document.getElementById('dashNotifMarkAll');

    if (!wrapper) return;

    async function fetchNotifs() {
        try {
            const res = await fetch('{{ route('traveler.notifications.index') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            const count = data.unread_count;
badge.textContent = count > 99 ? '99+' : count;

if (count > 0) {
    // 🔥 FLASHY RED — vivid color + custom pulse + strong glow
    badge.classList.add('notif-flash-red');
    badge.classList.remove('text-white');

    label.classList.add('notif-flash-red');
    label.classList.remove('text-blue-100/80');
} else {
    // ⚪ Normal white
    badge.classList.remove('notif-flash-red');
    badge.classList.add('text-white');

    label.classList.remove('notif-flash-red');
    label.classList.add('text-blue-100/80');
}

            if (data.notifications.length === 0) {
                list.innerHTML = '<p class="p-4 text-sm text-gray-500 text-center">{{ __('messages.notifications_empty') }}</p>';
            } else {
                list.innerHTML = data.notifications.map(n => `
                    <a href="${n.url}" data-id="${n.id}" class="dash-notif-item block px-4 py-3 border-b hover:bg-gray-50 ${n.read ? 'bg-white' : 'bg-blue-50'}">
                        <p class="text-sm text-gray-800">${n.message}</p>
                        <p class="text-xs text-gray-500 mt-1">${n.created_at}</p>
                    </a>
                `).join('');

                list.querySelectorAll('.dash-notif-item').forEach(item => {
                    item.addEventListener('click', async function() {
                        await fetch(`{{ url('traveler/notifications') }}/${this.dataset.id}/read`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                    });
                });
            }
        } catch (e) {
            console.error('Notif fetch error:', e);
        }
    }

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) fetchNotifs();
    });

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) panel.classList.add('hidden');
    });

    markAll.addEventListener('click', async function(e) {
        e.preventDefault();
        await fetch('{{ route('traveler.notifications.mark-all-read') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        fetchNotifs();
    });

    fetchNotifs();
    setInterval(fetchNotifs, 30000);
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const localHour = new Date().getHours();
    const greetingMorning = @json(__('messages.greeting_morning'));
    const greetingAfternoon = @json(__('messages.greeting_afternoon'));
    const greetingEvening = @json(__('messages.greeting_evening'));

    let greetingText = greetingMorning;
    if (localHour >= 12 && localHour < 17) {
        greetingText = greetingAfternoon;
    } else if (localHour >= 17 || localHour < 5) {
        greetingText = greetingEvening;
    }

    const greetingElement = document.getElementById('dynamic-greeting');
    if (greetingElement) {
        greetingElement.innerText = greetingText;
    }
});
</script>
@endpush
{{-- File input filename display --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('fileInput');
    const label = document.getElementById('fileInputLabel');
    if (input && label) {
        input.addEventListener('change', function(e) {
            label.textContent = (e.target.files && e.target.files.length > 0)
                ? e.target.files[0].name
                : '{{ __("messages.no_file_chosen") }}';
        });
    }
});
</script>
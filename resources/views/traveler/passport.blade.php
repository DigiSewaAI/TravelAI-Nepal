@extends('layouts.public')

@section('title', __('messages.passport_title') . ' - TravelAI Nepal')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">

    {{-- ==========================================
        SECTION 1: PASSPORT HEADER
        ========================================== --}}
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 rounded-3xl p-8 mb-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-400 opacity-10 rounded-full blur-3xl -ml-10 -mb-10"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/50 flex items-center justify-center text-3xl font-bold shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight">{{ __('messages.passport_header_title') }}</h1>
                    <p class="text-blue-100 text-lg">{{ $user->name }}</p>
                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                            🏅 Level {{ $level ?? 0 }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                            ⭐ {{ $xp ?? 0 }} XP
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                            📅 {{ __('messages.passport_member_since') }} {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <span class="text-sm text-blue-200 block">{{ __('messages.passport_public_id_label') }}</span>
                <span class="font-mono text-xs bg-black/20 px-3 py-1 rounded-lg backdrop-blur-sm">
                    {{ $user->passport_public_id ? substr($user->passport_public_id, 0, 8) . '...' : 'Not Set' }}
                </span>
                @if($user->passport_privacy === 'public')
                    <span class="ml-2 text-xs bg-green-500/30 px-2 py-0.5 rounded-full border border-green-400">{{ __('messages.passport_privacy_public') }}</span>
                @else
                    <span class="ml-2 text-xs bg-gray-500/30 px-2 py-0.5 rounded-full border border-gray-400">{{ __('messages.passport_privacy_private') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================================
        SECTION 2: STATISTICS CARDS
        ========================================== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="text-3xl font-extrabold text-blue-600">{{ $passportData['statistics']['total_treks'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ __('messages.passport_stats_total_treks') }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="text-3xl font-extrabold text-green-600">{{ $passportData['statistics']['total_checkins'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ __('messages.passport_stats_checkins') }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="text-3xl font-extrabold text-purple-600">{{ $passportData['statistics']['unique_waypoints'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ __('messages.passport_stats_unique_places') }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="text-3xl font-extrabold text-orange-600">{{ $passportData['statistics']['highest_altitude'] ?? 0 }}m</div>
            <div class="text-sm text-gray-500 mt-1">{{ __('messages.passport_stats_highest_altitude') }}</div>
        </div>
    </div>

    {{-- ==========================================
        SECTION 3: ACTIVE TREK
        ========================================== --}}
    @if($passportData['active_journey'])
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl shadow-lg p-6 mb-8 border border-green-100">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <span class="animate-pulse text-green-500">●</span> {{ __('messages.passport_active_trek') }}
                    </h2>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $passportData['active_journey']['name'] }}</h3>
                    <p class="text-gray-600 text-sm">
                        {{ __('messages.passport_member_since') }}: {{ $passportData['active_journey']['start_date']?->format('M d, Y') ?? 'N/A' }}
                        • {{ $passportData['active_journey']['checkins'] }} {{ __('messages.passport_stats_checkins') }}
                        • {{ $passportData['active_journey']['unique_waypoints'] }} {{ __('messages.passport_stats_unique_places') }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 w-full md:w-64">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>{{ __('messages.passport_active_progress') }}</span>
                        <span>{{ round($passportData['active_journey']['progress'] ?? 0) }}%</span>
                    </div>
                    <div class="w-full h-3 bg-white rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-green-500 to-emerald-600 rounded-full transition-all duration-1000"
                             style="width: {{ min(100, $passportData['active_journey']['progress'] ?? 0) }}%">
                        </div>
                    </div>
                    @if($passportData['active_journey']['last_checkin'])
                        <p class="text-xs text-gray-500 mt-2">
                            📍 {{ __('messages.passport_active_last_checkin') }}: {{ $passportData['active_journey']['last_checkin']->checkpoint_name }}
                            ({{ $passportData['active_journey']['last_checkin']->scanned_at->diffForHumans() }})
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 rounded-2xl p-8 mb-8 text-center border-2 border-dashed border-gray-300">
            <p class="text-gray-500 text-lg">{{ __('messages.passport_no_active_trek') }}</p>
            <a href="{{ route('public.services.index') }}" class="inline-block mt-3 text-blue-600 font-semibold hover:underline">
                {{ __('messages.passport_no_active_trek_action') }}
            </a>
        </div>
    @endif

    {{-- ==========================================
        SECTION 4: DIGITAL STAMPS
        ========================================== --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            {{ __('messages.passport_stamps_title') }}
            <span class="text-sm font-normal text-gray-400">({{ $passportData['stamps']->count() }} {{ __('messages.passport_stamps_collected') }})</span>
        </h2>
        @if($passportData['stamps']->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($passportData['stamps'] as $stamp)
                    <div class="group bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 text-center border border-gray-200 shadow-sm hover:shadow-xl hover:scale-105 transition-all duration-300 cursor-default">
                        <div class="text-3xl mb-2">🏔️</div>
                        <div class="font-semibold text-sm text-gray-800 truncate" title="{{ $stamp['location'] }}">
                            {{ $stamp['location'] }}
                        </div>
                        <div class="text-xs text-gray-500">{{ $stamp['date']->format('M d, Y') }}</div>
                        @if($stamp['altitude'])
                            <div class="text-xs text-blue-600 font-medium">{{ number_format($stamp['altitude']) }}m</div>
                        @endif
                        <div class="mt-1 flex justify-center">
                            <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ __('messages.passport_verified') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-6">{{ __('messages.passport_stamps_empty') }}</p>
        @endif
    </div>

    {{-- ==========================================
        SECTION 5: ACHIEVEMENTS
        ========================================== --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            {{ __('messages.passport_achievements_title') }}
            <span class="text-sm font-normal text-gray-400">({{ $achievements->count() }} {{ __('messages.passport_achievements_unlocked') }})</span>
        </h2>
        @if($achievements->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($achievements as $item)
                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-4 text-center border border-amber-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-3xl mb-1">{{ $item->achievement->icon ?? '🏅' }}</div>
                        <div class="font-semibold text-sm text-gray-800">{{ $item->achievement->name }}</div>
                        <div class="text-[10px] text-gray-500">{{ $item->earned_at->format('M d, Y') }}</div>
                        <div class="text-xs text-amber-600 font-medium">+{{ $item->achievement->points }} XP</div>
                        @if($item->achievement->rarity)
                            <div class="mt-1">
                                <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full
                                    @if($item->achievement->rarity === 'legendary') bg-purple-200 text-purple-800
                                    @elseif($item->achievement->rarity === 'epic') bg-indigo-200 text-indigo-800
                                    @elseif($item->achievement->rarity === 'rare') bg-blue-200 text-blue-800
                                    @else bg-gray-200 text-gray-700 @endif">
                                    {{ $item->achievement->rarity }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-6">{{ __('messages.passport_achievements_empty') }}</p>
        @endif
    </div>

    {{-- ==========================================
        SECTION 6: TREK HISTORY
        ========================================== --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            {{ __('messages.passport_trek_history_title') }}
            <span class="text-sm font-normal text-gray-400">({{ $passportData['journeys']->count() }} treks)</span>
        </h2>
        @if($passportData['journeys']->count() > 0)
            <div class="space-y-3">
                @foreach($passportData['journeys'] as $journey)
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-blue-300 transition-colors">
                        <div>
                            <div class="font-semibold text-gray-800">{{ $journey['name'] }}</div>
                            <div class="text-sm text-gray-500 flex items-center gap-3 flex-wrap">
                                <span>{{ $journey['start_date']?->format('M d, Y') ?? 'N/A' }}</span>
                                <span>• {{ $journey['checkins'] }} check-ins</span>
                                <span>• {{ $journey['unique_waypoints'] }} unique places</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold mt-2 sm:mt-0
                            @if($journey['status'] === 'completed') bg-green-100 text-green-700
                            @elseif($journey['status'] === 'confirmed' || $journey['status'] === 'active') bg-blue-100 text-blue-700
                            @elseif($journey['status'] === 'cancelled') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($journey['status'] ?? 'Unknown') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-6">{{ __('messages.passport_trek_history_empty') }}</p>
        @endif
    </div>

    {{-- ==========================================
        SECTION 7: BACK TO DASHBOARD
        ========================================== --}}
    <div class="flex justify-between items-center flex-wrap gap-4">
        <a href="{{ route('traveler.dashboard') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold transition-colors">
            {{ __('messages.passport_back_to_dashboard') }}
        </a>
        <div class="text-xs text-gray-400">
            {{ __('messages.passport_last_updated') }} {{ now()->format('M d, Y H:i') }}
        </div>
    </div>
</div>

<style>
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }
    .duration-1000 {
        transition-duration: 1000ms;
    }
</style>
@endsection
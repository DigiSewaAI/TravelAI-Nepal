@extends('layouts.app')

@section('title', $profile['name'] . ' - Trek Passport')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-purple-700 px-8 py-10 text-white">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/50 flex items-center justify-center text-3xl font-bold shadow-lg">
                    {{ substr($profile['name'], 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold">🎒 Trek Passport</h1>
                    <p class="text-xl">{{ $profile['name'] }}</p>
                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                            🏅 Level {{ $level }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                            ⭐ {{ $xp }} XP
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_treks'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Treks</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $statistics['total_checkins'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Check-ins</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $statistics['unique_waypoints'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Places</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $statistics['highest_altitude'] ?? 0 }}m</div>
                <div class="text-xs text-gray-500">Highest Altitude</div>
            </div>
        </div>

        {{-- Stamps --}}
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📮 Stamps</h2>
            @if($stamps->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($stamps as $stamp)
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-3 text-center border border-gray-200">
                            <div class="text-2xl">🏔️</div>
                            <div class="font-semibold text-sm text-gray-800 truncate">{{ $stamp['location'] }}</div>
                            <div class="text-xs text-gray-500">{{ $stamp['date'] }}</div>
                            @if($stamp['altitude'])
                                <div class="text-xs text-blue-600">{{ number_format($stamp['altitude']) }}m</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No stamps collected yet.</p>
            @endif
        </div>

        {{-- Achievements --}}
        <div class="p-6 border-t border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-4">🏆 Achievements</h2>
            @if($achievements->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($achievements as $ach)
                        <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-3 text-center border border-amber-200">
                            <div class="text-2xl">{{ $ach['icon'] ?? '🏅' }}</div>
                            <div class="font-semibold text-sm text-gray-800">{{ $ach['name'] }}</div>
                            <div class="text-[10px] text-gray-500">{{ $ach['earned_at'] }}</div>
                            <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full
                                @if($ach['rarity'] === 'legendary') bg-purple-200 text-purple-800
                                @elseif($ach['rarity'] === 'epic') bg-indigo-200 text-indigo-800
                                @elseif($ach['rarity'] === 'rare') bg-blue-200 text-blue-800
                                @else bg-gray-200 text-gray-700 @endif">
                                {{ $ach['rarity'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No achievements unlocked yet.</p>
            @endif
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 text-center text-xs text-gray-400 border-t border-gray-100">
            <p>Verified Trek Passport • TravelAI Nepal</p>
            <p class="mt-1">This passport is publicly shared by the traveler.</p>
        </div>
    </div>

    <div class="mt-4 text-center text-sm text-gray-400">
        <a href="{{ route('home') }}" class="hover:text-blue-600">← Back to TravelAI Nepal</a>
    </div>
</div>
@endsection
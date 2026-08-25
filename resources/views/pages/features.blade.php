@extends('layouts.public')

@section('title', __('messages.features_page_title'))

@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.features_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.features_hero_subtitle') }}
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== STATS BAR ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">9+</p>
            <p class="text-sm text-gray-600">{{ __('messages.features_stat_features') }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
            <p class="text-2xl font-bold text-green-600">24/7</p>
            <p class="text-sm text-gray-600">{{ __('messages.features_stat_support') }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
            <p class="text-2xl font-bold text-purple-600">100%</p>
            <p class="text-sm text-gray-600">{{ __('messages.features_stat_offline') }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
            <p class="text-2xl font-bold text-yellow-600">AI</p>
            <p class="text-sm text-gray-600">{{ __('messages.features_stat_powered') }}</p>
        </div>
    </div>

    {{-- ========== FEATURES GRID ========== --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-blue-300">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition">
                <i class="fas fa-robot text-2xl text-blue-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.ai_trip_planner') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_ai_planner_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_ai_powered') }}</span>
        </div>
        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-emerald-300">
            <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                <i class="fas fa-qrcode text-2xl text-emerald-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.digital_trek_passport') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_digital_passport_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_qr_tech') }}</span>
        </div>
        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-red-300">
            <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center mb-4 group-hover:bg-red-600 group-hover:text-white transition">
                <i class="fas fa-sos text-2xl text-red-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.offline_emergency_sos') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_sos_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_safety') }}</span>
        </div>
        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-purple-300">
            <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                <i class="fas fa-chart-line text-2xl text-purple-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.agency_dashboard') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_dashboard_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_business') }}</span>
        </div>
        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-amber-300">
            <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                <i class="fas fa-film text-2xl text-amber-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.trek_memory_replay') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_memory_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_ai_powered') }}</span>
        </div>
        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-slate-300">
            <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center mb-4 group-hover:bg-slate-700 group-hover:text-white transition">
                <i class="fas fa-link text-2xl text-slate-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.smart_permits_blockchain') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_permits_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_coming_soon') }}</span>
        </div>
        <!-- Feature 7 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-pink-300">
            <div class="w-14 h-14 rounded-xl bg-pink-100 flex items-center justify-center mb-4 group-hover:bg-pink-600 group-hover:text-white transition">
                <i class="fas fa-mobile-alt text-2xl text-pink-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.pwa_offline') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_pwa_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_pwa') }}</span>
        </div>
        <!-- Feature 8 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-indigo-300">
            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                <i class="fas fa-shield-alt text-2xl text-indigo-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.realtime_safety_score') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_safety_score_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_safety') }}</span>
        </div>
        <!-- Feature 9 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-orange-300">
            <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                <i class="fas fa-language text-2xl text-orange-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">{{ __('messages.multilingual') }}</h3>
            <p class="text-gray-600 mt-1 text-sm">{{ __('messages.features_multilingual_desc') }}</p>
            <span class="inline-block mt-3 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">{{ __('messages.features_badge_language') }}</span>
        </div>
    </div>

    {{-- ========== CTA SECTION ========== --}}
    <div class="mt-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-2xl md:text-3xl font-bold">{{ __('messages.features_cta_title') }}</h2>
        <p class="text-blue-100 mt-2">{{ __('messages.features_cta_sub') }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                {{ __('messages.get_started_free') }}
            </a>
            <a href="{{ route('public.services.index') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                {{ __('messages.features_cta_explore') }}
            </a>
        </div>
    </div>
</div>
@endsection
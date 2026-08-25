@extends('layouts.public')

@section('title', __('messages.how_it_works_page_title'))

@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.how_it_works_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.how_it_works_hero_subtitle') }}
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== STATS BAR ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">500+</p>
            <p class="text-sm text-gray-600">{{ __('messages.how_it_works_stat_treks') }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
            <p class="text-2xl font-bold text-green-600">50+</p>
            <p class="text-sm text-gray-600">{{ __('messages.how_it_works_stat_agencies') }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
            <p class="text-2xl font-bold text-purple-600">100%</p>
            <p class="text-sm text-gray-600">{{ __('messages.how_it_works_stat_satisfied') }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
            <p class="text-2xl font-bold text-yellow-600">24/7</p>
            <p class="text-sm text-gray-600">{{ __('messages.how_it_works_stat_safety') }}</p>
        </div>
    </div>

    {{-- ========== FOR TRAVELERS ========== --}}
    <div class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-blue-600 pl-3 mb-6 flex items-center gap-2">
            <i class="fas fa-user text-blue-600"></i> {{ __('messages.how_it_works_for_travelers') }}
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-blue-300">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-600 transition">
                    <span class="text-2xl font-bold text-blue-600 group-hover:text-white transition">1</span>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-robot text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_traveler_step1_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_traveler_step1_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_ai_powered') }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-emerald-300">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-600 transition">
                    <span class="text-2xl font-bold text-emerald-600 group-hover:text-white transition">2</span>
                </div>
                <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-calendar-check text-2xl text-emerald-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_traveler_step2_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_traveler_step2_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_secure_booking') }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-red-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-600 transition">
                    <span class="text-2xl font-bold text-red-600 group-hover:text-white transition">3</span>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-qrcode text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_traveler_step3_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_traveler_step3_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_safety_first') }}</span>
            </div>
        </div>
    </div>

    {{-- ========== FOR AGENCIES ========== --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-purple-600 pl-3 mb-6 flex items-center gap-2">
            <i class="fas fa-building text-purple-600"></i> {{ __('messages.how_it_works_for_agencies') }}
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-purple-300">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-600 transition">
                    <span class="text-2xl font-bold text-purple-600 group-hover:text-white transition">1</span>
                </div>
                <div class="w-14 h-14 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-plus text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_agency_step1_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_agency_step1_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_easy_onboarding') }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-amber-300">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-600 transition">
                    <span class="text-2xl font-bold text-amber-600 group-hover:text-white transition">2</span>
                </div>
                <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-folder-plus text-2xl text-amber-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_agency_step2_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_agency_step2_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_full_control') }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-green-300">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-600 transition">
                    <span class="text-2xl font-bold text-green-600 group-hover:text-white transition">3</span>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">{{ __('messages.how_it_works_agency_step3_title') }}</h3>
                <p class="text-gray-500 text-sm text-center mt-2">{{ __('messages.how_it_works_agency_step3_desc') }}</p>
                <span class="inline-block mt-3 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ __('messages.how_it_works_badge_real_time') }}</span>
            </div>
        </div>
    </div>

    {{-- ========== CTA SECTION ========== --}}
    <div class="mt-16 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-2xl md:text-3xl font-bold">{{ __('messages.how_it_works_cta_title') }}</h2>
        <p class="text-blue-100 mt-2">{{ __('messages.how_it_works_cta_sub') }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="{{ route('public.services.index') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                <i class="fas fa-map-marked-alt mr-2"></i> {{ __('messages.explore_trips') }}
            </a>
            <a href="{{ route('register') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                <i class="fas fa-handshake mr-2"></i> {{ __('messages.become_partner') }}
            </a>
        </div>
    </div>
</div>
@endsection
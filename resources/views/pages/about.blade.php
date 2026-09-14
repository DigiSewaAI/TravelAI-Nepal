@extends('layouts.public')

@section('title', 'About TravelAI Nepal — AI-Powered Trekking Ecosystem')
@section('meta_description', 'Learn about TravelAI Nepal — an AI-powered trekking ecosystem connecting travelers with verified local providers. Safer, smarter, and more accessible Nepal adventures.')
@section('meta_keywords', 'About TravelAI Nepal, Nepal trekking company, AI travel platform Nepal, Himalayan trekking, local provider network, Nepal tourism innovation')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "AboutPage",
  "name": "About TravelAI Nepal",
  "description": "Learn about TravelAI Nepal — an AI-powered trekking ecosystem connecting travelers with verified local providers.",
  "url": "{{ url()->current() }}",
  "mainEntity": {
    "@@type": "Organization",
    "name": "TravelAI Nepal",
    "url": "{{ url('/') }}",
    "foundingDate": "2026",
    "foundingLocation": {
      "@@type": "Place",
      "name": "Kathmandu, Nepal"
    }
  }
}
</script>
@endpush

@section('content')

{{-- ========== HERO ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.about_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.about_hero_subtitle') }}
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- ========== MISSION & VISION ========== --}}
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
            <i class="fas fa-flag text-blue-600 text-3xl"></i>
            <h3 class="text-xl font-bold text-gray-800 mt-3">{{ __('messages.about_mission_title') }}</h3>
            <p class="text-gray-600 mt-2">{{ __('messages.about_mission_text') }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
            <i class="fas fa-eye text-blue-600 text-3xl"></i>
            <h3 class="text-xl font-bold text-gray-800 mt-3">{{ __('messages.about_vision_title') }}</h3>
            <p class="text-gray-600 mt-2">{{ __('messages.about_vision_text') }}</p>
        </div>
    </div>

    {{-- ========== STATS SECTION ========== --}}
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('messages.about_stats_title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-xl p-6 text-center border border-blue-100">
                <p class="text-3xl font-black text-blue-600">138+</p>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.about_stats_routes') }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                <p class="text-3xl font-black text-green-600">1,169+</p>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.about_stats_services') }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-6 text-center border border-purple-100">
                <p class="text-3xl font-black text-purple-600">726+</p>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.about_stats_providers') }}</p>
            </div>
            <div class="bg-yellow-50 rounded-xl p-6 text-center border border-yellow-100">
                <p class="text-3xl font-black text-yellow-600">4</p>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.about_stats_languages') }}</p>
            </div>
        </div>
    </div>

    {{-- ========== STORY SECTION ========== --}}
    <div class="bg-white rounded-xl shadow-md border p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.about_story_title') }}</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed">
            <p>{{ __('messages.about_story_p1') }}</p>
            <p>{{ __('messages.about_story_p2') }}</p>
            <p>{{ __('messages.about_story_p3') }}</p>
        </div>
    </div>

    {{-- ========== WHY US SECTION ========== --}}
    <div class="mb-12">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.about_why_title') }}</h2>
            <p class="text-gray-500 mt-2">{{ __('messages.about_why_subtitle') }}</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
                <i class="fas fa-robot text-blue-600 text-3xl"></i>
                <h4 class="font-bold text-gray-800 mt-3">{{ __('messages.about_why_ai_title') }}</h4>
                <p class="text-sm text-gray-500 mt-2">{{ __('messages.about_why_ai_desc') }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
                <i class="fas fa-percentage text-blue-600 text-3xl"></i>
                <h4 class="font-bold text-gray-800 mt-3">{{ __('messages.about_why_zero_title') }}</h4>
                <p class="text-sm text-gray-500 mt-2">{{ __('messages.about_why_zero_desc') }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
                <i class="fas fa-shield-alt text-blue-600 text-3xl"></i>
                <h4 class="font-bold text-gray-800 mt-3">{{ __('messages.about_why_safety_title') }}</h4>
                <p class="text-sm text-gray-500 mt-2">{{ __('messages.about_why_safety_desc') }}</p>
            </div>
        </div>
    </div>

    {{-- ========== VALUES ========== --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('messages.about_values_title') }}</h2>
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-handshake text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">{{ __('messages.about_value_trust') }}</h4>
            <p class="text-sm text-gray-500">{{ __('messages.about_value_trust_desc') }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-lightbulb text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">{{ __('messages.about_value_innovation') }}</h4>
            <p class="text-sm text-gray-500">{{ __('messages.about_value_innovation_desc') }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-heart text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">{{ __('messages.about_value_community') }}</h4>
            <p class="text-sm text-gray-500">{{ __('messages.about_value_community_desc') }}</p>
        </div>
    </div>

    {{-- ========== CTA SECTION ========== --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-3xl md:text-4xl font-bold mb-3">{{ __('messages.about_cta_title') }}</h2>
        <p class="text-blue-100 text-lg max-w-2xl mx-auto mb-6">{{ __('messages.about_cta_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('public.services.index') }}" 
               class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                <i class="fas fa-compass mr-2"></i>{{ __('messages.about_cta_explore') }}
            </a>
            <a href="{{ route('register') }}" 
               class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                <i class="fas fa-handshake mr-2"></i>{{ __('messages.about_cta_partner') }}
            </a>
        </div>
    </div>

</div>

@endsection
@extends('layouts.public')

@section('title', __('messages.about_page_title'))
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.about_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.about_hero_subtitle') }}
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- Mission Section --}}
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

    {{-- Story Section --}}
    <div class="bg-white rounded-xl shadow-md border p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.about_story_title') }}</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed">
            <p>{{ __('messages.about_story_p1') }}</p>
            <p>{{ __('messages.about_story_p2') }}</p>
            <p>{{ __('messages.about_story_p3') }}</p>
        </div>
    </div>

    {{-- Values --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('messages.about_values_title') }}</h2>
    <div class="grid md:grid-cols-3 gap-6">
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
</div>

@endsection
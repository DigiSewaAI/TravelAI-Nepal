@extends('layouts.public')

@section('title', __('messages.terms_page_title'))
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.terms_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.terms_hero_subtitle') }}
    </p>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
        <p class="text-gray-500 text-sm">{{ __('messages.terms_last_updated', ['date' => 'August 2026']) }}</p>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section1_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section1_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section2_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section2_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section3_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section3_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section4_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section4_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section5_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section5_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section6_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section6_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section7_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section7_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section8_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section8_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.terms_section9_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.terms_section9_text') }}</p>
        </div>

        <div class="pt-4 border-t">
            <p class="text-sm text-gray-500">{{ __('messages.terms_contact') }} <a href="mailto:support@travelai.com" class="text-blue-600 hover:underline">support@travelai.com</a></p>
        </div>
    </div>
</div>

@endsection
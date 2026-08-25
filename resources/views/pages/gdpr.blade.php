@extends('layouts.public')

@section('title', __('messages.gdpr_page_title'))
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.gdpr_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.gdpr_hero_subtitle') }}
    </p>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
        <p class="text-gray-500 text-sm">{{ __('messages.gdpr_last_updated', ['date' => 'August 2026']) }}</p>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-700 text-sm"><i class="fas fa-shield-alt mr-2"></i> {{ __('messages.gdpr_compliance_notice') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section1_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section1_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section2_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section2_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section3_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section3_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section4_title') }}</h2>
            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                <li><strong>{{ __('messages.gdpr_right_access') }}</strong> {{ __('messages.gdpr_right_access_desc') }}</li>
                <li><strong>{{ __('messages.gdpr_right_rectification') }}</strong> {{ __('messages.gdpr_right_rectification_desc') }}</li>
                <li><strong>{{ __('messages.gdpr_right_erasure') }}</strong> {{ __('messages.gdpr_right_erasure_desc') }}</li>
                <li><strong>{{ __('messages.gdpr_right_restrict') }}</strong> {{ __('messages.gdpr_right_restrict_desc') }}</li>
                <li><strong>{{ __('messages.gdpr_right_portability') }}</strong> {{ __('messages.gdpr_right_portability_desc') }}</li>
            </ul>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_right_contact') }} <a href="mailto:support@travelai.com" class="text-blue-600 hover:underline">support@travelai.com</a>.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section5_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section5_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section6_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section6_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.gdpr_section7_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.gdpr_section7_text') }}</p>
        </div>

        <div class="pt-4 border-t">
            <p class="text-sm text-gray-500">{{ __('messages.gdpr_contact_dpo') }} <a href="mailto:dpo@travelai.com" class="text-blue-600 hover:underline">dpo@travelai.com</a></p>
        </div>
    </div>
</div>

@endsection
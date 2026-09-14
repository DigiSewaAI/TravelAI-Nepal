@extends('layouts.public')

@section('title', 'Terms of Service — TravelAI Nepal | User Agreement')
@section('meta_description', 'Read TravelAI Nepal\'s Terms of Service. Understand user accounts, bookings, payments, cancellations, liability, and platform rules before using our service.')
@section('meta_keywords', 'terms of service TravelAI Nepal, user agreement, booking terms, cancellation policy, Nepal travel terms')

@push('head')
{{-- ========== JSON-LD: WebPage ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebPage",
  "name": "Terms of Service — TravelAI Nepal",
  "description": "Read TravelAI Nepal's Terms of Service — user accounts, bookings, payments, and platform rules.",
  "url": "{{ url()->current() }}",
  "publisher": {
    "@@type": "Organization",
    "name": "TravelAI Nepal",
    "url": "{{ url('/') }}"
  }
}
</script>

{{-- ========== JSON-LD: BreadcrumbList ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ url('/') }}"
    },
    {
      "@@type": "ListItem",
      "position": 2,
      "name": "Terms of Service",
      "item": "{{ url('/terms') }}"
    }
  ]
}
</script>
@endpush

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
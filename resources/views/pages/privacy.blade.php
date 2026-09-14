@extends('layouts.public')

@section('title', 'Privacy Policy — TravelAI Nepal | How We Protect Your Data')
@section('meta_description', 'Read TravelAI Nepal\'s Privacy Policy. Learn what data we collect, how we use it, our security measures, third-party sharing, and your data rights.')
@section('meta_keywords', 'privacy policy TravelAI Nepal, data protection, personal data, GDPR privacy, Nepal travel privacy')

@push('head')
{{-- ========== JSON-LD: WebPage ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebPage",
  "name": "Privacy Policy — TravelAI Nepal",
  "description": "Read TravelAI Nepal's Privacy Policy — data collection, usage, security, and user rights.",
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
      "name": "Privacy Policy",
      "item": "{{ url('/privacy') }}"
    }
  ]
}
</script>
@endpush

@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.privacy_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.privacy_hero_subtitle') }}
    </p>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
        <p class="text-gray-500 text-sm">{{ __('messages.privacy_last_updated', ['date' => 'August 2026']) }}</p>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section1_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section1_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section2_title') }}</h2>
            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                <li>{{ __('messages.privacy_section2_item1') }}</li>
                <li>{{ __('messages.privacy_section2_item2') }}</li>
                <li>{{ __('messages.privacy_section2_item3') }}</li>
                <li>{{ __('messages.privacy_section2_item4') }}</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section3_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section3_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section4_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section4_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section5_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section5_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section6_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section6_text') }}</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ __('messages.privacy_section7_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('messages.privacy_section7_text') }}</p>
        </div>

        <div class="pt-4 border-t">
            <p class="text-sm text-gray-500">{{ __('messages.privacy_contact') }} <a href="mailto:support@travelai.com" class="text-blue-600 hover:underline">support@travelai.com</a></p>
        </div>
    </div>
</div>

@endsection
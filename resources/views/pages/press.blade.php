@extends('layouts.public')

@section('title', 'Press & Media — TravelAI Nepal | News, Features & Awards')
@section('meta_description', 'Latest news, media coverage, interviews, and awards for TravelAI Nepal — Nepal\'s first AI-native trekking ecosystem. Read our press releases and announcements.')
@section('meta_keywords', 'TravelAI Nepal press, Nepal tourism news, travel tech awards, AI trekking news, Nepal startup media')

@push('head')
{{-- ========== JSON-LD: WebPage ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebPage",
  "name": "Press & Media — TravelAI Nepal",
  "description": "Latest news, media coverage, interviews, and awards for TravelAI Nepal.",
  "url": "{{ url()->current() }}"
}
</script>

{{-- ========== JSON-LD: NewsArticle (per press release) ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "NewsArticle",
  "headline": "{{ addslashes(__('messages.press_news1_title')) }}",
  "description": "{{ addslashes(__('messages.press_news1_desc')) }}",
  "datePublished": "2026-08-15",
  "dateModified": "{{ now()->toAtomString() }}",
  "author": {
    "@@type": "Organization",
    "name": "TravelAI Nepal"
  },
  "publisher": {
    "@@type": "Organization",
    "name": "TravelAI Nepal",
    "logo": {
      "@@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@@type": "WebPage",
    "@@id": "{{ url()->current() }}"
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "NewsArticle",
  "headline": "{{ addslashes(__('messages.press_news2_title')) }}",
  "description": "{{ addslashes(__('messages.press_news2_desc')) }}",
  "datePublished": "2026-07-15",
  "dateModified": "{{ now()->toAtomString() }}",
  "author": {
    "@@type": "Organization",
    "name": "TravelAI Nepal"
  },
  "publisher": {
    "@@type": "Organization",
    "name": "TravelAI Nepal",
    "logo": {
      "@@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@@type": "WebPage",
    "@@id": "{{ url()->current() }}"
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "NewsArticle",
  "headline": "{{ addslashes(__('messages.press_news3_title')) }}",
  "description": "{{ addslashes(__('messages.press_news3_desc')) }}",
  "datePublished": "2026-06-15",
  "dateModified": "{{ now()->toAtomString() }}",
  "author": {
    "@@type": "Organization",
    "name": "TravelAI Nepal"
  },
  "publisher": {
    "@@type": "Organization",
    "name": "TravelAI Nepal",
    "logo": {
      "@@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@@type": "WebPage",
    "@@id": "{{ url()->current() }}"
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
      "name": "Press & Media",
      "item": "{{ url('/press') }}"
    }
  ]
}
</script>
@endpush

@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.press_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.press_hero_subtitle') }}
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">15+</p>
            <p class="text-xs text-gray-500">{{ __('messages.press_stat_articles') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">8</p>
            <p class="text-xs text-gray-500">{{ __('messages.press_stat_interviews') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">5</p>
            <p class="text-xs text-gray-500">{{ __('messages.press_stat_awards') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">4</p>
            <p class="text-xs text-gray-500">{{ __('messages.press_stat_countries') }}</p>
        </div>
    </div>

    {{-- Press Releases --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('messages.press_latest_news') }}</h2>
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ __('messages.press_news1_title') }}</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> {{ __('messages.press_news1_date') }}</span>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ __('messages.press_badge_featured') }}</span>
                    </div>
                    <p class="text-gray-600 mt-2">{{ __('messages.press_news1_desc') }}</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">{{ __('messages.press_read_more') }} →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ __('messages.press_news2_title') }}</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> {{ __('messages.press_news2_date') }}</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ __('messages.press_badge_partnership') }}</span>
                    </div>
                    <p class="text-gray-600 mt-2">{{ __('messages.press_news2_desc') }}</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">{{ __('messages.press_read_more') }} →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ __('messages.press_news3_title') }}</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> {{ __('messages.press_news3_date') }}</span>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">{{ __('messages.press_badge_award') }}</span>
                    </div>
                    <p class="text-gray-600 mt-2">{{ __('messages.press_news3_desc') }}</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">{{ __('messages.press_read_more') }} →</a>
            </div>
        </div>
    </div>
</div>

@endsection
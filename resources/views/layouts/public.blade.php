<!DOCTYPE html>
@php
    $htmlLang = match(app()->getLocale()) {
        'np' => 'ne',
        'zh' => 'zh-Hans',
        default => app()->getLocale(),
    };
@endphp
<html lang="{{ $htmlLang }}">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon-128x128.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64x64.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=3') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico?v=3') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png?v=3') }}">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="theme-color" content="#2563eb">

    <!-- ========== PWA ========== -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ __('messages.app_name') }}">

    <title>{{ __('messages.app_name') }} | @yield('title', __('messages.home_default_title'))</title>

    <!-- ========== SEO META TAGS ========== -->
    <meta name="description" content="@yield('meta_description', __('messages.home_meta_description'))">
    <meta name="keywords" content="@yield('meta_keywords', __('messages.home_meta_keywords'))">
    <meta name="robots" content="index, follow">
    <meta name="author" content="{{ __('messages.app_name') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ========== HREFLANG (multilingual) ========== --}}
<link rel="alternate" hreflang="en" href="{{ url()->current() }}">
<link rel="alternate" hreflang="ne" href="{{ route('lang.switch', 'np') }}">
<link rel="alternate" hreflang="hi" href="{{ route('lang.switch', 'hi') }}">
<link rel="alternate" hreflang="zh-Hans" href="{{ route('lang.switch', 'zh') }}">
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- ========== OPEN GRAPH & TWITTER CARDS (DYNAMIC) ========== -->
@hasSection('og_meta')
    @yield('og_meta')
@else
    @include('partials.og-meta')
@endif

{{-- ========== PUSHED HEAD (JSON-LD from pages) ========== --}}
@stack('head')

{{-- ========== JSON-LD: Organization ========== --}}
<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@@type' => 'TravelAgency',
    'name' => 'TravelAI Nepal',
    'url' => url('/'),
    'logo' => asset('images/logo.png'),
    'description' => 'AI-powered trekking ecosystem connecting travelers with local agencies in Nepal.',
    'address' => [
        '@@type' => 'PostalAddress',
        'addressLocality' => 'Kathmandu',
        'addressRegion' => 'Bagmati',
        'addressCountry' => 'NP',
    ],
    'sameAs' => [
        'https://twitter.com/travelainepal',
        'https://www.instagram.com/travelainepal',
        'https://github.com/travelainepal',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

    <!-- ========== Tailwind, Font Awesome, Fonts ========== -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #ffffff; scroll-behavior: smooth; }
        .hero-bg { background: radial-gradient(circle at 10% 30%, rgba(0, 102, 204, 0.03) 0%, rgba(255,255,255,0) 70%); }
        .glass-card { background: rgba(255, 255, 255, 0.96); border: 1px solid rgba(0, 0, 0, 0.05); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.12); border-color: rgba(0, 100, 200, 0.2); }
        .step-card { transition: all 0.2s; }
        .step-card:hover { background: #f8fafc; border-color: #3b82f6; }
        .nav-link { position: relative; }
        .nav-link:after { content: ''; position: absolute; bottom: -4px; left: 0; width: 0%; height: 2px; background: #3b82f6; transition: 0.25s; }
        .nav-link:hover:after { width: 100%; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        .trek-card:hover { transform: translateY(-8px); transition: 0.25s ease; }

        /* Ad Carousel — plain CSS (Tailwind CDN मा निर्भर हुनु हुँदैन) */
        #adsTrack {
            display: flex !important;
            transition: transform 0.5s ease-out !important;
            will-change: transform;
        }
        #adsTrack .ad-slide {
            min-width: 100% !important;
            width: 100% !important;
            flex-shrink: 0 !important;
        }
        #adsTrack .ad-slide > a {
            display: block;
            position: relative;
        }
        #adsTrack .ad-slide > a > span {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 10;
        }
    </style>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registered successfully');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</head>
<body class="antialiased overflow-x-hidden">

    <!-- ======================= HEADER ======================= -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200/70 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-10 py-2 flex justify-between items-center gap-2">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center space-x-0 group flex-shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="{{ __('messages.app_name') }}" class="h-10 sm:h-14 lg:h-16 w-auto -mr-1" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-mountain text-xl sm:text-2xl text-blue-600\'></i>'">
                <span class="font-extrabold text-sm sm:text-xl lg:text-2xl tracking-tight text-gray-800 whitespace-nowrap">
                    {{ __('messages.app_name_short') }} <span class="text-blue-600">{{ __('messages.nepal') }}</span>
                </span>
            </a>

            <!-- Right side: Navigation + Currency + Language Switcher + Auth -->
            <div class="flex gap-1 lg:gap-3 text-gray-700 font-medium items-center min-w-0">
                <!-- Desktop Navigation Links (hidden on mobile) -->
                <a href="{{ url('/') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.home') }}</a>
                <a href="{{ url('/features') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.features') }}</a>
                <a href="{{ route('public.services.index') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.explore') }}</a>
                <a href="{{ route('public.shop.index') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.nav_shop') }}</a>
                <a href="{{ route('public.rental.index') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.nav_rental') }}</a>
                <a href="{{ route('pages.pricing') }}" class="nav-link text-xs lg:text-sm hidden lg:inline-block">{{ __('messages.pricing') }}</a>
                <a href="{{ url('/how-it-works') }}" class="nav-link text-xs lg:text-sm hidden xl:inline-block">{{ __('messages.how_it_works') }}</a>
                <a href="{{ route('public.providers.index') }}" class="nav-link text-xs lg:text-sm hidden xl:inline-block">{{ __('messages.providers') }}</a>

                <!-- Desktop Language Switcher (hidden on mobile) -->
                <div class="relative hidden lg:block">
                    <button type="button" class="flex items-center text-gray-700 hover:text-gray-900 text-sm font-medium" id="languageDropdown">
                        <span class="mr-1">
                            @if(session('locale') == 'hi') 🇮🇳
                            @elseif(session('locale') == 'zh') 🇨🇳
                            @elseif(session('locale') == 'np') 🇳🇵
                            @else 🇬🇧
                            @endif
                        </span>
                        <span class="text-sm font-medium">
                            @if(session('locale') == 'hi') हिन्दी
                            @elseif(session('locale') == 'zh') 中文
                            @elseif(session('locale') == 'np') नेपाली
                            @else English
                            @endif
                        </span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="languageMenu">
                        <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇬🇧 English</a>
                        <a href="{{ route('lang.switch', 'hi') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇮🇳 हिन्दी</a>
                        <a href="{{ route('lang.switch', 'zh') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇨🇳 中文</a>
                        <a href="{{ route('lang.switch', 'np') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇳🇵 नेपाली</a>
                    </div>
                </div>

                <!-- Desktop Currency Selector (hidden on mobile) -->
                <div class="hidden lg:flex items-center">
                    <select id="currency-selector" class="bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-400 transition">
                        <option value="USD" {{ session('display_currency', 'USD') === 'USD' ? 'selected' : '' }}>🇺🇸 USD</option>
                        <option value="NPR" {{ session('display_currency', 'USD') === 'NPR' ? 'selected' : '' }}>🇳🇵 NPR</option>
                    </select>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const selector = document.getElementById('currency-selector');
                        if (selector) {
                            selector.addEventListener('change', function() {
                                const baseUrl = '{{ url('/') }}';
                                window.location.href = baseUrl + '/currency/switch?currency=' + this.value;
                            });
                        }
                    });
                </script>

                <!-- Desktop Auth Buttons (hidden on mobile) -->
                <div class="hidden lg:flex items-center gap-2">
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.dashboard') }}</a>
                        @elseif(auth()->user()->isProviderOwner())
                            <a href="{{ route('provider.dashboard') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.dashboard') }}</a>
                        @elseif(auth()->user()->isTraveler())
                            <a href="{{ route('traveler.dashboard') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.dashboard') }}</a>
                        @else
                            <a href="{{ route('home') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.home') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="border border-red-500 text-red-500 hover:bg-red-50 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.login') }}</a>
                        <a href="{{ route('register') }}" class="border border-blue-600 text-blue-600 hover:bg-blue-50 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">{{ __('messages.register') }}</a>
                    @endauth
                </div>

                <!-- Mobile Auth Button (Login icon only) -->
                <div class="flex lg:hidden items-center gap-1">
                    @auth
                        <a href="@if(auth()->user()->isSuperAdmin()){{ route('admin.dashboard') }}@elseif(auth()->user()->isProviderOwner()){{ route('provider.dashboard') }}@elseif(auth()->user()->isTraveler()){{ route('traveler.dashboard') }}@else{{ route('home') }}@endif" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">{{ __('messages.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">{{ __('messages.login') }}</a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <button type="button" id="mobileMenuToggle" class="flex lg:hidden items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 transition" aria-label="Menu">
                    <i class="fas fa-bars text-lg text-gray-700"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown (hidden by default) -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-gray-200 bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.home') }}</a>
                <a href="{{ url('/features') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.features') }}</a>
                <a href="{{ route('public.services.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.explore') }}</a>
                <a href="{{ route('public.shop.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.nav_shop') }}</a>
                <a href="{{ route('public.rental.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.nav_rental') }}</a>
                <a href="{{ route('pages.pricing') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.pricing') }}</a>
                <a href="{{ url('/how-it-works') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.how_it_works') }}</a>
                <a href="{{ route('public.providers.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">{{ __('messages.providers') }}</a>

                <div class="border-t border-gray-200 my-2 pt-2">
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ __('messages.language') ?? 'Language' }}</span>
                        <select onchange="window.location.href='{{ url('/lang') }}/' + this.value" class="bg-gray-100 border border-gray-300 rounded-lg px-2 py-1 text-xs">
                            <option value="en" {{ session('locale') == 'en' || !session('locale') ? 'selected' : '' }}>🇬🇧 English</option>
                            <option value="hi" {{ session('locale') == 'hi' ? 'selected' : '' }}>🇮🇳 हिन्दी</option>
                            <option value="zh" {{ session('locale') == 'zh' ? 'selected' : '' }}>🇨🇳 中文</option>
                            <option value="np" {{ session('locale') == 'np' ? 'selected' : '' }}>🇳🇵 नेपाली</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between px-3 py-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Currency</span>
                        <select id="mobile-currency-selector" class="bg-gray-100 border border-gray-300 rounded-lg px-2 py-1 text-xs">
                            <option value="USD" {{ session('display_currency', 'USD') === 'USD' ? 'selected' : '' }}>🇺🇸 USD</option>
                            <option value="NPR" {{ session('display_currency', 'USD') === 'NPR' ? 'selected' : '' }}>🇳🇵 NPR</option>
                        </select>
                    </div>
                </div>

                @auth
                    <div class="border-t border-gray-200 pt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">
                                {{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-gray-200 pt-2">
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-center border border-blue-600 text-blue-600 hover:bg-blue-50">{{ __('messages.register') }}</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- ======================= FOOTER ======================= -->
    <footer class="bg-white border-t border-gray-200 pt-4 pb-8 px-6 md:px-10">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Logo + text -->
            <div>
                                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="{{ __('messages.app_name') }}"
                         class="h-10 sm:h-12 w-auto flex-shrink-0"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-mountain text-xl text-blue-600\'></i>'">
                    <span class="font-bold text-sm sm:text-base text-gray-800 leading-tight">{{ __('messages.app_name') }}</span>
                </a>
                <p class="text-sm text-gray-500 mt-8">{{ __('messages.footer_tagline') }}</p>
                <div class="flex space-x-4 mt-2">
                    <i class="fab fa-twitter text-gray-400 hover:text-blue-500"></i>
                    <i class="fab fa-instagram text-gray-400 hover:text-pink-500"></i>
                    <i class="fab fa-github text-gray-400 hover:text-gray-800"></i>
                </div>
            </div>

            <div>
            <h4 class="font-bold text-gray-800 mb-3">{{ __('messages.product') }}</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('pages.features') }}" class="hover:text-blue-600">{{ __('messages.features') }}</a></li>
                    <li><a href="{{ route('pages.pricing') }}" class="hover:text-blue-600">{{ __('messages.pricing') }}</a></li>
                    <li><a href="{{ route('public.providers.index') }}" class="hover:text-blue-600">{{ __('messages.providers') }}</a></li>
                    <li><a href="{{ route('public.wholesale.index') }}" class="hover:text-blue-600">{{ __('messages.nav_wholesale') }}</a></li>
                    <li><a href="{{ route('safety.index') }}" class="hover:text-blue-600">🛡️ {{ __('messages.travel_safety') }}</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-blue-600">{{ __('messages.become_partner') }}</a></li>
                </ul>
            </div>

            <div>
            <h4 class="font-bold text-gray-800 mb-3">{{ __('messages.company') }}</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('pages.about') }}" class="hover:text-blue-600">{{ __('messages.about_nepal_trek') }}</a></li>
                    <li><a href="{{ route('pages.careers') }}" class="hover:text-blue-600">{{ __('messages.careers') }}</a></li>
                    <li><a href="{{ route('pages.press') }}" class="hover:text-blue-600">{{ __('messages.press') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="hover:text-blue-600">{{ __('messages.contact_us') }}</a></li>
                </ul>
            </div>

            <div>
            <h4 class="font-bold text-gray-800 mb-3">{{ __('messages.legal') }}</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('pages.privacy') }}" class="hover:text-blue-600">{{ __('messages.privacy_policy') }}</a></li>
                    <li><a href="{{ route('pages.terms') }}" class="hover:text-blue-600">{{ __('messages.terms_service') }}</a></li>
                    <li><a href="{{ route('pages.gdpr') }}" class="hover:text-blue-600">{{ __('messages.gdpr_data_safety') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-6 pt-6 text-center text-xs text-gray-400">
            {{ __('messages.footer_copyright', ['year' => date('Y')]) }}
        </div>
    </footer>

    <!-- Language Switcher Dropdown JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile menu toggle
            const mobileToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileToggle && mobileMenu) {
                mobileToggle.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-bars');
                        icon.classList.toggle('fa-times');
                    }
                });
            }

            // Mobile currency selector
            const mobileCurrency = document.getElementById('mobile-currency-selector');
            if (mobileCurrency) {
                mobileCurrency.addEventListener('change', function () {
                    const baseUrl = '{{ url('/') }}';
                    window.location.href = baseUrl + '/currency/switch?currency=' + this.value;
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownBtn = document.getElementById('languageDropdown');
            const dropdownMenu = document.getElementById('languageMenu');

            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
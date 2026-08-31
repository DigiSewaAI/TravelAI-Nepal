<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon-128x128.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64x64.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=3') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico?v=3') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png?v=3') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest?v=3') }}">
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

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', config('app.name') . ' | ' . __('messages.og_title_default'))">
    <meta property="og:description" content="@yield('og_description', __('messages.og_description_default'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('twitter_description', __('messages.twitter_description_default'))">
    <meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}">

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
<body class="antialiased">

    <!-- ======================= HEADER ======================= -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200/70 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 md:px-10 py-2 flex flex-wrap justify-between items-center">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center space-x-0 group">
                <img src="{{ asset('images/logo.png') }}" alt="{{ __('messages.app_name') }}" class="h-16 w-auto -mr-1" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-mountain text-2xl text-blue-600\'></i>'">
                <span class="font-extrabold text-2xl tracking-tight text-gray-800 hidden sm:inline">{{ __('messages.app_name_short') }} <span class="text-blue-600">{{ __('messages.nepal') }}</span></span>
            </a>

            <!-- Right side: Navigation + Currency + Language Switcher + Auth -->
            <div class="flex flex-wrap gap-3 md:gap-4 text-gray-700 font-medium mt-3 md:mt-0 items-center">
                <!-- Navigation Links (पहिले जस्तै) -->
                <a href="{{ url('/') }}" class="nav-link text-sm md:text-base">{{ __('messages.home') }}</a>
                <a href="{{ url('/features') }}" class="nav-link text-sm md:text-base">{{ __('messages.features') }}</a>
                <a href="{{ route('public.services.index') }}" class="nav-link text-sm md:text-base">{{ __('messages.explore') }}</a>
                <a href="{{ route('pages.pricing') }}" class="nav-link text-sm md:text-base">{{ __('messages.pricing') }}</a>
                <a href="{{ url('/how-it-works') }}" class="nav-link text-sm md:text-base">{{ __('messages.how_it_works') }}</a>
                <a href="{{ route('public.providers.index') }}" class="nav-link text-sm md:text-base">{{ __('messages.providers') }}</a>

                <!-- ✅ Language Switcher (Get Early Access को ठाउँमा) -->
                <div class="relative">
                    <button type="button" class="flex items-center text-gray-700 hover:text-gray-900 text-sm font-medium" id="languageDropdown" aria-haspopup="true">
                        <span class="mr-1">
                            @if(session('locale') == 'hi') 🇮🇳
                            @elseif(session('locale') == 'zh') 🇨🇳
                            @else 🇬🇧
                            @endif
                        </span>
                        <span class="text-sm font-medium">
                            @if(session('locale') == 'hi') हिन्दी
                            @elseif(session('locale') == 'zh') 中文
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
                    </div>
                </div>

                <!-- Currency Selector -->
                <div class="flex items-center">
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

                <!-- Auth Buttons (पहिले जस्तै) -->
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
                <a href="{{ url('/') }}" class="flex flex-nowrap items-center space-x-1">
                    <img src="{{ asset('images/logo-icon.png') }}"
                         alt="{{ __('messages.app_name') }}"
                         class="h-32 w-auto min-w-[128px]"
                         onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}'">
                    <span class="font-bold text-xl text-gray-800 whitespace-nowrap">{{ __('messages.app_name') }}</span>
                </a>
                <p class="text-sm text-gray-500 mt-8">{{ __('messages.footer_tagline') }}</p>
                <div class="flex space-x-4 mt-2">
                    <i class="fab fa-twitter text-gray-400 hover:text-blue-500"></i>
                    <i class="fab fa-instagram text-gray-400 hover:text-pink-500"></i>
                    <i class="fab fa-github text-gray-400 hover:text-gray-800"></i>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">{{ __('messages.product') }}</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('pages.features') }}" class="hover:text-blue-600">{{ __('messages.features') }}</a></li>
                    <li><a href="{{ route('pages.pricing') }}" class="hover:text-blue-600">{{ __('messages.pricing') }}</a></li>
                    <li><a href="{{ route('public.providers.index') }}" class="hover:text-blue-600">{{ __('messages.providers') }}</a></li>
                        <li><a href="{{ route('safety.index') }}" class="hover:text-blue-600">🛡️ Travel Safety</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-blue-600">{{ __('messages.become_partner') }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">{{ __('messages.company') }}</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('pages.about') }}" class="hover:text-blue-600">{{ __('messages.about_nepal_trek') }}</a></li>
                    <li><a href="{{ route('pages.careers') }}" class="hover:text-blue-600">{{ __('messages.careers') }}</a></li>
                    <li><a href="{{ route('pages.press') }}" class="hover:text-blue-600">{{ __('messages.press') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="hover:text-blue-600">{{ __('messages.contact_us') }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">{{ __('messages.legal') }}</h4>
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
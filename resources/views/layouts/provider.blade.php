<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico?v=3') }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon-128x128.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64x64.png?v=3') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=3') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png?v=3') }}">
    <meta name="theme-color" content="#2563eb">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('messages.dashboard') . ' | TravelAI Nepal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover { background: #f3f4f6; }
        .sidebar-link.active { background: #eff6ff; color: #2563eb; }
        .stat-card { transition: all 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="overflow-x-hidden">
    <div class="flex h-screen relative">
        <!-- Mobile sidebar overlay -->
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="providerSidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r shadow-sm p-4 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
            <!-- Mobile close button -->
            <button type="button" id="sidebarClose" class="lg:hidden absolute top-3 right-3 w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-600" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
            <a href="{{ route('provider.dashboard') }}" class="flex items-center space-x-2 mb-6">
                <img src="{{ asset('images/logo.png') }}"
                     alt="TravelAI Nepal"
                     class="h-10 w-auto">
                <span class="font-bold text-gray-800 text-lg">TravelAI Nepal</span>
            </a>

            <!-- Provider Info with Logo -->
            @if(isset($provider) && $provider)
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center space-x-3">
                        <!-- 🔥 Provider Logo -->
                        @if($provider->logo_url)
                            <img src="{{ Storage::url($provider->logo_url) }}"
                                 alt="{{ $provider->name }} logo"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200"
                                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-building text-blue-600 text-sm"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-sm text-gray-700 block leading-tight" title="{{ $provider->name }}">{{ $provider->name }}</span>
                            @if($provider->verification_status === 'verified')
                                <span class="text-xs text-green-600"><i class="fas fa-check-circle"></i> {{ __('messages.verified') }}</span>
                            @else
                                <span class="text-xs text-yellow-600"><i class="fas fa-clock"></i> {{ ucfirst($provider->verification_status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <nav class="flex-1 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('provider.dashboard') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>{{ __('messages.dashboard') }}</span>
                </a>

                <!-- Services -->
                <a href="{{ route('provider.services.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.services.*') ? 'active' : '' }}">
                    <i class="fas fa-list w-5"></i>
                    <span>{{ __('messages.services') }}</span>
                </a>

                <a href="{{ route('provider.quotation.create') }}"
   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.quotation.*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar w-5"></i>
    <span>AI Quotation</span>
</a>

<!-- Quotation Requests -->
<a href="{{ route('provider.quotation-requests.index') }}"
   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.quotation-requests.*') ? 'active' : '' }}">
    <i class="fas fa-inbox w-5"></i>
    <span>Quotation Requests</span>
    @php
        $pendingCount = 0;
        if (Auth::check() && Auth::user()->getCurrentProvider()) {
            $pendingCount = \App\Models\QuotationRequest::where('provider_id', Auth::user()->getCurrentProvider()->id)
                            ->where('status', 'pending')->count();
        }
    @endphp
    @if($pendingCount > 0)
        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pendingCount }}</span>
    @endif
</a>                <!-- Bookings -->
                <a href="{{ route('provider.bookings.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>{{ __('messages.bookings') }}</span>
                </a>

                <!-- Subscriptions (Phase 8) -->
                <a href="{{ route('provider.subscriptions.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-crown w-5"></i>
                    <span>{{ __('messages.subscriptions') }}</span>
                </a>

                <!-- Verification (Phase 8) -->
                <a href="{{ route('provider.verification.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt w-5"></i>
                    <span>{{ __('messages.verification') }}</span>
                </a>

                                <!-- Payments (Phase 9) -->
                <a href="{{ route('provider.payments.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.payments.index') || request()->routeIs('provider.payments.detail') || request()->routeIs('provider.payments.show') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>{{ __('messages.payments') }}</span>
                </a>

                <!-- PHASE 7E.1b — Payment Methods Settings -->
                @if(Route::has('provider.settings.payment-methods.index'))
                <a href="{{ route('provider.settings.payment-methods.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.settings.payment-methods.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet w-5"></i>
                    <span>{{ __('messages.pm_page_title') }}</span>
                </a>
                @endif

                <!-- Invoices (Phase 13) -->
                @if(Route::has('provider.invoices.index'))
                <a href="{{ route('provider.invoices.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-5"></i>
                    <span>{{ __('messages.invoices') }}</span>
                </a>
                @endif

                <!-- Profile -->
                <a href="{{ route('provider.profile') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.profile') ? 'active' : '' }}">
                    <i class="fas fa-user w-5"></i>
                    <span>{{ __('messages.profile') }}</span>
                </a>

                <!-- Team (Staff Management) -->
                <a href="{{ route('provider.staff.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.staff.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Team</span>
                </a>

                {{-- Analytics (Phase 11) — FIX-05 Phase 3: feature-gated --}}
@if(Auth::check() && Auth::user()->ownProvider()?->hasFeature('full_analytics'))
<a href="{{ route('provider.analytics.index') }}"
   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.analytics.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar w-5"></i>
    <span>{{ __('messages.analytics') }}</span>
</a>
@endif

                <!-- Check-ins (Phase 12) -->
                <a href="{{ route('provider.checkins.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.checkins.*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode w-5"></i>
                    <span>{{ __('messages.checkins') }}</span>
                </a>

                <!-- Safety (Phase 6) -->
<a href="{{ route('safety.index') }}"
   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
    <i class="fas fa-shield-alt w-5"></i>
        <span>{{ __('messages.safety') }}</span>
</a>
            </nav>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" class="flex items-center space-x-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>{{ __('messages.logout') }}</span>
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <!-- Header -->
                        <header class="bg-white border-b px-4 lg:px-6 py-3 lg:py-4 flex justify-between items-center shadow-sm gap-3">
                <!-- Left: Hamburger + Logo + Title -->
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <button type="button" id="sidebarToggle" class="lg:hidden flex-shrink-0 w-9 h-9 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-700" aria-label="Open menu">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <img src="{{ asset('images/logo.png') }}" alt="TravelAI Nepal" class="lg:hidden h-8 w-auto flex-shrink-0">
                    <h1 class="hidden lg:block text-xl font-semibold text-gray-800 truncate">@yield('header', __('messages.dashboard'))</h1>
                </div>

                <!-- Right: Language + User Dropdown -->
                <div class="flex items-center gap-2 lg:gap-3 flex-shrink-0">

                    <!-- Language Switcher -->
                    <div class="relative">
                        <button type="button" class="flex items-center gap-1 px-2 lg:px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-700 text-sm font-medium transition" id="languageDropdown" aria-haspopup="true">
                            <span>
                                @if(session('locale') == 'np') 🇳🇵
                                @else 🇬🇧
                                @endif
                            </span>
                            <span class="hidden sm:inline">
                                @if(session('locale') == 'np') नेपाली
                                @else English
                                @endif
                            </span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="languageMenu">
                            <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇬🇧 English</a>
                            <a href="{{ route('lang.switch', 'np') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇳🇵 नेपाली</a>
                        </div>
                    </div>

                    <!-- User Dropdown (Name + Logout inside) -->
                    <div class="relative">
                        <button type="button" class="flex items-center gap-2 px-2 lg:px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-700 text-sm font-medium transition max-w-[140px] lg:max-w-[200px]" id="userDropdown" aria-haspopup="true">
                            <i class="fas fa-user-circle text-lg flex-shrink-0"></i>
                            <span class="truncate">{{ Auth::user()->name ?? __('messages.guest') }}</span>
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="userMenu">
                            <!-- User info header -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name ?? __('messages.guest') }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                            </div>
                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    {{ __('messages.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </header>

            <!-- 🔥 Main Content – Flash Messages REMOVED from Layout -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Dropdown Toggle JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownBtn = document.getElementById('languageDropdown');
            const dropdownMenu = document.getElementById('languageMenu');

            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                });

                // Click बाहिर गर्दा बन्द गर्न
                document.addEventListener('click', function (e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }

            // User dropdown toggle
            const userBtn = document.getElementById('userDropdown');
            const userMenu = document.getElementById('userMenu');
            if (userBtn && userMenu) {
                userBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', function (e) {
                    if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <!-- Mobile Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('providerSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');
            const close = document.getElementById('sidebarClose');

            function openSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            if (toggle) toggle.addEventListener('click', openSidebar);
            if (close) close.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            if (sidebar) {
                sidebar.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.innerWidth < 1024) closeSidebar();
                    });
                });
            }

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) closeSidebar();
            });
        });
    </script>
</body>
</html>
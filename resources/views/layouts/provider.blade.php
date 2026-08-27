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
<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white border-r shadow-sm p-4 flex flex-col">
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
                            <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                                 alt="{{ $provider->name }} logo"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200"
                                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-building text-blue-600 text-sm"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-sm text-gray-700 truncate block">{{ $provider->name }}</span>
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

                <!-- Bookings -->
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
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>{{ __('messages.payments') }}</span>
                </a>

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

                <!-- Analytics (Phase 11) -->
                <a href="{{ route('provider.analytics.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>{{ __('messages.analytics') }}</span>
                </a>

                <!-- Check-ins (Phase 12) -->
                <a href="{{ route('provider.checkins.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.checkins.*') ? 'active' : '' }}">
                    <i class="fas fa-qrcode w-5"></i>
                    <span>{{ __('messages.checkins') }}</span>
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
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
                <h1 class="text-xl font-semibold text-gray-800">@yield('header', __('messages.dashboard'))</h1>
                <div class="flex items-center space-x-4">
                    <!-- User Name -->
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-user-circle text-lg mr-1"></i>
                        {{ Auth::user()->name ?? __('messages.guest') }}
                    </span>

                    <!-- ✅ Language Switcher Dropdown -->
                    <div class="relative">
                        <button type="button" class="flex items-center text-gray-700 hover:text-gray-900" id="languageDropdown" aria-haspopup="true">
                            <span class="mr-1">
                                @if(session('locale') == 'np') 🇳🇵
                                @else 🇬🇧
                                @endif
                            </span>
                            <span class="text-sm font-medium">
                                @if(session('locale') == 'np') नेपाली
                                @else English
                                @endif
                            </span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="languageMenu">
                            <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🇬🇧 English
                            </a>
                            <a href="{{ route('lang.switch', 'np') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                🇳🇵 नेपाली
                            </a>
                        </div>
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">{{ __('messages.logout') }}</button>
                    </form>
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
        });
    </script>
</body>
</html>
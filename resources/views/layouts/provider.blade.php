@php
    $currentProvider = auth()->user()->getCurrentProvider();
    $providerTypeSlug = $currentProvider?->types->first()?->slug;
    $isProductProvider = in_array($providerTypeSlug, ['shop-owner', 'rental-provider', 'wholesale-provider']);
@endphp
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('messages.dashboard') . ' | TravelAI Nepal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
                <!-- Dashboard (सबैले देख्ने) -->
                <a href="{{ route('provider.dashboard') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>{{ __('messages.dashboard') }}</span>
                </a>

                <!-- === OWNER ONLY MENUS === -->
                @if(auth()->user()->isProviderOwner())
                    <a href="{{ route('provider.sos-alerts.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.sos-alerts.*') ? 'active' : '' }}">
                        <i class="fas fa-exclamation-triangle text-red-500 w-5 text-center"></i>
                        <span class="flex-1">{{ __('messages.sos_alerts_menu') }}</span>
<span id="sos-badge" class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full {{ (isset($unreadSosCount) && $unreadSosCount > 0) ? '' : 'hidden' }}">{{ $unreadSosCount ?? 0 }}</span>
                    </a>

                    <!-- Services (Only for Service Providers) -->
                    @if(!$isProductProvider)
                    <a href="{{ route('provider.services.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.services.*') ? 'active' : '' }}">
                        <i class="fas fa-list w-5"></i>
                        <span>{{ __('messages.services') }}</span>
                    </a>
                    @endif

                    <!-- Products & Orders (Only for Product Providers) -->
                    @if($isProductProvider)
                    <a href="{{ route('provider.products.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box w-5"></i>
                        <span>{{ __('messages.products') ?? 'Products' }}</span>
                    </a>
                    
                    <a href="{{ route('provider.orders.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-bag w-5"></i>
                        <span class="flex-1">{{ __('messages.orders') }}</span>
                        @php
                            $pendingOrdersCount = 0;
                            if ($currentProvider) {
                                $pendingOrdersCount = \App\Models\Order::whereHas('items', function($q) use ($currentProvider) {
                                    $q->where('provider_id', $currentProvider->id);
                                })->where('status', 'pending')->count();
                            }
                        @endphp
                        <span id="orders-badge" class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full {{ $pendingOrdersCount > 0 ? '' : 'hidden' }}">{{ $pendingOrdersCount }}</span>
                    </a>
                    @endif

                    <!-- AI Quotation & Quotation Requests (Only for Service Providers) -->
                    @if(!$isProductProvider)
                    <a href="{{ route('provider.quotation.create') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.quotation.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-5"></i>
                        <span>AI Quotation</span>
                    </a>

                    <a href="{{ route('provider.quotation-requests.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.quotation-requests.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox w-5"></i>
                        <span>Quotation Requests</span>
                        @php
                            $pendingCount = 0;
                            if (Auth::check() && Auth::user()->getCurrentProvider()) {
                                $pendingCount = \App\Models\QuotationRequest::where('provider_id', Auth::user()->getCurrentProvider()->id)->where('status', 'pending')->count();
                            }
                        @endphp
<span id="quotation-badge" class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5 {{ $pendingCount > 0 ? '' : 'hidden' }}">{{ $pendingCount }}</span>
                    </a>
                    @endif

                    <a href="{{ route('provider.subscriptions.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.subscriptions.*') ? 'active' : '' }}">
                        <i class="fas fa-crown w-5"></i>
                        <span>{{ __('messages.subscriptions') }}</span>
                    </a>

                    <a href="{{ route('provider.ads.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.ads.*') ? 'active' : '' }}">
                        <i class="fas fa-bullhorn text-amber-500 w-5 text-center"></i>
                        <span class="flex-1">{{ __('messages.ads_menu') }}</span>
                    </a>

                    <a href="{{ route('provider.verification.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span>{{ __('messages.verification') }}</span>
                    </a>

                    @if(Route::has('provider.settings.payment-methods.index'))
                    <a href="{{ route('provider.settings.payment-methods.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.settings.payment-methods.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-5"></i>
                        <span>{{ __('messages.pm_page_title') }}</span>
                    </a>
                    @endif

                    @if(Route::has('provider.invoices.index'))
                    <a href="{{ route('provider.invoices.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice w-5"></i>
                        <span>{{ __('messages.invoices') }}</span>
                    </a>
                    @endif

                    @if(Auth::check() && Auth::user()->ownProvider()?->hasFeature('full_analytics'))
                    <a href="{{ route('provider.analytics.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.analytics.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>{{ __('messages.analytics') }}</span>
                    </a>
                    @endif
                @endif
                <!-- === END OWNER ONLY MENUS === -->

                <!-- === PERMISSION GATED MENUS === -->
                @if(!$isProductProvider && auth()->user()->hasPermission('view_bookings'))
                <a href="{{ route('provider.bookings.index') }}" ...>
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>{{ __('messages.bookings') }}</span>
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_payments'))
                <a href="{{ route('provider.payments.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.payments.index') || request()->routeIs('provider.payments.detail') || request()->routeIs('provider.payments.show') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>{{ __('messages.payments') }}</span>
                </a>
                @endif

                @if(!$isProductProvider && auth()->user()->hasPermission('manage_checkins'))
                <a href="{{ route('provider.checkins.index') }}" ...>
                    <i class="fas fa-qrcode w-5"></i>
                    <span>{{ __('messages.checkins') }}</span>
                </a>
                @endif
                <!-- === END PERMISSION GATED MENUS === -->

                <!-- Profile (सबैले देख्ने) -->
                <a href="{{ route('provider.profile') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.profile') ? 'active' : '' }}">
                    <i class="fas fa-user w-5"></i>
                    <span>{{ __('messages.profile') }}</span>
                </a>

                <!-- Team (Owner Only) -->
                @if(auth()->user()->isProviderOwner())
                <a href="{{ route('provider.staff.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.staff.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Team</span>
                </a>
                @endif

                <!-- Safety (सबैले देख्ने) -->
                <a href="{{ route('safety.index') }}" class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
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
                                            {{-- Provider Notification Bell --}}
                        <div class="relative" id="providerNotifWrapper">
                            <button type="button" id="providerNotifToggle" class="relative text-gray-700 hover:text-blue-600 p-2 cursor-pointer">
                                <i class="fas fa-bell text-lg"></i>
                                <span id="providerNotifBadge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">0</span>
                            </button>
                            <div id="providerNotifPanel" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                                <div class="px-4 py-3 border-b flex justify-between items-center">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                    <a href="#" id="providerNotifMarkAll" class="text-xs text-blue-600 hover:underline">Mark all read</a>
                                </div>
                                <div id="providerNotifList">
                                    <p class="p-4 text-sm text-gray-500 text-center">No notifications</p>
                                </div>
                            </div>
                        </div>

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
                
                {{-- KYC Verification Alert --}}
                @php
                    $currentProvider = auth()->user()->getCurrentProvider();
                    $needsVerification = $currentProvider && $currentProvider->verification_status !== 'verified';
                @endphp

                @if($needsVerification)
                <div id="kycAlert" class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-xl shadow-sm relative flex items-start gap-3 transition-all duration-300">
                    <div class="flex-shrink-0 pt-0.5">
                        <i class="fas fa-exclamation-triangle text-amber-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amber-800">Account Unverified</h3>
                        <p class="text-sm text-amber-700 mt-1">Your account is currently unverified. Please upload your KYC documents to start selling.</p>
                        <div class="mt-3 flex gap-3">
                            <a href="{{ route('provider.verification.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors shadow-sm inline-flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i> Verify Now
                            </a>
                        </div>
                    </div>
                    <button onclick="document.getElementById('kycAlert').style.display='none'" class="text-amber-400 hover:text-amber-600 transition-colors p-1" title="Dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endif

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
        @auth
    <script>
        // REALTIME-INCONSISTENCY-01: Poll for alerts every 45 seconds
        setInterval(function() {
            fetch("{{ route('provider.alerts.live-counts') }}")
                .then(response => response.json())
                .then(data => {
                    // Update SOS Badge
                    const sosBadge = document.getElementById('sos-badge');
                    if (sosBadge) {
                        if (data.sos > 0) {
                            sosBadge.innerText = data.sos;
                            sosBadge.classList.remove('hidden');
                        } else {
                            sosBadge.classList.add('hidden');
                        }
                    }

                    // Update Quotation Badge
                    const quotationBadge = document.getElementById('quotation-badge');
                    if (quotationBadge) {
                        if (data.quotations > 0) {
                            quotationBadge.innerText = data.quotations;
                            quotationBadge.classList.remove('hidden');
                        } else {
                            quotationBadge.classList.add('hidden');
                        }
                    }
                                        // Update Orders Badge
                    const ordersBadge = document.getElementById('orders-badge');
                    if (ordersBadge) {
                        if (data.orders > 0) {
                            ordersBadge.innerText = data.orders;
                            ordersBadge.classList.remove('hidden');
                        } else {
                            ordersBadge.classList.add('hidden');
                        }
                    }
                })
                .catch(error => console.error('Error fetching alerts:', error));
        }, 45000); // 45 seconds
    </script>
    @endauth
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const wrapper = document.getElementById('providerNotifWrapper');
    const toggle = document.getElementById('providerNotifToggle');
    const panel = document.getElementById('providerNotifPanel');
    const badge = document.getElementById('providerNotifBadge');
    const list = document.getElementById('providerNotifList');
    const markAll = document.getElementById('providerNotifMarkAll');

    if (!wrapper) return;

    async function fetchAlerts() {
        try {
            const res = await fetch('{{ route('provider.notifications.index') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            if (data.notifications.length === 0) {
                list.innerHTML = '<p class="p-4 text-sm text-gray-500 text-center">No notifications</p>';
            } else {
                list.innerHTML = data.notifications.map(n => `
                    <a href="${n.url}" data-id="${n.id}" class="provider-notif-item block px-4 py-3 border-b hover:bg-gray-50 ${n.read ? 'bg-white' : 'bg-blue-50'}">
                        <p class="text-xs font-semibold text-gray-800">${n.title}</p>
                        <p class="text-sm text-gray-600 mt-0.5">${n.message}</p>
                        <p class="text-xs text-gray-400 mt-1">${n.created_at}</p>
                    </a>
                `).join('');

                list.querySelectorAll('.provider-notif-item').forEach(item => {
                    item.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const url = this.href;
                        try {
                            await fetch(`{{ url('provider/notifications') }}/${this.dataset.id}/read`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                            });
                        } catch (err) {}
                        window.location.href = url;
                    });
                });
            }
        } catch (e) {
            console.error('Provider notif fetch error:', e);
        }
    }

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) fetchAlerts();
    });

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) panel.classList.add('hidden');
    });

   markAll.addEventListener('click', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    try {
        await fetch('{{ route('provider.notifications.mark-all-read') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
    } catch (err) {
        console.error('Mark all read failed:', err);
    }
    fetchAlerts();
});

    fetchAlerts();
    setInterval(fetchAlerts, 30000);
});
</script>
    @stack('scripts')
</body>
</html>
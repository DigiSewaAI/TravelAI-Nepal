<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Provider Dashboard | TravelAI Nepal')</title>
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
            <div class="flex items-center space-x-2 mb-6">
                <i class="fas fa-mountain text-blue-600 text-xl"></i>
                <span class="font-bold text-gray-800 text-lg">TravelAI Nepal</span>
            </div>

            <!-- Provider Info -->
            @if(isset($provider) && $provider)
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-building text-gray-500"></i>
                        <span class="font-semibold text-sm text-gray-700">{{ $provider->name }}</span>
                    </div>
                    @if($provider->verification_status === 'verified')
                        <span class="text-xs text-green-600"><i class="fas fa-check-circle"></i> Verified</span>
                    @else
                        <span class="text-xs text-yellow-600"><i class="fas fa-clock"></i> {{ ucfirst($provider->verification_status) }}</span>
                    @endif
                </div>
            @endif

            <!-- Navigation -->
            <nav class="flex-1 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('provider.dashboard') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Services -->
                <a href="{{ route('provider.services.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.services.*') ? 'active' : '' }}">
                    <i class="fas fa-list w-5"></i>
                    <span>Services</span>
                </a>

                <!-- Bookings -->
                <a href="{{ route('provider.bookings.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Bookings</span>
                </a>

                <!-- Subscriptions (Phase 8) -->
                <a href="{{ route('provider.subscriptions.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-crown w-5"></i>
                    <span>Subscriptions</span>
                </a>

                <!-- Verification (Phase 8) -->
                <a href="{{ route('provider.verification.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt w-5"></i>
                    <span>Verification</span>
                </a>

                <!-- Payments (Phase 9) -->
                <a href="{{ route('provider.payments.index') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>Payments</span>
                </a>

                <!-- Profile -->
                <a href="{{ route('provider.profile') }}" 
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('provider.profile') ? 'active' : '' }}">
                    <i class="fas fa-user w-5"></i>
                    <span>Profile</span>
                </a>
            </nav>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" class="flex items-center space-x-2 w-full px-3 py-2 text-left text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
                <h1 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-user-circle text-lg mr-1"></i>
                        {{ Auth::user()->name ?? 'Guest' }}
                    </span>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-3 mb-4 rounded">
                        {{ session('info') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
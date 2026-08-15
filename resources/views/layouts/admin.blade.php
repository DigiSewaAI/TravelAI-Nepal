<!DOCTYPE html>
<html lang="en">
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
    <title>@yield('title', 'Admin Panel | TravelAI Nepal')</title>

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
        .admin-footer { border-top: 1px solid #e5e7eb; background: white; padding: 0.75rem 1.5rem; }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- ===== SIDEBAR ===== -->
        <div class="w-64 bg-white border-r shadow-sm p-4 flex flex-col overflow-y-auto">
            <!-- 🔥 LOGO (Text + Icon → Image + Text) -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 mb-6">
                <img src="{{ asset('images/logo.png') }}"
                     alt="TravelAI Nepal"
                     class="h-10 w-auto">
                <span class="font-bold text-gray-800 text-lg">TravelAI Admin</span>
            </a>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1">
                @if(Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                @endif

                @if(Route::has('admin.providers.index'))
                <a href="{{ route('admin.providers.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
                    <i class="fas fa-building w-5"></i>
                    <span>Providers</span>
                </a>
                @endif

                @if(Route::has('admin.users.index'))
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Users</span>
                </a>
                @endif

                @if(Route::has('admin.services.index'))
                <a href="{{ route('admin.services.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-list w-5"></i>
                    <span>Services</span>
                </a>
                @endif

                @if(Route::has('admin.bookings.index'))
                <a href="{{ route('admin.bookings.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Bookings</span>
                </a>
                @endif

                @if(Route::has('admin.subscriptions.index'))
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-crown w-5"></i>
                    <span>Subscriptions</span>
                </a>
                @endif

                @if(Route::has('admin.payments.index'))
                <a href="{{ route('admin.payments.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i>
                    <span>Payments</span>
                </a>
                @endif

                @if(Route::has('admin.reports.index'))
                <a href="{{ route('admin.reports.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Reports</span>
                </a>
                @endif

                @if(Route::has('admin.settings.index'))
                <a href="{{ route('admin.settings.index') }}"
                   class="sidebar-link flex items-center space-x-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
                @endif
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

        <!-- ===== MAIN CONTENT ===== -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header (Logo + Admin Name) -->
            <header class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="TravelAI Nepal"
                         class="h-8 w-auto">
                    <h1 class="text-xl font-semibold text-gray-800">@yield('header', 'Admin Panel')</h1>
                </div>
                <span class="text-sm text-gray-600">
                    <i class="fas fa-user-circle text-lg mr-1"></i>
                    {{ Auth::user()->name ?? 'Admin' }}
                </span>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 bg-white px-6 py-3 flex justify-between items-center text-xs text-gray-500">
                <span>© {{ date('Y') }} TravelAI Nepal · All rights reserved.</span>
                <span>TravelAI OS v1.0</span>
            </footer>
        </div>
    </div>
</body>
</html>
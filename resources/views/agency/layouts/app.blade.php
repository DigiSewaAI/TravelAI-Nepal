<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TravelAI Nepal Agency Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white border-r shadow-sm p-5 flex flex-col">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-mountain text-blue-600 text-xl"></i>
                <span class="font-bold text-gray-800">TravelAI Nepal</span>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="{{ route('agency.dashboard') }}" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('agency.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-chart-line w-5"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('agency.treks.index') }}" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('agency.treks.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-hiking w-5"></i><span>Treks</span>
                </a>
                <a href="{{ route('agency.bookings.index') }}" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('agency.bookings.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-calendar-check w-5"></i><span>Bookings</span>
                </a>
            </nav>
            <form method="POST" action="{{ route('agency.logout') }}">
                @csrf
                <button type="submit" class="flex items-center space-x-2 p-2 w-full text-left text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
                </button>
            </form>
        </div>

        <!-- Main content -->
        <div class="flex-1 overflow-y-auto">
            <header class="bg-white border-b px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">@yield('header')</h1>
                <div class="text-gray-600">Welcome, {{ Auth::guard('agency')->user()->name }}</div>
            </header>
            <main class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
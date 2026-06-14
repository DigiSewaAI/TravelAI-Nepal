<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="flex h-screen">
        <!-- Sidebar (simplified) -->
        <div class="w-64 bg-white border-r shadow-sm p-5">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-mountain text-blue-600 text-xl"></i>
                <span class="font-bold text-gray-800">TravelAI Nepal</span>
            </div>
            <nav class="space-y-2">
                <a href="{{ route('agency.dashboard') }}" class="flex items-center space-x-2 p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-chart-line w-5"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('agency.treks.index') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-hiking w-5"></i><span>Treks</span>
                </a>
                <a href="{{ route('agency.bookings.index') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-calendar-check w-5"></i><span>Bookings</span>
                </a>
                <form method="POST" action="{{ route('agency.logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 p-2 w-full text-left text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 overflow-y-auto">
            <header class="bg-white border-b px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <div class="text-gray-600">Welcome, {{ $agency->name }}</div>
            </header>
            <main class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Treks</p>
                                <p class="text-3xl font-bold">{{ $totalTreks }}</p>
                            </div>
                            <i class="fas fa-hiking text-3xl text-blue-400"></i>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Bookings</p>
                                <p class="text-3xl font-bold">{{ $totalBookings }}</p>
                            </div>
                            <i class="fas fa-users text-3xl text-green-400"></i>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Pending Bookings</p>
                                <p class="text-3xl font-bold">{{ $pendingBookings }}</p>
                            </div>
                            <i class="fas fa-clock text-3xl text-yellow-400"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h2 class="text-lg font-semibold mb-4">Recent Bookings</h2>
                    @if($recentBookings->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="text-left py-2">Trekker</th>
                                        <th class="text-left py-2">Trek</th>
                                        <th class="text-left py-2">Start Date</th>
                                        <th class="text-left py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBookings as $booking)
                                    <tr>
                                        <td class="py-2">{{ $booking->trekker->name ?? 'N/A' }}</td>
                                        <td class="py-2">{{ $booking->trek->name }}</td>
                                        <td class="py-2">{{ $booking->start_date->format('Y-m-d') }}</td>
                                        <td class="py-2">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                                                @elseif($booking->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No bookings yet.</p>
                    @endif
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
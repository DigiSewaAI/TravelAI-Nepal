<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .stat-card {
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
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
                <a href="{{ route('agency.dashboard') }}" class="flex items-center space-x-2 p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-chart-line w-5"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('agency.treks.index') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-hiking w-5"></i><span>Treks</span>
                </a>
                <a href="{{ route('agency.bookings.index') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-calendar-check w-5"></i><span>Bookings</span>
                </a>
                <!-- Agencies Link (सुपर एडमिनको लागि) -->
                <a href="{{ route('agency.agencies.index') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg {{ request()->routeIs('agency.agencies.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                    <i class="fas fa-building w-5"></i><span>Agencies</span>
                </a>
                <!-- Quick Actions -->
                <div class="border-t mt-4 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Quick Actions</p>
                    <a href="{{ route('agency.treks.create') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg text-sm">
                        <i class="fas fa-plus-circle text-blue-500 w-5"></i><span>New Trek</span>
                    </a>
                    <a href="{{ route('agency.agencies.create') }}" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg text-sm">
                        <i class="fas fa-user-plus text-green-500 w-5"></i><span>New Agency</span>
                    </a>
                </div>
            </nav>
            <form method="POST" action="{{ route('agency.logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="flex items-center space-x-2 p-2 w-full text-left text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <div class="text-gray-600 text-sm">
                    Welcome, <span class="font-semibold">{{ $agency->name }}</span>
                    @if($agency->role === 'super_admin')
                        <span class="ml-2 px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Super Admin</span>
                    @endif
                </div>
            </div>

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

            {{-- ===== Super Admin Sections ===== --}}
            @if($agency->role === 'super_admin' && isset($totalAgencies))
                {{-- 1. Statistics Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                        <p class="text-gray-500 text-xs uppercase">Total Treks</p>
                        <p class="text-2xl font-bold">{{ $totalTreks }}</p>
                    </div>
                    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
                        <p class="text-gray-500 text-xs uppercase">Total Bookings</p>
                        <p class="text-2xl font-bold">{{ $totalBookings }}</p>
                    </div>
                    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
                        <p class="text-gray-500 text-xs uppercase">Pending</p>
                        <div class="flex items-center space-x-2">
                            <p class="text-2xl font-bold {{ $pendingBookings > 0 ? 'text-yellow-600' : 'text-green-600' }}">{{ $pendingBookings }}</p>
                            @if($pendingBookings == 0)
                                <span class="text-xs text-green-500">✓ All clear</span>
                            @endif
                        </div>
                    </div>
                    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
                        <p class="text-gray-500 text-xs uppercase">Agencies</p>
                        <p class="text-2xl font-bold">{{ $totalAgencies }}</p>
                    </div>
                    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-pink-500">
                        <p class="text-gray-500 text-xs uppercase">Trekkers</p>
                        <p class="text-2xl font-bold">{{ $totalTrekkers }}</p>
                    </div>
                </div>

                {{-- 2. Charts Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm border">
                        <h3 class="font-semibold text-gray-700 mb-3">📈 Bookings Trend (Last 30 Days)</h3>
                        @if($bookingsTrend->count() > 0)
                            <canvas id="trendChart" height="150"></canvas>
                        @else
                            <p class="text-gray-400 text-center py-8">No booking data in last 30 days</p>
                        @endif
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border">
                        <h3 class="font-semibold text-gray-700 mb-3">🏆 Top 5 Treks</h3>
                        @if($topTreks->count() > 0)
                            <canvas id="topTreksChart" height="150"></canvas>
                        @else
                            <p class="text-gray-400 text-center py-8">No trek data available</p>
                        @endif
                    </div>
                </div>

                {{-- 3. Agency Overview with Actions --}}
                <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">🏢 Agency Overview</h2>
                        <a href="{{ route('agency.agencies.index') }}" class="text-sm text-blue-600 hover:underline">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Name</th>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Email</th>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Treks</th>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Bookings</th>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Role</th>
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agencies as $agt)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 text-sm">
    <a href="{{ route('agency.agencies.show', $agt->id) }}" 
       class="text-blue-600 hover:underline font-medium">
        {{ $agt->name }}
    </a>
</td>
                                    <td class="py-2 text-sm">{{ $agt->email }}</td>
                                    <td class="py-2 text-sm">{{ $agt->treks_count ?? 0 }}</td>
                                    <td class="py-2 text-sm">{{ $agt->bookings_count ?? 0 }}</td>
                                    <td class="py-2 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($agt->role == 'super_admin') bg-purple-100 text-purple-800
                                            @elseif($agt->role == 'admin') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $agt->role ?? 'agency' }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-sm">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('agency.agencies.edit', $agt->id) }}" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($agt->role !== 'super_admin')
                                                <form method="POST" action="{{ route('agency.agencies.toggle-status', $agt->id) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Toggle Role (agency ↔ admin)">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('agency.agencies.destroy', $agt->id) }}" class="inline" onsubmit="return confirm('Delete this agency?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs italic">(protected)</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. Two-column: Recent Bookings & Recent Activities --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border p-5">
                        <h2 class="text-lg font-semibold mb-4">📋 Recent Bookings</h2>
                        @if($recentBookings->count())
                            <div class="space-y-3">
                                @foreach($recentBookings as $booking)
                                <div class="flex justify-between items-center border-b pb-2">
                                    <div>
                                        <p class="font-medium">{{ $booking->trekker->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">{{ $booking->trek->name }} • {{ $booking->start_date->format('Y-m-d') }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No bookings yet.</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border p-5">
                        <h2 class="text-lg font-semibold mb-4">🔄 Recent Activities</h2>
                        @if(isset($recentActivities) && $recentActivities->count())
                            <div class="space-y-3">
                                @foreach($recentActivities as $activity)
                                <div class="flex items-start space-x-3 border-b pb-2">
                                    <span class="text-sm">
                                        @if($activity->type == 'booking')
                                            <i class="fas fa-calendar-check text-green-500"></i>
                                        @else
                                            <i class="fas fa-user-plus text-blue-500"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <p class="text-sm">{{ $activity->description }}</p>
                                        <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No recent activities.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($agency->role === 'super_admin' && isset($bookingsTrend) && $bookingsTrend->count() > 0)
                // Trend Chart
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                const trendData = @json($bookingsTrend);
                const labels = trendData.map(item => item.date);
                const data = trendData.map(item => item.total);

                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Bookings per day',
                            data: data,
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            @endif

            @if($agency->role === 'super_admin' && isset($topTreks) && $topTreks->count() > 0)
                // Top Treks Bar Chart
                const topCtx = document.getElementById('topTreksChart').getContext('2d');
                const topData = @json($topTreks);
                const topLabels = topData.map(item => item.name);
                const topValues = topData.map(item => item.bookings_count);

                new Chart(topCtx, {
                    type: 'bar',
                    data: {
                        labels: topLabels,
                        datasets: [{
                            label: 'Bookings',
                            data: topValues,
                            backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            @endif
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
@extends('layouts.admin')

@section('title', 'Admin Dashboard | TravelAI Nepal')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
        <p class="text-gray-500 text-xs">Providers</p>
        <p class="text-2xl font-bold">{{ $totalProviders }}</p>
    </div>
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
        <p class="text-gray-500 text-xs">Verified</p>
        <p class="text-2xl font-bold">{{ $verifiedProviders }}</p>
    </div>
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
        <p class="text-gray-500 text-xs">Pending</p>
        <p class="text-2xl font-bold">{{ $pendingProviders }}</p>
    </div>
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
        <p class="text-gray-500 text-xs">Services</p>
        <p class="text-2xl font-bold">{{ $totalServices }}</p>
    </div>
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-pink-500">
        <p class="text-gray-500 text-xs">Bookings</p>
        <p class="text-2xl font-bold">{{ $totalBookings }}</p>
    </div>
    <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-indigo-500">
        <p class="text-gray-500 text-xs">Revenue</p>
        <p class="text-2xl font-bold">Rs. {{ number_format($totalPayments, 0) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">📈 Bookings Trend (Last 30 Days)</h3>
        @if($bookingsTrend->count() > 0)
            <canvas id="trendChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No booking data</p>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">🏆 Top Providers by Services</h3>
        @if($topProviders->count() > 0)
            <canvas id="topProvidersChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No provider data</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-4">📋 Recent Providers</h2>
        @if($recentProviders->count() > 0)
            <div class="space-y-3">
                @foreach($recentProviders as $provider)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="font-medium">{{ $provider->name }}</span>
                            <span class="text-sm text-gray-500 ml-2">{{ $provider->user->email ?? '' }}</span>
                        </div>
                        <div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                                @elseif($provider->verification_status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($provider->verification_status) }}
                            </span>
                            <a href="{{ route('admin.providers.show', $provider) }}" class="text-blue-600 hover:text-blue-800 ml-2 text-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No providers yet.</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-4">📌 Recent Bookings</h2>
        @if($recentBookings->count() > 0)
            <div class="space-y-3">
                @foreach($recentBookings as $booking)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-medium">{{ $booking->traveler->name ?? 'Guest' }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->service->name ?? 'N/A' }}</p>
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
            <p class="text-gray-500 text-center py-4">No bookings yet.</p>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border p-5">
    <h2 class="text-lg font-semibold mb-4">⚡ Quick Actions</h2>
    <div class="flex flex-wrap gap-3">
        <!-- Providers -->
        <a href="{{ route('admin.providers.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-building mr-1"></i> Manage Providers
        </a>
        @if($pendingProviders > 0)
            <a href="{{ route('admin.providers.index') }}?status=pending" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-clock mr-1"></i> Verify Pending ({{ $pendingProviders }})
            </a>
        @endif

        <!-- Users -->
        @if(Route::has('admin.users.index'))
        <a href="{{ route('admin.users.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-users mr-1"></i> Manage Users
        </a>
        @endif

        <!-- Services -->
        @if(Route::has('admin.services.index'))
        <a href="{{ route('admin.services.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-list mr-1"></i> Manage Services
        </a>
        @endif

        <!-- Bookings -->
        @if(Route::has('admin.bookings.index'))
        <a href="{{ route('admin.bookings.index') }}" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-calendar-check mr-1"></i> Manage Bookings
        </a>
        @endif

        <!-- Subscriptions -->
        @if(Route::has('admin.subscriptions.index'))
        <a href="{{ route('admin.subscriptions.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-crown mr-1"></i> Subscriptions
        </a>
        @endif

        <!-- Payments -->
        @if(Route::has('admin.payments.index'))
        <a href="{{ route('admin.payments.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-credit-card mr-1"></i> Payments
        </a>
        @endif

        <!-- Reports -->
        @if(Route::has('admin.reports.index'))
        <a href="{{ route('admin.reports.index') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-file-alt mr-1"></i> Reports
        </a>
        @endif

        <!-- Settings -->
        @if(Route::has('admin.settings.index'))
        <a href="{{ route('admin.settings.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-cog mr-1"></i> Settings
        </a>
        @endif

        <!-- Legacy -->
        <a href="{{ route('agency.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-history mr-1"></i> Legacy Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($bookingsTrend->count() > 0)
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            const trendData = @json($bookingsTrend);
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(item => item.date),
                    datasets: [{
                        label: 'Bookings per day',
                        data: trendData.map(item => item.total),
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }
                }
            });
        @endif

        @if($topProviders->count() > 0)
            const topCtx = document.getElementById('topProvidersChart').getContext('2d');
            const topData = @json($topProviders);
            new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: topData.map(item => item.name),
                    datasets: [{
                        label: 'Services',
                        data: topData.map(item => item.services_count),
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }
                }
            });
        @endif
    });
</script>
@endsection
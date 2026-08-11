@extends('layouts.admin')

@section('title', 'Analytics | TravelAI Nepal')
@section('header', 'Platform Analytics')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
        <p class="text-gray-500 text-xs">Users</p>
        <p class="text-2xl font-bold">{{ $totalUsers }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
        <p class="text-gray-500 text-xs">Providers</p>
        <p class="text-2xl font-bold">{{ $totalProviders }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
        <p class="text-gray-500 text-xs">Services</p>
        <p class="text-2xl font-bold">{{ $totalServices }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
        <p class="text-gray-500 text-xs">Bookings</p>
        <p class="text-2xl font-bold">{{ $totalBookings }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-500">
        <p class="text-gray-500 text-xs">Revenue</p>
        <p class="text-2xl font-bold">Rs. {{ number_format($totalRevenue, 0) }}</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">📈 Revenue Trend</h3>
        @if($revenueByMonth->count() > 0)
            <canvas id="revenueChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No revenue data</p>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">📊 Booking Status</h3>
        @if($bookingStatus->count() > 0)
            <canvas id="statusChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No booking data</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">📈 Provider Growth</h3>
        @if($providerGrowth->count() > 0)
            <canvas id="growthChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No provider data</p>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">🏆 Top 5 Providers (Revenue)</h3>
        @if($topProviders->count() > 0)
            <canvas id="topProvidersChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">No provider data</p>
        @endif
    </div>
</div>

<!-- Recent Activities -->
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h3 class="font-semibold text-gray-700 mb-3">🔄 Recent Activities</h3>
    @if($recentActivities->count() > 0)
        <div class="space-y-2">
            @foreach($recentActivities as $activity)
                <div class="flex justify-between items-center border-b pb-2">
                    <span>{{ $activity->description }}</span>
                    <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-400 text-center py-4">No recent activities</p>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($revenueByMonth->count() > 0)
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: @json($revenueByMonth->pluck('year') . '-' . $revenueByMonth->pluck('month')->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($revenueByMonth->pluck('total')),
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        borderColor: '#3b82f6',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        @endif

        @if($bookingStatus->count() > 0)
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($bookingStatus->pluck('status')),
                    datasets: [{
                        data: @json($bookingStatus->pluck('count')),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        @endif

        @if($providerGrowth->count() > 0)
            new Chart(document.getElementById('growthChart'), {
                type: 'bar',
                data: {
                    labels: @json($providerGrowth->pluck('year') . '-' . $providerGrowth->pluck('month')->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))),
                    datasets: [{
                        label: 'New Providers',
                        data: @json($providerGrowth->pluck('total')),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        @endif

        @if($topProviders->count() > 0)
            new Chart(document.getElementById('topProvidersChart'), {
                type: 'bar',
                data: {
                    labels: @json($topProviders->pluck('name')),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($topProviders->pluck('payments_sum_amount')),
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        @endif
    });
</script>
@endsection
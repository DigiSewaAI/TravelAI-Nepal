@extends('layouts.provider')

@section('title', __('messages.analytics_page_title'))
@section('header', __('messages.analytics_header'))

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
        <p class="text-gray-500 text-xs">{{ __('messages.revenue') }}</p>
        <p class="text-2xl font-bold">Rs. {{ number_format($totalRevenue, 0) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
        <p class="text-gray-500 text-xs">{{ __('messages.customers') }}</p>
        <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
        <p class="text-gray-500 text-xs">{{ __('messages.bookings') }}</p>
        <p class="text-2xl font-bold">{{ $totalBookings }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
        <p class="text-gray-500 text-xs">{{ __('messages.avg_booking_value') }}</p>
        <p class="text-2xl font-bold">Rs. {{ number_format($avgBookingValue, 0) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-pink-500">
        <p class="text-gray-500 text-xs">{{ __('messages.conversion_rate') }}</p>
        <p class="text-2xl font-bold">{{ $conversionRate }}%</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.revenue_by_month') }}</h3>
        @if($revenueByMonth->count() > 0)
            <canvas id="revenueChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">{{ __('messages.no_revenue_data') }}</p>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.booking_status_chart') }}</h3>
        @if($bookingsByStatus->count() > 0)
            <canvas id="statusChart" height="150"></canvas>
        @else
            <p class="text-gray-400 text-center py-8">{{ __('messages.no_booking_data') }}</p>
        @endif
    </div>
</div>

<!-- Top Services & Recent Bookings -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.top_services_heading') }}</h3>
        @if($topServices->count() > 0)
            <div class="space-y-2">
                @foreach($topServices as $service)
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium">{{ $service->name }}</span>
                        <span class="text-sm text-gray-500">{{ $service->bookings_count }} {{ __('messages.bookings_count') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-4">{{ __('messages.no_services_yet_analytics') }}</p>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.recent_bookings_analytics') }}</h3>
        @if($recentBookings->count() > 0)
            <div class="space-y-2">
                @foreach($recentBookings as $booking)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-medium text-sm">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->service->name ?? __('messages.na') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($booking->status === 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            @if($booking->status === 'pending') {{ __('messages.pending') }}
                            @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                            @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                            @else {{ __('messages.cancelled') }} @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-4">{{ __('messages.no_recent_bookings') }}</p>
        @endif
    </div>
</div>

<!-- Export Button -->
<div class="mt-6">
    <a href="{{ route('provider.analytics.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
        <i class="fas fa-download mr-1"></i> {{ __('messages.export_csv') }}
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($revenueByMonth->count() > 0)
            const revCtx = document.getElementById('revenueChart').getContext('2d');
            const revData = @json($revenueByMonth);
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: revData.map(item => item.year + '-' + String(item.month).padStart(2, '0')),
                    datasets: [{
                        label: '{{ __('messages.revenue') }}',
                        data: revData.map(item => item.total),
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

        @if($bookingsByStatus->count() > 0)
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = @json($bookingsByStatus);
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'];
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(item => item.status),
                    datasets: [{
                        data: statusData.map(item => item.count),
                        backgroundColor: colors.slice(0, statusData.length),
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        @endif
    });
</script>
@endsection
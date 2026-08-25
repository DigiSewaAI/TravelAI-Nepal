@extends('layouts.provider')

@section('title', __('messages.dashboard_page_title'))
@section('header', __('messages.dashboard_header'))

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs uppercase">{{ __('messages.total_services') }}</p>
            <p class="text-2xl font-bold">{{ $totalServices ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
            <p class="text-gray-500 text-xs uppercase">{{ __('messages.total_bookings') }}</p>
            <p class="text-2xl font-bold">{{ $totalBookings ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs uppercase">{{ __('messages.pending') }}</p>
            <p class="text-2xl font-bold">{{ $pendingBookings ?? 0 }}</p>
        </div>
        <div class="stat-card bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500">
            <p class="text-gray-500 text-xs uppercase">{{ __('messages.provider_status') }}</p>
            <p class="text-lg font-bold">
                @if($provider->verification_status === 'verified')
                    <span class="text-green-600">{{ __('messages.verified') }} ✅</span>
                @else
                    <span class="text-yellow-600">{{ __('messages.pending_status') }} ⏳</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.bookings_trend') }}</h3>
            @if(isset($bookingsTrend) && $bookingsTrend->count() > 0)
                <canvas id="trendChart" height="150"></canvas>
            @else
                <p class="text-gray-400 text-center py-8">{{ __('messages.no_booking_data') }}</p>
            @endif
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <h3 class="font-semibold text-gray-700 mb-3">{{ __('messages.top_services') }}</h3>
            @if(isset($topServices) && $topServices->count() > 0)
                <canvas id="topServicesChart" height="150"></canvas>
            @else
                <p class="text-gray-400 text-center py-8">{{ __('messages.no_service_data') }}</p>
            @endif
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-4">{{ __('messages.recent_bookings') }}</h2>
        @if($recentBookings && $recentBookings->count() > 0)
            <div class="space-y-3">
                @foreach($recentBookings as $booking)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-medium">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->service->name ?? __('messages.unknown_service') }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->start_date->format('Y-m-d') }}</p>
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
            <p class="text-gray-500 text-center py-4">{{ __('messages.no_bookings_yet') }}</p>
        @endif
    </div>

    <!-- 🔥 Recent Check-ins with View Button -->
    <div class="bg-white rounded-xl shadow-sm border p-5 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ __('messages.recent_checkins') }}</h2>
            <a href="#" class="text-sm text-blue-600 hover:underline">{{ __('messages.view_all') }} →</a>
        </div>
        @if(isset($checkinHistory) && $checkinHistory->count() > 0)
            <div class="space-y-3 max-h-60 overflow-y-auto">
                @foreach($checkinHistory as $scan)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-medium text-sm">
                                {{ $scan->booking->traveler->name ?? __('messages.guest') }}
                                <span class="text-xs text-gray-500">({{ $scan->booking->service->name ?? __('messages.na') }})</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-map-pin text-blue-500 mr-1"></i> 
                                {{ $scan->checkpoint_name ?? __('messages.checkin_default') }}
                            </p>
                            <p class="text-[10px] text-gray-400">{{ $scan->scanned_at ? $scan->scanned_at->format('M d, Y H:i') : __('messages.na') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                <i class="fas fa-check-circle mr-1"></i> {{ __('messages.checked_in') }}
                            </span>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('messages.view') }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">{{ __('messages.no_checkins_yet') }}</p>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($bookingsTrend) && $bookingsTrend->count() > 0)
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

        @if(isset($topServices) && $topServices->count() > 0)
            const topCtx = document.getElementById('topServicesChart').getContext('2d');
            const topData = @json($topServices);
            new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: topData.map(item => item.name),
                    datasets: [{
                        label: 'Bookings',
                        data: topData.map(item => item.bookings_count),
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
@endpush
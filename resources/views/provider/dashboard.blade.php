@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.dashboard_page_title'))
@section('header', __('messages.dashboard_header'))

@section('content')

<!-- ========== 1. WELCOME BANNER ========== -->
<div class="relative rounded-3xl p-8 mb-8 text-white overflow-hidden shadow-xl" style="background: linear-gradient(to right, #1d4ed8, #4338ca, #6b21a8);">
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white opacity-10 rounded-full blur-2xl"></div>
    <div class="absolute bottom-0 right-32 -mb-16 w-48 h-48 bg-white opacity-10 rounded-full blur-xl"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black mb-1 flex items-center gap-2 text-white">
                {{ __('messages.welcome_back') }}, {{ $provider->name ?? Auth::user()->name }}! 👋
            </h2>
            <p class="text-blue-100 font-medium">{{ __('messages.dashboard_subtitle') }}</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-blue-200 font-semibold uppercase tracking-wider">{{ __('messages.status') }}</p>
                <p class="font-bold text-white">{{ ucfirst($provider->verification_status ?? 'Active') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- ========== 2. STATS CARDS ========== -->
<div class="grid grid-cols-1 md:grid-cols-2 {{ $isProductProvider ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-6 mb-8">
    <!-- Total Services/Products -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                {{ $isProductProvider ? 'Total Products' : __('messages.total_services') }}
            </p>
            <h3 class="text-3xl font-black text-gray-800">
                {{ $isProductProvider ? $totalProducts : $totalServices }}
            </h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-concierge-bell text-2xl text-blue-500"></i>
        </div>
    </div>

    <!-- Total Bookings/Orders -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                {{ $isProductProvider ? 'Total Orders' : __('messages.total_bookings') }}
            </p>
            <h3 class="text-3xl font-black text-gray-800">
                {{ $isProductProvider ? $recentOrders->count() : $totalBookings }}
            </h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-calendar-check text-2xl text-emerald-500"></i>
        </div>
    </div>

    <!-- Pending -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.pending') }}</p>
            <h3 class="text-3xl font-black text-gray-800">{{ $pendingBookings }}</h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-clock text-2xl text-amber-500"></i>
        </div>
    </div>

        @if(!$isProductProvider)
    <!-- AI Usage (Dark Card) - Only for Service Providers -->
    <div class="bg-gray-900 p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.2)] hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center relative overflow-hidden group text-white">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-robot text-6xl"></i>
        </div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">🤖 {{ __('messages.ai_requests') }}</p>
        @if($aiUsage['is_unlimited'])
            <h3 class="text-3xl font-black text-white">♾️ {{ __('messages.unlimited') }}</h3>
        @else
            <h3 class="text-3xl font-black {{ $aiUsage['remaining'] > 0 ? 'text-white' : 'text-red-400' }}">
                {{ $aiUsage['remaining'] }} <span class="text-sm font-medium text-gray-500">{{ __('messages.left') }}</span>
            </h3>
            <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ __('messages.of_this_month', ['limit' => $aiUsage['limit']]) }}</p>
        @endif
    </div>
    @endif
</div>

<!-- ========== 3. CHARTS SECTION ========== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Trend Chart -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-500"></i> 
                {{ $isProductProvider ? 'Orders Trend' : __('messages.bookings_trend') }}
            </h3>
        </div>
        <div class="relative w-full h-72">
            @php $hasTrendData = $isProductProvider ? ($ordersTrend->count() > 0) : ($bookingsTrend->count() > 0); @endphp
            @if($hasTrendData)
                <canvas id="trendChart"></canvas>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-chart-area text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-400 font-medium">{{ $isProductProvider ? 'No order data' : __('messages.no_booking_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Chart (Services/Products) -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-trophy text-amber-500"></i> 
                {{ $isProductProvider ? 'Top Products' : __('messages.top_services') }}
            </h3>
        </div>
        <div class="relative w-full h-72">
            @if($isProductProvider && $totalProducts > 0)
                <canvas id="topServicesChart"></canvas>
            @elseif(!$isProductProvider && $topServices->count() > 0)
                <canvas id="topServicesChart"></canvas>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-medal text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-400 font-medium">{{ $isProductProvider ? 'No product data' : __('messages.no_service_data') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========== 4. RECENT ACTIVITY ========== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Bookings / Orders -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">{{ $isProductProvider ? 'Recent Orders' : __('messages.recent_bookings') }}</h3>
            <a href="{{ $isProductProvider ? route('provider.orders.index') : route('provider.bookings.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">{{ __('messages.view_all') }} &rarr;</a>
        </div>
        <div class="flex-1 divide-y divide-gray-100">
            @if($isProductProvider)
                @forelse($recentOrders as $order)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ substr($order->customer_name ?? $order->user->name ?? 'G', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Order #{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
                                {{ ucfirst($order->status) }}
                            </span>
                            <a href="{{ route('provider.orders.show', $order) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 font-bold text-xs transition-colors flex items-center gap-1.5" title="View Order">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <i class="fas fa-shopping-bag text-4xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500 font-medium">No orders yet.</p>
                    </div>
                @endforelse
            @else
                @forelse($recentBookings as $booking)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ substr($booking->traveler->name ?? 'G', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                                <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $booking->service->name ?? __('messages.unknown_service') }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5"><i class="far fa-calendar-alt mr-1"></i> {{ $booking->start_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="{{ route('provider.bookings.show', $booking) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 font-bold text-xs transition-colors flex items-center gap-1.5" title="View Booking">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500 font-medium">{{ __('messages.no_bookings_yet') }}</p>
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    <!-- Recent Check-ins (Only for Service Providers) -->
    @if(!$isProductProvider)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">{{ __('messages.recent_checkins') }}</h3>
            <a href="{{ route('provider.checkins.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">{{ __('messages.view_map') }} &rarr;</a>
        </div>
        <div class="flex-1 divide-y divide-gray-100 max-h-[350px] overflow-y-auto custom-scrollbar">
            @forelse($checkinHistory as $scan)
                <div class="p-4 flex items-start justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $scan->booking->traveler->name ?? __('messages.guest') }}</p>
                            <p class="text-xs text-gray-600 mt-0.5">{{ __('messages.checked_in_at') }} <span class="font-semibold text-gray-800">{{ $scan->checkpoint_name ?? __('messages.checkin_default') }}</span></p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <i class="fas fa-map-signs text-4xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 font-medium">{{ __('messages.no_checkins_yet') }}</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#9ca3af';

        // Data for Chart
        @if($isProductProvider)
            const trendData = @json($ordersTrend ?? []);
        @else
            const trendData = @json($bookingsTrend ?? []);
        @endif
        
        @if($isProductProvider)
            const topData = @json($topProducts ?? []); 
        @else
            const topData = @json($topServices ?? []);
        @endif

        if (trendData.length > 0 && document.getElementById('trendChart')) {
            const ctx = document.getElementById('trendChart').getContext('2d');
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.map(item => item.date),
                    datasets: [{
                        label: '{{ $isProductProvider ? "Orders" : "Bookings" }}',
                        data: trendData.map(item => item.total),
                        backgroundColor: gradient,
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, 
                        x: { grid: { display: false } } 
                    }
                }
            });
        }

        if (topData.length > 0 && document.getElementById('topServicesChart')) {
            const ctx = document.getElementById('topServicesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topData.map(item => {
                        let name = item.product ? item.product.name : (item.name || 'Unknown');
                        return name.length > 15 ? name.substring(0, 15) + '...' : name;
                    }),
                    datasets: [{
                        label: '{{ $isProductProvider ? "Sales" : "Bookings" }}',
                        data: topData.map(item => item.sales_count ?? item.bookings_count ?? 0),
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                        borderRadius: 6,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, 
                        x: { grid: { display: false } } 
                    }
                }
            });
        }
    });
</script>
@endpush
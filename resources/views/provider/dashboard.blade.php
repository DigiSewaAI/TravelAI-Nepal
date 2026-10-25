@extends('layouts.provider')

@section('title', __('messages.dashboard_page_title'))
@section('header', __('messages.dashboard_header'))

@section('content')

<!-- ========== 1. WELCOME BANNER (Gradient) ========== -->
<div class="relative rounded-3xl p-8 mb-8 text-white overflow-hidden shadow-xl" style="background: linear-gradient(to right, #1d4ed8, #4338ca, #6b21a8);">
    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white opacity-10 rounded-full blur-2xl"></div>
    <div class="absolute bottom-0 right-32 -mb-16 w-48 h-48 bg-white opacity-10 rounded-full blur-xl"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black mb-1 flex items-center gap-2 text-white">
                Welcome back, {{ $provider->name ?? Auth::user()->name }}! 👋
            </h2>
            <p class="text-blue-100 font-medium">Here is what's happening with your business today.</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-blue-200 font-semibold uppercase tracking-wider">Status</p>
                <p class="font-bold text-white">{{ ucfirst($provider->verification_status ?? 'Active') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- ========== 2. STATS CARDS (Colored & Modern) ========== -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Services -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.total_services') }}</p>
            <h3 class="text-3xl font-black text-gray-800">{{ $totalServices ?? 0 }}</h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-concierge-bell text-2xl text-blue-500"></i>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.total_bookings') }}</p>
            <h3 class="text-3xl font-black text-gray-800">{{ $totalBookings ?? 0 }}</h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-calendar-check text-2xl text-emerald-500"></i>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.pending') }}</p>
            <h3 class="text-3xl font-black text-gray-800">{{ $pendingBookings ?? 0 }}</h3>
        </div>
        <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-clock text-2xl text-amber-500"></i>
        </div>
    </div>

    <!-- AI Usage (Dark Card) -->
    <div class="bg-gray-900 p-6 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.2)] hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center relative overflow-hidden group text-white">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-robot text-6xl"></i>
        </div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">🤖 AI Requests</p>
        @if($aiUsage['is_unlimited'])
            <h3 class="text-3xl font-black text-white">♾️ Unlimited</h3>
        @else
            <h3 class="text-3xl font-black {{ $aiUsage['remaining'] > 0 ? 'text-white' : 'text-red-400' }}">
                {{ $aiUsage['remaining'] }} <span class="text-sm font-medium text-gray-500">left</span>
            </h3>
            <p class="text-[11px] text-gray-400 mt-1 font-medium">of {{ $aiUsage['limit'] }} this month</p>
        @endif
    </div>
</div>

<!-- ========== 3. CHARTS SECTION ========== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Bookings Trend -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-500"></i> {{ __('messages.bookings_trend') }}
            </h3>
            <!-- Toggle Buttons for Trend -->
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                <button onclick="changeChartType('trend', 'line', this)" class="chart-btn active px-3 py-1.5 text-xs font-bold rounded-md transition-all"><i class="fas fa-chart-line mr-1"></i> Line</button>
                <button onclick="changeChartType('trend', 'bar', this)" class="chart-btn px-3 py-1.5 text-xs font-bold rounded-md transition-all text-gray-500 hover:text-gray-800"><i class="fas fa-chart-bar mr-1"></i> Bar</button>
            </div>
        </div>
        <div class="relative w-full h-72">
            @if(isset($bookingsTrend) && $bookingsTrend->count() > 0)
                <canvas id="trendChart"></canvas>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-chart-area text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-400 font-medium">{{ __('messages.no_booking_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Services -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-trophy text-amber-500"></i> {{ __('messages.top_services') }}
            </h3>
            <!-- Toggle Buttons for Top Services -->
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                <button onclick="changeChartType('top', 'bar', this)" class="chart-btn active px-3 py-1.5 text-xs font-bold rounded-md transition-all"><i class="fas fa-chart-bar mr-1"></i> Bar</button>
                <button onclick="changeChartType('top', 'doughnut', this)" class="chart-btn px-3 py-1.5 text-xs font-bold rounded-md transition-all text-gray-500 hover:text-gray-800"><i class="fas fa-chart-pie mr-1"></i> Pie</button>
            </div>
        </div>
        <div class="relative w-full h-72">
            @if(isset($topServices) && $topServices->count() > 0)
                <canvas id="topServicesChart"></canvas>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-medal text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-400 font-medium">{{ __('messages.no_service_data') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Active Chart Button Style */
    .chart-btn.active {
        background-color: #ffffff;
        color: #2563eb; /* text-blue-600 */
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
</style>

<!-- ========== 4. RECENT ACTIVITY (Lists) ========== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Bookings -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">{{ __('messages.recent_bookings') }}</h3>
            <a href="{{ route('provider.bookings.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">View All &rarr;</a>
        </div>
        <div class="flex-1 divide-y divide-gray-100">
            @if($recentBookings && $recentBookings->count() > 0)
                @foreach($recentBookings as $booking)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Avatar Circle -->
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ substr($booking->traveler->name ?? 'G', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $booking->traveler->name ?? __('messages.guest') }}</p>
                                <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $booking->service->name ?? __('messages.unknown_service') }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5"><i class="far fa-calendar-alt mr-1"></i> {{ $booking->start_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        
                        <!-- यहाँ View बटन स्पष्ट देखिने गरी राखिएको छ -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider 
                                @if($booking->status == 'pending') bg-amber-100 text-amber-800
                                @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                                @elseif($booking->status == 'completed') bg-emerald-100 text-emerald-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                            
                            <a href="{{ route('provider.bookings.show', $booking) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 font-bold text-xs transition-colors flex items-center gap-1.5" title="View Booking">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex flex-col items-center justify-center py-10">
                    <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 font-medium">{{ __('messages.no_bookings_yet') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Check-ins -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">{{ __('messages.recent_checkins') }}</h3>
            <a href="{{ route('provider.checkins.index') ?? '#' }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">View Map &rarr;</a>
        </div>
        <div class="flex-1 divide-y divide-gray-100 max-h-[350px] overflow-y-auto custom-scrollbar">
            @if(isset($checkinHistory) && $checkinHistory->count() > 0)
                @foreach($checkinHistory as $scan)
                    <div class="p-4 flex items-start justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <!-- Location Icon -->
                            <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-map-marker-alt text-sm"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">
                                    {{ $scan->booking->traveler->name ?? __('messages.guest') }}
                                </p>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    Checked-in at <span class="font-semibold text-gray-800">{{ $scan->checkpoint_name ?? __('messages.checkin_default') }}</span>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ $scan->scanned_at ? $scan->scanned_at->format('M d, Y - H:i') : __('messages.na') }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                <i class="fas fa-check-circle mr-1"></i> {{ __('messages.checked_in') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex flex-col items-center justify-center py-10">
                    <i class="fas fa-map-signs text-4xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 font-medium">{{ __('messages.no_checkins_yet') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Slight CSS tweak for scrollbar in check-ins -->
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#9ca3af';

        // Data from Backend
        const trendData = @json($bookingsTrend ?? []);
        const topData = @json($topServices ?? []);

        // Chart Instances (पुरानो चार्ट मेटाउन प्रयोग हुन्छ)
        let trendChartInstance = null;
        let topChartInstance = null;

        // ==========================================
        // 1. Render Functions
        // ==========================================
        
        function renderTrendChart(type) {
            if (trendChartInstance) trendChartInstance.destroy();
            const ctx = document.getElementById('trendChart')?.getContext('2d');
            if (!ctx) return;

            let dataset = {
                label: 'Bookings',
                data: trendData.map(item => item.total),
                borderColor: '#3b82f6',
                backgroundColor: type === 'bar' ? '#3b82f6' : 'rgba(59, 130, 246, 0.2)',
                borderWidth: type === 'bar' ? 0 : 3,
                borderRadius: type === 'bar' ? 6 : 0,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                fill: type === 'line',
                tension: 0.4,
            };

            // Line Chart मा Gradient हाल्ने
            if (type === 'line') {
                let gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
                gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
                gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                dataset.backgroundColor = gradientBlue;
            }

            trendChartInstance = new Chart(ctx, {
                type: type,
                data: {
                    labels: trendData.map(item => item.date),
                    datasets: [dataset]
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

        function renderTopChart(type) {
            if (topChartInstance) topChartInstance.destroy();
            const ctx = document.getElementById('topServicesChart')?.getContext('2d');
            if (!ctx) return;

            const isPie = type === 'doughnut' || type === 'pie';

            topChartInstance = new Chart(ctx, {
                type: type,
                data: {
                    labels: topData.map(item => item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name),
                    datasets: [{
                        label: 'Bookings',
                        data: topData.map(item => item.bookings_count),
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
                        borderRadius: isPie ? 0 : 6,
                        barThickness: isPie ? undefined : 30,
                        cutout: type === 'doughnut' ? '60%' : 0, // Donut को बीच खाली गर्न
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            display: isPie, // Pie/Donut भए Legend देखाउने
                            position: 'right' 
                        } 
                    },
                    scales: isPie ? { 
                        x: { display: false }, 
                        y: { display: false } 
                    } : { 
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, 
                        x: { grid: { display: false } } 
                    }
                }
            });
        }

        // ==========================================
        // 2. Toggle Button Logic
        // ==========================================
        
        window.changeChartType = function(chartName, type, btn) {
            // बटनको एक्टिभ कलर चेन्ज गर्ने
            const parent = btn.parentElement;
            parent.querySelectorAll('.chart-btn').forEach(b => {
                b.classList.remove('active', 'text-blue-600');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('active', 'text-blue-600');
            btn.classList.remove('text-gray-500');

            // चार्ट रि-रेन्डर गर्ने
            if (chartName === 'trend') {
                renderTrendChart(type);
            } else {
                renderTopChart(type);
            }
        };

        // ==========================================
        // 3. Initial Render (पहिलो पटक लोड हुँदा)
        // ==========================================
        if (trendData.length > 0) renderTrendChart('line');
        if (topData.length > 0) renderTopChart('bar');
    });
</script>
@endpush
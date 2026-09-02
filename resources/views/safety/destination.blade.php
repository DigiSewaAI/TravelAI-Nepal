@extends('layouts.public')

@section('title', $entity->name . ' - ' . __('messages.safety_status'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-home"></i> {{ __('messages.home') }}
        </a>
        <span class="text-gray-300">›</span>
        <a href="{{ route('safety.index') }}" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-shield-alt"></i> {{ __('messages.travel_safety') }}
        </a>
        <span class="text-gray-300">›</span>
        <span class="text-gray-700 font-medium">{{ $entity->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Header Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-3xl">🏔️</span>
                            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">{{ $entity->name }}</h1>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-tag text-blue-500"></i>
                                @if($entity instanceof \App\Models\Waypoint)
                                    {{ __('messages.waypoint') }}
                                @elseif($entity instanceof \App\Models\Route)
                                    {{ __('messages.route') }}
                                @elseif($entity instanceof \App\Models\Trek)
                                    {{ __('messages.trek') }}
                                @endif
                            </span>
                            @if($entity->altitude)
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-mountain text-gray-400"></i>
                                    {{ $entity->altitude }}m
                                </span>
                            @endif
                            @if($entity->latitude && $entity->longitude)
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-map-pin text-red-400"></i>
                                    {{ number_format($entity->latitude, 4) }}, {{ number_format($entity->longitude, 4) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('safety.index') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-medium text-gray-700 transition-colors">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_safety') }}
                    </a>
                </div>
            </div>

            <!-- Weather Widget -->
            @if(isset($weather) && $weather)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 flex items-center gap-1.5">
                            <i class="fas fa-cloud-sun text-yellow-500"></i> 
                            {{ __('messages.current_weather') }}
                        </p>
                        <div class="flex items-end gap-3 mt-1">
                            <span class="text-4xl font-extrabold text-blue-700">{{ $weather['temp'] }}°C</span>
                            <span class="text-gray-600 text-lg capitalize">{{ $weather['condition'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-1"><i class="fas fa-tint text-blue-400"></i> {{ $weather['humidity'] ?? 0 }}%</span>
                            <span class="inline-flex items-center gap-1"><i class="fas fa-wind text-gray-400"></i> {{ $weather['wind_speed'] ?? 0 }} m/s</span>
                            <span class="inline-flex items-center gap-1"><i class="fas fa-thermometer-half text-orange-400"></i> {{ __('messages.feels_like') }} {{ $weather['feels_like'] ?? $weather['temp'] }}°C</span>
                        </div>
                    </div>
                    @if(isset($weather['icon']))
                        <div class="flex-shrink-0">
                            <img src="https://openweathermap.org/img/wn/{{ $weather['icon'] }}@2x.png" 
                                 alt="{{ $weather['condition'] ?? 'Weather' }}" 
                                 class="w-20 h-20 -my-2">
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-3 border-t border-blue-100/50 pt-3">
                    <i class="fas fa-sync-alt text-blue-400"></i> 
                    {{ __('messages.last_updated') }}: {{ now()->format('M d, Y h:i A') }}
                </p>
            </div>
            @else
            <div class="bg-gray-50 border border-dashed border-gray-300 rounded-2xl p-6 text-center text-gray-400">
                <i class="fas fa-cloud-sun text-3xl block mb-2 text-gray-300"></i>
                <span class="text-sm">{{ __('messages.weather_unavailable') }}</span>
            </div>
            @endif

            <!-- Safety Status Card -->
            @php
                // ✅ $status variable Controller बाट आउँछ, जहाँ हामीले 'unknown' लाई 'normal' मा Set गरिसकेका छौं
                $statusText = $status['status'] ?? 'unknown';
                $score = $status['score'] ?? 0;
                
                $statusConfig = match($statusText) {
                    'avoid', 'critical' => ['color' => 'red', 'icon' => '🔴', 'label' => __('messages.status_avoid'), 'score' => max($score, 85)],
                    'high_risk', 'high' => ['color' => 'orange', 'icon' => '🟠', 'label' => __('messages.status_high_risk'), 'score' => max($score, 65)],
                    'caution', 'moderate' => ['color' => 'amber', 'icon' => '🟡', 'label' => __('messages.status_caution'), 'score' => max($score, 40)],
                    'normal', 'low' => ['color' => 'emerald', 'icon' => '🟢', 'label' => __('messages.status_normal'), 'score' => max($score, 15)],
                    default => ['color' => 'gray', 'icon' => '⚪', 'label' => __('messages.status_unknown'), 'score' => 0]
                };
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                    <h2 class="text-white font-bold flex items-center gap-2 text-lg">
                        <i class="fas fa-shield-alt"></i> {{ __('messages.current_safety_status') }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-full bg-{{ $statusConfig['color'] }}-100 flex items-center justify-center text-4xl shadow-inner">
                                {{ $statusConfig['icon'] }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-900">{{ $statusConfig['label'] }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('messages.risk_score') }}: 
                                    <span class="font-bold text-gray-700">{{ $statusConfig['score'] }} / 100</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full bg-{{ $statusConfig['color'] }}-500 transition-all duration-1000" 
                                     style="width: {{ $statusConfig['score'] }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                @if($statusText !== 'unknown')
                                    {{ __('messages.safety_status_tip') }}
                                @else
                                    {{ __('messages.no_safety_data') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    @if(isset($status['pending_count']) && $status['pending_count'] > 0)
                        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-center gap-2 text-sm text-amber-700">
                            <i class="fas fa-clock"></i>
                            {{ __('messages.incidents_under_review', ['count' => $status['pending_count']]) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Incidents List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-rose-700 px-6 py-4">
                    <h2 class="text-white font-bold flex items-center gap-2 text-lg">
                        <i class="fas fa-exclamation-triangle"></i> {{ __('messages.related_incidents') }}
                        @if(!empty($incidents) && $incidents->count() > 0)
                            <span class="bg-white/20 text-white text-xs px-2.5 py-0.5 rounded-full">{{ $incidents->count() }}</span>
                        @endif
                    </h2>
                </div>
                <div class="p-6">
                    @if(!empty($incidents) && $incidents->count() > 0)
                        <div class="space-y-4">
                            @foreach($incidents as $inc)
                                @php
                                    $severityIcon = match($inc->severity) {
                                        'critical' => '🔴',
                                        'high' => '🟠',
                                        'moderate' => '🟡',
                                        default => '🟢'
                                    };
                                    $severityColor = match($inc->severity) {
                                        'critical' => 'border-l-4 border-red-500',
                                        'high' => 'border-l-4 border-orange-500',
                                        'moderate' => 'border-l-4 border-amber-500',
                                        default => 'border-l-4 border-emerald-500'
                                    };
                                @endphp
                                <div class="bg-gray-50 rounded-xl p-5 hover:shadow-md transition-shadow {{ $severityColor }}">
                                    <div class="flex items-start gap-3">
                                        <span class="text-2xl">{{ $severityIcon }}</span>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('safety.incident', $inc->id) }}" 
                                               class="text-lg font-semibold text-gray-800 hover:text-blue-600 transition-colors">
                                                {{ $inc->title }}
                                            </a>
                                            <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-gray-500">
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fas fa-tag"></i> {{ str_replace('_', ' ', $inc->incident_type) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fas fa-circle" style="color: {{ match($inc->severity) { 'critical' => '#dc3545', 'high' => '#fd7e14', 'moderate' => '#ffc107', default => '#28a745' } }}"></i>
                                                    {{ ucfirst($inc->severity) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="far fa-clock"></i> {{ $inc->reported_at?->diffForHumans() }}
                                                </span>
                                            </div>
                                            @if($inc->description)
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ Str::limit($inc->description, 120) }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ route('safety.incident', $inc->id) }}" 
                                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                            {{ __('messages.view_details') }} <i class="fas fa-arrow-right text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">✅ {{ __('messages.no_active_incidents') }}</p>
                            <p class="text-sm text-gray-400 mt-1">{{ __('messages.all_clear_message') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50/60 border border-blue-100/70 rounded-2xl p-6">
                <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-3 text-lg">
                    <i class="fas fa-lightbulb text-yellow-500"></i> 
                    {{ __('messages.trekking_safety_tips') }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-2 bg-white/70 rounded-xl p-3">
                        <span class="text-emerald-500 text-sm">✓</span>
                        <span class="text-sm text-gray-700">{{ __('messages.tip_weather') }}</span>
                    </div>
                    <div class="flex items-start gap-2 bg-white/70 rounded-xl p-3">
                        <span class="text-emerald-500 text-sm">✓</span>
                        <span class="text-sm text-gray-700">{{ __('messages.tip_water_snacks') }}</span>
                    </div>
                    <div class="flex items-start gap-2 bg-white/70 rounded-xl p-3">
                        <span class="text-emerald-500 text-sm">✓</span>
                        <span class="text-sm text-gray-700">{{ __('messages.tip_inform_agency') }}</span>
                    </div>
                    <div class="flex items-start gap-2 bg-white/70 rounded-xl p-3">
                        <span class="text-emerald-500 text-sm">✓</span>
                        <span class="text-sm text-gray-700">{{ __('messages.tip_offline_maps') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-700 to-gray-900 px-5 py-3.5">
                    <h3 class="text-white font-semibold flex items-center gap-2 text-sm">
                        <i class="fas fa-chart-simple"></i> {{ __('messages.quick_stats') }}
                    </h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2.5">
                        <span class="text-gray-500 text-sm">{{ __('messages.altitude') }}</span>
                        <span class="font-medium text-gray-800">{{ $entity->altitude ?? 'N/A' }}m</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2.5">
                        <span class="text-gray-500 text-sm">{{ __('messages.type') }}</span>
                        <span class="font-medium text-gray-800">
                            @if($entity instanceof \App\Models\Waypoint) Waypoint
                            @elseif($entity instanceof \App\Models\Route) Route
                            @elseif($entity instanceof \App\Models\Trek) Trek
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 text-sm">{{ __('messages.safety_status') }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                            {{ $statusText === 'avoid' || $statusText === 'critical' ? 'bg-red-100 text-red-700' : 
                               ($statusText === 'high_risk' || $statusText === 'high' ? 'bg-orange-100 text-orange-700' : 
                                ($statusText === 'caution' || $statusText === 'moderate' ? 'bg-amber-100 text-amber-700' : 
                                 ($statusText === 'normal' || $statusText === 'low' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'))) }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Safety Advisory (if incidents) -->
            @if(!empty($incidents) && $incidents->count() > 0)
            <div class="bg-gradient-to-br from-red-50 to-orange-50 border border-red-200 rounded-2xl p-5">
                <h4 class="font-bold text-red-800 flex items-center gap-2 text-sm mb-3">
                    <i class="fas fa-triangle-exclamation"></i> {{ __('messages.safety_advisory') }}
                </h4>
                @php
                    $highest = $incidents->sortByDesc(function($i) {
                        return match($i->severity) {
                            'critical' => 4,
                            'high' => 3,
                            'moderate' => 2,
                            'low' => 1,
                            default => 0
                        };
                    })->first();
                @endphp
                @if($highest)
                    <p class="text-sm text-gray-700 mb-2">
                        <span class="font-bold">{{ __('messages.current_advisory') }}:</span>
                        @if(in_array($highest->severity, ['critical', 'high']))
                            <span class="text-red-700 font-bold">{{ __('messages.avoid_travel') }}</span>
                        @elseif($highest->severity === 'moderate')
                            <span class="text-amber-700 font-bold">{{ __('messages.exercise_caution') }}</span>
                        @else
                            <span class="text-emerald-700 font-bold">{{ __('messages.normal_precautions') }}</span>
                        @endif
                    </p>
                    <div class="text-xs text-gray-500 space-y-1">
                        <p><strong>{{ __('messages.confidence') }}:</strong> {{ ($highest->confidence_score ?? 0) * 100 }}%</p>
                        <p><strong>{{ __('messages.last_verified') }}:</strong> {{ $highest->last_verified_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>
            @endif

            <!-- Quick Links -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-3.5">
                    <h3 class="text-white font-semibold flex items-center gap-2 text-sm">
                        <i class="fas fa-link"></i> {{ __('messages.quick_links') }}
                    </h3>
                </div>
                <div class="p-4 space-y-1.5">
                    <a href="{{ route('safety.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 text-sm">
                        <i class="fas fa-shield-alt text-blue-500 w-5 text-center"></i>
                        {{ __('messages.safety_overview') }}
                    </a>
                    <a href="{{ route('public.providers.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 text-sm">
                        <i class="fas fa-building text-emerald-500 w-5 text-center"></i>
                        {{ __('messages.find_providers') }}
                    </a>
                    <a href="{{ route('home') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-gray-700 text-sm">
                        <i class="fas fa-home text-gray-400 w-5 text-center"></i>
                        {{ __('messages.home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
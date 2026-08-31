@extends('layouts.public')

@section('title', $entity->name . ' - ' . __('messages.safety_status'))

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('safety.index') }}">{{ __('messages.travel_safety') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $entity->name }}</li>
        </ol>
    </nav>

    <!-- Main Section -->
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-3">{{ $entity->name }}</h1>
            <p class="text-muted">
                @if($entity instanceof \App\Models\Waypoint)
                    <span class="badge bg-secondary">{{ __('messages.waypoint') }}</span>
                @elseif($entity instanceof \App\Models\Route)
                    <span class="badge bg-secondary">{{ __('messages.route') }}</span>
                @elseif($entity instanceof \App\Models\Trek)
                    <span class="badge bg-secondary">{{ __('messages.trek') }}</span>
                @endif
            </p>

            <!-- Safety Status Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">🛡️ {{ __('messages.current_safety_status') }}</h5>
                    <div class="d-flex align-items-center mt-2">
                        <div class="me-3" style="font-size: 3rem;">
                            @php
                                $statusColor = match($status['status'] ?? 'unknown') {
                                    'normal' => '🟢',
                                    'caution' => '🟡',
                                    'high_risk' => '🟠',
                                    'avoid' => '🔴',
                                    default => '⚪'
                                };
                            @endphp
                            {{ $statusColor }}
                        </div>
                        <div>
                            <h4 class="mb-0">
                                {{ ucfirst(str_replace('_', ' ', $status['status'] ?? __('messages.unknown'))) }}
                            </h4>
                            <small class="text-muted">
                                {{ __('messages.risk_score') }}: {{ number_format($status['score'] ?? 0, 1) }} / 100
                            </small>
                            @if(isset($status['status_color']))
                                <div><small>{{ $status['status_color'] }}</small></div>
                            @endif
                        </div>
                    </div>

                    @if(isset($status['pending_count']) && $status['pending_count'] > 0)
                        <div class="alert alert-warning mt-3 mb-0">
                            ⏳ {{ __('messages.incidents_under_review', ['count' => $status['pending_count']]) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Incidents List -->
            <h3 class="mb-3">🚨 {{ __('messages.related_incidents') }}</h3>
            @if(!empty($incidents) && $incidents->count() > 0)
                @foreach($incidents as $inc)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="me-3" style="font-size: 1.5rem;">
                                    @if($inc->severity === 'critical') 🔴
                                    @elseif($inc->severity === 'high') 🟠
                                    @elseif($inc->severity === 'moderate') 🟡
                                    @else 🟢
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title">
                                        <a href="{{ route('safety.incident', $inc->id) }}" class="text-decoration-none">
                                            {{ $inc->title }}
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small">
                                        <strong>{{ __('messages.type') }}:</strong> {{ str_replace('_', ' ', $inc->incident_type) }} |
                                        <strong>{{ __('messages.severity') }}:</strong> {{ ucfirst($inc->severity) }} |
                                        <strong>{{ __('messages.reported') }}:</strong> {{ $inc->reported_at?->diffForHumans() }}
                                    </p>
                                    @if($inc->description)
                                        <p class="card-text">{{ Str::limit($inc->description, 200) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-success">
                    ✅ {{ __('messages.no_active_incidents') }}
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            @if(!empty($incidents) && $incidents->count() > 0)
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">⚠️ {{ __('messages.safety_recommendations') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            @php
                                $highestSeverity = $incidents->sortByDesc(function($i) {
                                    return match($i->severity) {
                                        'critical' => 4,
                                        'high' => 3,
                                        'moderate' => 2,
                                        'low' => 1,
                                        default => 0
                                    };
                                })->first();
                            @endphp
                            @if($highestSeverity)
                                <li class="mb-2">
                                    <strong>{{ __('messages.current_advisory') }}:</strong>
                                    @if(in_array($highestSeverity->severity, ['critical', 'high']))
                                        <span class="text-danger">{{ __('messages.avoid_travel') }}</span>
                                    @elseif($highestSeverity->severity === 'moderate')
                                        <span class="text-warning">{{ __('messages.exercise_caution') }}</span>
                                    @else
                                        <span class="text-success">{{ __('messages.normal_precautions') }}</span>
                                    @endif
                                </li>
                                <li><strong>{{ __('messages.last_updated') }}:</strong> {{ $highestSeverity->last_verified_at?->format('F j, Y, g:i A') ?? __('messages.unknown') }}</li>
                                <li><strong>{{ __('messages.confidence') }}:</strong> {{ ($highestSeverity->confidence_score ?? 0) * 100 }}%</li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Quick Links -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">📍 {{ __('messages.quick_links') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ route('safety.index') }}">📌 {{ __('messages.safety_overview') }}</a></li>
                        <li><a href="{{ route('public.providers.index') }}">🏔️ {{ __('messages.find_providers') }}</a></li>
                        <li><a href="{{ route('home') }}">🏠 {{ __('messages.home') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
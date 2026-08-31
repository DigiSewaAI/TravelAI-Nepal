@extends('layouts.public')

@section('title', $incident->title)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('safety.index') }}">{{ __('messages.travel_safety') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($incident->title, 50) }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">{{ $incident->title }}</h2>
                    <span class="badge 
                        @if($incident->severity === 'critical') bg-danger
                        @elseif($incident->severity === 'high') bg-warning
                        @elseif($incident->severity === 'moderate') bg-info
                        @else bg-secondary @endif
                        fs-6">
                        {{ strtoupper($incident->severity ?? __('messages.unknown')) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Incident Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>📌 {{ __('messages.type') }}:</strong> {{ str_replace('_', ' ', $incident->incident_type ?? __('messages.unknown')) }}</p>
                            <p><strong>📍 {{ __('messages.location') }}:</strong> {{ $incident->location_name ?? __('messages.unknown') }}</p>
                            <p><strong>📅 {{ __('messages.reported') }}:</strong> {{ $incident->reported_at?->format('F j, Y, g:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>✅ {{ __('messages.status') }}:</strong> 
                                <span class="badge 
                                    @if($incident->status === 'active') bg-success
                                    @elseif($incident->status === 'verified') bg-primary
                                    @elseif($incident->status === 'under_review') bg-warning
                                    @elseif($incident->status === 'resolved') bg-info
                                    @else bg-secondary @endif">
                                    {{ ucfirst(str_replace('_', ' ', $incident->status)) }}
                                </span>
                            </p>
                            <p><strong>🎯 {{ __('messages.confidence') }}:</strong> {{ ($incident->confidence_score ?? 0) * 100 }}%</p>
                            @if($incident->travel_impact)
                                <p><strong>🚗 {{ __('messages.travel_impact') }}:</strong> {{ ucfirst($incident->travel_impact) }}</p>
                            @endif
                        </div>
                    </div>

                    @if($incident->description)
                        <div class="mb-4">
                            <h5>📝 {{ __('messages.description') }}</h5>
                            <p>{{ $incident->description }}</p>
                        </div>
                    @endif

                    @if($incident->recommended_action)
                        <div class="alert alert-info">
                            <strong>💡 {{ __('messages.recommendation') }}:</strong> {{ $incident->recommended_action }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Affected Areas -->
            @if($affectedWaypoints->count() > 0 || $affectedRoutes->count() > 0 || $affectedTreks->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📍 {{ __('messages.affected_areas') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($affectedWaypoints->count() > 0)
                            <h6>🚩 {{ __('messages.waypoints') }}</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($affectedWaypoints as $wp)
                                    <span class="badge bg-secondary">{{ $wp->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($affectedRoutes->count() > 0)
                            <h6>🛤️ {{ __('messages.routes') }}</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($affectedRoutes as $route)
                                    <span class="badge bg-secondary">{{ $route->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($affectedTreks->count() > 0)
                            <h6>🏔️ {{ __('messages.treks') }}</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($affectedTreks as $trek)
                                    <span class="badge bg-secondary">{{ $trek->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Sources -->
            @if($incident->sources && $incident->sources->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">📰 {{ __('messages.sources') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach($incident->sources as $source)
                            <div class="mb-2">
                                <strong>{{ $source->name }}</strong>
                                @if($source->pivot->source_url)
                                    <a href="{{ $source->pivot->source_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                        🔗 {{ __('messages.view_source') }}
                                    </a>
                                @endif
                                <br>
                                <small class="text-muted">
                                    {{ __('messages.reliability') }}: {{ ($source->pivot->source_reliability ?? 0) * 100 }}% |
                                    {{ __('messages.published') }}: {{ $source->pivot->published_at?->format('F j, Y') ?? __('messages.unknown') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">🔗 {{ __('messages.quick_actions') }}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('safety.index') }}" class="btn btn-outline-primary w-100 mb-2">📌 {{ __('messages.safety_overview') }}</a>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <a href="{{ route('admin.safety.incidents') }}" class="btn btn-outline-danger w-100">⚙️ {{ __('messages.admin_panel') }}</a>
                    @endif
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">🛡️ {{ __('messages.safety_tips') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li>✅ {{ __('messages.tip_stay_informed') }}</li>
                        <li>✅ {{ __('messages.tip_follow_authorities') }}</li>
                        <li>✅ {{ __('messages.tip_emergency_contacts') }}</li>
                        <li>✅ {{ __('messages.tip_avoid_affected') }}</li>
                        <li>✅ {{ __('messages.tip_check_updates') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
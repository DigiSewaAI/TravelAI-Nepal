@extends('layouts.public')

@section('title', __('messages.travel_safety_nepal'))

@section('content')
<div class="container py-5">
    <h1 class="mb-4">🇳🇵 {{ __('messages.travel_safety_nepal') }}</h1>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">🟢 {{ __('messages.status_normal') }}</h5>
                    <h2 class="mb-0">{{ $summary['normal'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">🟡 {{ __('messages.status_caution') }}</h5>
                    <h2 class="mb-0">{{ $summary['caution'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">🟠 {{ __('messages.status_high_risk') }}</h5>
                    <h2 class="mb-0">{{ $summary['high_risk'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5 class="card-title">🔴 {{ __('messages.status_avoid') }}</h5>
                    <h2 class="mb-0">{{ $summary['avoid'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">📌 {{ __('messages.safety_map') }}</h5>
            <div id="safetyMap" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Active Incidents -->
    <div class="row">
        <div class="col-md-8">
            <h3>🚨 {{ __('messages.active_incidents') }}</h3>
            @forelse($incidents as $incident)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <span style="font-size: 2rem;">
                                    @if($incident->severity === 'critical') 🔴
                                    @elseif($incident->severity === 'high') 🟠
                                    @elseif($incident->severity === 'moderate') 🟡
                                    @else 🟢
                                    @endif
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">
                                    <a href="{{ route('safety.incident', $incident->id) }}">
                                        {{ $incident->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted small">
                                    <strong>{{ __('messages.location') }}:</strong> {{ $incident->location_name ?? __('messages.unknown') }} |
                                    <strong>{{ __('messages.type') }}:</strong> {{ str_replace('_', ' ', $incident->incident_type) }} |
                                    <strong>{{ __('messages.reported') }}:</strong> {{ $incident->reported_at?->diffForHumans() }}
                                </p>
                                @if($incident->description)
                                    <p class="card-text">{{ Str::limit($incident->description, 150) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.no_active_incidents_reported') }}</p>
            @endforelse
        </div>
        <div class="col-md-4">
            <h3>📍 {{ __('messages.affected_areas') }}</h3>
            @foreach($affectedWaypoints as $wp)
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <span class="badge {{ $wp->safety_status === 'caution' ? 'bg-warning' : ($wp->safety_status === 'high_risk' ? 'bg-danger' : 'bg-dark') }}">
                            {{ strtoupper(str_replace('_', ' ', $wp->safety_status)) }}
                        </span>
                        {{ $wp->name }}
                    </div>
                </div>
            @endforeach
            @foreach($affectedTreks as $trek)
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <span class="badge {{ $trek->safety_status === 'caution' ? 'bg-warning' : ($trek->safety_status === 'high_risk' ? 'bg-danger' : 'bg-dark') }}">
                            {{ strtoupper(str_replace('_', ' ', $trek->safety_status)) }}
                        </span>
                        {{ $trek->name }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route("api.safety.markers") }}')
            .then(response => response.json())
            .then(data => {
                // Initialize Leaflet map
                const map = L.map('safetyMap').setView([28.3949, 84.1240], 7);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Add markers
                data.forEach(incident => {
                    const marker = L.circleMarker([incident.latitude, incident.longitude], {
                        radius: 12,
                        color: incident.color,
                        fillColor: incident.color,
                        fillOpacity: 0.7,
                        weight: 2
                    }).addTo(map);

                    const popupContent = `
                        <strong>${incident.title}</strong><br>
                        <span style="color: ${incident.color}">${incident.severity.toUpperCase()}</span><br>
                        ${incident.location || ''}<br>
                        <small>${incident.reported_at ? new Date(incident.reported_at).toLocaleString() : ''}</small><br>
                        <a href="${incident.url}" target="_blank">{{ __('messages.view_details') }}</a>
                    `;
                    marker.bindPopup(popupContent);

                    // Add radius circle
                    if (incident.affected_radius) {
                        L.circle([incident.latitude, incident.longitude], {
                            radius: incident.affected_radius,
                            color: incident.color,
                            fillColor: incident.color,
                            fillOpacity: 0.1,
                            weight: 1,
                            opacity: 0.3
                        }).addTo(map);
                    }
                });
            })
            .catch(error => console.error('Error loading safety markers:', error));
    });
</script>
@endpush
@endsection
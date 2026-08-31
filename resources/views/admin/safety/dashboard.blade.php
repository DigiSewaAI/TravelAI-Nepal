@extends('layouts.admin')

@section('title', __('messages.safety_dashboard'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>🛡️ {{ __('messages.safety_dashboard') }}</h1>
        <div>
            <span class="badge bg-info">{{ __('messages.last_updated') }}: {{ now()->format('H:i') }}</span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">{{ __('messages.active_incidents') }}</h6>
                    <h2 class="mb-0">{{ $summary['active'] ?? 0 }}</h2>
                    <small class="text-muted">
                        {{ $summary['under_review'] ?? 0 }} {{ __('messages.under_review') }}, 
                        {{ $summary['verified'] ?? 0 }} {{ __('messages.verified') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">{{ __('messages.severity_breakdown') }}</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-danger">🔴 {{ $summary['critical'] ?? 0 }}</span>
                        <span class="badge bg-warning">🟠 {{ $summary['high'] ?? 0 }}</span>
                        <span class="badge bg-info">🟡 {{ $summary['moderate'] ?? 0 }}</span>
                        <span class="badge bg-secondary">🟢 {{ $summary['low'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">{{ __('messages.source_health') }}</h6>
                    <h2 class="mb-0">{{ $sourceStats['healthy'] ?? 0 }}/{{ $sourceStats['total'] ?? 0 }}</h2>
                    <small class="text-danger">{{ $sourceStats['failed'] ?? 0 }} {{ __('messages.sources_failed') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">{{ __('messages.traveler_alerts') }}</h6>
                    <h2 class="mb-0">{{ $alertStats->total ?? 0 }}</h2>
                    <small class="text-warning">{{ $alertStats->unread ?? 0 }} {{ __('messages.unread') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Incidents -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📋 {{ __('messages.recent_incidents') }}</h5>
                    <a href="{{ route('admin.safety.incidents') }}" class="btn btn-sm btn-primary">{{ __('messages.view_all') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.title') }}</th>
                                    <th>{{ __('messages.type') }}</th>
                                    <th>{{ __('messages.severity') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.reported') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIncidents as $incident)
                                    <tr>
                                        <td>
                                            <a href="{{ route('safety.incident', $incident->id) }}" target="_blank">
                                                {{ Str::limit($incident->title, 30) }}
                                            </a>
                                        </td>
                                        <td>{{ str_replace('_', ' ', $incident->incident_type) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($incident->severity === 'critical') bg-danger
                                                @elseif($incident->severity === 'high') bg-warning
                                                @elseif($incident->severity === 'moderate') bg-info
                                                @else bg-secondary @endif">
                                                {{ $incident->severity ?? __('messages.unknown') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($incident->status === 'active') bg-success
                                                @elseif($incident->status === 'verified') bg-primary
                                                @elseif($incident->status === 'under_review') bg-warning
                                                @elseif($incident->status === 'resolved') bg-info
                                                @else bg-secondary @endif">
                                                {{ $incident->status }}
                                            </span>
                                        </td>
                                        <td>{{ $incident->reported_at?->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.safety.incidents') }}?search={{ $incident->id }}" 
                                               class="btn btn-sm btn-outline-primary">{{ __('messages.manage') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ __('messages.no_incidents_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📜 {{ __('messages.recent_activity') }}</h5>
                    <a href="{{ route('admin.safety.audit') }}" class="btn btn-sm btn-primary">{{ __('messages.view_all') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($auditLogs as $log)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                        <div>
                                            <span class="badge bg-secondary">{{ $log->action }}</span>
                                            @if($log->incident)
                                                <a href="{{ route('safety.incident', $log->incident_id) }}" target="_blank">
                                                    #{{ $log->incident_id }}
                                                </a>
                                            @endif
                                        </div>
                                        @if($log->reason)
                                            <small class="text-muted d-block">{{ Str::limit($log->reason, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted">{{ __('messages.no_recent_activity') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
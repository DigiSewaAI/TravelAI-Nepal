@extends('layouts.admin')

@section('title', 'Safety Dashboard')
@section('header', 'Safety Dashboard')

@section('content')

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">🛡️ Safety Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">Real-time safety monitoring across Nepal · Updated {{ now()->format('H:i') }}</p>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border-l-4 border-red-500 shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Incidents</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['active'] ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $summary['under_review'] ?? 0 }} review · {{ $summary['verified'] ?? 0 }} verified</p>
            </div>
            <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl border-l-4 border-amber-500 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Severity Breakdown</p>
        <div class="flex gap-1.5 flex-wrap">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">🔴 {{ $summary['critical'] ?? 0 }}</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">🟠 {{ $summary['high'] ?? 0 }}</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">🟡 {{ $summary['moderate'] ?? 0 }}</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">🟢 {{ $summary['low'] ?? 0 }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border-l-4 border-blue-500 shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Source Health</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $sourceStats['healthy'] ?? 0 }}/{{ $sourceStats['total'] ?? 0 }}</p>
                <p class="text-xs {{ ($sourceStats['failed'] ?? 0) > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">{{ $sourceStats['failed'] ?? 0 }} failed</p>
            </div>
            <i class="fas fa-rss text-2xl text-blue-500"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl border-l-4 border-purple-500 shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Traveler Alerts</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $alertStats->total ?? 0 }}</p>
                <p class="text-xs {{ ($alertStats->unread ?? 0) > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }} mt-1">{{ $alertStats->unread ?? 0 }} unread</p>
            </div>
            <i class="fas fa-bell text-2xl text-purple-500"></i>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.safety.incidents') }}" class="bg-white rounded-xl shadow-sm border hover:shadow-md transition p-4 flex items-center gap-3 text-decoration-none">
        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <p class="font-bold text-gray-900 text-sm">Manage Incidents</p>
            <p class="text-xs text-gray-500">Verify · Override · Merge</p>
        </div>
    </a>
    <a href="{{ route('admin.safety.sources') }}" class="bg-white rounded-xl shadow-sm border hover:shadow-md transition p-4 flex items-center gap-3 text-decoration-none">
        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
            <i class="fas fa-rss"></i>
        </div>
        <div>
            <p class="font-bold text-gray-900 text-sm">Data Sources</p>
            <p class="text-xs text-gray-500">RSS · APIs · Scrapers</p>
        </div>
    </a>
    <a href="{{ route('admin.safety.audit') }}" class="bg-white rounded-xl shadow-sm border hover:shadow-md transition p-4 flex items-center gap-3 text-decoration-none">
        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <p class="font-bold text-gray-900 text-sm">Audit Log</p>
            <p class="text-xs text-gray-500">All admin actions</p>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Recent Incidents --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-bold text-gray-900">📋 Recent Incidents</h3>
            <a href="{{ route('admin.safety.incidents') }}" class="text-xs font-bold text-blue-600 hover:underline">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Severity</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Reported</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentIncidents as $incident)
                        @php
                            $sev = match($incident->severity) {
                                'critical' => 'bg-red-100 text-red-700',
                                'high' => 'bg-orange-100 text-orange-700',
                                'moderate' => 'bg-amber-100 text-amber-700',
                                default => 'bg-green-100 text-green-700',
                            };
                            $stat = match($incident->status) {
                                'active' => 'bg-red-100 text-red-700',
                                'verified' => 'bg-green-100 text-green-700',
                                'resolved' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ Str::limit($incident->title, 28) }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $sev }}">{{ $incident->severity }}</span></td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $stat }}">{{ $incident->status }}</span></td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $incident->reported_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400">
                                <i class="fas fa-shield-alt text-3xl opacity-30 mb-2"></i>
                                <div class="text-sm">No incidents found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-bold text-gray-900">📜 Recent Activity</h3>
            <a href="{{ route('admin.safety.audit') }}" class="text-xs font-bold text-blue-600 hover:underline">View All →</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($auditLogs as $log)
                <div class="px-4 py-3">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded uppercase">{{ $log->action }}</span>
                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @if($log->incident_id)
                        <p class="text-xs text-gray-600">Incident #{{ $log->incident_id }}</p>
                    @endif
                    @if($log->reason)
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($log->reason, 50) }}</p>
                    @endif
                </div>
            @empty
                <div class="px-4 py-12 text-center text-gray-400">
                    <i class="fas fa-history text-3xl opacity-30 mb-2"></i>
                    <div class="text-sm">No recent activity</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
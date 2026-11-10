@extends('layouts.admin')

@section('title', 'Safety Incidents')
@section('header', 'Safety Incidents')

@section('content')

<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Safety Incidents</h2>
        <p class="text-sm text-gray-500 mt-1">All reported incidents across Nepal</p>
    </div>
    <a href="{{ route('admin.safety.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-center">
    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <option value="">All Status</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
    </select>
    <select name="severity" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <option value="">All Severity</option>
        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
        <option value="moderate" {{ request('severity') === 'moderate' ? 'selected' : '' }}>Moderate</option>
        <option value="normal" {{ request('severity') === 'normal' ? 'selected' : '' }}>Normal</option>
    </select>
    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
        <i class="fas fa-filter"></i> Filter
    </button>
    <a href="{{ route('admin.safety.incidents') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200">Reset</a>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Incident</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">District</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Reported</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $incident)
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
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $incident->title }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($incident->description, 60) }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $sev }}">{{ $incident->severity }}</span></td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $stat }}">{{ $incident->status }}</span></td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $incident->district ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $incident->reported_at ? \Carbon\Carbon::parse($incident->reported_at)->diffForHumans() : $incident->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($incident->status === 'active')
                                <form action="{{ route('admin.safety.verify', $incident->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <i class="fas fa-shield-alt text-4xl opacity-30 mb-3"></i>
                            <div class="text-sm font-semibold">No incidents found</div>
                            <div class="text-xs mt-1">Try adjusting filters</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $incidents->links() }}</div>

@endsection
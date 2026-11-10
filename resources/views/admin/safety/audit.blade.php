@extends('layouts.admin')

@section('title', 'Safety Audit Log')
@section('header', 'Safety Audit Log')

@section('content')

<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Safety Audit Log</h2>
        <p class="text-sm text-gray-500 mt-1">All admin actions on safety incidents</p>
    </div>
    <a href="{{ route('admin.safety.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-center">
    <select name="action" class="px-3 py-2 border border-gray-300 rounded-lg text-sm min-w-[180px]">
        <option value="">All Actions</option>
        <option value="verify" {{ request('action') === 'verify' ? 'selected' : '' }}>Verify</option>
        <option value="override" {{ request('action') === 'override' ? 'selected' : '' }}>Override</option>
        <option value="merge" {{ request('action') === 'merge' ? 'selected' : '' }}>Merge</option>
        <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Create</option>
        <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Update</option>
    </select>
    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
        <i class="fas fa-filter"></i> Filter
    </button>
    <a href="{{ route('admin.safety.audit') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200">Reset</a>
</form>

{{-- Timeline --}}
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @forelse($logs as $log)
        @php
            $colors = match($log->action) {
                'verify' => ['bg-green-100', 'text-green-600', 'fa-check'],
                'override' => ['bg-amber-100', 'text-amber-600', 'fa-edit'],
                'merge' => ['bg-purple-100', 'text-purple-600', 'fa-compress'],
                'create' => ['bg-blue-100', 'text-blue-600', 'fa-plus'],
                'delete' => ['bg-red-100', 'text-red-600', 'fa-trash'],
                default => ['bg-gray-100', 'text-gray-600', 'fa-history'],
            };
        @endphp
        <div class="flex gap-4 p-4 border-b border-gray-100 hover:bg-gray-50">
            <div class="w-10 h-10 rounded-lg {{ $colors[0] }} {{ $colors[1] }} flex items-center justify-center flex-shrink-0">
                <i class="fas {{ $colors[2] }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="px-2 py-0.5 {{ $colors[0] }} {{ $colors[1] }} text-xs font-bold rounded uppercase">{{ $log->action }}</span>
                    <span class="text-xs text-gray-400 font-mono">{{ $log->created_at->format('M d, Y H:i') }}</span>
                    @if($log->user_id)
                        <span class="text-xs text-gray-500">· User #{{ $log->user_id }}</span>
                    @endif
                </div>
                <div class="text-sm text-gray-700">
                    @if($log->incident_id)Incident #{{ $log->incident_id }}@endif
                    @if($log->reason)— {{ $log->reason }}@endif
                </div>
                @if($log->old_values || $log->new_values)
                    <div class="mt-1 text-xs text-gray-400">
                        @if($log->old_values)<div><strong>From:</strong> <code class="bg-gray-100 px-1.5 py-0.5 rounded">{{ Str::limit(json_encode($log->old_values), 80) }}</code></div>@endif
                        @if($log->new_values)<div><strong>To:</strong> <code class="bg-gray-100 px-1.5 py-0.5 rounded">{{ Str::limit(json_encode($log->new_values), 80) }}</code></div>@endif
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-history text-4xl opacity-30 mb-3"></i>
            <div class="text-sm font-semibold">No audit logs yet</div>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $logs->links() }}</div>

@endsection
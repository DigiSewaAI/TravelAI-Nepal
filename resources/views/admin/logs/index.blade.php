@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">System Logs</h1>
        <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Clear all logs?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-semibold shadow-sm">
                <i class="fas fa-trash"></i> Clear Logs
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
    @endif

    {{-- 🆕 Changed to inline styles to guarantee visibility --}}
    <div style="background-color: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; overflow-x: auto; max-height: 600px; font-family: 'Courier New', Courier, monospace; font-size: 13px; white-space: pre-wrap; line-height: 1.5; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);">
{{ $logs }}
    </div>
</div>
@endsection
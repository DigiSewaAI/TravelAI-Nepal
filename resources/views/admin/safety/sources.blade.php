@extends('layouts.admin')

@section('title', 'Safety Sources')
@section('header', 'Safety Sources')

@section('content')

<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Safety Data Sources</h2>
        <p class="text-sm text-gray-500 mt-1">RSS feeds, APIs & scrapers for incident monitoring</p>
    </div>
    <a href="{{ route('admin.safety.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

{{-- Add Source --}}
<div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
    <h3 class="text-sm font-bold text-gray-900 mb-4"><i class="fas fa-plus-circle text-blue-600"></i> Add New Source</h3>
    <form action="{{ route('admin.safety.sources.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
        @csrf
        <input type="text" name="name" required placeholder="Source name" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="type" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Type</option>
            <option value="rss">RSS</option>
            <option value="json">JSON API</option>
            <option value="html">HTML Scrape</option>
        </select>
        <input type="url" name="feed_url" required placeholder="https://..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="source_category" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Category</option>
            <option value="government">Government</option>
            <option value="news">News</option>
            <option value="weather">Weather</option>
            <option value="traffic">Traffic</option>
            <option value="health">Health</option>
            <option value="other">Other</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
            <i class="fas fa-plus"></i> Add Source
        </button>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Source</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase">Last Check</th>
                    <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $source)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $source->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($source->feed_url, 50) }}</div>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded uppercase">{{ $source->type }}</span></td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ ucfirst($source->source_category) }}</td>
                        <td class="px-4 py-3">
                            @if($source->enabled)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">Enabled</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase">Disabled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $source->last_checked_at ? \Carbon\Carbon::parse($source->last_checked_at)->diffForHumans() : 'Never' }}
                            @if($source->last_error)
                                <div class="text-red-500 mt-0.5">{{ Str::limit($source->last_error, 40) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('admin.safety.sources.toggle', $source->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 {{ $source->enabled ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-xs font-bold">
                                    <i class="fas fa-{{ $source->enabled ? 'pause' : 'play' }}"></i> {{ $source->enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <i class="fas fa-rss text-4xl opacity-30 mb-3"></i>
                            <div class="text-sm font-semibold">No sources configured</div>
                            <div class="text-xs mt-1">Add your first source above</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
@extends('layouts.provider')

@section('title', 'Preview Itinerary — ' . $service->name)
@section('header', 'Itinerary Preview: ' . $service->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Preview banner --}}
    <div class="flex justify-between items-center flex-wrap gap-3 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
        <div class="flex items-center gap-2">
            <i class="fas fa-eye text-yellow-600"></i>
            <span class="text-sm font-semibold text-yellow-800">Preview Mode</span>
            <span class="text-xs text-yellow-700">
                @if($service->isItineraryPublished())
                    (Currently published — this is a private preview)
                @else
                    (Draft — not visible to public yet)
                @endif
            </span>
        </div>
        <a href="{{ route('provider.services.itinerary.index', $service) }}"
           class="text-sm font-semibold px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
            ← Back to editor
        </a>
    </div>

    {{-- Public-style rendering --}}
    @if($service->itineraryDays->isNotEmpty())
        <div class="mt-4">
            <h2 class="text-2xl font-bold text-gray-900">Itinerary</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $service->itineraryDays->count() }} days</p>

            <div class="space-y-3 mt-6">
                @foreach($service->itineraryDays as $index => $day)
                    <details class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" {{ $index === 0 ? 'open' : '' }}>
                        <summary class="px-5 py-4 flex justify-between items-center cursor-pointer hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">DAY {{ $day->day_number }}</span>
                                <span class="font-semibold text-gray-900">{{ $day->title }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </summary>

                        <div class="px-5 pb-5 pt-2 space-y-4 border-t border-gray-100">
                            @if($day->description)
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $day->description }}</p>
                            @endif

                            @if($day->startWaypoint || $day->overnightWaypoint || $day->endWaypoint)
                                <div class="flex flex-wrap gap-2">
                                    @if($day->startWaypoint)
                                        <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-play-circle text-gray-400"></i>
                                            Start: {{ $day->startWaypoint->name }}
                                        </span>
                                    @endif
                                    @if($day->overnightWaypoint)
                                        <span class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-bed text-emerald-500"></i>
                                            Overnight: {{ $day->overnightWaypoint->name }}
                                        </span>
                                    @endif
                                    @if($day->endWaypoint)
                                        <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-flag-checkered text-gray-400"></i>
                                            End: {{ $day->endWaypoint->name }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @php
                                $hasMeta = $day->distance_km || $day->estimated_time_hours || $day->elevation_gain_m || $day->elevation_loss_m || $day->altitude_m || $day->accommodation || !empty($day->meals_included);
                            @endphp
                            @if($hasMeta)
                                <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                    @if($day->distance_km)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">📏 {{ $day->distance_km }} km</span>@endif
                                    @if($day->estimated_time_hours)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">⏱ {{ $day->estimated_time_hours }} hrs</span>@endif
                                    @if($day->elevation_gain_m)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">↑ {{ $day->elevation_gain_m }} m</span>@endif
                                    @if($day->elevation_loss_m)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">↓ {{ $day->elevation_loss_m }} m</span>@endif
                                    @if($day->altitude_m)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">⛰ {{ $day->altitude_m }} m</span>@endif
                                    @if($day->accommodation)<span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">🏨 {{ $day->accommodation }}</span>@endif
                                </div>
                            @endif

                            @if($day->items->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Activities</h4>
                                    <ul class="space-y-1.5">
                                        @foreach($day->items as $item)
                                            <li class="text-sm text-gray-700">
                                                <span class="font-medium">{{ $item->title }}</span>
                                                @if($item->is_optional)<span class="text-xs text-gray-400 italic ml-1">(optional)</span>@endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($day->media->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media ({{ $day->media->count() }})</h4>
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach($day->media as $media)
                                            <div class="rounded border overflow-hidden bg-gray-100">
                                                @if($media->media_type === 'image')
                                                    <img src="{{ asset('storage/' . $media->file_path) }}"
                                                         alt="{{ $media->alt_text ?? '' }}"
                                                         class="w-full h-16 object-cover">
                                                @else
                                                    <div class="w-full h-16 flex items-center justify-center text-xs text-gray-500">🎬 Video</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-500">
            No itinerary days yet. Add days in the editor before previewing.
        </div>
    @endif

</div>
@endsection
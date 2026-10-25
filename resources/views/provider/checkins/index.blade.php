@extends('layouts.provider')

@section('title', __('messages.checkins_page_title'))
@section('header', __('messages.checkins_header'))

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="bg-white rounded-xl shadow-sm border p-6">
    <!-- Search & Filters -->
    <div class="flex flex-wrap gap-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-center w-full">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="{{ __('messages.checkins_search_placeholder') }}"
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-search mr-1"></i> {{ __('messages.filter') }}
            </button>
            <a href="{{ route('provider.checkins.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                {{ __('messages.reset') }}
            </a>
        </form>
    </div>

    @if($checkins->count() > 0)
        <!-- Map Container -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-map-marked-alt text-blue-500"></i> {{ __('messages.checkin_map_overview') }}
            </h3>
            <!-- relative z-0 ले नक्सालाई साइडबारको माथि नआउन दिन्छ -->
            <div id="checkinsMap" class="w-full h-96 rounded-xl border border-gray-200 relative z-0"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.traveler') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.service') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.checkpoint') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.time') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkins as $scan)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">
                            {{ $scan->booking->traveler->name ?? __('messages.guest') }}
                        </td>
                        <td class="py-3 text-sm">{{ $scan->booking->service->name ?? __('messages.na') }}</td>
                        <td class="py-3 text-sm">
                            <i class="fas fa-map-pin text-blue-500 mr-1"></i>
                            {{ $scan->checkpoint_name ?? __('messages.checkin_default') }}
                        </td>
                        <td class="py-3 text-sm">{{ $scan->scanned_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('provider.checkins.show', $scan) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i> {{ __('messages.view') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $checkins->appends(request()->query())->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">{{ __('messages.no_checkins_yet') }}</p>
    @endif
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($checkins->count() > 0)
            // Fix for missing default Leaflet icons
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });

            // Initialize Map (Center of Nepal default)
            const map = L.map('checkinsMap').setView([28.3949, 84.1240], 7);

            // Add Tile Layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const markers = [];
            const checkinsData = @json($checkins->items());

            // 👇 यहाँ ट्रान्सलेसनको लागि भेरिएबल राखिएको छ
            const guestText = @json(__('messages.guest'));
            const checkpointText = @json(__('messages.checkpoint') ?? 'Checkpoint');

            checkinsData.forEach(function(scan) {
                if (scan.latitude && scan.longitude) {
                    const lat = parseFloat(scan.latitude);
                    const lng = parseFloat(scan.longitude);
                    
                    const marker = L.marker([lat, lng]).addTo(map);
                    
                    const popupContent = `
                        <div class="text-sm font-sans">
                            <strong>${scan.booking?.traveler?.name || guestText}</strong><br>
                            <span class="text-gray-500">${scan.checkpoint_name || checkpointText}</span><br>
                            <small class="text-gray-400">${new Date(scan.scanned_at).toLocaleString()}</small>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    markers.push(marker);
                }
            });

            // Auto-zoom to fit all markers
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        @endif
    });
</script>
@endpush
@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.checkin_detail_title'))
@section('header', __('messages.checkin_detail_header'))

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('provider.checkins.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('messages.back_to_checkins') }}
        </a>
    </div>

    <div class="flex justify-between items-start mb-4">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.checkin_detail_heading') }}</h2>
        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
            <i class="fas fa-check-circle mr-1"></i> {{ __('messages.checked_in') }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.traveler') }}</p>
            <p class="font-medium">{{ $scan->booking->traveler->name ?? __('messages.guest') }}</p>
            <p class="text-sm text-gray-600">{{ $scan->booking->traveler->email ?? '' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.service') }}</p>
            <p class="font-medium">{{ $scan->booking->service->name ?? __('messages.na') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.provider') }}</p>
            <p class="font-medium">{{ $scan->booking->service->provider->name ?? __('messages.na') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.checkpoint') }}</p>
            <p class="font-medium"><i class="fas fa-map-pin text-blue-500 mr-1"></i> {{ $scan->checkpoint_name ?? __('messages.checkin_default') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.checked_in_at') }}</p>
            <p class="font-medium">{{ $scan->scanned_at->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <!-- Location & Map Section -->
    @if($scan->latitude && $scan->longitude)
    <div class="mt-6 border-t pt-4">
        <p class="text-sm text-gray-500 mb-2">{{ __('messages.location') }} (Lat: {{ $scan->latitude }}, Lng: {{ $scan->longitude }})</p>
        <!-- relative z-0 ले नक्सालाई साइडबारको माथि नआउन दिन्छ -->
        <div id="singleCheckinMap" class="w-full h-64 rounded-xl border border-gray-200 relative z-0"></div>
    </div>
    @endif

    <div class="mt-6 border-t pt-4">
        <a href="{{ route('provider.bookings.show', $scan->booking_id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-calendar-check mr-1"></i> {{ __('messages.view_full_booking') }}
        </a>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($scan->latitude && $scan->longitude)
            // Fix for missing default Leaflet icons
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });

            const lat = {{ $scan->latitude }};
            const lng = {{ $scan->longitude }};

            // Initialize Map centered on the specific check-in location
            const map = L.map('singleCheckinMap').setView([lat, lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Add Marker
            const marker = L.marker([lat, lng]).addTo(map);
            
            // 👇 यहाँ 'Checkpoint' लाई __('messages.checkpoint') ले फेरिएको छ
            const popupContent = `
                <div class="text-sm font-sans text-center">
                    <strong>${@json($scan->checkpoint_name ?? __('messages.checkpoint'))}</strong><br>
                    <span class="text-gray-500">${new Date(@json($scan->scanned_at)).toLocaleString()}</span>
                </div>
            `;
            marker.bindPopup(popupContent).openPopup();
        @endif
    });
</script>
@endpush
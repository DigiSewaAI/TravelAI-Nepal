@extends('layouts.public')

@section('title', 'QR Check‑in | TravelAI Nepal')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white text-center">
                <i class="fas fa-qrcode mr-2"></i> Trek Check‑in
            </h1>
        </div>

        <div class="p-6">
            <!-- Booking info -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h2 class="font-semibold text-gray-800">Booking Details</h2>
                <p class="text-sm text-gray-600 mt-1">
                    <strong>Trek:</strong> {{ $booking->trek->name ?? 'N/A' }}<br>
                    <strong>Trekker:</strong> {{ $booking->trekker->name ?? 'N/A' }}<br>
                    <strong>Start Date:</strong> {{ $booking->start_date ? $booking->start_date->format('Y-m-d') : 'N/A' }}
                </p>
            </div>

            <!-- Check-in Form -->
            <form id="checkinForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Checkpoint Name *</label>
                    <input type="text" name="checkpoint" id="checkpoint" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Nayapul, Tikhedhunga, Ghorepani">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Location (auto‑detected)</label>
                    <div id="locationStatus" class="text-sm text-gray-500 flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i> Detecting your location...
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Record Check‑in
                </button>
            </form>

            <div id="resultMessage" class="mt-4 text-center text-sm font-medium hidden"></div>

            <div class="mt-6 text-center">
                <a href="/" class="text-blue-600 hover:underline text-sm">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto‑detect user location
    let lat = null, lng = null;
    const statusDiv = document.getElementById('locationStatus');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                lat = position.coords.latitude;
                lng = position.coords.longitude;
                latInput.value = lat;
                lngInput.value = lng;
                statusDiv.innerHTML = '<i class="fas fa-map-marker-alt text-green-600"></i> Location captured: ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
            },
            (error) => {
                let errorMsg = 'Unable to get location. Please enter manually or enable GPS.';
                if (error.code === 1) errorMsg = 'Location permission denied.';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600"></i> ' + errorMsg;
            }
        );
    } else {
        statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-600"></i> Geolocation not supported.';
    }

    // Handle form submission
    const form = document.getElementById('checkinForm');
    const submitBtn = document.getElementById('submitBtn');
    const resultDiv = document.getElementById('resultMessage');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recording...';

        const checkpoint = document.getElementById('checkpoint').value;
        if (!checkpoint) {
            showMessage('Please enter the checkpoint name.', 'red');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Record Check‑in';
            return;
        }

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('checkpoint', checkpoint);
        if (lat && lng) {
            formData.append('latitude', lat);
            formData.append('longitude', lng);
        }

        try {
            const response = await fetch('{{ route("scan.checkin", $booking->id) }}', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showMessage('✅ Check‑in successful!', 'green');
                form.reset();
                // Optionally reset location
            } else {
                showMessage(data.message || 'Something went wrong.', 'red');
            }
        } catch (err) {
            showMessage('Network error. Please try again.', 'red');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Record Check‑in';
        }
    });

    function showMessage(msg, color) {
        resultDiv.innerHTML = `<span class="text-${color}-600">${msg}</span>`;
        resultDiv.classList.remove('hidden');
        setTimeout(() => resultDiv.classList.add('hidden'), 4000);
    }
</script>
@endsection
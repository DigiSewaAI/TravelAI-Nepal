@extends('layouts.public')

@section('title', $trek->name . ' | TravelAI Nepal')

@section('content')
<style>
    .lightbox {
        display: none;
        position: fixed;
        z-index: 999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }
    .lightbox.active {
        display: flex;
    }
    .lightbox img {
        max-width: 90%;
        max-height: 85%;
        object-fit: contain;
        border-radius: 8px;
    }
    .lightbox-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.2);
        color: white;
        border: none;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        font-size: 28px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lightbox-btn:hover {
        background: rgba(255,255,255,0.4);
    }
    .prev-btn { left: 20px; }
    .next-btn { right: 20px; }
    .close-btn {
        position: absolute;
        top: 20px;
        right: 30px;
        background: none;
        border: none;
        color: white;
        font-size: 36px;
        cursor: pointer;
        z-index: 1000;
    }
    .itinerary-list {
        list-style: none;
        padding-left: 0;
    }
    .itinerary-list li {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .itinerary-list li i {
        color: #3b82f6;
        margin-top: 0.2rem;
    }
    .gallery-thumb {
        cursor: pointer;
        transition: transform 0.2s, opacity 0.2s;
    }
    .gallery-thumb:hover {
        transform: scale(1.02);
        opacity: 0.9;
    }
</style>

<!-- Lightbox Modal with navigation -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <button class="close-btn" onclick="closeLightbox()">&times;</button>
    <button class="lightbox-btn prev-btn" onclick="prevImage(event)"><i class="fas fa-chevron-left"></i></button>
    <img id="lightbox-img" src="" alt="Enlarged view">
    <button class="lightbox-btn next-btn" onclick="nextImage(event)"><i class="fas fa-chevron-right"></i></button>
</div>

<div class="max-w-6xl mx-auto px-4 py-8 md:py-12">
    <!-- Back button -->
    <a href="{{ url('/treks?category=' . ($trek->category ?? 'trek')) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to {{ ucfirst($trek->category ?? 'Treks') }}
    </a>

    <!-- Cover Image Hero -->
    <div class="rounded-2xl overflow-hidden shadow-lg mb-8 bg-gray-200 h-64 md:h-96">
        @if($trek->cover_image)
            <img src="{{ asset('storage/' . $trek->cover_image) }}" alt="{{ $trek->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-blue-400 to-indigo-500">
                <i class="fas fa-mountain text-6xl text-white/70"></i>
            </div>
        @endif
    </div>

    <!-- Trek Info Grid -->
    <div class="grid md:grid-cols-3 gap-8">
        <!-- Left Column: Main info & Itinerary -->
        <div class="md:col-span-2">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ $trek->name }}</h1>
            <div class="flex flex-wrap gap-4 text-gray-600 mb-6">
                <span><i class="fas fa-clock text-blue-500"></i> {{ $trek->duration_days }} days</span>
                <span><i class="fas fa-chart-line text-blue-500"></i> {{ ucfirst($trek->difficulty) }}</span>
                <span><i class="fas fa-tag text-blue-500"></i> ${{ number_format($trek->price, 2) }}</span>
                <span><i class="fas fa-building text-blue-500"></i> {{ $trek->agency->name ?? 'Independent' }}</span>
            </div>

            <!-- Itinerary Section -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-list-check text-blue-600"></i> Detailed Itinerary
                </h2>
                @php
                    $days = $trek->itinerary;
                @endphp
                @if($days && is_array($days) && count($days))
                    <ul class="itinerary-list">
                        @foreach($days as $day)
                            <li>
                                <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                <span>{{ $day }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No detailed itinerary available.</p>
                @endif
            </div>

            <!-- Gallery Section -->
            @php
                $gallery = $trek->gallery;
            @endphp
            @if($gallery && is_array($gallery) && count($gallery))
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-images text-blue-600"></i> Gallery
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="galleryGrid">
                        @foreach($gallery as $index => $img)
                            <div class="overflow-hidden rounded-lg shadow-sm gallery-thumb" onclick="openLightbox({{ $index }})">
                                <img src="{{ asset('storage/' . $img) }}" class="w-full h-40 object-cover transition duration-300 hover:scale-105" alt="Gallery image {{ $index+1 }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <script>
                    window.galleryImages = @json(array_map(function($img) {
                        return asset('storage/' . $img);
                    }, $gallery));
                </script>
            @endif
        </div>

        <!-- Right Column: Quick Booking Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-md p-6 sticky top-24">
                <div class="text-center mb-4">
                    <p class="text-3xl font-bold text-blue-600">${{ number_format($trek->price, 2) }}</p>
                    <p class="text-gray-500 text-sm">per person</p>
                </div>
                <div class="border-t border-gray-100 my-4"></div>
                <div class="space-y-3 text-sm text-gray-600 mb-6">
                    <div class="flex justify-between">
                        <span><i class="fas fa-calendar-alt"></i> Duration</span>
                        <span class="font-medium">{{ $trek->duration_days }} days</span>
                    </div>
                    <div class="flex justify-between">
                        <span><i class="fas fa-thermometer-half"></i> Difficulty</span>
                        <span class="font-medium capitalize">{{ $trek->difficulty }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span><i class="fas fa-building"></i> Agency</span>
                        <span class="font-medium">{{ $trek->agency->name ?? '-' }}</span>
                    </div>
                </div>
                <a href="{{ route('trek.book', $trek) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition text-center shadow-md">
                    <i class="fas fa-calendar-check mr-2"></i> Book Now
                </a>
                <p class="text-xs text-gray-400 text-center mt-4">Secure booking • Free cancellation • 24/7 support</p>
            </div>
        </div>
    </div>
</div>

<script>
    let lightboxActive = false;
    let currentIndex = 0;

    function openLightbox(index) {
        if (window.galleryImages && window.galleryImages.length) {
            currentIndex = index;
            updateLightboxImage();
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function updateLightboxImage() {
        const imgElement = document.getElementById('lightbox-img');
        if (imgElement && window.galleryImages && window.galleryImages[currentIndex]) {
            imgElement.src = window.galleryImages[currentIndex];
        }
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }

    function nextImage(event) {
        if (event) event.stopPropagation();
        if (window.galleryImages && window.galleryImages.length) {
            currentIndex = (currentIndex + 1) % window.galleryImages.length;
            updateLightboxImage();
        }
    }

    function prevImage(event) {
        if (event) event.stopPropagation();
        if (window.galleryImages && window.galleryImages.length) {
            currentIndex = (currentIndex - 1 + window.galleryImages.length) % window.galleryImages.length;
            updateLightboxImage();
        }
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('lightbox');
        if (!lightbox.classList.contains('active')) return;

        if (e.key === 'ArrowLeft') {
            prevImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'Escape') {
            closeLightbox();
        }
    });
</script>
@endsection
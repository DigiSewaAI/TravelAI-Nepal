@extends('layouts.public')

@section('title', 'How It Works | TravelAI Nepal')

@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">How It Works</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Simple, transparent, and efficient – for travelers and agencies.
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== STATS BAR ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">500+</p>
            <p class="text-sm text-gray-600">Treks & Tours</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
            <p class="text-2xl font-bold text-green-600">50+</p>
            <p class="text-sm text-gray-600">Partner Agencies</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
            <p class="text-2xl font-bold text-purple-600">100%</p>
            <p class="text-sm text-gray-600">Satisfied Travelers</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
            <p class="text-2xl font-bold text-yellow-600">24/7</p>
            <p class="text-sm text-gray-600">Safety Support</p>
        </div>
    </div>

    {{-- ========== FOR TRAVELERS ========== --}}
    <div class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-blue-600 pl-3 mb-6 flex items-center gap-2">
            <i class="fas fa-user text-blue-600"></i> For Travelers
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-blue-300">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-600 transition">
                    <span class="text-2xl font-bold text-blue-600 group-hover:text-white transition">1</span>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-robot text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Plan with AI or Browse Packages</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Use AI Trip Planner to generate a custom itinerary, or browse hundreds of treks, tours, and hotels posted by local agencies.</p>
                <span class="inline-block mt-3 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">AI Powered</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-emerald-300">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-600 transition">
                    <span class="text-2xl font-bold text-emerald-600 group-hover:text-white transition">2</span>
                </div>
                <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-calendar-check text-2xl text-emerald-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Book Instantly</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Choose a trek, tour, or hotel – fill your details and book securely. A QR code is generated for check‑in.</p>
                <span class="inline-block mt-3 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Secure Booking</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-red-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-600 transition">
                    <span class="text-2xl font-bold text-red-600 group-hover:text-white transition">3</span>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-qrcode text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Check‑in & Stay Safe</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Scan QR at checkpoints. Agency tracks your progress. Offline SOS available in emergencies.</p>
                <span class="inline-block mt-3 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Safety First</span>
            </div>
        </div>
    </div>

    {{-- ========== FOR AGENCIES ========== --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-purple-600 pl-3 mb-6 flex items-center gap-2">
            <i class="fas fa-building text-purple-600"></i> For Travel Agencies
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-purple-300">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-600 transition">
                    <span class="text-2xl font-bold text-purple-600 group-hover:text-white transition">1</span>
                </div>
                <div class="w-14 h-14 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-plus text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Register & Onboard</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Create your agency profile, add logo, contact information, and business type (trek, tour, hotel).</p>
                <span class="inline-block mt-3 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">Easy Onboarding</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-amber-300">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-600 transition">
                    <span class="text-2xl font-bold text-amber-600 group-hover:text-white transition">2</span>
                </div>
                <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-folder-plus text-2xl text-amber-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Manage Packages</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Add treks, tours, hotels with itineraries, cover images, gallery, pricing, and availability.</p>
                <span class="inline-block mt-3 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Full Control</span>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-green-300">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-600 transition">
                    <span class="text-2xl font-bold text-green-600 group-hover:text-white transition">3</span>
                </div>
                <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 text-center">Track Bookings & Check‑ins</h3>
                <p class="text-gray-500 text-sm text-center mt-2">Real-time dashboard, QR scan history, status updates, and instant SOS alerts via email/queue.</p>
                <span class="inline-block mt-3 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Real-time Tracking</span>
            </div>
        </div>
    </div>

    {{-- ========== CTA SECTION ========== --}}
    <div class="mt-16 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-2xl md:text-3xl font-bold">Ready to start your journey?</h2>
        <p class="text-blue-100 mt-2">Explore treks & tours or become a partner today.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="{{ route('public.services.index') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                <i class="fas fa-map-marked-alt mr-2"></i> Explore Trips
            </a>
            <a href="{{ route('register') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                <i class="fas fa-handshake mr-2"></i> Become a Partner
            </a>
        </div>
    </div>
</div>
@endsection
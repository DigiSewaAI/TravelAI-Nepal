@extends('layouts.public')

@section('title', 'How It Works | TravelAI Nepal')

@section('content')
<style>
    .step-card { transition: 0.2s; }
    .step-card:hover { transform: translateY(-4px); }
</style>

<main class="max-w-5xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-gray-900 text-center mb-4">How It Works</h1>
    <p class="text-gray-600 text-center mb-12">Simple, transparent, and efficient – for travelers and agencies.</p>

    <div class="space-y-16">
        <!-- For Travelers -->
        <div>
            <h2 class="text-2xl font-bold text-gray-800 border-l-4 border-blue-600 pl-3 mb-8">🧑‍🤝‍🧑 For Travelers</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-blue-600">1</div>
                    <i class="fas fa-robot text-3xl text-blue-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Plan with AI or Browse Packages</h3>
                    <p class="text-gray-500 text-sm">Use AI Trip Planner to generate a custom itinerary, or browse hundreds of treks, tours, and hotels posted by local agencies.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-blue-600">2</div>
                    <i class="fas fa-calendar-check text-3xl text-blue-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Book Instantly</h3>
                    <p class="text-gray-500 text-sm">Choose a trek, tour, or hotel – fill your details and book securely. A QR code is generated for check‑in.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-blue-600">3</div>
                    <i class="fas fa-qrcode text-3xl text-blue-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Check‑in & Stay Safe</h3>
                    <p class="text-gray-500 text-sm">Scan QR at checkpoints. Agency tracks your progress. Offline SOS available in emergencies.</p>
                </div>
            </div>
        </div>

        <!-- For Agencies -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-800 border-l-4 border-blue-600 pl-3 mb-8">🏢 For Travel Agencies</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-green-600">1</div>
                    <i class="fas fa-user-plus text-3xl text-green-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Register & Onboard</h3>
                    <p class="text-gray-500 text-sm">Create your agency profile, add logo, contact information, and business type (trek, tour, hotel).</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-green-600">2</div>
                    <i class="fas fa-folder-plus text-3xl text-green-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Manage Packages</h3>
                    <p class="text-gray-500 text-sm">Add treks, tours, hotels with itineraries, cover images, gallery, pricing, and availability.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6 step-card text-center border border-gray-100">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold text-green-600">3</div>
                    <i class="fas fa-chart-line text-3xl text-green-500 mb-3"></i>
                    <h3 class="text-lg font-semibold mb-2">Track Bookings & Check‑ins</h3>
                    <p class="text-gray-500 text-sm">Real-time dashboard, QR scan history, status updates, and instant SOS alerts via email/queue.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to action -->
    <div class="mt-20 text-center bg-blue-50 rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-gray-800">Ready to start your journey?</h2>
        <p class="text-gray-600 mt-2">Explore treks & tours or become a partner today.</p>
        <div class="flex justify-center gap-4 mt-6">
            <a href="{{ url('/treks') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Explore Trips</a>
            <a href="{{ route('agency.register') }}" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900">Become a Partner</a>
        </div>
    </div>
</main>
@endsection
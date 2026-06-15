@extends('layouts.public')

@section('title', 'Features | TravelAI Nepal')

@section('content')
<style>
    .feature-card:hover { transform: translateY(-5px); transition: 0.2s; }
</style>

<main class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-gray-900 text-center mb-4">All Features</h1>
    <p class="text-gray-600 text-center mb-12">Everything you need to plan, book, and enjoy your Nepal journey.</p>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
                <i class="fas fa-robot text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">AI Trip Planner</h3>
            <p class="text-gray-600">Generate fully personalized itineraries based on your destination, days, budget, and interests – powered by Groq AI (Llama 3).</p>
        </div>
        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                <i class="fas fa-qrcode text-2xl text-emerald-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Digital Trek Passport</h3>
            <p class="text-gray-600">QR check‑in at checkpoints. Real‑time visibility for agencies, offline‑capable, and secure.</p>
        </div>
        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center mb-4">
                <i class="fas fa-sos text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Offline Emergency SOS</h3>
            <p class="text-gray-600">One‑tap SOS with location stored offline. Auto‑sync when network returns – agencies alerted instantly via email/queue.</p>
        </div>
        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center mb-4">
                <i class="fas fa-chart-line text-2xl text-purple-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Agency Dashboard</h3>
            <p class="text-gray-600">Manage treks, bookings, customers, itineraries, and analytics. Reduce manual work by 80%.</p>
        </div>
        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-4">
                <i class="fas fa-film text-2xl text-amber-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Trek Memory Replay</h3>
            <p class="text-gray-600">After your trek, AI generates a cinematic route replay with photo timeline – share on social media.</p>
        </div>
        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center mb-4">
                <i class="fas fa-link text-2xl text-slate-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Smart Permits (Coming 2025)</h3>
            <p class="text-gray-600">Blockchain‑ready digital permits for TIMS & Conservation – transparent, instant, immutable.</p>
        </div>
        <!-- Feature 7 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-pink-100 flex items-center justify-center mb-4">
                <i class="fas fa-mobile-alt text-2xl text-pink-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">PWA + Offline First</h3>
            <p class="text-gray-600">Install as app on your phone. Works offline, syncs when connected.</p>
        </div>
        <!-- Feature 8 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center mb-4">
                <i class="fas fa-shield-alt text-2xl text-indigo-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Real‑time Safety Score</h3>
            <p class="text-gray-600">Live weather, altitude, and trail risk assessment for every trek.</p>
        </div>
        <!-- Feature 9 -->
        <div class="bg-white rounded-2xl shadow-md p-6 feature-card border border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center mb-4">
                <i class="fas fa-language text-2xl text-orange-600"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Multi‑lingual (Nepali/English)</h3>
            <p class="text-gray-600">Content in both languages – accessible to international and local travelers.</p>
        </div>
    </div>
</main>
@endsection
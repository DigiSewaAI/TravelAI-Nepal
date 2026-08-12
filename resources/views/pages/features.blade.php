@extends('layouts.public')

@section('title', 'Features | TravelAI Nepal')

@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">All Features</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Everything you need to plan, book, and enjoy your Nepal journey.
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== STATS BAR ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">9+</p>
            <p class="text-sm text-gray-600">Features</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
            <p class="text-2xl font-bold text-green-600">24/7</p>
            <p class="text-sm text-gray-600">Support</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
            <p class="text-2xl font-bold text-purple-600">100%</p>
            <p class="text-sm text-gray-600">Offline Capable</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
            <p class="text-2xl font-bold text-yellow-600">AI</p>
            <p class="text-sm text-gray-600">Powered</p>
        </div>
    </div>

    {{-- ========== FEATURES GRID ========== --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-blue-300">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition">
                <i class="fas fa-robot text-2xl text-blue-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">AI Trip Planner</h3>
            <p class="text-gray-600 mt-1 text-sm">Generate fully personalized itineraries based on your destination, days, budget, and interests – powered by Groq AI (Llama 3).</p>
            <span class="inline-block mt-3 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">AI Powered</span>
        </div>
        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-emerald-300">
            <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition">
                <i class="fas fa-qrcode text-2xl text-emerald-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Digital Trek Passport</h3>
            <p class="text-gray-600 mt-1 text-sm">QR check‑in at checkpoints. Real‑time visibility for agencies, offline‑capable, and secure.</p>
            <span class="inline-block mt-3 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">QR Technology</span>
        </div>
        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-red-300">
            <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center mb-4 group-hover:bg-red-600 group-hover:text-white transition">
                <i class="fas fa-sos text-2xl text-red-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Offline Emergency SOS</h3>
            <p class="text-gray-600 mt-1 text-sm">One‑tap SOS with location stored offline. Auto‑sync when network returns – agencies alerted instantly.</p>
            <span class="inline-block mt-3 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Safety</span>
        </div>
        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-purple-300">
            <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                <i class="fas fa-chart-line text-2xl text-purple-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Agency Dashboard</h3>
            <p class="text-gray-600 mt-1 text-sm">Manage treks, bookings, customers, itineraries, and analytics. Reduce manual work by 80%.</p>
            <span class="inline-block mt-3 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">Business</span>
        </div>
        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-amber-300">
            <div class="w-14 h-14 rounded-xl bg-amber-100 flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                <i class="fas fa-film text-2xl text-amber-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Trek Memory Replay</h3>
            <p class="text-gray-600 mt-1 text-sm">After your trek, AI generates a cinematic route replay with photo timeline – share on social media.</p>
            <span class="inline-block mt-3 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">AI Powered</span>
        </div>
        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-slate-300">
            <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center mb-4 group-hover:bg-slate-700 group-hover:text-white transition">
                <i class="fas fa-link text-2xl text-slate-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Smart Permits (Coming 2025)</h3>
            <p class="text-gray-600 mt-1 text-sm">Blockchain‑ready digital permits for TIMS & Conservation – transparent, instant, immutable.</p>
            <span class="inline-block mt-3 text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">Coming Soon</span>
        </div>
        <!-- Feature 7 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-pink-300">
            <div class="w-14 h-14 rounded-xl bg-pink-100 flex items-center justify-center mb-4 group-hover:bg-pink-600 group-hover:text-white transition">
                <i class="fas fa-mobile-alt text-2xl text-pink-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">PWA + Offline First</h3>
            <p class="text-gray-600 mt-1 text-sm">Install as app on your phone. Works offline, syncs when connected.</p>
            <span class="inline-block mt-3 text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full">PWA</span>
        </div>
        <!-- Feature 8 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-indigo-300">
            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                <i class="fas fa-shield-alt text-2xl text-indigo-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Real‑time Safety Score</h3>
            <p class="text-gray-600 mt-1 text-sm">Live weather, altitude, and trail risk assessment for every trek.</p>
            <span class="inline-block mt-3 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Safety</span>
        </div>
        <!-- Feature 9 -->
        <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition border border-gray-100 group hover:border-orange-300">
            <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                <i class="fas fa-language text-2xl text-orange-600 group-hover:text-white transition"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Multi‑lingual (Nepali/English)</h3>
            <p class="text-gray-600 mt-1 text-sm">Content in both languages – accessible to international and local travelers.</p>
            <span class="inline-block mt-3 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Language</span>
        </div>
    </div>

    {{-- ========== CTA SECTION ========== --}}
    <div class="mt-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-2xl md:text-3xl font-bold">Ready to experience these features?</h2>
        <p class="text-blue-100 mt-2">Start your journey with TravelAI Nepal today.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                Get Started Free
            </a>
            <a href="{{ route('public.services.index') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                Explore Services
            </a>
        </div>
    </div>
</div>
@endsection
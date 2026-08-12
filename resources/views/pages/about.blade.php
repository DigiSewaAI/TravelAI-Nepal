@extends('layouts.public')

@section('title', 'About Nepal Trek | TravelAI Nepal')
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">About Nepal Trek</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Discover the heart of the Himalayas and our mission to modernize trekking.
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- Mission Section --}}
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
            <i class="fas fa-flag text-blue-600 text-3xl"></i>
            <h3 class="text-xl font-bold text-gray-800 mt-3">Our Mission</h3>
            <p class="text-gray-600 mt-2">To make Nepal's trekking experiences safer, smarter, and more accessible through AI-powered technology and trusted local partnerships.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border hover:shadow-lg transition">
            <i class="fas fa-eye text-blue-600 text-3xl"></i>
            <h3 class="text-xl font-bold text-gray-800 mt-3">Our Vision</h3>
            <p class="text-gray-600 mt-2">A world where every traveler can explore Nepal with confidence, and every local provider can thrive in the digital age.</p>
        </div>
    </div>

    {{-- Story Section --}}
    <div class="bg-white rounded-xl shadow-md border p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Story</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed">
            <p>Nepal is home to eight of the world's fourteen highest peaks, including Mount Everest. Trekking is not just an activity here – it's a way of life. At TravelAI Nepal, we're dedicated to making this experience safer, smarter, and more accessible.</p>
            <p>Our platform connects travelers with trusted local providers, uses AI for personalized planning, and ensures safety through real-time tracking and SOS features.</p>
            <p>Whether you're a first-time trekker or a seasoned mountaineer, TravelAI is your ultimate companion for exploring the Himalayas.</p>
        </div>
    </div>

    {{-- Values --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Values</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-handshake text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">Trust</h4>
            <p class="text-sm text-gray-500">Building honest relationships with travelers and providers.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-lightbulb text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">Innovation</h4>
            <p class="text-sm text-gray-500">Using cutting-edge AI to transform Nepal's tourism.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-heart text-blue-600 text-3xl"></i>
            <h4 class="font-bold text-gray-800 mt-2">Community</h4>
            <p class="text-sm text-gray-500">Empowering local businesses and preserving culture.</p>
        </div>
    </div>
</div>

@endsection
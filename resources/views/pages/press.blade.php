@extends('layouts.public')

@section('title', 'Press & Media | TravelAI Nepal')
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Press & Media</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        TravelAI Nepal in the news — stories, features, and announcements.
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">15+</p>
            <p class="text-xs text-gray-500">Media Articles</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">8</p>
            <p class="text-xs text-gray-500">Interviews</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">5</p>
            <p class="text-xs text-gray-500">Awards</p>
        </div>
        <div class="bg-white rounded-xl p-4 text-center border hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">4</p>
            <p class="text-xs text-gray-500">Countries Covered</p>
        </div>
    </div>

    {{-- Press Releases --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Latest News</h2>
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">TravelAI Nepal Launches AI-Powered Trekking Platform</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> August 2026</span>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Featured</span>
                    </div>
                    <p class="text-gray-600 mt-2">New platform aims to revolutionize Nepal's trekking industry with AI itineraries and real-time safety features.</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">Read More →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Nepal Tourism Goes Digital with TravelAI</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> July 2026</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Partnership</span>
                    </div>
                    <p class="text-gray-600 mt-2">TravelAI partners with local agencies to bring blockchain-ready permits and AI planning to the Himalayas.</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">Read More →</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
            <div class="flex flex-wrap justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">TravelAI Nepal Wins Innovation Award</h3>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> June 2026</span>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Award</span>
                    </div>
                    <p class="text-gray-600 mt-2">Recognized for excellence in tourism technology and AI-driven solutions for the travel industry.</p>
                </div>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 md:mt-0">Read More →</a>
            </div>
        </div>
    </div>
</div>

@endsection
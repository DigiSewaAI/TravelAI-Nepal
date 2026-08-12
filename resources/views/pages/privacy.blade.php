@extends('layouts.public')

@section('title', 'Privacy Policy | TravelAI Nepal')
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Privacy Policy</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Your privacy matters to us. Learn how we protect your data.
    </p>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-md border p-8 space-y-6">
        <p class="text-gray-500 text-sm">Last updated: August 2026</p>

        <div>
            <h2 class="text-xl font-bold text-gray-800">1. Information We Collect</h2>
            <p class="text-gray-600 mt-2">We collect information you provide directly, such as your name, email, phone, and booking details. We also collect usage data to improve our services.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">2. How We Use Your Information</h2>
            <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                <li>To process bookings and provide itineraries</li>
                <li>To send notifications and updates</li>
                <li>To improve our platform and services</li>
                <li>To ensure safety and security</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">3. Data Security</h2>
            <p class="text-gray-600 mt-2">We implement industry-standard measures to protect your data from unauthorized access, alteration, or destruction.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">4. Third-Party Sharing</h2>
            <p class="text-gray-600 mt-2">We share data only with trusted partners (providers, payment gateways) as necessary to fulfill your bookings. We never sell your data.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">5. Your Rights</h2>
            <p class="text-gray-600 mt-2">You may access, correct, or delete your data by contacting us at support@travelai.com.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">6. Cookies</h2>
            <p class="text-gray-600 mt-2">We use cookies to enhance your experience. You can control cookie preferences in your browser settings.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-800">7. Changes to This Policy</h2>
            <p class="text-gray-600 mt-2">We may update this policy from time to time. We will notify you of any significant changes.</p>
        </div>

        <div class="pt-4 border-t">
            <p class="text-sm text-gray-500">Questions? Contact us at <a href="mailto:support@travelai.com" class="text-blue-600 hover:underline">support@travelai.com</a></p>
        </div>
    </div>
</div>

@endsection
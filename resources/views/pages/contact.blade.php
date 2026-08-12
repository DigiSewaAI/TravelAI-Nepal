@extends('layouts.public')

@section('title', 'Contact Us | TravelAI Nepal')
@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Contact Us</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        We'd love to hear from you. Reach out and we'll respond as soon as possible.
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    <div class="grid md:grid-cols-2 gap-8">
        {{-- Contact Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
                <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                <h3 class="font-bold text-gray-800 mt-2">Email</h3>
                <p class="text-gray-600">support@travelai.com</p>
                <p class="text-gray-600">sales@travelai.com</p>
            </div>

            <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
                <i class="fas fa-map-marker-alt text-blue-600 text-2xl"></i>
                <h3 class="font-bold text-gray-800 mt-2">Address</h3>
                <p class="text-gray-600">Lazimpat, Kathmandu</p>
                <p class="text-gray-600">Nepal</p>
            </div>

            <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
                <i class="fas fa-phone-alt text-blue-600 text-2xl"></i>
                <h3 class="font-bold text-gray-800 mt-2">Phone</h3>
                <p class="text-gray-600">+977-1-4XXXXXX</p>
                <p class="text-gray-500 text-sm">Mon–Fri, 9 AM – 6 PM</p>
            </div>

            <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-lg transition">
                <i class="fas fa-clock text-blue-600 text-2xl"></i>
                <h3 class="font-bold text-gray-800 mt-2">Working Hours</h3>
                <p class="text-gray-600">Sunday–Friday: 9 AM – 6 PM</p>
                <p class="text-gray-600">Saturday: Closed</p>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="bg-white rounded-xl shadow-md border p-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Send a Message</h3>
            <form action="mailto:support@travelai.com" method="POST" enctype="text/plain">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1">Your Name</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1">Your Email</label>
                    <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1">Subject</label>
                    <input type="text" name="subject" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="How can we help?">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-1">Message</label>
                    <textarea name="message" rows="5" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Write your message here..."></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition shadow-md">
                    <i class="fas fa-paper-plane mr-2"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
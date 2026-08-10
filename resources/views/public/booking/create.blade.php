@extends('layouts.public')

@section('title', 'Book ' . $service->name . ' | TravelAI Nepal')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">Explore</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.show', $service->slug) }}" class="hover:text-blue-600">{{ $service->name }}</a>
        <span class="mx-2">/</span>
        <span>Book</span>
    </nav>

    <div class="grid md:grid-cols-3 gap-8">
        <!-- Booking Form -->
        <div class="md:col-span-2 bg-white rounded-2xl shadow-md border p-6">
            <h1 class="text-2xl font-bold text-gray-900">Book: {{ $service->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">Fill in your details to confirm your booking.</p>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mt-4 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.services.book', $service->slug) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Special Requests (Optional)</label>
                    <textarea name="message" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i> Confirm Booking
                </button>
            </form>
        </div>

        <!-- Service Summary -->
        <div class="bg-gray-50 rounded-2xl border p-6 h-fit">
            <h3 class="font-bold text-gray-800 text-lg">Booking Summary</h3>
            <div class="mt-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Service</span>
                    <span class="font-medium">{{ $service->name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Provider</span>
                    <span class="font-medium">{{ $service->provider->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Category</span>
                    <span class="font-medium">{{ $service->category->name ?? 'N/A' }}</span>
                </div>
                @if($service->trekDetail)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Duration</span>
                        <span class="font-medium">{{ $service->trekDetail->duration_days }} days</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Difficulty</span>
                        <span class="font-medium">{{ ucfirst($service->trekDetail->difficulty) }}</span>
                    </div>
                @endif
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span class="text-blue-600">Rs. {{ number_format($service->price, 0) }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-4"><i class="fas fa-lock mr-1"></i> Your information is secure and will only be shared with the provider.</p>
        </div>
    </div>
</div>
@endsection
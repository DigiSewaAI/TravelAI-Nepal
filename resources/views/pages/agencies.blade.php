@extends('layouts.public')

@section('title', 'Agency Directory | TravelAI Nepal')

@section('content')
<main class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-4">Our Partner Agencies</h1>
    <p class="text-gray-600 text-center mb-8">Trusted local agencies ready to serve you.</p>

    <!-- Search form -->
    <div class="max-w-md mx-auto mb-10">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="flex-1 border rounded-lg px-4 py-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Search</button>
            @if(request('search'))
            <a href="{{ url('/agencies') }}" class="text-red-500 hover:underline text-sm flex items-center">Clear</a>
            @endif
        </form>
    </div>

    @if($agencies->count())
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($agencies as $agency)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition border border-gray-100">
            <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                @if($agency->logo_url)
                    <img src="{{ asset('storage/' . $agency->logo_url) }}" class="h-16 w-auto rounded-full bg-white p-1">
                @else
                    <i class="fas fa-building text-5xl text-white/70"></i>
                @endif
            </div>
            <div class="p-5">
                <h3 class="text-xl font-bold text-gray-800">{{ $agency->name }}</h3>
                <p class="text-gray-500 text-sm mt-1"><i class="fas fa-envelope"></i> {{ $agency->email }}</p>
                @if($agency->phone)
                <p class="text-gray-500 text-sm"><i class="fas fa-phone"></i> {{ $agency->phone }}</p>
                @endif
                @if($agency->address)
                <p class="text-gray-500 text-sm"><i class="fas fa-map-marker-alt"></i> {{ $agency->address }}</p>
                @endif
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Verified Partner</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-10">
        {{ $agencies->links() }}
    </div>
    @else
    <div class="text-center py-12 bg-white rounded-xl shadow">
        <i class="fas fa-building text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">No agencies found.</p>
    </div>
    @endif
</main>
@endsection
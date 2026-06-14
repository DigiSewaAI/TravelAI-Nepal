<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $trek->name }} | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <a href="/" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Home</a>
        <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $trek->name }}</h1>
        <div class="flex flex-wrap gap-4 text-gray-600 mb-6">
            <span><i class="fas fa-clock"></i> {{ $trek->duration_days }} days</span>
            <span><i class="fas fa-chart-line"></i> {{ ucfirst($trek->difficulty) }}</span>
            <span><i class="fas fa-tag"></i> ${{ number_format($trek->price, 2) }}</span>
            <span><i class="fas fa-building"></i> {{ $trek->agency->name }}</span>
        </div>

        @if($trek->cover_image)
        <img src="{{ asset('storage/' . $trek->cover_image) }}" alt="{{ $trek->name }}" class="w-full h-80 object-cover rounded-2xl mb-8 shadow-md">
        @else
        <div class="w-full h-80 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center mb-8">
            <i class="fas fa-mountain text-6xl text-white/70"></i>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-3">📅 Itinerary</h2>
            @if($trek->itinerary)
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                @foreach(json_decode($trek->itinerary, true) as $day)
                    <li>{{ $day }}</li>
                @endforeach
                </ul>
            @else
                <p class="text-gray-500">No itinerary provided yet.</p>
            @endif
        </div>

        {{-- ✅ Book Now Button --}}
        <div class="mb-6">
            <a href="{{ route('trek.book', $trek) }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl transition shadow-md hover:shadow-lg">
                <i class="fas fa-calendar-check mr-2"></i> Book Now
            </a>
        </div>

        @if($trek->gallery)
        <div class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-xl font-semibold mb-3">📸 Gallery</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach(json_decode($trek->gallery, true) as $img)
                <img src="{{ asset('storage/' . $img) }}" class="rounded-xl object-cover h-32 w-full cursor-pointer">
                @endforeach
            </div>
        </div>
        @endif
    </div>
</body>
</html>
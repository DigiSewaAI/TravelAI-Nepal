<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book {{ $trek->name }} | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto px-4 py-10">
        <a href="/" class="text-blue-600 hover:underline inline-block mb-6"><i class="fas fa-arrow-left"></i> Back to Home</a>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h1 class="text-2xl font-bold text-white">Book Your Trek</h1>
                <p class="text-blue-100">{{ $trek->name }} – {{ $trek->duration_days }} days, {{ ucfirst($trek->difficulty) }}</p>
            </div>

            <form method="POST" action="{{ route('trek.book', $trek) }}" class="p-6">
                @csrf

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded-lg px-3 py-2">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Phone Number *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full border rounded-lg px-3 py-2">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Start Date *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full border rounded-lg px-3 py-2">
                        @error('start_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 font-medium mb-1">Special Requests (optional)</label>
                    <textarea name="message" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('message') }}</textarea>
                </div>

                <div class="mt-8 flex gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">Confirm Booking</button>
                    <a href="/" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
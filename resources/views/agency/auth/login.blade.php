<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Login | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <i class="fas fa-mountain text-3xl text-blue-600"></i>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">TravelAI Nepal</h1>
            <p class="text-gray-500">Agency Login</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('agency.login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Sign In
            </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
            Don't have an account? 
            <a href="{{ route('agency.register') }}" class="text-blue-600 hover:underline">Register here</a>
        </p>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
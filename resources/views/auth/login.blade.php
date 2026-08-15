<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
            <!-- ===== LOGO & HEADER (Updated) ===== -->
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}"
                     alt="TravelAI Nepal"
                     class="h-16 mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-800">Welcome Back</h1>
                <p class="text-gray-500">Log in to your TravelAI account</p>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="remember" class="mr-2">
                        Remember me
                    </label>
                    <span class="text-sm text-gray-400">Forgot password? Contact support</span>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Log In
                </button>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Register here</a>
            </p>

            {{-- Link to old agency login --}}
            <div class="mt-4 text-center text-sm">
                <span class="text-gray-400">or</span>
                {{-- <a href="{{ route('agency.login') }}" class="text-blue-600 hover:underline ml-1">Agency Login (Legacy)</a> --}}
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
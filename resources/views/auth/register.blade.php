<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
            <div class="text-center mb-6">
                <i class="fas fa-mountain text-3xl text-blue-600"></i>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">TravelAI Nepal</h1>
                <p class="text-gray-500">Create your account</p>
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

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Basic Fields -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Plan Selection -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Choose Your Plan</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($plans ?? [] as $plan)
                            <label class="border rounded-lg p-3 cursor-pointer hover:border-blue-500 transition 
                                          {{ request('plan') == $plan->slug ? 'border-blue-600 bg-blue-50' : 'border-gray-200' }}">
                                <input type="radio" name="plan" value="{{ $plan->slug }}" 
                                       {{ request('plan') == $plan->slug ? 'checked' : '' }}
                                       class="hidden">
                                <div class="text-center">
                                    <span class="font-bold text-gray-800">{{ $plan->name }}</span>
                                    @if($plan->price_monthly == 0)
                                        <span class="text-sm text-green-600 block">Free</span>
                                    @elseif($plan->price_monthly !== null)
                                        <span class="text-sm text-blue-600 block">${{ number_format($plan->price_monthly, 0) }}/mo</span>
                                    @else
                                        <span class="text-sm text-gray-500 block">Custom</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Don't worry, you can change your plan later.</p>
                </div>

                <!-- Checkbox: Register as Business/Provider -->
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="is_provider" class="mr-2 w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               {{ old('provider_type') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Register as a business / provider</span>
                    </label>
                </div>

                <!-- Provider Fields (hidden by default) -->
                <div id="provider_fields" style="{{ old('provider_type') ? 'display: block;' : 'display: none;' }}">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Business Name</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Provider Type</label>
                        <select name="provider_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select type</option>
                            @foreach($providerTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ old('provider_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Register
                </button>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Log in here</a>
            </p>

            <div class="mt-4 text-center text-sm">
                <span class="text-gray-400">or</span>
                <a href="{{ route('agency.register') }}" class="text-blue-600 hover:underline ml-1">Agency Registration (Legacy)</a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        // Toggle provider fields when checkbox changes
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('is_provider');
            const fields = document.getElementById('provider_fields');

            if (checkbox && fields) {
                checkbox.addEventListener('change', function() {
                    fields.style.display = this.checked ? 'block' : 'none';
                });
            }
        });
    </script>
</body>
</html>
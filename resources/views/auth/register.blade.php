<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.register_page_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .account-card {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .account-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        .account-card.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 0 0 2px #2563eb, 0 10px 25px rgba(37, 99, 235, 0.15);
        }
        .account-card .checkmark {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background: #2563eb;
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .account-card.selected .checkmark {
            display: flex;
        }
        .plan-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .plan-card.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-2xl">
            <!-- ===== LOGO & HEADER ===== -->
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}"
                     alt="{{ __('messages.app_name') }}"
                     class="h-16 mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.app_name') }}</h1>
                <p class="text-gray-500">{{ __('messages.register_subtitle') }}</p>
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

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                {{-- ========== ACCOUNT TYPE SELECTION ========== --}}
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-3">{{ __('messages.register_account_type_question') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Traveler Card --}}
                        <div class="account-card border rounded-xl p-4 text-center {{ old('account_type', 'traveler') === 'traveler' ? 'selected' : '' }}" data-account="traveler">
                            <div class="checkmark"><i class="fas fa-check"></i></div>
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-user text-blue-600 text-xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">🧳 {{ __('messages.register_traveler') }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ __('messages.register_traveler_desc') }}</p>
                            <input type="radio" name="account_type" value="traveler" {{ old('account_type', 'traveler') === 'traveler' ? 'checked' : '' }} class="hidden account-radio">
                        </div>

                        {{-- Business/Provider Card --}}
                        <div class="account-card border rounded-xl p-4 text-center {{ old('account_type') === 'provider' ? 'selected' : '' }}" data-account="provider">
                            <div class="checkmark"><i class="fas fa-check"></i></div>
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-building text-green-600 text-xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">🏢 {{ __('messages.register_business') }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ __('messages.register_business_desc') }}</p>
                            <input type="radio" name="account_type" value="provider" {{ old('account_type') === 'provider' ? 'checked' : '' }} class="hidden account-radio">
                        </div>
                    </div>
                </div>

                {{-- ========== BASIC FIELDS ========== --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.full_name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.email') }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.password') }} *</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.password_confirmation') }} *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- ========== PROVIDER FIELDS ========== --}}
                <div id="providerFields" class="{{ old('account_type') === 'provider' ? 'block' : 'hidden' }} mt-6 pt-6 border-t">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_name') }} *</label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_type') }} *</label>
                        <select name="provider_type" id="provider_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('messages.select_business_type') }}</option>
                            @foreach($providerTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ old('provider_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('provider_type') == 'other' ? 'selected' : '' }}>{{ __('messages.other_specify') }}</option>
                        </select>
                    </div>

                    <div id="customTypeSection" style="{{ old('provider_type') == 'other' ? 'display: block;' : 'display: none;' }}" class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.specify_business_type') }} *</label>
                        <input type="text" name="custom_provider_type" value="{{ old('custom_provider_type') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="{{ __('messages.specify_business_type_placeholder') }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_description') }}</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.business_address') }}</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Plan Selection --}}
                    <div class="mt-6 pt-6 border-t">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.choose_plan') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($plans ?? [] as $plan)
                                <label class="plan-card border rounded-lg p-3 cursor-pointer transition {{ old('plan', 'free') == $plan->slug ? 'selected' : 'border-gray-200' }}">
                                    <input type="radio" name="plan" value="{{ $plan->slug }}" 
                                           {{ old('plan', 'free') == $plan->slug ? 'checked' : '' }}
                                           class="hidden">
                                    <div class="text-center">
                                        <span class="font-bold text-gray-800 block">{{ $plan->name }}</span>
                                        @if($plan->slug == 'enterprise')
                                            <span class="text-sm text-blue-600 block">{{ __('messages.contact_for_pricing') }}</span>
                                        @elseif($plan->price_monthly === 0 || $plan->price_yearly === 0)
                                            <span class="text-sm text-green-600 block">{{ __('messages.free') }}</span>
                                        @else
                                            <span class="text-sm text-blue-600 block">
                                                {{ session('display_currency', 'USD') === 'USD' ? '$' : 'Rs. ' }}
                                                {{ number_format($plan->price_monthly, 0) }}/mo
                                            </span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('messages.change_plan_later') }}</p>
                    </div>
                </div>

                {{-- ========== SUBMIT BUTTON ========== --}}
                <div class="mt-6">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <span id="submitBtnText">{{ old('account_type', 'traveler') === 'traveler' ? __('messages.create_traveler_account') : __('messages.create_business_account') }}</span>
                    </button>
                </div>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">
                {{ __('messages.already_have_account') }}
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">{{ __('messages.login_here') }}</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Account Type Selection
            const accountCards = document.querySelectorAll('.account-card');
            const providerFields = document.getElementById('providerFields');
            const submitBtnText = document.getElementById('submitBtnText');

            accountCards.forEach(card => {
                card.addEventListener('click', function() {
                    accountCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');

                    const radio = this.querySelector('.account-radio');
                    if (radio) radio.checked = true;

                    const accountType = this.dataset.account;
                    if (accountType === 'provider') {
                        providerFields.classList.remove('hidden');
                        submitBtnText.textContent = '{{ __('messages.create_business_account') }}';
                    } else {
                        providerFields.classList.add('hidden');
                        submitBtnText.textContent = '{{ __('messages.create_traveler_account') }}';
                    }
                });
            });

            // Custom Type Input Toggle
            const typeSelect = document.getElementById('provider_type');
            const customSection = document.getElementById('customTypeSection');

            if (typeSelect && customSection) {
                typeSelect.addEventListener('change', function() {
                    customSection.style.display = this.value === 'other' ? 'block' : 'none';
                });
            }

            // Plan Card Selection
            document.querySelectorAll('.plan-card').forEach(card => {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                });
            });
        });
    </script>
</body>
</html>
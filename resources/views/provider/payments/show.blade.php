@extends('layouts.provider')

@section('title', 'Complete Your Payment')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Back --}}
    <a href="{{ route('provider.subscriptions.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600 mb-4 transition">
        <i class="fas fa-arrow-left"></i> Back to Subscriptions
    </a>

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Complete Your Payment</h1>
            <p class="text-gray-500 mt-1">
                {{ $subscription->plan->name }} Plan ·
                Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }} / month
            </p>
        </div>

        {{-- 3-Step Progress --}}
        <div class="flex items-center gap-3 text-xs font-semibold">
            <span class="flex items-center gap-2 text-blue-600">
                <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center">1</span>
                Make Payment
            </span>
            <span class="w-8 h-0.5 bg-gray-300"></span>
            <span class="flex items-center gap-2 {{ $pendingPayment ? 'text-blue-600' : 'text-gray-400' }}">
                <span class="w-7 h-7 rounded-full {{ $pendingPayment ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center">2</span>
                Submit Proof
            </span>
            <span class="w-8 h-0.5 bg-gray-300"></span>
            <span class="flex items-center gap-2 text-gray-400">
                <span class="w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center">3</span>
                Verification
            </span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Plan summary --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-gray-900">{{ $subscription->plan->name }} Plan</h2>
                        <p class="text-blue-600 font-bold text-xl">
                            Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }}
                            <span class="text-sm text-gray-500 font-normal">/ month</span>
                        </p>
                        @if(!empty($subscription->plan->limits))
                            <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-1 text-sm text-gray-600">
                                @foreach((array) $subscription->plan->limits as $key => $value)
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-check text-green-500"></i>
                                        <span>{{ ucfirst(str_replace('_', ' ', $key)) }}: <strong>{{ $value }}</strong></span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment methods --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="fas fa-hand-holding-usd text-blue-600"></i>
                    Choose how to pay TravelAI
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Pay to one of the accounts below. Then submit your proof for verification.
                </p>

                @if($platformMethods->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800 text-sm">
                        <i class="fas fa-info-circle mr-1"></i>
                        Payment methods are being configured. Please contact support.
                    </div>
                @else
                    <div class="grid md:grid-cols-3 gap-4">
                        @foreach($platformMethods as $method)
                            <div class="border border-gray-200 rounded-xl p-4 flex flex-col">
                                <div class="flex items-center gap-2 mb-3">
                                    @if($method->type === 'bank')
                                        <i class="fas fa-university text-blue-600 text-xl"></i>
                                    @elseif($method->type === 'esewa')
                                        <i class="fas fa-mobile-alt text-green-600 text-xl"></i>
                                    @elseif($method->type === 'khalti')
                                        <i class="fas fa-wallet text-purple-600 text-xl"></i>
                                    @else
                                        <i class="fas fa-credit-card text-gray-600 text-xl"></i>
                                    @endif
                                    <span class="font-semibold text-gray-800">{{ $method->label }}</span>
                                </div>

                                @if($method->bank_name)
                                    <p class="text-xs text-gray-500">Bank</p>
                                    <p class="text-sm font-medium text-gray-800 mb-1">{{ $method->bank_name }}</p>
                                @endif
                                @if($method->account_name)
                                    <p class="text-xs text-gray-500">Account Name</p>
                                    <p class="text-sm font-medium text-gray-800 mb-1">{{ $method->account_name }}</p>
                                @endif
                                @if($method->account_number)
                                    <p class="text-xs text-gray-500">Account Number</p>
                                    <p class="text-sm font-mono font-medium text-gray-800 mb-1">{{ $method->account_number }}</p>
                                @endif
                                @if($method->identifier)
                                    <p class="text-xs text-gray-500">{{ $method->type === 'esewa' ? 'eSewa ID' : ($method->type === 'khalti' ? 'Khalti Number' : 'ID') }}</p>
                                    <p class="text-sm font-mono font-medium text-gray-800 mb-1">{{ $method->identifier }}</p>
                                @endif

                                @if($method->qr_image_path)
                                    <div class="mt-2 text-center">
                                        <img src="{{ asset('storage/' . $method->qr_image_path) }}"
                                             alt="{{ $method->label }} QR"
                                             class="w-32 h-32 mx-auto object-contain rounded border border-gray-200 bg-white p-1">
                                    </div>
                                @endif

                                <button type="button"
                                        onclick="copyText(this, '{{ $method->account_number ?? $method->identifier }}')"
                                        class="mt-auto w-full mt-3 text-sm bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg py-2 flex items-center justify-center gap-2 transition">
                                    <i class="fas fa-copy"></i>
                                    <span>Copy {{ $method->type === 'bank' ? 'Details' : 'ID' }}</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Submit proof --}}
            @if($pendingPayment)
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-hourglass-half text-orange-500 text-2xl mt-1"></i>
                        <div>
                            <h3 class="font-bold text-orange-800">Awaiting Verification</h3>
                            <p class="text-sm text-orange-700 mt-1">
                                Your payment proof was submitted on
                                {{ $pendingPayment->created_at->format('M d, Y g:i A') }}.
                                Reference: <strong>{{ $pendingPayment->reference_number }}</strong>
                            </p>
                            <p class="text-xs text-orange-600 mt-2">
                                We verify payments within 24 hours. You'll be notified once approved.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <form method="POST"
                      action="{{ route('provider.payments.create', $subscription->id) }}"
                      enctype="multipart/form-data"
                      class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    @csrf

                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-upload text-blue-600"></i>
                        Submit payment proof
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Reference Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="reference_number" required
                                   value="{{ old('reference_number') }}"
                                   placeholder="e.g., TXN-1234567890"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reference_number') border-red-500 @enderror">
                            @error('reference_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Upload Receipt <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="receipt" accept="image/*,application/pdf" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('receipt') border-red-500 @enderror">
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, or PDF · Max 5 MB</p>
                            @error('receipt')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit"
                            class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Submit for Verification
                    </button>
                </form>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-receipt text-blue-600"></i>
                    Payment Summary
                </h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Plan</dt>
                        <dd class="font-semibold text-gray-800">{{ $subscription->plan->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="font-semibold text-gray-800">Rs. {{ number_format($subscription->plan->price_monthly ?? 0, 2) }} / mo</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Billing</dt>
                        <dd class="font-semibold text-gray-800">{{ ucfirst($subscription->billing_interval ?? 'monthly') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Important:</strong> This payment is for your TravelAI subscription
                and will be manually verified by our team. Once verified, your subscription
                will be activated and you'll get full access to {{ $subscription->plan->name }} features.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyText(btn, text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
        btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
        setTimeout(function () {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
        }, 1500);
    });
}
</script>
@endpush
@endsection
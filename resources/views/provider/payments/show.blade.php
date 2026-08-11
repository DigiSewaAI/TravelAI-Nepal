@extends('layouts.provider')

@section('title', 'Payment | TravelAI Nepal')
@section('header', 'Payment')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Complete Your Payment</h2>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Plan</p>
                    <p class="font-semibold text-lg">{{ $subscription->plan->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Amount</p>
                    <p class="font-bold text-2xl text-blue-600">
                        Rs. {{ number_format($subscription->plan->price_yearly ?? $subscription->plan->price_monthly ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div id="payment-element"></div>

        <button id="submit-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition mt-4 disabled:opacity-50 disabled:cursor-not-allowed">
            Pay Now
        </button>

        <p class="text-center text-sm text-gray-500 mt-4">
            <i class="fas fa-lock mr-1"></i> Secure payment powered by Stripe
        </p>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');

    document.addEventListener('DOMContentLoaded', async function() {
        const submitBtn = document.getElementById('submit-btn');

        // Fetch payment intent
        try {
            const response = await fetch('{{ route('provider.payments.create', $subscription->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Failed to initialize payment');
                return;
            }

            // Create Stripe Elements
            const elements = stripe.elements({
                clientSecret: data.client_secret,
            });

            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            submitBtn.addEventListener('click', async () => {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                const { error } = await stripe.confirmPayment({
                    elements,
                    redirect: 'if_required',
                });

                if (error) {
                    alert(error.message || 'Payment failed');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Pay Now';
                } else {
                    // Payment succeeded
                    window.location.href = '{{ route('provider.payments.confirm') }}?payment_id=' + data.payment_id;
                }
            });

        } catch (error) {
            alert('Failed to initialize payment. Please try again.');
            console.error(error);
        }
    });
</script>
@endsection
@extends('layouts.provider')

@section('title', 'AI Quotation')
@section('header', 'AI Quotation Generator')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Generate AI Quotation</h2>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                {{ session('error') }}
                @if(str_contains(session('error'), 'limit'))
                    <br>
                    <a href="{{ route('provider.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800 underline">
                        Upgrade Your Plan
                    </a>
                @endif
            </div>
        @endif

        {{-- AI Usage Widget --}}
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">AI Requests Available:</span>
                <span class="text-sm font-bold">
                    @if($usage['is_unlimited'])
                        <span class="text-green-600">♾️ Unlimited</span>
                    @else
                        <span class="{{ $usage['remaining'] > 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ $usage['remaining'] }} / {{ $usage['limit'] }}
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <form id="quotationForm" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium mb-1">Customer Name *</label>
                <input type="text" name="customer_name" id="customer_name"
                       class="w-full border rounded-lg px-4 py-2" required>
            </div>

            <div>
                <label class="block font-medium mb-1">Customer Email</label>
                <input type="email" name="customer_email" id="customer_email"
                       class="w-full border rounded-lg px-4 py-2">
            </div>

            <div>
                <label class="block font-medium mb-1">Customer Phone</label>
                <input type="text" name="customer_phone" id="customer_phone"
                       class="w-full border rounded-lg px-4 py-2">
            </div>

            <div>
                <label class="block font-medium mb-1">Service (optional)</label>
                <select name="service_id" id="service_id" class="w-full border rounded-lg px-4 py-2">
                    <option value="">Select a service...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
                        </div>

            {{-- Phase 5C: Trip details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('messages.quotation_days') }} *</label>
                    <input type="number" name="days" id="days" min="1" max="30" value="7" required
                           class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('messages.quotation_pax') }} *</label>
                    <input type="number" name="pax" id="pax" min="1" max="20" value="2" required
                           class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('messages.quotation_start_date') }} *</label>
                    <input type="date" name="start_date" id="start_date" required
                           min="{{ now()->format('Y-m-d') }}"
                           value="{{ now()->addDays(7)->format('Y-m-d') }}"
                           class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('messages.quotation_accommodation') }} *</label>
                    <select name="accommodation" id="accommodation" required
                            class="w-full border rounded-lg px-4 py-2">
                        <option value="budget">{{ __('messages.quotation_accommodation_budget') }}</option>
                        <option value="standard" selected>{{ __('messages.quotation_accommodation_standard') }}</option>
                        <option value="luxury">{{ __('messages.quotation_accommodation_luxury') }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-medium mb-1">Additional Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full border rounded-lg px-4 py-2"
                          placeholder="e.g., Special requests, travel dates, group size..."></textarea>
            </div>

            <button type="submit" id="generateBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-magic"></i> Generate Quotation
            </button>
        </form>

        <div id="result" class="mt-6 hidden">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xl font-bold text-gray-800">Generated Quotation</h3>
                <div class="flex gap-2">
                    <button onclick="copyQuotation()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button onclick="downloadQuotation()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            <div id="quotationResult" class="bg-gray-50 p-5 rounded-xl border border-gray-200 text-gray-800 max-h-[500px] overflow-y-auto whitespace-pre-wrap font-mono text-sm">
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('quotationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('generateBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        try {
                        const payload = {
                customer_name: document.getElementById('customer_name').value,
                customer_email: document.getElementById('customer_email').value,
                customer_phone: document.getElementById('customer_phone').value,
                service_id: document.getElementById('service_id').value,
                notes: document.getElementById('notes').value,
                days: parseInt(document.getElementById('days').value, 10),
                pax: parseInt(document.getElementById('pax').value, 10),
                start_date: document.getElementById('start_date').value,
                accommodation: document.getElementById('accommodation').value,
            };

            const response = await fetch('{{ route("provider.quotation.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                const resultDiv = document.getElementById('result');
                const contentDiv = document.getElementById('quotationResult');

                const q = data.quotation;
                // ✅ Controller बाट आएको पूरा Content Display गर्ने (Duplicate Header हटाइयो)
contentDiv.innerHTML = q.content;

                resultDiv.classList.remove('hidden');
            } else {
                alert(data.message || 'Failed to generate quotation.');
            }
        } catch (error) {
            console.error(error);
            alert('Server error. Please try again.');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    });

    function copyQuotation() {
        const text = document.getElementById('quotationResult').innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('Quotation copied to clipboard!');
        }).catch(() => {
            alert('Copy failed. Please select and copy manually.');
        });
    }

    function downloadQuotation() {
        const text = document.getElementById('quotationResult').innerText;
        const blob = new Blob([text], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'quotation.txt';
        a.click();
        URL.revokeObjectURL(a.href);
    }
</script>
@endsection
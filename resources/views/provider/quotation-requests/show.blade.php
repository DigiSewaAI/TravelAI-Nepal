@extends('layouts.provider')

@section('title', 'Quotation Request #' . $quotationRequest->id)
@section('header', 'Quotation Request Detail')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Request Header --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold">Request #{{ $quotationRequest->id }}</h2>
                <p class="text-gray-500">
                    From: <strong>
                        {{ $quotationRequest->traveler_name ?? $quotationRequest->traveler->name ?? 'Guest' }}
                    </strong>
                    ({{ $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? 'N/A' }})
                </p>
                <p class="text-gray-500">Received: {{ $quotationRequest->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="px-3 py-1 text-sm rounded-full
                @if($quotationRequest->status == 'pending') bg-yellow-100 text-yellow-800
                @elseif($quotationRequest->status == 'viewed') bg-blue-100 text-blue-800
                @elseif($quotationRequest->status == 'processing') bg-purple-100 text-purple-800
                @elseif($quotationRequest->status == 'completed') bg-green-100 text-green-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst($quotationRequest->status) }}
            </span>
        </div>

        @if($quotationRequest->message)
            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                <p class="font-semibold">Message from traveler:</p>
                <p class="text-gray-700">{{ $quotationRequest->message }}</p>
            </div>
        @endif
    </div>

    {{-- Traveler Contact Information (NEW) --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-4">📞 Traveler Contact Information</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <strong>Name:</strong>
                <span class="block text-gray-700">
                    {{ $quotationRequest->traveler_name ?? $quotationRequest->traveler->name ?? 'Guest' }}
                </span>
            </div>
            <div>
                <strong>Email:</strong>
                <span class="block text-gray-700">
                    {{ $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? 'N/A' }}
                </span>
            </div>
            <div>
                <strong>Phone:</strong>
                <span class="block text-gray-700">
                    {{ $quotationRequest->traveler_phone ?? $quotationRequest->traveler->phone ?? 'N/A' }}
                </span>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-400">
            @if($quotationRequest->traveler_id)
                <span class="text-green-600">✅ Registered User</span>
            @else
                <span class="text-yellow-600">🟡 Guest User</span>
            @endif
        </div>
    </div>

    {{-- Traveler Input --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-4">Traveler's Request</h3>
        <div class="grid grid-cols-2 gap-4">
            <div><strong>Destination:</strong> {{ $quotationRequest->traveler_input['destination'] ?? 'N/A' }}</div>
            <div><strong>Days:</strong> {{ $quotationRequest->traveler_input['days'] ?? 'N/A' }}</div>
            <div><strong>Budget:</strong> ${{ $quotationRequest->traveler_input['budget'] ?? 'N/A' }}</div>
            <div><strong>Style:</strong> {{ $quotationRequest->traveler_input['travel_style'] ?? 'N/A' }}</div>
            <div class="col-span-2">
                <strong>Interests:</strong> {{ implode(', ', $quotationRequest->traveler_input['interests'] ?? []) ?: 'None' }}
            </div>
        </div>
    </div>

    {{-- Itinerary --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-bold mb-4">Itinerary</h3>
        @if(isset($quotationRequest->itinerary_data['days']))
            @foreach($quotationRequest->itinerary_data['days'] as $day)
                <div class="border-b border-gray-100 pb-3 mb-3 last:border-0">
                    <h4 class="font-semibold text-blue-700">Day {{ $day['day_number'] }}: {{ $day['title'] }}</h4>
                    <p class="text-sm text-gray-600">{{ $day['description'] ?? '' }}</p>
                    @if(isset($day['items']))
                        <ul class="list-disc ml-5 text-sm text-gray-600">
                            @foreach($day['items'] as $item)
                                <li>{{ $item['title'] }} – {{ $item['description'] ?? '' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @else
            <p class="text-gray-500">No itinerary data available.</p>
        @endif
    </div>

    {{-- Generate Quotation --}}
    @if($quotationRequest->status !== 'completed')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Generate Quotation</h3>
            <p class="text-sm text-gray-500 mb-4">AI will use the itinerary above to generate a professional quotation.</p>
            <button onclick="generateQuotation()" id="generateBtn" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2">
                <i class="fas fa-magic"></i> Generate AI Quotation
            </button>
            <div id="quotationResult" class="mt-4 hidden">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-bold">Generated Quotation</h4>
                    <div class="flex gap-2">
                        <button onclick="copyQuotation()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">Copy</button>
                        <button onclick="downloadQuotation()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Download</button>
                    </div>
                </div>
                <div id="quotationContent" class="bg-gray-50 p-4 rounded border border-gray-200 max-h-96 overflow-y-auto whitespace-pre-wrap text-sm font-mono"></div>
            </div>
            <div id="quotationLoading" class="hidden text-center py-4">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
                <p class="mt-2 text-gray-500">Generating quotation... Please wait.</p>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-green-600">✅ Quotation Generated</h3>
            <div class="bg-gray-50 p-4 rounded border border-gray-200 max-h-96 overflow-y-auto whitespace-pre-wrap text-sm font-mono">
                {{ $quotationRequest->quotation_text ?? 'Quotation not available.' }}
            </div>
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('provider.quotation-requests.index') }}" class="text-blue-600 hover:underline">← Back to Requests</a>
    </div>
</div>

<script>
function generateQuotation() {
    const btn = document.getElementById('generateBtn');
    const loading = document.getElementById('quotationLoading');
    const result = document.getElementById('quotationResult');
    const content = document.getElementById('quotationContent');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    loading.classList.remove('hidden');
    result.classList.add('hidden');

    fetch('{{ route("provider.quotation-requests.generate", $quotationRequest) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        loading.classList.add('hidden');
        if (data.success) {
            content.textContent = data.quotation;
            result.classList.remove('hidden');
            btn.innerHTML = '<i class="fas fa-check"></i> Regenerate Quotation';
            btn.disabled = false;
            
            // Reload page after 2 seconds to show updated status
            setTimeout(() => location.reload(), 3000);
        } else {
            alert(data.message || 'Failed to generate quotation.');
            btn.innerHTML = '<i class="fas fa-magic"></i> Generate AI Quotation';
            btn.disabled = false;
        }
    })
    .catch(err => {
        loading.classList.add('hidden');
        alert('Server error. Please try again.');
        btn.innerHTML = '<i class="fas fa-magic"></i> Generate AI Quotation';
        btn.disabled = false;
    });
}

function copyQuotation() {
    const text = document.getElementById('quotationContent').textContent;
    navigator.clipboard.writeText(text).then(() => alert('Copied!')).catch(() => alert('Copy failed.'));
}

function downloadQuotation() {
    const text = document.getElementById('quotationContent').textContent;
    const blob = new Blob([text], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'quotation.txt';
    a.click();
    URL.revokeObjectURL(a.href);
}
</script>
@endsection
@extends('layouts.admin')

@section('header', 'Manual Claim — ' . $cat['label'])

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Back --}}
    <a href="{{ route('admin.claims.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold text-sm mb-4">
        ← Back to Claims List
    </a>

    {{-- Errors --}}
    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
        ❌ {{ $errors->first() }}
    </div>
    @endif

    {{-- Existing Claim Warning --}}
    @if($existingClaim)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-4 rounded">
        ⚠️ <strong>Already claimed!</strong> This transaction was claimed by Traveler ID {{ $existingClaim->traveler_id }} on {{ $existingClaim->claimed_at ? $existingClaim->claimed_at->format('M d, Y') : 'N/A' }}.
    </div>
    @endif

    {{-- Transaction Detail --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-5">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-xs opacity-90 uppercase font-bold">{{ $cat['icon'] }} {{ $cat['label'] }} Transaction</div>
                    <h2 class="text-2xl font-bold mt-1">{{ $transaction->{$cat['ref_field']} ?? ('#' . $transaction->id) }}</h2>
                </div>
                <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-bold">ID: {{ $transaction->id }}</span>
            </div>
        </div>

        <div class="p-5 space-y-3">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Customer Name</div>
                    <div class="text-sm font-bold">
                        {{ $transaction->customer_name ?? $transaction->client_name ?? $transaction->guest_name ?? $transaction->passenger_name ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Email</div>
                    <div class="text-sm font-bold">
                        @if($transaction->{$cat['email_field']})
                            {{ $transaction->{$cat['email_field']} }}
                        @else
                            <span class="text-red-600">❌ NULL — email not provided</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Amount</div>
                    <div class="text-lg font-bold text-green-700">
                        NPR {{ number_format($transaction->{$cat['amount_field']} ?? 0, 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Date</div>
                    <div class="text-sm font-bold">
                        {{ $transaction->{$cat['date_field']} ? \Carbon\Carbon::parse($transaction->{$cat['date_field']})->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve Form --}}
    @if(!$existingClaim)
    <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-bold text-gray-800 mb-4">🔗 Link to Traveler Account</h3>

        <form method="POST" action="{{ route('admin.claims.approve', [$type, $transaction->id]) }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Select Traveler *</label>
                <select name="traveler_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">— Choose Traveler —</option>
                    @foreach($travelers as $traveler)
                    <option value="{{ $traveler->id }}">
                        {{ $traveler->name }} ({{ $traveler->email }})
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">If traveler is not listed, they must register first.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Reason for manual claim (e.g. customer called, email not provided, etc.)"></textarea>
            </div>

            <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded">
                <p class="text-xs font-bold text-red-800">⚠️ This will IMMEDIATELY link the transaction to the traveler's dashboard. Cannot be undone.</p>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-bold text-sm">
                    ✅ Approve & Claim
                </button>
                <a href="{{ route('admin.claims.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-bold text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection
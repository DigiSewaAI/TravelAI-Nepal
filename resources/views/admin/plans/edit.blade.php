@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Plan: {{ $plan->name }}</h1>
        <a href="{{ route('admin.plans.index') }}" class="text-gray-500 hover:text-gray-700">&larr; Back to Plans</a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4 shadow-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Plan Name</label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Monthly Price (Rs)</label>
                <input type="number" step="0.01" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Yearly Price (Rs)</label>
                <input type="number" step="0.01" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-4 flex items-center bg-gray-50 p-3 rounded border border-gray-200">
            <input type="checkbox" name="requires_contact" id="requires_contact" value="1" {{ old('requires_contact', $plan->requires_contact) ? 'checked' : '' }} class="mr-3 w-4 h-4 text-blue-600 rounded">
            <label for="requires_contact" class="text-gray-800 font-bold cursor-pointer">Requires Contact (Enterprise Plan)</label>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Limits (JSON format)</label>
            <textarea name="limits" rows="6" class="w-full border border-gray-300 p-2 rounded font-mono text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('limits', json_encode($plan->limits, JSON_PRETTY_PRINT)) }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Define the maximum allowed entities for this plan. Example: {"max_rooms": 30, "max_staff": 5}</p>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Features (JSON format)</label>
            <textarea name="features" rows="4" class="w-full border border-gray-300 p-2 rounded font-mono text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('features', json_encode($plan->features, JSON_PRETTY_PRINT)) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.plans.index') }}" class="px-5 py-2 text-gray-600 hover:bg-gray-100 rounded font-semibold">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold shadow-sm">Update Plan</button>
        </div>
    </form>
</div>
@endsection
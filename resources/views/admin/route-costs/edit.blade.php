@extends('layouts.admin')
@section('title', 'Edit Route Cost')
@section('header', 'Edit Route Cost')
@section('content')
<form method="POST" action="{{ route('admin.route-costs.update', $routeCost) }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium mb-1">Route *</label>
            <select name="route_id" class="w-full border rounded-lg px-4 py-2" required>
                @foreach($routes as $id => $name)
                <option value="{{ $id }}" {{ old('route_id', $routeCost->route_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">Cost Type *</label>
            <input type="text" name="type" class="w-full border rounded-lg px-4 py-2" value="{{ old('type', $routeCost->type) }}" placeholder="e.g., permit, food, guide" required>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Name</label>
            <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name', $routeCost->name) }}" placeholder="Display name">
        </div>
        <div>
            <label class="block font-medium mb-1">Amount *</label>
            <input type="number" step="0.01" name="amount" class="w-full border rounded-lg px-4 py-2" value="{{ old('amount', $routeCost->amount) }}" required>
        </div>
        <div>
            <label class="block font-medium mb-1">Currency</label>
            <select name="currency" class="w-full border rounded-lg px-4 py-2">
                <option value="NPR" {{ old('currency', $routeCost->currency) == 'NPR' ? 'selected' : '' }}>NPR</option>
                <option value="USD" {{ old('currency', $routeCost->currency) == 'USD' ? 'selected' : '' }}>USD</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Unit *</label>
            <select name="unit" class="w-full border rounded-lg px-4 py-2" required>
                <option value="per_person" {{ old('unit', $routeCost->unit) == 'per_person' ? 'selected' : '' }}>Per Person</option>
                <option value="per_group" {{ old('unit', $routeCost->unit) == 'per_group' ? 'selected' : '' }}>Per Group</option>
                <option value="per_day" {{ old('unit', $routeCost->unit) == 'per_day' ? 'selected' : '' }}>Per Day</option>
                <option value="per_km" {{ old('unit', $routeCost->unit) == 'per_km' ? 'selected' : '' }}>Per Km</option>
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">Effective From *</label>
            <input type="date" name="effective_from" class="w-full border rounded-lg px-4 py-2" value="{{ old('effective_from', $routeCost->effective_from->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Effective Until</label>
            <input type="date" name="effective_until" class="w-full border rounded-lg px-4 py-2" value="{{ old('effective_until', $routeCost->effective_until ? $routeCost->effective_until->format('Y-m-d') : '') }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Metadata (JSON)</label>
            <textarea name="metadata" class="w-full border rounded-lg px-4 py-2" rows="1" placeholder='{"source":"govt"}'>{{ old('metadata', json_encode($routeCost->metadata)) }}</textarea>
        </div>
    </div>
    <div class="mt-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_mandatory" value="1" {{ old('is_mandatory', $routeCost->is_mandatory) ? 'checked' : '' }} class="mr-2"> Mandatory
        </label>
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
        <a href="{{ route('admin.route-costs.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection
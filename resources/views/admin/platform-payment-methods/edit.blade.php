@extends('layouts.admin')

@section('title', 'Edit Payment Method')
@section('header', 'Edit Payment Method')

@section('content')
<div class="max-w-3xl mx-auto">

    <a href="{{ route('admin.platform-payment-methods.index') }}" class="text-sm text-gray-500 hover:text-blue-600">
        ← Back to Payment Methods
    </a>

    <div class="bg-white rounded-xl shadow-sm border p-6 mt-4">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Edit: {{ $method->label }}</h1>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.platform-payment-methods.update', $method) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        @foreach(['bank','esewa','khalti','other'] as $t)
                            <option value="{{ $t }}" {{ $method->type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Label *</label>
                    <input type="text" name="label" value="{{ old('label', $method->label) }}" required maxlength="100"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Account Name</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $method->account_name) }}" maxlength="150"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $method->account_number) }}" maxlength="50"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $method->bank_name) }}" maxlength="100"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Identifier</label>
                    <input type="text" name="identifier" value="{{ old('identifier', $method->identifier) }}" maxlength="50"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Currency *</label>
                    <input type="text" name="currency" value="{{ old('currency', $method->currency) }}" required maxlength="3"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Instructions</label>
                <textarea name="instructions" rows="3" maxlength="1000"
                          class="w-full border rounded-lg px-3 py-2">{{ old('instructions', $method->instructions) }}</textarea>
            </div>

            @if($method->qr_image_path)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-2">Current QR:</p>
                    <img src="{{ Storage::url($method->qr_image_path) }}" class="h-24">
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">Replace QR Image (optional)</label>
                <input type="file" name="qr_image" accept="image/*"
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $method->sort_order) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex items-center mt-6">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $method->is_active) ? 'checked' : '' }}
                           class="mr-2">
                    <label class="text-sm font-medium">Active</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.platform-payment-methods.index') }}"
                   class="px-4 py-2 border rounded-lg text-sm">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                    Update Method
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
@extends('layouts.admin')

@section('title', 'Add Payment Method')
@section('header', 'Add Payment Method')

@section('content')
<div class="max-w-3xl mx-auto">

    <a href="{{ route('admin.platform-payment-methods.index') }}" class="text-sm text-gray-500 hover:text-blue-600">
        ← Back to Payment Methods
    </a>

    <div class="bg-white rounded-xl shadow-sm border p-6 mt-4">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Add Payment Method</h1>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.platform-payment-methods.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type *</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="esewa" {{ old('type') === 'esewa' ? 'selected' : '' }}>eSewa</option>
                        <option value="khalti" {{ old('type') === 'khalti' ? 'selected' : '' }}>Khalti</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Label * (shown to providers)</label>
                    <input type="text" name="label" value="{{ old('label') }}" required maxlength="100"
                           placeholder="e.g., NIC Asia Bank Transfer"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Account Name</label>
                    <input type="text" name="account_name" value="{{ old('account_name') }}" maxlength="150"
                           placeholder="e.g., TravelAI Nepal Pvt. Ltd."
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" maxlength="50"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" maxlength="100"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Identifier / Merchant ID</label>
                    <input type="text" name="identifier" value="{{ old('identifier') }}" maxlength="50"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Currency *</label>
                    <input type="text" name="currency" value="{{ old('currency', 'NPR') }}" required maxlength="3"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Instructions</label>
                <textarea name="instructions" rows="3" maxlength="1000"
                          class="w-full border rounded-lg px-3 py-2">{{ old('instructions') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">QR Image (optional)</label>
                <input type="file" name="qr_image" accept="image/*"
                       class="w-full border rounded-lg px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Max 2 MB</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex items-center mt-6">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="mr-2">
                    <label class="text-sm font-medium">Active (visible to providers)</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.platform-payment-methods.index') }}"
                   class="px-4 py-2 border rounded-lg text-sm">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                    Save Method
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
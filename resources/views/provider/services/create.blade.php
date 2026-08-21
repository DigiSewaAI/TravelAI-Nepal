@extends('layouts.provider')

@section('title', 'Create Service | TravelAI Nepal')
@section('header', 'Create Service')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border p-6">

    {{-- ✅ Success Message (only when service created) --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close float-right" data-bs-dismiss="alert" aria-label="Close">&times;</button>
        </div>
    @endif

    {{-- ❌ Remove session('error') block — it's redundant now --}}

    {{-- ✅ General Validation Errors (excluding 'limit' field) --}}
    @php
        $generalErrors = $errors->getMessages();
        unset($generalErrors['limit']); // Remove limit error from general list
    @endphp

    @if (!empty($generalErrors))
        <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded-lg alert-dismissible fade show" role="alert">
            <strong>Validation Error!</strong>
            <ul class="mb-0 mt-1 list-disc list-inside">
                @foreach ($generalErrors as $field => $messages)
                    @foreach ($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @endforeach
            </ul>
            <button type="button" class="btn-close float-right" data-bs-dismiss="alert" aria-label="Close">&times;</button>
        </div>
    @endif

    {{-- ✅ Specific Limit Error (with Upgrade Link) — only one message --}}
    @error('limit')
        <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded-lg alert-dismissible fade show" role="alert">
            {!! $message !!}  {{-- HTML link inside --}}
            <button type="button" class="btn-close float-right" data-bs-dismiss="alert" aria-label="Close">&times;</button>
        </div>
    @enderror

    <form method="POST" action="{{ route('provider.services.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Service Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Category *</label>
                <select name="service_category_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('service_category_id') border-red-500 @enderror">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Price</label>
                <div class="flex gap-2">
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01"
                           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                    <select name="currency" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="NPR" {{ old('currency', 'USD') == 'NPR' ? 'selected' : '' }}>NPR</option>
                    </select>
                </div>
                @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('currency')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Cover Image</label>
                <input type="file" name="cover_image" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cover_image') border-red-500 @enderror">
                @error('cover_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-1"></i> Create Service
            </button>
            <a href="{{ route('provider.services.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
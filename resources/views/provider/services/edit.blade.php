@extends('layouts.provider')

@section('title', 'Edit Service | TravelAI Nepal')
@section('header', 'Edit Service')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <form method="POST" action="{{ route('provider.services.update', $service) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Service Name *</label>
                <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Category *</label>
                <select name="service_category_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Price (NPR)</label>
                <input type="number" name="price" value="{{ old('price', $service->price) }}" step="0.01"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $service->description) }}</textarea>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Cover Image</label>
                <input type="file" name="cover_image" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($service->cover_image)
                    <p class="text-xs text-gray-400 mt-1">Current: <a href="{{ asset('storage/' . $service->cover_image) }}" target="_blank">View</a></p>
                @endif
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ $service->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $service->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-1"></i> Update Service
            </button>
            <a href="{{ route('provider.services.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
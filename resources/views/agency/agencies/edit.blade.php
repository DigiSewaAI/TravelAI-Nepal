@extends('agency.layouts.app')

@section('header', 'Edit Agency')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl mx-auto">
    <h2 class="text-lg font-semibold mb-4">Edit Agency</h2>
    <form action="{{ route('agency.agencies.update', $agency->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name', $agency->name) }}" class="w-full border rounded-lg px-3 py-2 @error('name') border-red-500 @enderror" required>
            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $agency->email) }}" class="w-full border rounded-lg px-3 py-2 @error('email') border-red-500 @enderror" required>
            @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="w-full border rounded-lg px-3 py-2 @error('password') border-red-500 @enderror">
            @error('password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $agency->phone) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Address</label>
            <input type="text" name="address" value="{{ old('address', $agency->address) }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 mb-1">Role</label>
            <select name="role" class="w-full border rounded-lg px-3 py-2">
                <option value="agency" {{ $agency->role == 'agency' ? 'selected' : '' }}>Agency</option>
                <option value="admin" {{ $agency->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="super_admin" {{ $agency->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Update Agency</button>
            <a href="{{ route('agency.agencies.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
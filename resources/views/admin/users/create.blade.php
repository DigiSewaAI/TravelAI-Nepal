@extends('layouts.admin')

@section('title', 'Create User | TravelAI Nepal')
@section('header', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Password *</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Confirm Password *</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Role *</label>
                <select name="role" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="traveler" {{ old('role') === 'traveler' ? 'selected' : '' }}>Traveler</option>
                    <option value="provider_owner" {{ old('role') === 'provider_owner' ? 'selected' : '' }}>Provider Owner</option>
                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-1"></i> Create User
            </button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
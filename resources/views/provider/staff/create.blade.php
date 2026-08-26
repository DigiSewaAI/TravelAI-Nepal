@extends('layouts.provider')

@section('title', 'Add Staff Member')
@section('header', 'Add Staff')

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Add Staff Member</h2>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
            {{ session('error') }}
            @if(str_contains(session('error'), 'staff limit') || str_contains(session('error'), 'Staff limit'))
                <br>
                <a href="{{ route('provider.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800 font-medium underline inline-block mt-1">
                    <i class="fas fa-arrow-up"></i> Upgrade Your Plan
                </a>
            @endif
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('provider.staff.store') }}" class="bg-white p-6 rounded-lg shadow">
        @csrf

        <div class="mb-4">
            <label class="block font-medium mb-1">Full Name *</label>
            <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name') }}" required>
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Email *</label>
            <input type="email" name="email" class="w-full border rounded-lg px-4 py-2" value="{{ old('email') }}" required>
            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Password *</label>
            <input type="password" name="password" class="w-full border rounded-lg px-4 py-2" required>
            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Confirm Password *</label>
            <input type="password" name="password_confirmation" class="w-full border rounded-lg px-4 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Role (optional)</label>
            <input type="text" name="role" class="w-full border rounded-lg px-4 py-2" value="{{ old('role') }}" placeholder="e.g., Manager, Guide, Accountant">
            @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
            Add Staff
        </button>
        <a href="{{ route('provider.staff.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
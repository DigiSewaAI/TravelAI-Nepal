@extends('layouts.admin')

@section('title', 'User Details | TravelAI Nepal')
@section('header', 'User Details')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="flex items-center space-x-4 mb-6">
        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="fas fa-user text-2xl text-blue-600"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-gray-500">{{ $user->email }}</p>
            <span class="px-2 py-1 text-xs rounded-full
                @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                @elseif($user->role === 'provider_owner') bg-blue-100 text-blue-800
                @elseif($user->role === 'traveler') bg-green-100 text-green-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ str_replace('_', ' ', $user->role) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">Phone</p>
            <p class="font-medium">{{ $user->phone ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Joined</p>
            <p class="font-medium">{{ $user->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Last Updated</p>
            <p class="font-medium">{{ $user->updated_at->format('M d, Y H:i') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">User ID</p>
            <p class="font-medium">#{{ $user->id }}</p>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-edit mr-1"></i> Edit User
        </a>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
              onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </form>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@endsection
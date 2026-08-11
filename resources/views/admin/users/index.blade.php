@extends('layouts.admin')

@section('title', 'Users | TravelAI Nepal')
@section('header', 'Manage Users')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">All Users</h2>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-plus mr-1"></i> Add User
        </a>
    </div>

    @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Name</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Email</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Role</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Phone</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">{{ $user->name }}</td>
                        <td class="py-3 text-sm">{{ $user->email }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                                @elseif($user->role === 'provider_owner') bg-blue-100 text-blue-800
                                @elseif($user->role === 'traveler') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">{{ $user->phone ?? 'Not Provided' }}</td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                  onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No users found.</p>
    @endif
</div>
@endsection
@extends('layouts.provider')

@section('title', 'Team Management')
@section('header', 'Team Members')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Team Members</h2>
    <a href="{{ route('provider.staff.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
        + Add Staff
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($staff as $member)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-medium text-sm">{{ substr($member->user->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $member->user->name }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $member->user->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($member->role == 'staff') bg-blue-100 text-blue-800
                        @elseif($member->role == 'manager') bg-purple-100 text-purple-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($member->role ?? 'Staff') }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="{{ route('provider.staff.edit', $member) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                        Edit
                    </a>
                    <form action="{{ route('provider.staff.destroy', $member) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to remove this staff member?')">
                            Remove
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                    No staff members yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-sm text-gray-500 flex justify-between items-center">
    <span>
        Total Staff: <strong>{{ $staff->count() }}</strong>
    </span>
    <span>
        Max staff allowed: 
        <strong>{{ $maxStaff == -1 ? 'Unlimited' : $maxStaff }}</strong>
    </span>
</div>
@endsection
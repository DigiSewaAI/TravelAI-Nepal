@extends('agency.layouts.app')

@section('header', 'Manage Agencies')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">
            All Agencies 
            <span class="text-sm font-normal text-gray-500 ml-2">({{ $agencies->count() }} total)</span>
        </h2>
        <a href="{{ route('agency.agencies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            + Add Agency
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

    @if($agencies->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Treks</th>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                        <th class="text-left py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agencies as $agt)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2 text-sm">
                            <a href="{{ route('agency.agencies.show', $agt->id) }}" 
                               class="text-blue-600 hover:underline font-medium">
                                {{ $agt->name }}
                            </a>
                        </td>
                        <td class="py-2 text-sm">{{ $agt->email }}</td>
                        <td class="py-2 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full inline-flex items-center space-x-1
                                @if($agt->role == 'super_admin') bg-purple-100 text-purple-800
                                @elseif($agt->role == 'admin') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($agt->role == 'super_admin')
                                    <i class="fas fa-crown text-purple-500"></i>
                                @elseif($agt->role == 'admin')
                                    <i class="fas fa-user-shield text-blue-500"></i>
                                @else
                                    <i class="fas fa-user text-gray-500"></i>
                                @endif
                                <span>{{ $agt->role ?? 'agency' }}</span>
                            </span>
                        </td>
                        <td class="py-2 text-sm text-center">{{ $agt->treks_count ?? 0 }}</td>
                        <td class="py-2 text-sm text-center">{{ $agt->bookings_count ?? 0 }}</td>
                        <td class="py-2 text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('agency.agencies.edit', $agt->id) }}" 
                                   class="text-blue-600 hover:text-blue-800" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($agt->role !== 'super_admin')
                                    <form action="{{ route('agency.agencies.toggle-status', $agt->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" 
                                                class="text-yellow-600 hover:text-yellow-800" 
                                                title="Toggle role (agency ↔ admin)">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('agency.agencies.destroy', $agt->id) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this agency? This action cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs italic">(protected)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <i class="fas fa-building text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No agencies registered yet.</p>
            <a href="{{ route('agency.agencies.create') }}" class="mt-2 inline-block text-blue-600 hover:underline">
                Create your first agency
            </a>
        </div>
    @endif
</div>
@endsection
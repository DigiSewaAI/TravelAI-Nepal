@extends('layouts.provider')

@section('title', 'Quotation Requests')
@section('header', 'Quotation Requests')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Quotation Requests</h2>
    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
        Pending: {{ $pendingCount ?? 0 }}
    </span>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    @if($requests->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Traveler</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($requests as $req)
                <tr>
                    <td class="px-6 py-4">{{ $req->traveler->name ?? 'Guest' }}</td>
                    <td class="px-6 py-4">{{ $req->traveler_input['destination'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $req->traveler_input['days'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4">${{ $req->traveler_input['budget'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($req->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($req->status == 'viewed') bg-blue-100 text-blue-800
                            @elseif($req->status == 'processing') bg-purple-100 text-purple-800
                            @elseif($req->status == 'completed') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $req->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('provider.quotation-requests.show', $req) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="p-6 text-center text-gray-500">No quotation requests yet.</div>
    @endif
</div>
@endsection
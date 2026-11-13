@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Subscription Plans</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 text-sm">
                <tr>
                    <th class="p-4 border-b">Name</th>
                    <th class="p-4 border-b">Monthly Price (Rs)</th>
                    <th class="p-4 border-b">Yearly Price (Rs)</th>
                    <th class="p-4 border-b">Contact Only?</th>
                    <th class="p-4 border-b">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700">
                @forelse($plans as $plan)
                <tr class="hover:bg-gray-50 border-b">
                    <td class="p-4 font-semibold text-gray-900">{{ $plan->name }}</td>
                    <td class="p-4">{{ number_format($plan->price_monthly ?? 0, 2) }}</td>
                    <td class="p-4">{{ number_format($plan->price_yearly ?? 0, 2) }}</td>
                    <td class="p-4">
                        @if($plan->requires_contact)
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold">Yes (Enterprise)</span>
                        @else
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">No</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">No plans found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
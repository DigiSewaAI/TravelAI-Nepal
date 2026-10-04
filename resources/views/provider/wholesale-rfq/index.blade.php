@extends('layouts.provider')

@section('title', __('messages.provider_rfq_title'))
@section('header', __('messages.provider_rfq_title'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.provider_rfq_title') }}</h2>

    <div class="flex gap-2 mb-4 border-b pb-2 overflow-x-auto">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'quoted' => 'Quoted', 'accepted' => 'Accepted'] as $key => $label)
            <a href="{{ route('provider.wholesale-rfq.index', ['status' => $key]) }}"
               class="px-3 py-1.5 text-sm rounded-lg whitespace-nowrap {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $label }} ({{ $counts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    @if($rfqs->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">RFQ</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Buyer</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Product</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Qty</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rfqs as $rfq)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 text-sm font-medium">{{ $rfq->rfq_number }}</td>
                            <td class="py-3 text-sm">{{ $rfq->buyer->name ?? 'N/A' }}</td>
                            <td class="py-3 text-sm">{{ $rfq->product->name ?? 'N/A' }}</td>
                            <td class="py-3 text-sm">{{ $rfq->quantity }}</td>
                            <td class="py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($rfq->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($rfq->status === 'quoted') bg-blue-100 text-blue-800
                                    @elseif($rfq->status === 'accepted') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($rfq->status) }}
                                </span>
                            </td>
                            <td class="py-3 text-sm">
                                <a href="{{ route('provider.wholesale-rfq.show', $rfq) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $rfqs->links() }}</div>
    @else
        <div class="text-center py-12 text-gray-500">
            <p>{{ __('messages.provider_rfq_empty') }}</p>
        </div>
    @endif
</div>
@endsection
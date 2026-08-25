@extends('layouts.provider')

@section('title', __('messages.invoices_page_title'))
@section('header', __('messages.invoices_header'))

@section('content')
<div class="mb-6">
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-500">{{ __('messages.total_invoices') }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg shadow-sm border border-green-200">
            <p class="text-sm text-green-600">{{ __('messages.paid') }}</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg shadow-sm border border-yellow-200">
            <p class="text-sm text-yellow-600">{{ __('messages.pending') }}</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-2">
            <select name="status" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>{{ __('messages.overdue') }}</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm">{{ __('messages.filter') }}</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('messages.invoice_number') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('messages.amount') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('messages.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('messages.date') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            @if($invoice->status === 'paid') {{ __('messages.paid') }}
                            @elseif($invoice->status === 'pending') {{ __('messages.pending') }}
                            @elseif($invoice->status === 'overdue') {{ __('messages.overdue') }}
                            @else {{ ucfirst($invoice->status) }} @endif
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $invoice->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('provider.invoices.show', $invoice) }}" class="text-blue-600 hover:underline text-sm">{{ __('messages.view') }}</a>
                        <a href="{{ route('provider.invoices.download', $invoice) }}" class="text-green-600 hover:underline text-sm ml-2">{{ __('messages.download') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('messages.no_invoices_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
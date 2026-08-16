@extends('layouts.admin')

@section('title', 'Invoices | Admin Panel')
@section('header', 'Invoices Management')

@section('content')
<div class="mb-6">
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg shadow-sm border border-green-200">
            <p class="text-sm text-green-600">Paid</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg shadow-sm border border-yellow-200">
            <p class="text-sm text-yellow-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-red-50 p-4 rounded-lg shadow-sm border border-red-200">
            <p class="text-sm text-red-600">Overdue</p>
            <p class="text-2xl font-bold text-red-700">{{ $stats['overdue'] }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <div class="p-4 border-b flex flex-wrap gap-4 justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Search invoice # or provider..." value="{{ request('search') }}" class="border rounded-lg px-3 py-1.5 text-sm">
            <select name="status" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Invoice #</th>
                    <th class="px-4 py-3 text-left">Provider</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3">{{ $invoice->provider->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}
                        ">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $invoice->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-blue-600 hover:underline text-sm">View</a>
                        <a href="{{ route('admin.invoices.download', $invoice) }}" class="text-green-600 hover:underline text-sm ml-2">Download</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No invoices found.</td>
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
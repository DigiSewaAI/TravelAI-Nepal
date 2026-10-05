@extends('layouts.admin')

@section('title', 'Platform Payment Methods')
@section('header', 'Platform Payment Methods')

@section('content')
<div class="max-w-6xl mx-auto">

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Platform Payment Methods</h1>
            <p class="text-sm text-gray-500 mt-1">Manage payment accounts shown to providers for subscription payments.</p>
        </div>
        <a href="{{ route('admin.platform-payment-methods.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-1"></i> Add Method
        </a>
    </div>

    @if($methods->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
            <p class="text-yellow-800 font-medium">No payment methods configured yet.</p>
            <p class="text-sm text-yellow-700 mt-1">Add at least one method so providers can submit subscription payments.</p>
            <a href="{{ route('admin.platform-payment-methods.create') }}"
               class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Add First Method
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Order</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Label</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Account</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($methods as $method)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $method->sort_order }}</td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ $method->label }}</div>
                                @if($method->bank_name)
                                    <div class="text-xs text-gray-500">{{ $method->bank_name }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs font-semibold uppercase">
                                    {{ $method->type }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <div class="text-gray-800">{{ $method->account_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $method->account_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <form method="POST" action="{{ route('admin.platform-payment-methods.toggle', $method) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold px-3 py-1 rounded-full
                                        {{ $method->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.platform-payment-methods.edit', $method) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.platform-payment-methods.destroy', $method) }}"
                                      class="inline" onsubmit="return confirm('Delete this method?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
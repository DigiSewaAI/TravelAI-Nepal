@extends('layouts.admin')

@section('header', 'Manual Claims')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🔗 Manual Claim Tool</h1>
        <p class="text-sm text-gray-600 mt-1">Link unclaimed transactions to traveler accounts (Super Admin only)</p>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
        ❌ {{ $errors->first() }}
    </div>
    @endif

    {{-- Search & Filter --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="TXN, Email, Customer name..." class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Category</label>
                <select name="category" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $key => $cat)
                    <option value="{{ $key }}" {{ $selectedCategory === $key ? 'selected' : '' }}>
                        {{ $cat['icon'] }} {{ $cat['label'] }} ({{ $categoryCounts[$key] ?? 0 }})
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold">
                🔍 Filter
            </button>
            @if($search || $selectedCategory !== 'all')
            <a href="{{ route('admin.claims.index') }}" class="text-gray-600 hover:text-gray-800 px-3 py-2 text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Category Tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.claims.index') }}" class="px-3 py-1 rounded-full text-xs font-bold {{ $selectedCategory === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
            All ({{ array_sum($categoryCounts) }})
        </a>
        @foreach($categories as $key => $cat)
        <a href="{{ route('admin.claims.index', ['category' => $key]) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ $selectedCategory === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
            {{ $cat['icon'] }} {{ $cat['label'] }} ({{ $categoryCounts[$key] ?? 0 }})
        </a>
        @endforeach
    </div>

    {{-- Results Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        @if($allUnclaimed->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <div class="text-4xl mb-2">✅</div>
                <p class="font-bold">No unclaimed transactions</p>
                <p class="text-sm mt-1">All transactions have been claimed.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-bold text-gray-700">Category</th>
                            <th class="text-left px-4 py-3 font-bold text-gray-700">Reference</th>
                            <th class="text-left px-4 py-3 font-bold text-gray-700">Customer</th>
                            <th class="text-left px-4 py-3 font-bold text-gray-700">Email</th>
                            <th class="text-right px-4 py-3 font-bold text-gray-700">Amount</th>
                            <th class="text-left px-4 py-3 font-bold text-gray-700">Date</th>
                            <th class="text-center px-4 py-3 font-bold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allUnclaimed as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="text-sm">{{ $item['icon'] }} {{ $item['category_label'] }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs font-bold text-blue-700">{{ $item['ref'] }}</td>
                            <td class="px-4 py-3">{{ $item['customer_name'] }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($item['email'])
                                    {{ $item['email'] }}
                                @else
                                    <span class="text-red-600 font-bold">❌ NULL</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold">
                                NPR {{ number_format($item['amount'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.claims.show', [$item['category'], $item['id']]) }}"
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-bold">
                                    Claim →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-gray-50 text-xs text-gray-600 border-t">
                Showing {{ $allUnclaimed->count() }} unclaimed transaction(s) — Total unclaimed across all: <strong>{{ array_sum($categoryCounts) }}</strong>
            </div>
        @endif
    </div>

    {{-- Warning --}}
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
        <p class="text-sm font-bold text-yellow-800">⚠️ Important</p>
        <ul class="text-sm text-yellow-700 mt-2 space-y-1 list-disc list-inside">
            <li>Verify traveler identity (phone/email) before claiming</li>
            <li>All actions are logged with admin name + timestamp</li>
            <li>Once claimed, cannot be undone</li>
            <li>Traveler will see the transaction in their dashboard immediately</li>
        </ul>
    </div>

</div>
@endsection
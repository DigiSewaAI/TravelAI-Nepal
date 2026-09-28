@extends('layouts.admin')

@section('title', 'Verify Payments')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-shield-alt text-blue-600"></i>
                Manage Payments
            </h1>
            <p class="text-sm text-gray-500 mt-1">Verify pending subscription payments.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.payments.index') }}"
               class="text-sm bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg px-4 py-2 flex items-center gap-2">
                <i class="fas fa-list"></i> All Payments
            </a>
            <a href="{{ route('admin.payments.verify') }}"
               class="text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 flex items-center gap-2">
                <i class="fas fa-sync"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Verified Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['verified'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Rejected This Week</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Revenue (month)</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($stats['revenue'], 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Pending Queue --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list text-orange-500"></i>
                    Pending Verification ({{ $pending->total() }})
                </h2>
            </div>

            @if($pending->isEmpty())
                <div class="p-12 text-center text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No payments pending verification.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="text-left px-4 py-3">Provider</th>
                            <th class="text-left px-4 py-3">Plan</th>
                            <th class="text-left px-4 py-3">Amount</th>
                            <th class="text-left px-4 py-3">Method</th>
                            <th class="text-left px-4 py-3">Reference</th>
                            <th class="text-left px-4 py-3">Receipt</th>
                            <th class="text-left px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $p)
                            <tr class="border-t border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                    {{ $p->provider->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs">
                                        {{ optional($p->payable->plan ?? null)->name ?? 'Subscription' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800">
                                    Rs. {{ number_format($p->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <i class="fas fa-university text-gray-400 mr-1"></i>
                                    {{ ucfirst($p->gateway) }}
                                </td>
                                <td class="px-4 py-3 text-xs font-mono text-gray-600">
                                    {{ $p->reference_number ?? '—' }}
                                </td>
                                <td class="px-3 py-3">
                                    @if($p->receipt_image_path)
                                        <a href="{{ route('admin.payments.receipt', $p->id) }}"
                                           target="_blank"
                                           class="text-xs bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded px-2 py-1 inline-flex items-center gap-1 whitespace-nowrap">
                                            <i class="fas fa-file-image"></i> View
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex gap-1 whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.payments.approve', $p->id) }}"
                                              class="inline"
                                              onsubmit="return confirm('Approve this payment?')">
                                            @csrf
                                            <button class="text-xs bg-green-500 hover:bg-green-600 text-white rounded px-2.5 py-1.5 flex items-center gap-1">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </form>
                                        <button type="button"
                                                onclick="openReject({{ $p->id }})"
                                                class="text-xs bg-red-500 hover:bg-red-600 text-white rounded px-2.5 py-1.5 flex items-center gap-1">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $pending->links() }}
                </div>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-history text-gray-400"></i>
                Recent Activity
            </h2>

            @if($recentActivity->isEmpty())
                <p class="text-sm text-gray-400">No recent activity.</p>
            @else
                <ul class="space-y-3">
                    @foreach($recentActivity as $a)
                        <li class="flex items-start gap-3">
                            @if($a->status === 'verified')
                                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            @else
                                <i class="fas fa-times-circle text-red-500 mt-0.5"></i>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">
                                    {{ $a->provider->name ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Rs. {{ number_format($a->amount, 2) }} ·
                                    {{ $a->updated_at->diffForHumans() }}
                                </p>
                                @if($a->status === 'rejected' && $a->admin_note)
                                    <p class="text-xs text-red-500 mt-1 truncate">{{ $a->admin_note }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-3">Reject Payment</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Reason <span class="text-red-500">*</span>
            </label>
            <textarea name="admin_note" required minlength="10" maxlength="500"
                      rows="4"
                      placeholder="Explain why this payment is being rejected (min 10 chars)"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeReject()"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openReject(id) {
    var modal = document.getElementById('rejectModal');
    var form = document.getElementById('rejectForm');
    form.action = '/admin/payments/' + id + '/reject';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeReject() {
    var modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
@endsection
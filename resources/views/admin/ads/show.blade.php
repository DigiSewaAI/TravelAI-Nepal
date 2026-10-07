@extends('layouts.admin')

@section('title', 'Review Ad')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    <a href="{{ route('admin.ads.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-amber-600 mb-4">
        <i class="fas fa-arrow-left"></i> {{ __('messages.ads_back') }}
    </a>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-5">

        {{-- Left: Ad preview --}}
        <div class="md:col-span-2 space-y-5">

            {{-- Ad preview --}}
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <img src="{{ Storage::url($ad->image_path) }}" class="w-full h-56 object-cover">
                <div class="p-5">
                    <h1 class="text-xl font-bold text-gray-900">{{ $ad->title }}</h1>
                    @if($ad->description)
                        <p class="text-sm text-gray-600 mt-1">{{ $ad->description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3 mt-3 text-xs text-gray-500">
                        <span><i class="fas fa-link mr-1"></i>{{ $ad->link_url }}</span>
                        <span><i class="fas fa-clock mr-1"></i>{{ $ad->duration_days }} days</span>
                    </div>
                </div>
            </div>

            {{-- Payment proof --}}
            @php $payment = $ad->payments->first(); @endphp
            @if($payment)
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-receipt text-amber-500"></i> Payment Proof
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <img src="{{ Storage::url($payment->proof_path) }}"
                                 class="w-full rounded-lg border">
                        </div>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-500">Method:</span> <strong>{{ $payment->payment_method }}</strong></div>
                            @if($payment->payment_reference)
                                <div><span class="text-gray-500">Ref:</span> <strong>{{ $payment->payment_reference }}</strong></div>
                            @endif
                            <div><span class="text-gray-500">Amount:</span> <strong>Rs. {{ number_format($payment->amount) }}</strong></div>
                            <div><span class="text-gray-500">Submitted:</span> {{ $payment->created_at->format('M d, Y H:i') }}</div>
                            <div><span class="text-gray-500">Status:</span>
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    {{ $payment->status === 'verified' ? 'bg-green-100 text-green-700' : ($payment->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Right: Provider + Actions --}}
        <div class="space-y-5">

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="font-bold text-gray-800 mb-3 text-sm">Provider</h3>
                <div class="space-y-1 text-sm">
                    <div class="font-medium text-gray-900">{{ $ad->provider->name ?? '—' }}</div>
                    <div class="text-gray-500 text-xs">{{ $ad->provider->contact_email ?? '' }}</div>
                    <div class="text-gray-500 text-xs">{{ $ad->provider->contact_phone ?? '' }}</div>
                </div>
            </div>

            @if($ad->status === 'pending_review')
                {{-- Approve --}}
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-bold text-gray-800 mb-3 text-sm">Actions</h3>

                    <form action="{{ route('admin.ads.approve', $ad) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Approve and activate this ad?')"
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg font-bold transition">
                            <i class="fas fa-check-circle mr-1"></i> Approve & Activate
                        </button>
                    </form>

                    <button type="button" onclick="document.getElementById('rejectForm').classList.toggle('hidden')"
                            class="w-full bg-red-100 hover:bg-red-200 text-red-700 py-2.5 rounded-lg font-bold transition">
                        <i class="fas fa-times-circle mr-1"></i> Reject
                    </button>

                    <div id="rejectForm" class="hidden mt-3">
                        <form action="{{ route('admin.ads.reject', $ad) }}" method="POST">
                            @csrf
                            <textarea name="rejection_reason" rows="3" required
                                      placeholder="Reason for rejection..."
                                      class="w-full border rounded-lg px-3 py-2 text-sm mb-2"></textarea>
                            <button type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-bold">
                                Confirm Rejection
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($ad->status === 'active')
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-bold text-gray-800 mb-3 text-sm">Actions</h3>
                    <form action="{{ route('admin.ads.toggle', $ad) }}" method="POST" class="mb-3">
                        @csrf @method('PATCH')
                        <button type="submit"
                                onclick="return confirm('Pause this ad?')"
                                class="w-full bg-amber-100 hover:bg-amber-200 text-amber-700 py-2.5 rounded-lg font-bold">
                            <i class="fas fa-pause mr-1"></i> Pause Ad
                        </button>
                    </form>
                    <a href="{{ route('admin.ads.analytics', $ad) }}"
                       class="block w-full bg-blue-100 hover:bg-blue-200 text-blue-700 py-2.5 rounded-lg font-bold text-center">
                        <i class="fas fa-chart-bar mr-1"></i> Analytics
                    </a>
                </div>
            @endif

            <div class="bg-gray-50 rounded-xl border p-4 text-xs text-gray-500">
                <div class="flex justify-between mb-1">
                    <span>Ad ID:</span><strong>{{ $ad->id }}</strong>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Created:</span><span>{{ $ad->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>Payment:</span>
                    <span class="font-bold">{{ ucfirst($ad->payment_status) }}</span>
                </div>
                @if($ad->start_date)
                    <div class="flex justify-between mb-1">
                        <span>Starts:</span><span>{{ $ad->start_date->format('M d, Y') }}</span>
                    </div>
                @endif
                @if($ad->end_date)
                    <div class="flex justify-between">
                        <span>Ends:</span><span>{{ $ad->end_date->format('M d, Y') }}</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
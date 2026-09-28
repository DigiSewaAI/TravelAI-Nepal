@extends('layouts.provider')

@section('title', __('messages.pm_page_title'))
@section('header', __('messages.pm_header'))

@section('content')
<div class="max-w-5xl mx-auto">

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

    {{-- Disclaimer --}}
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-6 flex items-start">
        <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <p class="text-sm text-blue-800">{{ __('messages.pm_disclaimer') }}</p>
    </div>

    @php
        $sections = [
            ['key' => 'domestic',      'flag' => '🇳🇵', 'title' => __('messages.pm_domestic_section'),      'add' => __('messages.pm_add_domestic'),      'items' => $domestic],
            ['key' => 'international', 'flag' => '🌍', 'title' => __('messages.pm_international_section'), 'add' => __('messages.pm_add_international'), 'items' => $international],
        ];
    @endphp

    @foreach($sections as $s)
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">{{ $s['flag'] }}</span> {{ $s['title'] }}
                </h2>
                <button type="button" onclick="openPmModal('{{ $s['key'] }}')"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-2 flex items-center gap-2">
                    <i class="fas fa-plus"></i> {{ $s['add'] }}
                </button>
            </div>

            @if($s['items']->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p class="text-sm">{{ __('messages.pm_empty') }}</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach($s['items'] as $m)
                        @php
                            $icon = [
                                'bank' => 'university', 'esewa' => 'mobile-alt', 'khalti' => 'wallet',
                                'cash' => 'money-bill-wave', 'paypal' => 'paypal', 'wise' => 'exchange-alt',
                                'international_bank' => 'globe',
                            ][$m->type] ?? 'credit-card';
                        @endphp
                        <div class="border rounded-lg p-4 {{ $m->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fas fa-{{ $icon }}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 truncate">
                                            {{ $m->label ?: __('messages.pm_type_' . $m->type) }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $m->account_number ?: $m->identifier ?: $m->account_name ?: '—' }}
                                        </p>
                                        @if($m->qr_image_path)
                                            <a href="{{ Storage::url($m->qr_image_path) }}" target="_blank"
                                               class="text-xs text-blue-600 hover:underline">
                                                <i class="fas fa-qrcode"></i> QR
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('provider.settings.payment-methods.toggle', $m->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="p-2 rounded {{ $m->is_active ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100' }}"
                                                title="{{ __('messages.pm_toggle_active') }}">
                                            <i class="fas fa-{{ $m->is_active ? 'toggle-on' : 'toggle-off' }} text-lg"></i>
                                        </button>
                                    </form>
                                                                        <button type="button"
                                            data-pm-edit="{{ base64_encode(json_encode($m->only(['id','type','label','account_name','account_number','identifier','bank_name','swift_code','currency','sort_order','instructions','qr_image_path']))) }}"
                                            onclick="openPmEdit(this)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('provider.settings.payment-methods.destroy', $m->id) }}"
                                          onsubmit="return confirm('{{ __('messages.pm_confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

{{-- Modal --}}
<div id="pmModal" class="hidden fixed inset-0 bg-black/50 z-50 items-start justify-center overflow-y-auto">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 my-8">
        <div class="flex justify-between items-center mb-4">
            <h3 id="pmModalTitle" class="text-lg font-bold text-gray-900">{{ __('messages.pm_add_method') }}</h3>
            <button type="button" onclick="closePmModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="pmForm" method="POST" enctype="multipart/form-data" action="{{ route('provider.settings.payment-methods.store') }}">
            @csrf
            <input type="hidden" name="_method" id="pmMethod" value="POST">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_type') }} *</label>
                    <select name="type" id="pmType" required class="w-full px-3 py-2 border rounded-lg">
                        <optgroup label="{{ __('messages.pm_domestic_section') }}">
                            <option value="bank">{{ __('messages.pm_type_bank') }}</option>
                            <option value="esewa">{{ __('messages.pm_type_esewa') }}</option>
                            <option value="khalti">{{ __('messages.pm_type_khalti') }}</option>
                            <option value="cash">{{ __('messages.pm_type_cash') }}</option>
                        </optgroup>
                        <optgroup label="{{ __('messages.pm_international_section') }}">
                            <option value="paypal">{{ __('messages.pm_type_paypal') }}</option>
                            <option value="wise">{{ __('messages.pm_type_wise') }}</option>
                            <option value="international_bank">{{ __('messages.pm_type_international_bank') }}</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_label') }}</label>
                    <input type="text" name="label" id="pmLabel" maxlength="100"
                           placeholder="e.g. NIC Asia Bank" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_account_name') }}</label>
                        <input type="text" name="account_name" id="pmAccountName" maxlength="150" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_account_number') }}</label>
                        <input type="text" name="account_number" id="pmAccountNumber" maxlength="100" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_identifier') }}</label>
                    <input type="text" name="identifier" id="pmIdentifier" maxlength="150" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_bank_name') }}</label>
                        <input type="text" name="bank_name" id="pmBankName" maxlength="150" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_swift_code') }}</label>
                        <input type="text" name="swift_code" id="pmSwiftCode" maxlength="30" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_currency') }}</label>
                        <input type="text" name="currency" id="pmCurrency" maxlength="3" value="NPR" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_sort_order') }}</label>
                        <input type="number" name="sort_order" id="pmSortOrder" min="0" max="999" value="0" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_qr_image') }}</label>
                    <input type="file" name="qr_image" accept="image/png,image/jpeg" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">PNG / JPG, max 2MB</p>
                    <div id="pmQrPreview" class="mt-2 hidden">
                        <img id="pmQrPreviewImg" src="" alt="QR" class="w-24 h-24 border rounded object-contain">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.pm_instructions') }}</label>
                    <textarea name="instructions" id="pmInstructions" rows="2" maxlength="1000" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closePmModal()" class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg">
                    {{ __('messages.pm_cancel') }}
                </button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    {{ __('messages.pm_save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const PM_BASE  = '{{ route('provider.settings.payment-methods.store') }}';
const PM_INDEX = '{{ route('provider.settings.payment-methods.index') }}';

function openPmModal(context) {
    const f = document.getElementById('pmForm');
    f.reset();
    f.action = PM_BASE;
    document.getElementById('pmMethod').value = 'POST';
    document.getElementById('pmModalTitle').textContent = context === 'domestic'
        ? '{{ __('messages.pm_add_domestic') }}'
        : '{{ __('messages.pm_add_international') }}';
    document.getElementById('pmType').value = context === 'domestic' ? 'bank' : 'paypal';
    document.getElementById('pmCurrency').value = 'NPR';
    document.getElementById('pmSortOrder').value = 0;
    document.getElementById('pmQrPreview').classList.add('hidden');
    document.getElementById('pmModal').classList.remove('hidden');
    document.getElementById('pmModal').classList.add('flex');
}

function openPmEdit(btn) {
    const m = JSON.parse(atob(btn.dataset.pmEdit));
    const f = document.getElementById('pmForm');
    f.reset();
    f.action = PM_INDEX + '/' + m.id;
    document.getElementById('pmMethod').value = 'PUT';
    document.getElementById('pmModalTitle').textContent = '{{ __('messages.pm_edit_method') }}';
    document.getElementById('pmType').value          = m.type || 'bank';
    document.getElementById('pmLabel').value         = m.label || '';
    document.getElementById('pmAccountName').value   = m.account_name || '';
    document.getElementById('pmAccountNumber').value = m.account_number || '';
    document.getElementById('pmIdentifier').value    = m.identifier || '';
    document.getElementById('pmBankName').value      = m.bank_name || '';
    document.getElementById('pmSwiftCode').value     = m.swift_code || '';
    document.getElementById('pmCurrency').value      = m.currency || 'NPR';
    document.getElementById('pmSortOrder').value     = m.sort_order || 0;
    document.getElementById('pmInstructions').value  = m.instructions || '';
    if (m.qr_image_path) {
        document.getElementById('pmQrPreviewImg').src = '/storage/' + m.qr_image_path;
        document.getElementById('pmQrPreview').classList.remove('hidden');
    } else {
        document.getElementById('pmQrPreview').classList.add('hidden');
    }
    document.getElementById('pmModal').classList.remove('hidden');
    document.getElementById('pmModal').classList.add('flex');
}

function closePmModal() {
    document.getElementById('pmModal').classList.add('hidden');
    document.getElementById('pmModal').classList.remove('flex');
}
</script>
@endsection
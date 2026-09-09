@extends('layouts.provider')

@section('title', 'Edit Quotation')
@section('header', 'Edit Quotation')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow p-6 mb-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Edit Quotation</h2>
                <p class="text-gray-500">Request #{{ $quotationRequest->id }} – {{ $quotationRequest->traveler_name ?? 'Guest' }}</p>
            </div>
            <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                Status: {{ ucfirst($quotationRequest->quotation_status) }}
            </span>
        </div>
    </div>

    {{-- AI Draft vs Final Comparison --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Left: AI Draft (read-only) --}}
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-bold text-gray-600 mb-3">📄 Original AI Draft (read-only)</h3>
            <div class="text-sm space-y-2">
                @foreach($draft['cost_breakdown']['items'] ?? [] as $item)
                    <div class="flex justify-between border-b border-gray-200 pb-1">
                        <span>{{ $item['description'] ?? 'Item' }}</span>
                        <span>${{ number_format($item['per_person'] ?? 0, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between font-bold pt-2 border-t-2 border-gray-300">
                    <span>Total</span>
                    <span>${{ number_format($draft['cost_breakdown']['grand_total'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Right: Final Quotation (editable) --}}
        <div class="bg-white rounded-lg p-4 border-2 border-blue-200">
            <h3 class="font-bold text-blue-600 mb-3">✏️ Your Final Quotation (editable)</h3>
            
            <form id="editForm" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Items Container --}}
                <div id="itemsContainer" class="space-y-2">
                    @foreach($final['cost_breakdown']['items'] ?? [] as $index => $item)
                        <div class="item-row grid grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded border border-gray-100">
                            <div class="col-span-5">
                                <label class="text-xs text-gray-600">Description</label>
                                <input type="text" name="items[{{ $index }}][description]" 
                                       value="{{ $item['description'] ?? '' }}" 
                                       class="w-full border rounded px-2 py-1 text-sm" required>
                            </div>
                            <div class="col-span-3">
                                <label class="text-xs text-gray-600">Price per Person (USD)</label>
                                <input type="number" name="items[{{ $index }}][per_person]" 
                                       value="{{ $item['per_person'] ?? 0 }}" 
                                       step="0.01" min="0"
                                       class="w-full border rounded px-2 py-1 text-sm price-input" required>
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs text-gray-600">
                                    Qty 
                                    <span class="text-gray-400 text-[10px]">(units)</span>
                                </label>
                                <input type="number" name="items[{{ $index }}][quantity]" 
                                       value="{{ $item['quantity'] ?? 1 }}" 
                                       min="1"
                                       class="w-full border rounded px-2 py-1 text-sm qty-input">
                            </div>
                            <div class="col-span-1 text-center">
                                <span class="item-total text-sm font-bold text-blue-600">
                                    ${{ number_format($item['total'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="col-span-1 flex justify-center items-center">
                                <button type="button" class="remove-item text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded transition" title="Remove Item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Add Item Button --}}
                <button type="button" id="addItemBtn" class="text-blue-600 hover:text-blue-800 text-sm border border-dashed border-blue-300 rounded-lg px-4 py-2 w-full hover:bg-blue-50 transition">
                    <i class="fas fa-plus"></i> Add Item
                </button>

                {{-- Discount & Totals --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mt-4">
                    <div class="flex flex-wrap items-center gap-6">
                        <div>
                            <label class="text-xs text-gray-600">Discount (USD)</label>
                            <input type="number" name="discount" id="discountInput" 
                                   value="{{ $final['discount'] ?? 0 }}" 
                                   step="0.01" min="0"
                                   class="border rounded px-2 py-1 text-sm w-32">
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotalDisplay" class="font-bold text-gray-800">$0.00</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-600">Discount:</span>
                            <span id="discountDisplay" class="font-bold text-red-500">-$0.00</span>
                        </div>
                        <div class="text-sm border-l pl-4 border-gray-300">
                            <span class="text-gray-600">Grand Total:</span>
                            <span id="grandTotalDisplay" class="font-bold text-lg text-blue-600">$0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Terms & Conditions --}}
                <div class="mt-4">
                    <label class="text-xs text-gray-600">Terms & Conditions <span class="text-gray-400">(one per line)</span></label>
                    <textarea name="terms" id="termsInput" rows="4" 
                              class="w-full border rounded px-3 py-2 text-sm"
                              placeholder="Enter each term on a new line">{{ implode("\n", $final['terms_and_conditions'] ?? []) }}</textarea>
                </div>

                {{-- Special Notes --}}
                <div class="mt-4">
                    <label class="text-xs text-gray-600">Special Notes</label>
                    <textarea name="special_notes" id="specialNotesInput" rows="2" 
                              class="w-full border rounded px-3 py-2 text-sm"
                              placeholder="Any special instructions for the traveler">{{ $final['special_notes'] ?? '' }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                        💾 Save Changes
                    </button>
                    <button type="button" id="previewBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                        👁️ Preview
                    </button>
                    <button type="button" id="sendBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                        📧 Send to Traveler
                    </button>
                    <a href="{{ route('provider.quotation-requests.show', $quotationRequest) }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Loading/Result --}}
    <div id="loading" class="hidden text-center py-4">
        <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
        <p class="mt-2 text-gray-500">Processing...</p>
    </div>
    <div id="resultMessage" class="hidden p-4 rounded-lg mt-4"></div>
</div>

{{-- ✅ Send Confirmation Modal --}}
<div id="sendModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">📧 Send Final Quotation?</h3>
        <p class="text-gray-600 mb-4">
            This quotation will be emailed to:
            <br>
            <strong class="text-blue-600">
                {{ $quotationRequest->traveler_email ?? $quotationRequest->traveler->email ?? 'N/A' }}
            </strong>
        </p>
        <div class="bg-gray-50 p-3 rounded-lg mb-4">
            <span class="text-gray-600">Grand Total:</span>
            <span id="modalGrandTotal" class="font-bold text-xl text-blue-600">$0.00</span>
        </div>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 mb-4 text-sm text-yellow-700">
            ⚠️ Once sent, this quotation cannot be edited.
        </div>
        <div class="flex gap-3 justify-end">
            <button id="modalCancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                Cancel
            </button>
            <button id="modalConfirmBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-check"></i> Confirm & Send
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addBtn = document.getElementById('addItemBtn');
    const saveBtn = document.getElementById('saveBtn');
    const previewBtn = document.getElementById('previewBtn');
    const sendBtn = document.getElementById('sendBtn');
    const discountInput = document.getElementById('discountInput');
    const termsInput = document.getElementById('termsInput');
    const specialNotesInput = document.getElementById('specialNotesInput');
    const loading = document.getElementById('loading');
    const resultMessage = document.getElementById('resultMessage');

    // ✅ Modal elements
    const sendModal = document.getElementById('sendModal');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    const modalGrandTotal = document.getElementById('modalGrandTotal');

    // Add item
    addBtn.addEventListener('click', function() {
        const index = itemsContainer.querySelectorAll('.item-row').length;
        const row = document.createElement('div');
        row.className = 'item-row grid grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded border border-gray-100';
        row.innerHTML = `
            <div class="col-span-5">
                <label class="text-xs text-gray-600">Description</label>
                <input type="text" name="items[${index}][description]" 
                       value="New Service" class="w-full border rounded px-2 py-1 text-sm" required>
            </div>
            <div class="col-span-3">
                <label class="text-xs text-gray-600">Price per Person (USD)</label>
                <input type="number" name="items[${index}][per_person]" 
                       value="0" step="0.01" min="0"
                       class="w-full border rounded px-2 py-1 text-sm price-input" required>
            </div>
            <div class="col-span-2">
                <label class="text-xs text-gray-600">Qty <span class="text-gray-400">(units)</span></label>
                <input type="number" name="items[${index}][quantity]" 
                       value="1" min="1"
                       class="w-full border rounded px-2 py-1 text-sm qty-input">
            </div>
            <div class="col-span-1 text-center">
                <span class="item-total text-sm font-bold text-blue-600">$0.00</span>
            </div>
            <div class="col-span-1 flex justify-center items-center">
                <button type="button" class="remove-item text-red-500 hover:text-red-700 p-2 rounded transition" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        itemsContainer.appendChild(row);
        recalculateTotals();
    });

    // Remove item (delegate)
    itemsContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-item');
        if (btn) {
            const row = btn.closest('.item-row');
            if (itemsContainer.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalculateTotals();
            } else {
                alert('At least one item is required.');
            }
        }
    });

    // Recalculate on input change (delegate)
    itemsContainer.addEventListener('input', function(e) {
        if (e.target.matches('.price-input, .qty-input')) {
            recalculateTotals();
        }
    });

    discountInput.addEventListener('input', recalculateTotals);

    // Recalculate totals function
    function recalculateTotals() {
        const rows = itemsContainer.querySelectorAll('.item-row');
        let subtotal = 0;
        
        rows.forEach(row => {
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const qty = parseInt(row.querySelector('.qty-input').value) || 1;
            const total = price * qty;
            row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            subtotal += total;
        });

        const discount = parseFloat(discountInput.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount);
        
        document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('discountDisplay').textContent = '-$' + discount.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = '$' + grandTotal.toFixed(2);
        
        // Update modal grand total (always show current calculated total)
        modalGrandTotal.textContent = '$' + grandTotal.toFixed(2);
    }

    // Save
    saveBtn.addEventListener('click', function() {
        const formData = buildFormData();
        saveBtn.disabled = true;
        showLoading(true);

        fetch('{{ route("provider.quotation-requests.update", $quotationRequest) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            showLoading(false);
            if (data.success) {
                showMessage('success', data.message || 'Quotation saved successfully.');
                setTimeout(() => location.reload(), 2000);
            } else {
                showMessage('error', data.error || data.message || 'Save failed.');
            }
        })
        .catch(err => {
            showLoading(false);
            showMessage('error', 'Server error. Please try again.');
        })
        .finally(() => {
            saveBtn.disabled = false;
        });
    });

    // Preview
    previewBtn.addEventListener('click', function() {
        window.open('{{ route("provider.quotation-requests.preview", $quotationRequest) }}', '_blank', 'width=800,height=600');
    });

    // ✅ Send – Show confirmation modal
    sendBtn.addEventListener('click', function() {
        const grandTotalText = document.getElementById('grandTotalDisplay').textContent;
        modalGrandTotal.textContent = grandTotalText;
        sendModal.classList.remove('hidden');
    });

    // Modal Cancel
    modalCancelBtn.addEventListener('click', function() {
        sendModal.classList.add('hidden');
    });

    // Modal Confirm → Actually send
    modalConfirmBtn.addEventListener('click', function() {
        sendModal.classList.add('hidden');
        
        // Auto-save before send
        const formData = buildFormData();
        sendBtn.disabled = true;
        showLoading(true);

        // First save
        fetch('{{ route("provider.quotation-requests.update", $quotationRequest) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Save failed');
            }
            // Then send
            return fetch('{{ route("provider.quotation-requests.send", $quotationRequest) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        })
        .then(res => res.json())
        .then(data => {
            showLoading(false);
            if (data.success) {
                showMessage('success', data.message || 'Quotation sent successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("provider.quotation-requests.show", $quotationRequest) }}';
                }, 2000);
            } else {
                showMessage('error', data.error || data.message || 'Send failed.');
            }
        })
        .catch(err => {
            showLoading(false);
            showMessage('error', err.message || 'Server error. Please try again.');
        })
        .finally(() => {
            sendBtn.disabled = false;
        });
    });

    // Close modal on click outside
    sendModal.addEventListener('click', function(e) {
        if (e.target === this) {
            sendModal.classList.add('hidden');
        }
    });

    // Build form data
    function buildFormData() {
        const rows = itemsContainer.querySelectorAll('.item-row');
        const items = [];
        rows.forEach(row => {
            items.push({
                description: row.querySelector('input[name*="[description]"]').value,
                per_person: parseFloat(row.querySelector('input[name*="[per_person]"]').value) || 0,
                quantity: parseInt(row.querySelector('input[name*="[quantity]"]').value) || 1,
            });
        });

        const terms = termsInput.value.split('\n').filter(t => t.trim());

        return {
            items: items,
            discount: parseFloat(discountInput.value) || 0,
            terms: terms,
            special_notes: specialNotesInput.value,
        };
    }

    // Helpers
    function showLoading(show) {
        loading.classList.toggle('hidden', !show);
    }

    function showMessage(type, text) {
        resultMessage.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
        if (type === 'success') {
            resultMessage.className = 'p-4 rounded-lg mt-4 bg-green-100 text-green-800';
        } else {
            resultMessage.className = 'p-4 rounded-lg mt-4 bg-red-100 text-red-800';
        }
        resultMessage.textContent = text;
    }

    // Initial calculation
    recalculateTotals();
});
</script>
@endsection
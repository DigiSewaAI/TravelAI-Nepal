{{-- Partial: Universal Offers CRUD UI for all verticals --}}

<style>
        /* Local styles for the offers page (loaded directly) */
    .offers-table { width: 100%; border-collapse: collapse; }
    .offers-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted); padding: 12px 15px; border-bottom: 1px solid var(--line); }
    .offers-table td { padding: 15px; font-size: 13px; border-bottom: 1px solid #f3f5fb; color: var(--text); }
    .offer-pill { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 700; }
    .offer-pill.active { background: var(--green-soft); color: var(--green); }
    .offer-pill.inactive { background: var(--red-soft); color: var(--red); }
    
    /* Modal Styles */
    .offer-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(3px); }
    .offer-modal-box { background: var(--card); border-radius: 18px; width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,.2); max-height: 90vh; overflow-y: auto; }
    .offer-form-group { margin-bottom: 15px; }
    .offer-form-label { display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
    .offer-form-input { width: 100%; padding: 12px 14px; border: 1px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 13px; background: var(--bg); color: var(--text); outline: none; transition: border-color 0.2s; }
    .offer-form-input:focus { border-color: var(--accent); background: var(--card); }
    
    .offer-btn-action { background: var(--accent); color: #fff; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 13px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
    .offer-btn-action:hover { filter: brightness(0.9); transform: translateY(-1px); }
    .offer-btn-back { background: var(--bg); color: var(--text); padding: 10px 18px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; border: 1px solid var(--line); transition: all 0.2s; }
    .offer-btn-back:hover { background: var(--line); }
</style>

<div class="card" style="background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:15px;">
        <h3 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">{{ __('messages.offers_management') ?? 'Offers & Promotions' }}</h3>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('provider.dashboard') }}" class="offer-btn-back">Back</a>
            <button onclick="openOfferModal()" class="offer-btn-action">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14m-7-7h14"/></svg>
                Add Offer
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:var(--green-soft); color:var(--green); padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:700; border:1px solid var(--green);">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($offers->isEmpty())
        <div style="text-align:center; padding:50px 20px; color:var(--muted); background:var(--bg); border-radius:14px; border:1px dashed var(--line);">
            <div style="font-size:40px; margin-bottom:12px; opacity:0.5;">🏷️</div>
            <p style="font-size:15px; font-weight:800; color:var(--text); margin:0 0 5px;">No active offers right now.</p>
            <p style="font-size:13px; margin:0;">Click "Add Offer" to create your first promotion.</p>
        </div>
    @else
        <div style="overflow-x:auto; border:1px solid var(--line); border-radius:14px;">
            <table class="offers-table">
                <thead style="background:var(--bg);">
                    <tr>
                        <th>Offer Title</th>
                        <th>Discount</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offers as $offer)
                    <tr>
                        <td><strong style="color:var(--text);">{{ $offer->title }}</strong></td>
                        <td style="font-weight:800; color:var(--accent);">{{ $offer->discount }}</td>
                        <td style="color:var(--muted);">{{ $offer->valid_until->format('M d, Y') }}</td>
                        <td>
                            @if($offer->is_active && $offer->valid_until->isFuture())
                                <span class="offer-pill active">Active</span>
                            @else
                                <span class="offer-pill inactive">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <button onclick='editOffer(@json($offer))' style="background:var(--blue-soft); color:var(--blue); border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:700; font-size:12px; margin-right:6px; transition:0.2s;">Edit</button>
                            <form action="{{ route('provider.' . $verticalPrefix . '.offers.destroy', $offer) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this offer?');">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:var(--red-soft); color:var(--red); border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:700; font-size:12px; transition:0.2s;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Add/Edit Modal --}}
<div id="offerModal" class="offer-modal-overlay">
    <div class="offer-modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 id="modalTitle" style="font-size:20px; font-weight:800; color:var(--text); margin:0;">Add Offer</h3>
            <button type="button" onclick="closeOfferModal()" style="background:var(--bg); border:none; width:32px; height:32px; border-radius:50%; font-size:20px; cursor:pointer; color:var(--muted); display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        
        <form id="offerForm" method="POST" action="{{ route('provider.' . $verticalPrefix . '.offers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="offer-form-group">
                <label class="offer-form-label">Offer Title</label>
                <input type="text" name="title" id="offerTitle" required class="offer-form-input" placeholder="e.g. Happy Hour (4-6 PM)">
            </div>
            
            <div class="offer-form-group">
                <label class="offer-form-label">Discount</label>
                <input type="text" name="discount" id="offerDiscount" required class="offer-form-input" placeholder="e.g. 20% Off">
            </div>
            
            <div class="offer-form-group">
                <label class="offer-form-label">Valid Until</label>
                <input type="date" name="valid_until" id="offerValidUntil" required class="offer-form-input" min="{{ date('Y-m-d') }}">
            </div>

            <div class="offer-form-group" style="display:flex; align-items:center; gap:10px; margin-top:24px; padding:12px; background:var(--bg); border-radius:10px; border:1px solid var(--line);">
                <input type="checkbox" name="is_active" id="offerIsActive" value="1" checked style="width:18px; height:18px; cursor:pointer; accent-color:var(--accent);">
                <label for="offerIsActive" style="font-size:14px; font-weight:700; color:var(--text); cursor:pointer; margin:0;">Active</label>
            </div>

            <button type="submit" class="offer-btn-action" style="width:100%; justify-content:center; margin-top:24px; padding:14px; font-size:14px;">Save Offer</button>
        </form>
    </div>
</div>

<script>
    const offerModal = document.getElementById('offerModal');
    const offerForm = document.getElementById('offerForm');
    const methodInput = document.getElementById('formMethod');
    const titleInput = document.getElementById('offerTitle');
    const discountInput = document.getElementById('offerDiscount');
    const validUntilInput = document.getElementById('offerValidUntil');
    const isActiveInput = document.getElementById('offerIsActive');

    function openOfferModal() {
        document.getElementById('modalTitle').textContent = 'Add Offer';
        offerForm.action = "{{ route('provider.' . $verticalPrefix . '.offers.store') }}";
        methodInput.value = 'POST';
        offerForm.reset();
        isActiveInput.checked = true;
        offerModal.style.display = 'flex';
    }

    function editOffer(offer) {
        document.getElementById('modalTitle').textContent = 'Edit Offer';
        offerForm.action = `/provider/{{ $verticalPrefix }}/offers/${offer.id}`;
        methodInput.value = 'PUT';
        titleInput.value = offer.title;
        discountInput.value = offer.discount;
        validUntilInput.value = offer.valid_until.split('T')[0];
        isActiveInput.checked = offer.is_active;
        offerModal.style.display = 'flex';
    }

    function closeOfferModal() {
        offerModal.style.display = 'none';
    }

    offerModal.addEventListener('click', function(e) {
        if (e.target === offerModal) closeOfferModal();
    });
</script>
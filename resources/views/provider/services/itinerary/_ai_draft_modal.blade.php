{{-- PHASE X-01: AI Itinerary Draft Modal --}}
<div id="ai-draft-modal"
     class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
     data-modal>

    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.ai_draft_modal_title') }}</h3>
            <button type="button"
                    onclick="closeAiDraftModal()"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                    aria-label="{{ __('messages.ai_draft_cancel_button') }}">
                &times;
            </button>
        </div>

        {{-- Stage 1 — Form --}}
        <div id="ai-draft-form-stage" class="p-6 space-y-4">
            <div>
                <label for="ai-draft-days" class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('messages.ai_draft_days_label') }}
                </label>
                <input type="number"
                       id="ai-draft-days"
                       min="1"
                       max="21"
                       value="7"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">{{ __('messages.ai_draft_days_hint') }}</p>
            </div>

                        <div>
                <label for="ai-draft-notes" class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('messages.ai_draft_notes_label') }}
                </label>
                <textarea id="ai-draft-notes"
                          rows="3"
                          maxlength="500"
                          placeholder="{{ __('messages.ai_draft_notes_placeholder') }}"
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            {{-- F1 HOTFIX: Verify warning --}}
            <div class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3 flex gap-2">
                <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                <span>{{ __('messages.ai_draft_verify_warning') }}</span>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                        onclick="closeAiDraftModal()"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-semibold">
                    {{ __('messages.ai_draft_cancel_button') }}
                </button>
                <button type="button"
                        id="ai-draft-generate-btn"
                        class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold">
                    {{ __('messages.ai_draft_generate_button') }}
                </button>
            </div>

            <div id="ai-draft-loading" class="hidden text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                {{ __('messages.ai_draft_loading') }}
            </div>

            <div id="ai-draft-error" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3"></div>
        </div>

        {{-- Stage 2 — Preview --}}
        <div id="ai-draft-preview-stage" class="hidden p-6 space-y-4">
            <h4 id="ai-draft-preview-heading" class="text-base font-semibold text-gray-800"></h4>
            <div id="ai-draft-preview-list" class="space-y-3"></div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                <button type="button"
                        onclick="closeAiDraftModal()"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-semibold">
                    {{ __('messages.ai_draft_cancel_button') }}
                </button>
                <button type="button"
                        id="ai-draft-apply-btn"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                    {{ __('messages.ai_draft_apply_button') }}
                </button>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    var modal          = document.getElementById('ai-draft-modal');
    var formStage      = document.getElementById('ai-draft-form-stage');
    var previewStage   = document.getElementById('ai-draft-preview-stage');
    var previewList    = document.getElementById('ai-draft-preview-list');
    var previewHeading = document.getElementById('ai-draft-preview-heading');
    var generateBtn    = document.getElementById('ai-draft-generate-btn');
    var applyBtn       = document.getElementById('ai-draft-apply-btn');
    var loadingEl      = document.getElementById('ai-draft-loading');
    var errorEl        = document.getElementById('ai-draft-error');
    var daysInput      = document.getElementById('ai-draft-days');
    var notesInput     = document.getElementById('ai-draft-notes');

    var aiDraftUrl      = null;
    var aiDraftApplyUrl = null;
    var csrfToken       = null;
    var currentDraftId  = null;

    window.openAiDraftModal = function (btn) {
        aiDraftUrl      = btn.dataset.aiDraftUrl;
        aiDraftApplyUrl = btn.dataset.aiDraftApplyUrl;
        csrfToken       = btn.dataset.csrf;

        // Reset state
        formStage.classList.remove('hidden');
        previewStage.classList.add('hidden');
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
        loadingEl.classList.add('hidden');
        previewList.innerHTML = '';
        previewHeading.textContent = '';
        currentDraftId = null;
        generateBtn.disabled = false;
        generateBtn.textContent = @json(__('messages.ai_draft_generate_button'));
        applyBtn.disabled = false;
        applyBtn.textContent = @json(__('messages.ai_draft_apply_button'));

        modal.classList.remove('hidden');
    };

    window.closeAiDraftModal = function () {
        modal.classList.add('hidden');
    };

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeAiDraftModal();
        }
    });

    generateBtn.addEventListener('click', async function () {
        var days = parseInt(daysInput.value || '0', 10);
        var notes = notesInput.value || '';

        if (days < 1 || days > 21) {
            showError(@json(__('messages.ai_draft_error_generic')));
            return;
        }

        errorEl.classList.add('hidden');
        loadingEl.classList.remove('hidden');
        generateBtn.disabled = true;
        generateBtn.textContent = @json(__('messages.ai_draft_loading'));

        try {
            var res = await fetch(aiDraftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ days: days, notes: notes }),
            });

            var data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || @json(__('messages.ai_draft_error_generic')));
            }

            currentDraftId = data.draft_id;
            renderPreview(data.preview, data.days_count);

            formStage.classList.add('hidden');
            previewStage.classList.remove('hidden');
        } catch (err) {
            showError(err.message || @json(__('messages.ai_draft_error_generic')));
        } finally {
            loadingEl.classList.add('hidden');
            generateBtn.disabled = false;
            generateBtn.textContent = @json(__('messages.ai_draft_generate_button'));
        }
    });

    applyBtn.addEventListener('click', function () {
        if (!currentDraftId) return;

        applyBtn.disabled = true;
        applyBtn.textContent = @json(__('messages.ai_draft_apply_button')) + '...';

        // Submit via form (redirect endpoint)
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = aiDraftApplyUrl;

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = csrfToken;
        form.appendChild(csrf);

        var draftId = document.createElement('input');
        draftId.type = 'hidden';
        draftId.name = 'draft_id';
        draftId.value = currentDraftId;
        form.appendChild(draftId);

        document.body.appendChild(form);
        form.submit();
    });

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    }

    function renderPreview(days, count) {
        previewList.innerHTML = '';
        previewHeading.textContent = @json(__('messages.ai_draft_preview_heading')).replace(':days', count || (days ? days.length : 0));

        (days || []).forEach(function (day) {
            var dayEl = document.createElement('div');
            dayEl.className = 'bg-gray-50 rounded-lg border border-gray-200 p-4';

            var header = document.createElement('div');
            header.className = 'flex items-center gap-2';

            var badge = document.createElement('span');
            badge.className = 'text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full';
            badge.textContent = 'Day ' + (day.day_number || '?');
            header.appendChild(badge);

            var titleEl = document.createElement('span');
            titleEl.className = 'font-semibold text-gray-800 text-sm';
            titleEl.textContent = day.title || '';
            header.appendChild(titleEl);

            dayEl.appendChild(header);

            if (day.description) {
                var descEl = document.createElement('p');
                descEl.className = 'text-xs text-gray-600 mt-2';
                descEl.textContent = day.description;
                dayEl.appendChild(descEl);
            }

            // Metadata chips
            var chips = [];
            if (day.distance_km) chips.push('\uD83D\uDCCF ' + day.distance_km + ' km');
            if (day.estimated_time_hours) chips.push('\u23F1 ' + day.estimated_time_hours + ' hrs');
            if (day.altitude_m) chips.push('\u26F0 ' + day.altitude_m + ' m');
            if (day.accommodation) chips.push('\uD83C\uDFE8 ' + day.accommodation);
            if (day.meals_included && day.meals_included.length) chips.push('\uD83C\uDF7D ' + day.meals_included.join(' '));

            if (chips.length) {
                var chipsEl = document.createElement('div');
                chipsEl.className = 'flex flex-wrap gap-1 mt-2 text-[11px] text-gray-500';
                chips.forEach(function (c) {
                    var s = document.createElement('span');
                    s.className = 'bg-white border border-gray-200 px-2 py-0.5 rounded';
                    s.textContent = c;
                    chipsEl.appendChild(s);
                });
                dayEl.appendChild(chipsEl);
            }

            var itemCount = (day.items || []).length;
            if (itemCount > 0) {
                var itemsEl = document.createElement('div');
                itemsEl.className = 'text-[11px] text-gray-500 mt-2';
                itemsEl.textContent = itemCount + ' ' + @json(__('messages.ai_draft_activities_label'));
                dayEl.appendChild(itemsEl);
            }

            previewList.appendChild(dayEl);
        });
    }
})();
</script>
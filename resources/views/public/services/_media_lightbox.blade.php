{{-- PHASE 4C: Media Lightbox --}}
{{-- Vanilla JS lightbox for day media images --}}

<div id="mediaLightbox"
     class="hidden fixed inset-0 z-[99999] bg-black/90 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-hidden="true">

    {{-- Close --}}
    <button type="button"
            class="media-lightbox-close absolute top-4 right-4 md:top-6 md:right-6 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
            aria-label="{{ __('messages.media_close') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Prev --}}
    <button type="button"
            class="media-lightbox-prev absolute left-2 md:left-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-14 md:h-14 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
            aria-label="{{ __('messages.media_prev') }}">
        <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Next --}}
    <button type="button"
            class="media-lightbox-next absolute right-2 md:right-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-14 md:h-14 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
            aria-label="{{ __('messages.media_next') }}">
        <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Content --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 pointer-events-none">
        <img src=""
             alt=""
             class="media-lightbox-image max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl pointer-events-auto select-none">
        <div class="media-lightbox-day text-white/60 text-xs font-semibold uppercase tracking-wider mt-4 pointer-events-auto"></div>
        <div class="media-lightbox-caption text-white text-sm mt-1 text-center max-w-2xl pointer-events-auto px-4"></div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    var triggers = document.querySelectorAll('.media-lightbox-trigger');
    if (!triggers.length) return;

    var modal = document.getElementById('mediaLightbox');
    if (!modal) return;

    var items = [];
    triggers.forEach(function (btn, i) {
        var img = btn.querySelector('img');
        items.push({
            src: btn.getAttribute('data-src') || '',
            caption: btn.getAttribute('data-caption') || '',
            day: btn.getAttribute('data-day') || '',
            alt: img ? img.getAttribute('alt') : ''
        });
        btn.setAttribute('data-lightbox-index', i);
    });

    var imgEl = modal.querySelector('.media-lightbox-image');
    var captionEl = modal.querySelector('.media-lightbox-caption');
    var dayEl = modal.querySelector('.media-lightbox-day');
    var closeBtn = modal.querySelector('.media-lightbox-close');
    var prevBtn = modal.querySelector('.media-lightbox-prev');
    var nextBtn = modal.querySelector('.media-lightbox-next');

    var currentIndex = 0;
    var isOpen = false;

    function show(index) {
        if (index < 0) index = items.length - 1;
        if (index >= items.length) index = 0;
        currentIndex = index;
        var item = items[index];
        if (!item) return;
        imgEl.src = item.src;
        imgEl.alt = item.alt || item.caption || '';
        captionEl.textContent = item.caption || '';
        dayEl.textContent = item.day || '';
        if (!isOpen) {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            isOpen = true;
        }
    }

    function close() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        imgEl.src = '';
        isOpen = false;
    }

    function prev() { show(currentIndex - 1); }
    function next() { show(currentIndex + 1); }

    triggers.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var idx = parseInt(btn.getAttribute('data-lightbox-index'), 10);
            show(isNaN(idx) ? 0 : idx);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (prevBtn) prevBtn.addEventListener('click', function (e) { e.stopPropagation(); prev(); });
    if (nextBtn) nextBtn.addEventListener('click', function (e) { e.stopPropagation(); next(); });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', function (e) {
        if (!isOpen) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') prev();
        else if (e.key === 'ArrowRight') next();
    });

    var touchStartX = 0;
    modal.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    modal.addEventListener('touchend', function (e) {
        var diff = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) prev(); else next();
        }
    }, { passive: true });

})();
</script>
@endpush
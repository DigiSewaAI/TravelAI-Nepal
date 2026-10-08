{{-- Ads: "Featured for You" section (Phase 1 MVP — Carousel) --}}
@if(isset($featuredAds) && $featuredAds->count() > 0)
    @php $adsCount = $featuredAds->count(); @endphp

    <div style="background:#fff; border-radius:0.75rem; border:1px solid #f3f4f6; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.05);">

        {{-- Header --}}
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.5rem; background:#fffbeb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-star" style="color:#f59e0b;"></i>
            </div>
            <div>
                <h4 style="font-weight:600; color:#1f2937; margin:0;">{{ __('messages.ads_featured_title') }}</h4>
                <p style="font-size:0.75rem; color:#6b7280; margin:0;">{{ __('messages.ads_featured_subtitle') }}</p>
            </div>
        </div>

        {{-- Carousel wrapper --}}
        <div style="position:relative; overflow:hidden; border-radius:0.75rem; background:#f9fafb;">

            {{-- Track (slides) --}}
            <div id="adsTrack" style="display:flex; transition:transform 0.5s ease-out; transform:translateX(0);">

                @foreach($featuredAds as $ad)
                    <div class="ad-slide" data-ad-id="{{ $ad->id }}" style="min-width:100%; width:100%; flex-shrink:0;">
                        <a href="{{ route('traveler.ads.click', $ad) }}"
                           style="display:block; position:relative; text-decoration:none; color:inherit;"
                           data-impression-url="{{ route('traveler.ads.impression', $ad) }}">

                            {{-- Sponsored label --}}
                            <span style="position:absolute; top:0.5rem; right:0.5rem; z-index:10; background:#facc15; color:#713f12; font-size:9px; text-transform:uppercase; letter-spacing:0.05em; font-weight:700; padding:0.125rem 0.5rem; border-radius:0.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.15);">
                                {{ __('messages.ads_sponsored_label') }}
                            </span>

                            <div style="display:flex; flex-direction:row; align-items:stretch; background:#fff; border-radius:0.5rem; overflow:hidden;">
                                <img src="{{ Storage::url($ad->image_path) }}"
                                     alt="{{ $ad->title }}"
                                     style="width:11rem; height:8rem; object-fit:cover; flex-shrink:0;">

                                <div style="flex:1; padding:1rem; min-width:0;">
                                    <h5 style="font-weight:700; color:#111827; font-size:1rem; margin:0 0 0.25rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                        {{ $ad->title }}
                                    </h5>

                                    @if($ad->provider)
                                        <p style="font-size:0.75rem; color:#6b7280; margin:0.25rem 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <i class="fas fa-store" style="color:#f59e0b; margin-right:0.25rem;"></i>
                                            {{ $ad->provider->name }}
                                        </p>
                                    @endif

                                    @if($ad->description)
                                        <p style="font-size:0.75rem; color:#4b5563; margin:0.5rem 0 0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                            {{ $ad->description }}
                                        </p>
                                    @endif

                                    <span style="display:inline-flex; align-items:center; gap:0.25rem; margin-top:0.75rem; font-size:0.75rem; font-weight:700; color:#d97706;">
                                        {{ $ad->cta_text ?? __('messages.ads_learn_more') }}
                                        <i class="fas fa-arrow-right" style="font-size:10px;"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach

            </div>

            {{-- Arrows --}}
            @if($adsCount > 1)
                <button type="button" onclick="carouselPrev()"
                        style="position:absolute; top:50%; transform:translateY(-50%); left:0.5rem; z-index:20; width:2rem; height:2rem; border-radius:9999px; background:rgba(255,255,255,0.95); box-shadow:0 2px 8px rgba(0,0,0,0.15); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#374151;">
                    <i class="fas fa-chevron-left" style="font-size:12px;"></i>
                </button>
                <button type="button" onclick="carouselNext()"
                        style="position:absolute; top:50%; transform:translateY(-50%); right:0.5rem; z-index:20; width:2rem; height:2rem; border-radius:9999px; background:rgba(255,255,255,0.95); box-shadow:0 2px 8px rgba(0,0,0,0.15); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#374151;">
                    <i class="fas fa-chevron-right" style="font-size:12px;"></i>
                </button>
            @endif

        </div>

        {{-- Dots --}}
        @if($adsCount > 1)
            <div id="carouselDots" style="display:flex; justify-content:center; gap:0.375rem; margin-top:0.75rem;">
                @for($i = 0; $i < $adsCount; $i++)
                    <button type="button" data-index="{{ $i }}"
                            class="carousel-dot"
                            style="width:{{ $i === 0 ? '1.25rem' : '0.5rem' }}; height:0.5rem; border-radius:9999px; border:none; cursor:pointer; transition:all 0.3s; background:{{ $i === 0 ? '#f59e0b' : '#d1d5db' }};"></button>
                @endfor
            </div>
        @endif

    </div>

    <script>
    (function () {
        const track = document.getElementById('adsTrack');
        const dots = document.querySelectorAll('.carousel-dot');
        if (!track) return;

        const total = {{ $adsCount }};
        let current = 0;
        let autoTimer = null;

        function updateSlide() {
            track.style.transform = 'translateX(-' + (current * 100) + '%)';

            dots.forEach(function (dot, i) {
                if (i === current) {
                    dot.style.width = '1.25rem';
                    dot.style.background = '#f59e0b';
                } else {
                    dot.style.width = '0.5rem';
                    dot.style.background = '#d1d5db';
                }
            });

            const activeSlide = track.querySelectorAll('.ad-slide')[current];
            if (activeSlide) {
                const link = activeSlide.querySelector('a[data-impression-url]');
                if (link) {
                    trackImpression(link.dataset.impressionUrl, activeSlide.dataset.adId);
                }
            }
        }

        function trackImpression(url, adId) {
            const tracked = sessionStorage.getItem('ads_tracked') || '';
            const ids = tracked ? tracked.split(',') : [];
            if (ids.includes(adId)) return;

            const csrf = document.querySelector('meta[name="csrf-token"]');
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            }).then(function () {
                ids.push(adId);
                sessionStorage.setItem('ads_tracked', ids.join(','));
            }).catch(function () { /* silent */ });
        }

        window.carouselNext = function () {
            current = (current + 1) % total;
            updateSlide();
            restartAuto();
        };

        window.carouselPrev = function () {
            current = (current - 1 + total) % total;
            updateSlide();
            restartAuto();
        };

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                current = parseInt(this.dataset.index);
                updateSlide();
                restartAuto();
            });
        });

        function restartAuto() {
            if (autoTimer) clearInterval(autoTimer);
            if (total > 1) {
                autoTimer = setInterval(function () {
                    current = (current + 1) % total;
                    updateSlide();
                }, 6000);
            }
        }

        updateSlide();
        restartAuto();
    })();
    </script>
@endif
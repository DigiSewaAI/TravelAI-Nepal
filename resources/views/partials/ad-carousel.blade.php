{{-- Ads: "Featured for You" — Marquee (single line, right-to-left) --}}
@if(isset($featuredAds) && $featuredAds->count() > 0)
    @php $adsCount = $featuredAds->count(); @endphp

    <style>
        @keyframes adMarqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ad-marquee-wrap {
    background: linear-gradient(90deg, #1e293b 0%, #334155 50%, #1e293b 100%);
    border-radius: 12px;
    padding: 12px 16px 14px;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    border: 1px solid #475569;
}
        .ad-marquee-label {
    color: #fbbf24;
    font-weight: 800;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 8px;
    border-bottom: 1px solid #475569;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}
        .ad-marquee-viewport {
            flex: 1;
            overflow: hidden;
            position: relative;
            -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 4%, #000 96%, transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0%, #000 4%, #000 96%, transparent 100%);
        }
        .ad-marquee-track {
            display: flex;
            gap: 28px;
            animation: adMarqueeScroll 40s linear infinite;
            width: max-content;
        }
        
        .ad-marquee-item {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            text-decoration: none;
            color: #fff;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background 0.15s;
            cursor: pointer;
        }
        .ad-marquee-item:hover { background: rgba(255,255,255,0.12); }
        .ad-marquee-thumb {
            width: 42px;
            height: 42px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid #64748b;
        }
        .ad-marquee-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .ad-marquee-title {
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ad-marquee-provider {
            font-size: 10px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }
        .ad-marquee-video-badge {
            font-size: 8px;
            font-weight: 800;
            background: #ef4444;
            color: #fff;
            padding: 1px 5px;
            border-radius: 3px;
            flex-shrink: 0;
        }
    </style>

    <div class="ad-marquee-wrap">
        <div class="ad-marquee-label">🎬 {{ __('messages.ads_featured_title') }}</div>
        <div class="ad-marquee-viewport">
            <div class="ad-marquee-track">
                {{-- Loop twice for seamless animation --}}
                @for($i = 0; $i < 2; $i++)
                    @foreach($featuredAds as $ad)
                        @if($ad->isVideo())
                            <div class="ad-marquee-item"
                                 data-impression-url="{{ route('traveler.ads.impression', $ad) }}"
                                 data-ad-id="{{ $ad->id }}"
                                 onclick="trackAdImpression(this); openVideoAdModal('{{ Storage::url($ad->video_path) }}', '{{ route('traveler.ads.click', $ad) }}', '{{ $ad->cta_text ?? __('messages.ads_learn_more') }}')">
                                <img src="{{ Storage::url($ad->image_path) }}" alt="{{ $ad->title }}" class="ad-marquee-thumb">
                                <div class="ad-marquee-info">
                                    <div class="ad-marquee-title">
                                        <span style="overflow:hidden;text-overflow:ellipsis;">{{ $ad->title }}</span>
                                        <span class="ad-marquee-video-badge">▶ VIDEO</span>
                                    </div>
                                    @if($ad->provider)
                                        <div class="ad-marquee-provider">{{ $ad->provider->name }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <a href="{{ route('traveler.ads.click', $ad) }}"
                               class="ad-marquee-item"
                               data-impression-url="{{ route('traveler.ads.impression', $ad) }}"
                               data-ad-id="{{ $ad->id }}"
                               onclick="trackAdImpression(this)">
                                <img src="{{ Storage::url($ad->image_path) }}" alt="{{ $ad->title }}" class="ad-marquee-thumb">
                                <div class="ad-marquee-info">
                                    <div class="ad-marquee-title">{{ $ad->title }}</div>
                                    @if($ad->provider)
                                        <div class="ad-marquee-provider">{{ $ad->provider->name }}</div>
                                    @endif
                                </div>
                            </a>
                        @endif
                    @endforeach
                @endfor
            </div>
        </div>
    </div>

    <script>
    // 🆕 Marquee Pause — Desktop (hover) + Mobile (touch)
    (function() {
        var viewport = document.querySelector('.ad-marquee-viewport');
        var track = document.querySelector('.ad-marquee-track');
        if (!viewport || !track) return;

        function pause()  { track.style.animationPlayState = 'paused'; }
        function resume() { track.style.animationPlayState = 'running'; }

        viewport.addEventListener('mouseenter', pause);
        viewport.addEventListener('mouseleave', resume);
        viewport.addEventListener('touchstart', pause, { passive: true });
        viewport.addEventListener('touchend', resume, { passive: true });
        viewport.addEventListener('touchcancel', resume, { passive: true });
    })();

    // Track impression (once per session per ad)
    function trackAdImpression(el) {
        var url = el.dataset.impressionUrl;
        var adId = el.dataset.adId;
        if (!url || !adId) return;
        var tracked = sessionStorage.getItem('ads_tracked') || '';
        var ids = tracked ? tracked.split(',') : [];
        if (ids.includes(adId)) return;
        var csrf = document.querySelector('meta[name="csrf-token"]');
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
        }).catch(function () {});
    }

    // Video Ad Modal
    window.openVideoAdModal = function(videoUrl, ctaUrl, ctaText) {
        var modal = document.getElementById('videoAdModal');
        var player = document.getElementById('adVideoPlayer');
        var cta = document.getElementById('videoAdCta');
        if (!modal || !player || !cta) return;
        player.src = videoUrl;
        cta.href = ctaUrl;
        cta.textContent = ctaText + ' →';
        modal.style.display = 'flex';
        player.play().catch(function () {});
    };

    window.closeVideoAdModal = function() {
        var modal = document.getElementById('videoAdModal');
        var player = document.getElementById('adVideoPlayer');
        if (modal) modal.style.display = 'none';
        if (player) {
            player.pause();
            player.currentTime = 0;
            player.src = '';
        }
    };
    </script>

    {{-- Video Ad Modal --}}
    <div id="videoAdModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.92); z-index:99999; align-items:center; justify-content:center; padding:1rem;">
        <div style="position:relative; max-width:800px; width:100%;">
            <button type="button" onclick="closeVideoAdModal()"
                    style="position:absolute; top:-44px; right:0; background:#fff; color:#111; border:0; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:18px; font-weight:bold; display:flex; align-items:center; justify-content:center;">
                &times;
            </button>
            <video id="adVideoPlayer" controls playsinline style="width:100%; border-radius:12px; background:#000; max-height:70vh;"></video>
            <a id="videoAdCta" href="#" target="_blank"
               style="display:block; margin-top:1rem; text-align:center; background:#f59e0b; color:#fff; padding:14px; border-radius:8px; font-weight:bold; text-decoration:none; font-size:15px;">
                Learn More →
            </a>
        </div>
    </div>
@endif
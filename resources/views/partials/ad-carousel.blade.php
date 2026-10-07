{{-- Ads: "Featured for You" section (Phase 1 MVP) --}}
@if(isset($featuredAds) && $featuredAds->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">

        {{-- Header --}}
        <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-star text-amber-500"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800">{{ __('messages.ads_featured_title') }}</h4>
                <p class="text-xs text-gray-500">{{ __('messages.ads_featured_subtitle') }}</p>
            </div>
        </div>

        {{-- Ads list (simple stacked — no carousel complexity for MVP) --}}
        <div class="space-y-3">
            @foreach($featuredAds as $ad)
                <div class="relative border rounded-xl overflow-hidden bg-gray-50 hover:shadow-lg transition">

                    {{-- Sponsored label --}}
                    <span class="absolute top-2 right-2 z-10 bg-yellow-400 text-yellow-900 text-[9px] uppercase tracking-wider font-bold px-2 py-0.5 rounded shadow">
                        {{ __('messages.ads_sponsored_label') }}
                    </span>

                    <a href="{{ route('traveler.ads.click', $ad) }}"
                       class="block"
                       data-ad-id="{{ $ad->id }}"
                       data-impression-url="{{ route('traveler.ads.impression', $ad) }}">

                        <div class="flex flex-col sm:flex-row">
                            <img src="{{ Storage::url($ad->image_path) }}"
                                 alt="{{ $ad->title }}"
                                 class="w-full sm:w-40 h-32 sm:h-24 object-cover flex-shrink-0">

                            <div class="flex-1 p-3 min-w-0">
                                <h5 class="font-bold text-gray-900 text-sm truncate">
                                    {{ $ad->title }}
                                </h5>

                                @if($ad->provider)
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        <i class="fas fa-store mr-1 text-amber-500"></i>
                                        {{ $ad->provider->name }}
                                    </p>
                                @endif

                                @if($ad->description)
                                    <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                        {{ $ad->description }}
                                    </p>
                                @endif

                                <span class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-amber-600 hover:text-amber-700">
                                    {{ $ad->cta_text ?? __('messages.ads_learn_more') }}
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Impression tracking (once per page load, deduped via session) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ads = document.querySelectorAll('[data-impression-url]');
        const csrf = document.querySelector('meta[name="csrf-token"]');
        const tracked = sessionStorage.getItem('ads_tracked') || '';
        const trackedIds = tracked ? tracked.split(',') : [];

        ads.forEach(function (el) {
            const id = el.dataset.adId;
            if (trackedIds.includes(id)) return;   // Already tracked this session

            fetch(el.dataset.impressionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            }).then(function () {
                trackedIds.push(id);
                sessionStorage.setItem('ads_tracked', trackedIds.join(','));
            }).catch(function () { /* silent */ });
        });
    });
    </script>
@endif
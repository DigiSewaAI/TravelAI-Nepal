{{-- Cookie Consent Banner (GDPR-compliant) --}}
<div id="cookie-banner" class="hidden fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50 shadow-2xl">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex-1 text-sm">
            <p class="font-semibold mb-1">{{ __('messages.cookie_banner_title') }}</p>
            <p class="text-gray-300">
                {{ __('messages.cookie_banner_text') }}
                <a href="{{ route('pages.privacy') }}" class="underline hover:text-white">{{ __('messages.cookie_banner_link') }}</a>
            </p>
        </div>
        <div class="flex gap-2 shrink-0">
            <button type="button" id="cookie-reject"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition">
                {{ __('messages.cookie_banner_reject') }}
            </button>
            <button type="button" id="cookie-accept"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold transition">
                {{ __('messages.cookie_banner_accept') }}
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const KEY = 'cookie_consent';
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const consent = localStorage.getItem(KEY);

    // Show banner if no decision yet
    if (consent === null) {
        banner.classList.remove('hidden');
    }

    // Enable GA4 if accepted
    function enableGA() {
        if (typeof gtag === 'function' && window.__GA_ID) {
            gtag('consent', 'update', {
                'analytics_storage': 'granted'
            });
        }
    }

    // Accept
    document.getElementById('cookie-accept').addEventListener('click', function() {
        localStorage.setItem(KEY, 'accepted');
        banner.classList.add('hidden');
        enableGA();
    });

    // Reject
    document.getElementById('cookie-reject').addEventListener('click', function() {
        localStorage.setItem(KEY, 'rejected');
        banner.classList.add('hidden');
    });
})();
</script>
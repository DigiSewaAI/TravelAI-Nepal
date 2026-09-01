@if(isset($ogTitle))
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDescription ?? 'Journey with TravelAI Nepal' }}" />
    <meta property="og:image" content="{{ $ogImage ?? asset('images/default-share.jpg') }}" />
    <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDescription ?? '' }}" />
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/default-share.jpg') }}" />
@endif
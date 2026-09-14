@php
    $ogTitle       = $ogTitle       ?? (trim($__env->yieldContent('title')) ?: __('messages.home_default_title'));
    $ogDescription = $ogDescription ?? (trim($__env->yieldContent('meta_description')) ?: __('messages.home_meta_description'));
    $ogImage       = $ogImage       ?? asset('images/default-share.jpg');
    $ogUrl         = $ogUrl         ?? url()->current();
    $ogType        = $ogType        ?? 'website';
    $ogLocale      = match(app()->getLocale()) {
        'np' => 'ne_NP',
        'hi' => 'hi_IN',
        'zh' => 'zh_CN',
        default => 'en_US',
    };
@endphp

{{-- ================= Open Graph (Facebook, LinkedIn, WhatsApp) ================= --}}
<meta property="og:site_name" content="{{ __('messages.app_name') }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:secure_url" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:locale" content="{{ $ogLocale }}">

{{-- Optional: यदि तपाईंको content multiple languages मा छ भने --}}
@if(app()->getLocale() !== 'en')
    <meta property="og:locale:alternate" content="en_US">
@endif

{{-- ================= Twitter / X ================= --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $ogTitle }}">
<meta name="twitter:site" content="@travelainepal">
<meta name="twitter:creator" content="@travelainepal">

{{-- ================= Facebook App ID (optional) ================= --}}
@if(config('services.facebook.app_id'))
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">
@endif
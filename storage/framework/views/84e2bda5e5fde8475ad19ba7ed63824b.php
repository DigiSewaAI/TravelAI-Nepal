<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- ========== FAVICON ========== -->
    <link rel="icon" type="image/png" sizes="128x128" href="<?php echo e(asset('favicon-128x128.png?v=3')); ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?php echo e(asset('favicon-64x64.png?v=3')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('favicon-32x32.png?v=3')); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico?v=3')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png?v=3')); ?>">
    <link rel="manifest" href="<?php echo e(asset('site.webmanifest?v=3')); ?>">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="theme-color" content="#2563eb">

    <!-- ========== PWA ========== -->
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo e(__('messages.app_name')); ?>">

    <title><?php echo e(__('messages.app_name')); ?> | <?php echo $__env->yieldContent('title', __('messages.home_default_title')); ?></title>

    <!-- ========== SEO META TAGS ========== -->
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', __('messages.home_meta_description')); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', __('messages.home_meta_keywords')); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?php echo e(__('messages.app_name')); ?>">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', config('app.name') . ' | ' . __('messages.og_title_default')); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', __('messages.og_description_default')); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo e(asset('images/og-image.jpg')); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('twitter_title', config('app.name')); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('twitter_description', __('messages.twitter_description_default')); ?>">
    <meta name="twitter:image" content="<?php echo e(asset('images/og-image.jpg')); ?>">

    <!-- ========== Tailwind, Font Awesome, Fonts ========== -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #ffffff; scroll-behavior: smooth; }
        .hero-bg { background: radial-gradient(circle at 10% 30%, rgba(0, 102, 204, 0.03) 0%, rgba(255,255,255,0) 70%); }
        .glass-card { background: rgba(255, 255, 255, 0.96); border: 1px solid rgba(0, 0, 0, 0.05); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .glass-card:hover { transform: translateY(-6px); box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.12); border-color: rgba(0, 100, 200, 0.2); }
        .step-card { transition: all 0.2s; }
        .step-card:hover { background: #f8fafc; border-color: #3b82f6; }
        .nav-link { position: relative; }
        .nav-link:after { content: ''; position: absolute; bottom: -4px; left: 0; width: 0%; height: 2px; background: #3b82f6; transition: 0.25s; }
        .nav-link:hover:after { width: 100%; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        .trek-card:hover { transform: translateY(-8px); transition: 0.25s ease; }
    </style>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registered successfully');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</head>
<body class="antialiased">

    <!-- ======================= HEADER ======================= -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200/70 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 md:px-10 py-2 flex flex-wrap justify-between items-center">
            <!-- Logo -->
            <a href="<?php echo e(url('/')); ?>" class="flex items-center space-x-0 group">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="<?php echo e(__('messages.app_name')); ?>" class="h-16 w-auto -mr-1" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-mountain text-2xl text-blue-600\'></i>'">
                <span class="font-extrabold text-2xl tracking-tight text-gray-800 hidden sm:inline"><?php echo e(__('messages.app_name_short')); ?> <span class="text-blue-600"><?php echo e(__('messages.nepal')); ?></span></span>
            </a>

            <!-- Right side: Navigation + Currency + Language Switcher + Auth -->
            <div class="flex flex-wrap gap-3 md:gap-4 text-gray-700 font-medium mt-3 md:mt-0 items-center">
                <!-- Navigation Links (पहिले जस्तै) -->
                <a href="<?php echo e(url('/')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.home')); ?></a>
                <a href="<?php echo e(url('/features')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.features')); ?></a>
                <a href="<?php echo e(route('public.services.index')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.explore')); ?></a>
                <a href="<?php echo e(route('pages.pricing')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.pricing')); ?></a>
                <a href="<?php echo e(url('/how-it-works')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.how_it_works')); ?></a>
                <a href="<?php echo e(route('public.providers.index')); ?>" class="nav-link text-sm md:text-base"><?php echo e(__('messages.providers')); ?></a>

                <!-- ✅ Language Switcher (Get Early Access को ठाउँमा) -->
                <div class="relative">
                    <button type="button" class="flex items-center text-gray-700 hover:text-gray-900 text-sm font-medium" id="languageDropdown" aria-haspopup="true">
                        <span class="mr-1">
                            <?php if(session('locale') == 'hi'): ?> 🇮🇳
                            <?php elseif(session('locale') == 'zh'): ?> 🇨🇳
                            <?php else: ?> 🇬🇧
                            <?php endif; ?>
                        </span>
                        <span class="text-sm font-medium">
                            <?php if(session('locale') == 'hi'): ?> हिन्दी
                            <?php elseif(session('locale') == 'zh'): ?> 中文
                            <?php else: ?> English
                            <?php endif; ?>
                        </span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-50 hidden" id="languageMenu">
                        <a href="<?php echo e(route('lang.switch', 'en')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇬🇧 English</a>
                        <a href="<?php echo e(route('lang.switch', 'hi')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇮🇳 हिन्दी</a>
                        <a href="<?php echo e(route('lang.switch', 'zh')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🇨🇳 中文</a>
                    </div>
                </div>

                <!-- Currency Selector -->
                <div class="flex items-center">
                    <select id="currency-selector" class="bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-400 transition">
                        <option value="USD" <?php echo e(session('display_currency', 'USD') === 'USD' ? 'selected' : ''); ?>>🇺🇸 USD</option>
                        <option value="NPR" <?php echo e(session('display_currency', 'USD') === 'NPR' ? 'selected' : ''); ?>>🇳🇵 NPR</option>
                    </select>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const selector = document.getElementById('currency-selector');
                        if (selector) {
                            selector.addEventListener('change', function() {
                                const baseUrl = '<?php echo e(url('/')); ?>';
                                window.location.href = baseUrl + '/currency/switch?currency=' + this.value;
                            });
                        }
                    });
                </script>

                <!-- Auth Buttons (पहिले जस्तै) -->
                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.dashboard')); ?></a>
                    <?php elseif(auth()->user()->isProviderOwner()): ?>
                        <a href="<?php echo e(route('provider.dashboard')); ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.dashboard')); ?></a>
                    <?php elseif(auth()->user()->isTraveler()): ?>
                        <a href="<?php echo e(route('traveler.dashboard')); ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.dashboard')); ?></a>
                    <?php else: ?>
                        <a href="<?php echo e(route('home')); ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.home')); ?></a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="border border-red-500 text-red-500 hover:bg-red-50 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.logout')); ?></button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.login')); ?></a>
                    <a href="<?php echo e(route('register')); ?>" class="border border-blue-600 text-blue-600 hover:bg-blue-50 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap"><?php echo e(__('messages.register')); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- ======================= FOOTER ======================= -->
    <footer class="bg-white border-t border-gray-200 pt-4 pb-8 px-6 md:px-10">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Logo + text -->
            <div>
                <a href="<?php echo e(url('/')); ?>" class="flex flex-nowrap items-center space-x-1">
                    <img src="<?php echo e(asset('images/logo-icon.png')); ?>"
                         alt="<?php echo e(__('messages.app_name')); ?>"
                         class="h-32 w-auto min-w-[128px]"
                         onerror="this.onerror=null; this.src='<?php echo e(asset('images/logo.png')); ?>'">
                    <span class="font-bold text-xl text-gray-800 whitespace-nowrap"><?php echo e(__('messages.app_name')); ?></span>
                </a>
                <p class="text-sm text-gray-500 mt-8"><?php echo e(__('messages.footer_tagline')); ?></p>
                <div class="flex space-x-4 mt-2">
                    <i class="fab fa-twitter text-gray-400 hover:text-blue-500"></i>
                    <i class="fab fa-instagram text-gray-400 hover:text-pink-500"></i>
                    <i class="fab fa-github text-gray-400 hover:text-gray-800"></i>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10"><?php echo e(__('messages.product')); ?></h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="<?php echo e(route('pages.features')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.features')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.pricing')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.pricing')); ?></a></li>
                    <li><a href="<?php echo e(route('public.providers.index')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.providers')); ?></a></li>
                        <li><a href="<?php echo e(route('safety.index')); ?>" class="hover:text-blue-600">🛡️ Travel Safety</a></li>
                    <li><a href="<?php echo e(route('register')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.become_partner')); ?></a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10"><?php echo e(__('messages.company')); ?></h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="<?php echo e(route('pages.about')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.about_nepal_trek')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.careers')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.careers')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.press')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.press')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.contact')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.contact_us')); ?></a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10"><?php echo e(__('messages.legal')); ?></h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="<?php echo e(route('pages.privacy')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.privacy_policy')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.terms')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.terms_service')); ?></a></li>
                    <li><a href="<?php echo e(route('pages.gdpr')); ?>" class="hover:text-blue-600"><?php echo e(__('messages.gdpr_data_safety')); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-6 pt-6 text-center text-xs text-gray-400">
            <?php echo e(__('messages.footer_copyright', ['year' => date('Y')])); ?>

        </div>
    </footer>

    <!-- Language Switcher Dropdown JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownBtn = document.getElementById('languageDropdown');
            const dropdownMenu = document.getElementById('languageMenu');

            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/layouts/public.blade.php ENDPATH**/ ?>
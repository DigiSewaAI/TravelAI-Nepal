<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>TravelAI Nepal | @yield('title', 'Plan Entire Nepal Journey')</title>
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
        .nav-link:after { content: ''; position: absolute; bottom: -4px; left: 0; width: 0%; height: 2px; background: #3b82f6; transition: 0.25s; }
        .nav-link:hover:after { width: 100%; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        .trek-card:hover { transform: translateY(-8px); transition: 0.25s ease; }
        .itinerary-content { line-height: 1.6; }
        .itinerary-content strong { font-weight: 700; color: #1e40af; }
        .itinerary-content ul { margin: 0.5rem 0 0.5rem 1.5rem; list-style-type: disc; }
        .itinerary-content li { margin: 0.25rem 0; }
        .itinerary-content h3 { font-size: 1.25rem; font-weight: 700; margin-top: 1rem; margin-bottom: 0.5rem; color: #0f172a; border-left: 4px solid #3b82f6; padding-left: 0.75rem; }
        .itinerary-content p { margin-bottom: 0.75rem; }
    </style>
</head>
<body class="antialiased">

    <!-- ======================= HEADER ======================= -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200/70 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 md:px-10 py-2 flex flex-wrap justify-between items-center">
            <!-- Logo लाई होम पेजमा लिंक गरियो -->
            <a href="{{ url('/') }}" class="flex items-center space-x-0 group">
                <img src="{{ asset('images/logo.png') }}" alt="TravelAI Nepal" class="h-16 w-auto -mr-1" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-mountain text-2xl text-blue-600\'></i>'">
                <span class="font-extrabold text-2xl tracking-tight text-gray-800 hidden sm:inline">TravelAI <span class="text-blue-600">Nepal</span></span>
                <span class="ml-2 bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full border border-blue-200">OS v1.0</span>
            </a>
            <div class="flex flex-wrap gap-5 text-gray-700 font-medium mt-3 md:mt-0 items-center">
                <a href="{{ url('/') }}" class="nav-link text-sm md:text-base">Home</a>
                <a href="{{ url('/features') }}" class="nav-link text-sm md:text-base">Features</a>
                <a href="{{ route('public.services.index') }}" class="nav-link text-sm md:text-base">Explore</a>
                <a href="{{ url('/how-it-works') }}" class="nav-link text-sm md:text-base">How it works</a>
                <a href="{{ url('/agencies') }}" class="nav-link text-sm md:text-base">Agencies</a>
                <a href="{{ url('/#early-access') }}" class="nav-link text-sm md:text-base text-blue-600">Get Early Access</a>

                @guest('agency')
                    <a href="{{ route('agency.login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Agency Login</a>
                    <a href="{{ route('agency.register') }}" class="border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-semibold transition">Register</a>
                @else
                    <a href="{{ route('agency.dashboard') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Dashboard</a>
                    <form method="POST" action="{{ route('agency.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="border border-red-500 text-red-500 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-semibold transition">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- ======================= FOOTER ======================= -->
    <footer class="bg-white border-t border-gray-200 pt-4 pb-8 px-6 md:px-10">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Logo + text लाई होममा लिंक -->
            <div>
                <a href="{{ url('/') }}" class="flex flex-nowrap items-center space-x-1">
                    <img src="{{ asset('images/logo-icon.png') }}" 
                         alt="{{ config('app.name') }}" 
                         class="h-32 w-auto min-w-[128px]"
                         onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}'">
                    <span class="font-bold text-xl text-gray-800 whitespace-nowrap">TravelAI Nepal</span>
                </a>
                <p class="text-sm text-gray-500 mt-8">AI + data-driven trekking ecosystem. Built for Nepal, by passion.</p>
                <div class="flex space-x-4 mt-2">
                    <i class="fab fa-twitter text-gray-400 hover:text-blue-500"></i>
                    <i class="fab fa-instagram text-gray-400 hover:text-pink-500"></i>
                    <i class="fab fa-github text-gray-400 hover:text-gray-800"></i>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">Product</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ url('/features') }}" class="hover:text-blue-600">Features</a></li>
                    <li><a href="#" class="hover:text-blue-600">Agency SaaS</a></li>
                    <li><a href="#" class="hover:text-blue-600">Mobile App (PWA)</a></li>
                    <li><a href="#" class="hover:text-blue-600">Pricing</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">Company</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-blue-600">About Nepal Trek</a></li>
                    <li><a href="#" class="hover:text-blue-600">Careers</a></li>
                    <li><a href="#" class="hover:text-blue-600">Press</a></li>
                    <li><a href="#" class="hover:text-blue-600">Contact us</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gray-800 mt-16 md:mt-10">Legal</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-blue-600">Privacy policy</a></li>
                    <li><a href="#" class="hover:text-blue-600">Terms of service</a></li>
                    <li><a href="#" class="hover:text-blue-600">GDPR & data safety</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-6 pt-6 text-center text-xs text-gray-400">
            © <script>document.write(new Date().getFullYear())</script> TravelAI Nepal — Redefining Himalayan adventures with AI & open tech. 🇳🇵 Made in Nepal for the world.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
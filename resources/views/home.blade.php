<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>TravelAI Nepal | Smart Himalayan OS</title>
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
    /* Itinerary styling */
    .itinerary-content {
        line-height: 1.6;
    }
    .itinerary-content strong {
        font-weight: 700;
        color: #1e40af;
    }
    .itinerary-content ul {
        margin: 0.5rem 0 0.5rem 1.5rem;
        list-style-type: disc;
    }
    .itinerary-content li {
        margin: 0.25rem 0;
    }
    .itinerary-content h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        color: #0f172a;
        border-left: 4px solid #3b82f6;
        padding-left: 0.75rem;
    }
    .itinerary-content p {
        margin-bottom: 0.75rem;
    }
  </style>
</head>
<body class="antialiased">

  <!-- Navigation -->
  <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200/70 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 md:px-10 py-4 flex flex-wrap justify-between items-center">
      <div class="flex items-center space-x-2">
        <i class="fas fa-mountain text-2xl text-blue-600"></i>
        <span class="font-extrabold text-2xl tracking-tight text-gray-800">TravelAI <span class="text-blue-600">Nepal</span></span>
        <span class="ml-2 bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full border border-blue-200">OS v1.0</span>
      </div>
      <div class="flex flex-wrap gap-5 text-gray-700 font-medium mt-3 md:mt-0">
        <a href="#home" class="nav-link text-sm md:text-base">Home</a>
        <a href="#features" class="nav-link text-sm md:text-base">Features</a>
        <a href="#treks" class="nav-link text-sm md:text-base">Treks</a>
        <a href="#workflow" class="nav-link text-sm md:text-base">How it works</a>
        <a href="#agencies" class="nav-link text-sm md:text-base">Agencies</a>
        <a href="#early-access" class="nav-link text-sm md:text-base text-blue-600">Get Early Access</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section (unchanged) -->
  <section id="home" class="hero-bg relative overflow-hidden pt-12 pb-20 md:pt-20 md:pb-28">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div>
          <div class="inline-flex items-center gap-2 bg-blue-50 rounded-full px-4 py-1.5 border border-blue-100 mb-6">
            <i class="fas fa-microchip text-blue-600 text-xs"></i>
            <span class="text-xs font-semibold text-blue-700 tracking-wide">AI + Blockchain Ready • Nepal's first Travel OS</span>
          </div>
          <h1 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight text-gray-900">
            The Smart Operating System <br> for <span class="text-blue-600 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Himalayas</span>
          </h1>
          <p class="text-gray-600 text-lg md:text-xl mt-6 max-w-xl leading-relaxed">
            One ecosystem connecting trekkers, agencies & guides — AI itineraries, offline SOS, digital trek passport, and zero‑commission smart contracts.
          </p>
          <div class="flex flex-wrap gap-4 mt-8">
            <a href="#early-access" class="bg-gray-900 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-xl flex items-center gap-2"><i class="fas fa-arrow-right"></i> Start Exploring</a>
            <a href="#features" class="border border-gray-300 hover:border-blue-400 bg-white text-gray-800 font-semibold px-6 py-3 rounded-xl transition-all hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-eye"></i> See Features</a>
          </div>
          <div class="flex flex-wrap gap-6 mt-10 text-sm text-gray-500">
            <div class="flex items-center gap-1"><i class="fas fa-check-circle text-green-500"></i> No hidden fees</div>
            <div class="flex items-center gap-1"><i class="fas fa-shield-alt text-blue-500"></i> Real-time safety</div>
            <div class="flex items-center gap-1"><i class="fas fa-headset text-purple-500"></i> 24/7 AI assistant</div>
          </div>
        </div>
        <div class="relative flex justify-center">
          <div class="w-full max-w-md bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-5 shadow-2xl border border-white/40 backdrop-blur-sm">
            <div class="bg-white/80 rounded-2xl p-4 shadow-inner">
              <div class="flex justify-between items-center border-b pb-2 mb-3"><span class="font-bold text-gray-700"><i class="fas fa-map-marked-alt text-blue-500 mr-2"></i>Live route tracking</span><span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Annapurna Circuit</span></div>
              <div class="h-40 bg-gradient-to-br from-slate-200 to-gray-300 rounded-xl flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-mountain text-6xl text-white/50 absolute"></i>
                <div class="absolute bottom-2 left-2 text-white text-xs font-mono bg-black/20 px-2 py-0.5 rounded-full">📍 Elevation 3,200m</div>
                <div class="absolute top-2 right-2 bg-blue-600/80 text-white text-xs px-2 py-0.5 rounded-full"><i class="fas fa-location-dot"></i> Live</div>
              </div>
              <div class="flex mt-3 justify-between text-xs text-gray-500">
                <span><i class="fas fa-clock"></i> 2hr ago</span>
                <span><i class="fas fa-wifi"></i> Offline backup</span>
                <span><i class="fas fa-temperature-low"></i> -2°C</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- AI Travel Planner Section – Enhanced Professional -->
  <div class="max-w-4xl mx-auto px-4 my-12">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
          <i class="fas fa-robot"></i> AI Travel Planner
        </h2>
        <p class="text-blue-100 text-sm">Generate a personalized day-by-day itinerary powered by advanced AI</p>
      </div>
      <div class="p-6">
        <form id="itineraryForm" class="space-y-5">
          @csrf
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-gray-700 font-semibold mb-1">Destination *</label>
              <input type="text" name="destination" required placeholder="e.g., Pokhara, Everest Base Camp" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">Number of Days *</label>
              <input type="number" name="days" min="1" max="30" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">Budget (NPR/USD) *</label>
              <input type="number" name="budget" min="1000" required placeholder="e.g., 50000" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">Travel Style *</label>
              <select name="travel_style" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                <option value="budget">Budget Travel</option>
                <option value="mid-range">Mid-Range</option>
                <option value="luxury">Luxury</option>
                <option value="backpacker">Backpacker</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1">Your Interests (optional)</label>
            <textarea name="interests" rows="2" placeholder="e.g., adventure, culture, nature, food, photography" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          <button type="submit" id="generateBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2 shadow-md">
            <i class="fas fa-magic"></i> Generate Itinerary
          </button>
        </form>

        <!-- Result Section with Download Buttons -->
        <div id="result" class="mt-8 hidden">
          <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
              <i class="fas fa-map-marked-alt text-blue-600"></i> Your Personalized Itinerary
            </h3>
            <div class="flex gap-2">
              <button onclick="downloadItinerary()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 transition">
                <i class="fas fa-download"></i> Download TXT
              </button>
              <button onclick="copyItinerary()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 transition">
                <i class="fas fa-copy"></i> Copy
              </button>
            </div>
          </div>
          <div id="itineraryResult" class="bg-gray-50 p-5 rounded-xl border border-gray-200 text-gray-800 itinerary-content max-h-[600px] overflow-y-auto"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Banner -->
  <div class="bg-gray-50 border-y border-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-6 flex flex-wrap justify-around gap-6 text-center">
      @foreach($stats as $stat)
      <div>
        <span class="text-3xl font-black text-blue-600">{{ $stat['value'] }}</span>
        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
      </div>
      @endforeach
    </div>
  </div>

  <!-- Featured Treks Section (dynamic) -->
  <section id="treks" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">Explore Nepal</span>
      <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">Popular Treks & Tours</h2>
      <p class="text-gray-500 mt-3">Handpicked adventures by local agencies – from Everest Base Camp to hidden valleys.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      @forelse($featuredTreks as $trek)
      <div class="bg-white rounded-2xl shadow-md overflow-hidden trek-card transition duration-300 hover:shadow-xl border border-gray-100">
        <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
          @if($trek->cover_image)
            <img src="{{ asset('storage/' . $trek->cover_image) }}" class="w-full h-full object-cover">
          @else
            <i class="fas fa-mountain text-5xl text-white/80"></i>
          @endif
        </div>
        <div class="p-5">
          <div class="flex justify-between items-start">
            <h3 class="text-xl font-bold text-gray-800">{{ $trek->name }}</h3>
            <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">${{ number_format($trek->price, 0) }}</span>
          </div>
          <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
            <span><i class="far fa-calendar-alt"></i> {{ $trek->duration_days }} days</span>
            <span><i class="fas fa-chart-line"></i> {{ ucfirst($trek->difficulty) }}</span>
          </div>
          <p class="text-gray-600 text-sm mt-3">{{ $trek->agency->name ?? 'TravelAI Partner' }}</p>
          <a href="{{ route('trek.show', $trek) }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">View Details →</a>
        </div>
      </div>
      @empty
      <div class="col-span-full text-center py-10 text-gray-500">No treks available yet. Check back soon!</div>
      @endforelse
    </div>
  </section>

  <!-- Features & other sections (unchanged) -->
  <section id="features" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">Core intelligence</span>
      <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">Everything you need for a smarter trek</h2>
      <p class="text-gray-500 mt-3">From AI travel planner to offline emergency SOS — built for Nepal's terrain and modern travellers.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7" id="featuresGrid"></div>
    <div class="grid md:grid-cols-3 gap-5 mt-10 text-center text-sm text-gray-600" id="extraBadges"></div>
  </section>

  <section id="workflow" class="bg-gray-50 py-20 px-6 md:px-10">
    <div class="max-w-7xl mx-auto">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="text-blue-600 font-semibold text-xs uppercase tracking-wide bg-blue-100 px-3 py-1 rounded-full">Seamless experience</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3 text-gray-900">Simple, powerful workflow</h2>
        <p class="text-gray-500 mt-2">For trekkers & travel agencies — unified experience</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8" id="workflowSteps"></div>
    </div>
  </section>

  <section id="agencies" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="grid md:grid-cols-2 gap-12 items-center bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-8 md:p-12">
      <div>
        <span class="text-blue-700 font-semibold text-sm uppercase tracking-wider"><i class="fas fa-building"></i> For travel agencies</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-2 text-gray-900">Supercharge your trekking business</h2>
        <p class="text-gray-700 mt-4 leading-relaxed">Join Nepal's first AI-native OS that automates booking, permits, and client management. Zero commission smart contracts, digital contracts & real-time analytics.</p>
        <ul class="mt-6 space-y-3">
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Reduce manual work by 80% with AI quotations & itineraries</span></li>
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Own branded dashboard – no commission leakage</span></li>
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Access global trekker network & blockchain-ready permits</span></li>
        </ul>
        <div class="mt-8 flex items-center gap-3">
          <i class="fas fa-chart-line text-2xl text-blue-600"></i>
          <span class="text-sm font-medium text-gray-700">Trusted by early partners: Himalayan Guides, Nepal Eco Treks, & more</span>
        </div>
      </div>
      <div class="bg-white/40 backdrop-blur-sm p-6 rounded-2xl border border-white shadow-lg">
        <div class="flex justify-between items-center border-b pb-3 mb-3"><span class="font-bold"><i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Today's dashboard preview</span><span class="text-xs bg-green-100 px-2 py-0.5 rounded-full">+32% efficiency</span></div>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between"><span>📊 Active treks</span><span class="font-semibold">18</span></div>
          <div class="flex justify-between"><span>🧾 Permits issued (auto)</span><span class="font-semibold">46</span></div>
          <div class="flex justify-between"><span>📈 AI revenue forecast</span><span class="font-semibold text-green-600">+22%</span></div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-2"><div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div></div>
        <p class="text-xs text-gray-500 mt-3">80% less paperwork → agencies scale faster</p>
        <div class="mt-5 text-center text-xs text-gray-500"><i class="fas fa-lock"></i> Zero commission smart contract ready</div>
      </div>
    </div>
  </section>

  <section id="early-access" class="py-20 px-6 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-80 h-80 bg-blue-500 opacity-10 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto text-center relative z-10">
      <i class="fas fa-hiking text-4xl text-blue-300 mb-4"></i>
      <h2 class="text-3xl md:text-5xl font-extrabold">Ready to transform Nepal trekking?</h2>
      <p class="text-gray-300 text-lg mt-4 max-w-2xl mx-auto">Join the waitlist — early agencies and trekkers get 6 months free + lifetime discounted upgrades.</p>
      <form class="mt-8 flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" action="#" method="POST">
        <input type="email" required placeholder="Your email address" class="flex-1 px-5 py-3 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 border-0">
        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2"><i class="fas fa-rocket"></i> Reserve early spot</button>
      </form>
      <p class="text-gray-400 text-xs mt-4">No spam, only product updates & launch benefits. Zero commitment.</p>
    </div>
  </section>

  <footer class="bg-white border-t border-gray-200 pt-16 pb-8 px-6 md:px-10">
    <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
      <div>
        <div class="flex items-center space-x-2"><i class="fas fa-mountain text-blue-600 text-xl"></i><span class="font-bold text-xl text-gray-800">TravelAI Nepal</span></div>
        <p class="text-sm text-gray-500 mt-3">AI + data-driven trekking ecosystem. Built for Nepal, by passion.</p>
        <div class="flex space-x-4 mt-4"><i class="fab fa-twitter text-gray-400 hover:text-blue-500"></i><i class="fab fa-instagram text-gray-400 hover:text-pink-500"></i><i class="fab fa-github text-gray-400 hover:text-gray-800"></i></div>
      </div>
      <div><h4 class="font-bold text-gray-800">Product</h4><ul class="mt-3 space-y-2 text-sm text-gray-500"><li><a href="#features" class="hover:text-blue-600">Features</a></li><li><a href="#" class="hover:text-blue-600">Agency SaaS</a></li><li><a href="#" class="hover:text-blue-600">Mobile App (PWA)</a></li><li><a href="#" class="hover:text-blue-600">Pricing</a></li></ul></div>
      <div><h4 class="font-bold text-gray-800">Company</h4><ul class="mt-3 space-y-2 text-sm text-gray-500"><li><a href="#" class="hover:text-blue-600">About Nepal Trek</a></li><li><a href="#" class="hover:text-blue-600">Careers</a></li><li><a href="#" class="hover:text-blue-600">Press</a></li><li><a href="#" class="hover:text-blue-600">Contact us</a></li></ul></div>
      <div><h4 class="font-bold text-gray-800">Legal</h4><ul class="mt-3 space-y-2 text-sm text-gray-500"><li><a href="#" class="hover:text-blue-600">Privacy policy</a></li><li><a href="#" class="hover:text-blue-600">Terms of service</a></li><li><a href="#" class="hover:text-blue-600">GDPR & data safety</a></li></ul></div>
    </div>
    <div class="border-t border-gray-200 mt-12 pt-6 text-center text-xs text-gray-400">
      © <script>document.write(new Date().getFullYear())</script> TravelAI Nepal — Redefining Himalayan adventures with AI & open tech. 🇳🇵 Made in Nepal for the world.
    </div>
  </footer>

  <!-- Core Static Data & Rendering (unchanged) -->
  <script>
    const siteData = {
      features: [
        { icon: "fas fa-robot", gradient: "from-blue-500 to-indigo-600", title: "AI Trip Planner", desc: "Generate full itineraries based on budget, days, fitness & altitude. Hybrid LLM (Groq/Llama3) for smart solo/group recs.", tags: ["Smart recommendations", "Dynamic pricing"] },
        { icon: "fas fa-qrcode", gradient: "from-emerald-500 to-teal-600", title: "Digital Trek Passport", desc: "QR code check-in/out at checkposts. Real-time location visibility for agencies & rescue teams. Privacy-first design.", tags: [] },
        { icon: "fas fa-sos", gradient: "from-red-500 to-rose-600", title: "Offline Emergency SOS", desc: "No network? No problem. SOS alerts store location locally and auto-sync when signal returns. One tap to alert agencies & guides.", tags: [], extra: "Response within 5 min" },
        { icon: "fas fa-chart-line", gradient: "from-purple-500 to-pink-600", title: "Agency Dashboard", desc: "Bookings, permits, guide assignment, AI quotations and analytics — all in one place. Reduce manual work by 80%.", tags: [] },
        { icon: "fas fa-film", gradient: "from-amber-500 to-orange-600", title: "Trek Memory Replay", desc: "After your trek, AI generates a cinematic route replay with photo timeline. Share on social media — viral growth engine.", tags: [] },
        { icon: "fas fa-link", gradient: "from-slate-600 to-gray-800", title: "Smart Permits (Blockchain)", desc: "Blockchain-ready digital permits for TIMS & Conservation. Reduce corruption and delays — transparent & fast.", tags: [], extraBadge: "Coming 2025", extraIcon: "fas fa-cube", extraText: "Immutable & instant" }
      ],
      extraBadges: [
        { icon: "fas fa-mobile-alt", text: "PWA + Offline first" },
        { icon: "fas fa-chart-simple", text: "Real-time safety score" },
        { icon: "fas fa-language", text: "Multi-lingual (Nepali/English)" }
      ],
      workflowSteps: [
        { step: 1, icon: "fas fa-brain", title: "AI generates your trek", desc: "Answer a few questions about budget, duration and interests — get a custom itinerary." },
        { step: 2, icon: "fas fa-qrcode", title: "Digital QR Passport", desc: "Check-in at trailheads, share live status with your agency & family (offline-capable)." },
        { step: 3, icon: "fas fa-shield-heart", title: "Safe trek + memories", desc: "Use SOS if needed, then replay your journey with AI-generated highlight reel." }
      ]
    };
    function renderFeatures() {
      document.getElementById("featuresGrid").innerHTML = siteData.features.map(f => `
        <div class="glass-card rounded-2xl p-6 shadow-sm bg-white relative">
          ${f.extraBadge ? `<div class="absolute top-3 right-3 bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full">${f.extraBadge}</div>` : ''}
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${f.gradient} flex items-center justify-center mb-4"><i class="${f.icon} text-white text-xl"></i></div>
          <h3 class="text-xl font-bold text-gray-800">${f.title}</h3>
          <p class="text-gray-500 text-sm mt-1">${f.desc}</p>
          ${f.tags ? `<div class="mt-3 flex gap-2">${f.tags.map(t => `<span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full">${t}</span>`).join('')}</div>` : ''}
          ${f.extra ? `<div class="mt-3 flex items-center gap-1 text-xs text-green-600"><i class="fas fa-clock"></i> ${f.extra}</div>` : ''}
          ${f.extraIcon ? `<div class="mt-2 text-xs text-gray-500"><i class="${f.extraIcon}"></i> ${f.extraText}</div>` : ''}
        </div>
      `).join('');
    }
    function renderExtraBadges() {
      document.getElementById("extraBadges").innerHTML = siteData.extraBadges.map(b => `<div class="bg-gray-50 p-3 rounded-xl flex flex-col items-center"><i class="${b.icon} text-blue-500 text-lg mb-1"></i><span>${b.text}</span></div>`).join('');
    }
    function renderWorkflow() {
      document.getElementById("workflowSteps").innerHTML = siteData.workflowSteps.map(s => `
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center step-card transition-all">
          <div class="w-16 h-16 bg-blue-50 text-blue-700 rounded-2xl flex items-center justify-center text-2xl font-black mx-auto mb-5">${s.step}</div>
          <i class="${s.icon} text-3xl text-blue-500 mb-3"></i>
          <h3 class="text-xl font-bold">${s.title}</h3>
          <p class="text-gray-500 mt-2">${s.desc}</p>
        </div>
      `).join('');
    }
    function initForm() {
      document.querySelector('#early-access form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('✨ Thanks! You\'re on the waitlist. We\'ll notify you at launch.');
      });
    }
    function initSmoothScroll() {
      document.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
      }));
    }
    renderFeatures();
    renderExtraBadges();
    renderWorkflow();
    initForm();
    initSmoothScroll();
  </script>

  <!-- Enhanced AI Itinerary Script with Markdown & Download -->
  <script>
    // Improved Markdown to HTML converter for the itinerary
    function formatMarkdown(text) {
      if (!text) return '';
      let html = text;
      // Headings: # Title (only if line starts with #)
      html = html.replace(/^# (.*?)$/gm, '<h3>$1</h3>');
      // Bold text: **bold**
      html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      // Italic: *italic* (but not list items)
      html = html.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
      // Unordered list items: lines starting with "* " or "- "
      html = html.replace(/^[\*\-] (.*?)$/gm, '<li>$1</li>');
      // Wrap consecutive <li> tags into <ul>
      html = html.replace(/(<li>.*?<\/li>\n?)+/g, '<ul class="list-disc ml-5 my-2">$&</ul>');
      // Convert newlines to <br> except those already inside HTML tags
      html = html.replace(/\n/g, '<br>');
      // Cleanup multiple <br> after lists
      html = html.replace(/<\/ul><br>/g, '</ul>');
      return html;
    }

    // Download itinerary as plain text
    function downloadItinerary() {
      const rawText = document.getElementById('itineraryResult').innerText;
      const blob = new Blob([rawText], {type: 'text/plain'});
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'travel_itinerary.txt';
      a.click();
      URL.revokeObjectURL(a.href);
    }

    // Copy itinerary to clipboard
    function copyItinerary() {
      const rawText = document.getElementById('itineraryResult').innerText;
      navigator.clipboard.writeText(rawText).then(() => {
        alert('Itinerary copied to clipboard!');
      }).catch(() => {
        alert('Failed to copy.');
      });
    }

    // Form submission
    document.getElementById('itineraryForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = document.getElementById('generateBtn');
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
      btn.disabled = true;

      try {
        const formData = new FormData(this);
        const response = await fetch('/api/itinerary/generate', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          const formatted = formatMarkdown(data.itinerary);
          document.getElementById('itineraryResult').innerHTML = formatted;
          document.getElementById('result').classList.remove('hidden');
        } else {
          alert(data.message || 'Something went wrong. Please try again.');
        }
      } catch (error) {
        console.error(error);
        alert('Server error. Please try again later.');
      } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>
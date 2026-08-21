@extends('layouts.public')

@section('title', 'Plan Entire Nepal Journey')

@section('content')
  <!-- Hero Section -->
  <section id="home" class="hero-bg relative overflow-hidden pt-4 md:pt-8 pb-12 md:pb-16">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div>
          <h1 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight text-gray-900">
            Plan Entire <span class="text-blue-600">Nepal Journey</span>
          </h1>
          <p class="text-gray-600 text-lg md:text-xl mt-6 max-w-xl leading-relaxed">
            One ecosystem connecting travelers, agencies & local partners — AI itineraries, offline friendly, trusted local support.
          </p>
          <div class="flex flex-wrap gap-4 mt-8">
            <a href="{{ route('public.services.index') }}" class="bg-gray-900 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-xl flex items-center gap-2"><i class="fas fa-map-marked-alt"></i> Explore Trips</a>
            <a href="{{ route('register') }}" class="border border-gray-300 hover:border-blue-400 bg-white text-gray-800 font-semibold px-6 py-3 rounded-xl transition-all hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-handshake"></i> Become Partner</a>
          </div>
          <div class="flex flex-wrap gap-6 mt-10 text-sm text-gray-500">
            <div class="flex items-center gap-1"><i class="fas fa-check-circle text-green-500"></i> No hidden fees</div>
            <div class="flex items-center gap-1"><i class="fas fa-shield-alt text-blue-500"></i> Real-time safety</div>
            <div class="flex items-center gap-1"><i class="fas fa-headset text-purple-500"></i> 24/7 local support</div>
          </div>
        </div>

        <!-- Recent Check‑ins कार्ड -->
        <div class="relative flex justify-center">
          <div class="w-full max-w-md bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-5 shadow-2xl border border-white/40 backdrop-blur-sm">
            <div class="bg-white/80 rounded-2xl p-4 shadow-inner overflow-hidden relative">
              <div class="flex justify-between items-center border-b pb-2 mb-3">
                <span class="font-bold text-gray-700"><i class="fas fa-history text-blue-500 mr-2"></i> Recent Check‑ins</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full" id="checkin-timer">Live</span>
              </div>
              <div id="checkin-slider" class="h-40 relative flex flex-col justify-center transition-all duration-500 bg-cover bg-center bg-no-repeat rounded-lg"></div>
              <div class="flex justify-center mt-3 space-x-1" id="checkin-dots"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- AI Travel Planner सेक्सन -->
   
<div class="max-w-4xl mx-auto px-4 my-12">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2"><i class="fas fa-robot"></i> AI Travel Planner</h2>
            <p class="text-blue-100 text-sm">Generate a personalized day-by-day itinerary powered by advanced AI</p>
        </div>
        <div class="p-6">
            <form id="itineraryForm" class="space-y-5">
                @csrf
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Destination *</label>
<input type="text" 
       name="destination" 
       id="destination" 
       list="routeList" 
       required 
       placeholder="e.g., Annapurna Base Camp, Pokhara, Paragliding..." 
       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
<datalist id="routeList">
    @foreach($routes as $route)
        <option value="{{ $route->name }}"></option>
    @endforeach
</datalist>
<p class="text-xs text-gray-400 mt-1">Type to search from {{ $routes->count() }}+ destinations</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Number of Days *</label>
                        <input type="number" name="days" id="days" min="1" max="30" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Budget (USD) *</label>
                        <input type="number" name="budget" id="budget" min="100" required placeholder="e.g., 1500" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Travel Style *</label>
                        <select name="travel_style" id="travel_style" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            <option value="budget">Budget Travel</option>
                            <option value="mid_range" selected>Mid-Range</option>
                            <option value="luxury">Luxury</option>
                            <option value="backpacker">Backpacker</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Your Interests (optional)</label>
                    <textarea name="interests" id="interests" rows="2" placeholder="e.g., adventure, culture, nature, food, photography" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Fitness Level (optional)</label>
                    <select name="fitness_level" id="fitness_level" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="easy">Easy</option>
                        <option value="moderate" selected>Moderate</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <button type="submit" id="generateBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2 shadow-md">
                    <i class="fas fa-magic"></i> Generate Itinerary
                </button>
                <div id="costPreview" class="mt-4 text-center text-lg font-semibold text-gray-700"></div>
            </form>
            <div id="result" class="mt-8 hidden">
                <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-map-marked-alt text-blue-600"></i> Your Personalized Itinerary</h3>
                    <div class="flex gap-2">
                        <button onclick="downloadItinerary()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                            <i class="fas fa-download"></i> Download TXT
                        </button>
                        <button onclick="copyItinerary()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div id="itineraryResult" class="bg-gray-50 p-5 rounded-xl border border-gray-200 text-gray-800 max-h-[600px] overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

  <!-- Stats Banner -->
  <div class="bg-gray-50 border-y border-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-6 flex flex-wrap justify-around gap-6 text-center">
      @foreach($stats as $stat)
      <div><span class="text-3xl font-black text-blue-600">{{ $stat['value'] }}</span><p class="text-xs text-gray-500">{{ $stat['label'] }}</p></div>
      @endforeach
    </div>
  </div>

    {{-- Featured Services सेक्सन --}}
<section id="services" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
        <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">Explore Nepal</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">Popular Treks, Tours & Hotels</h2>
        <p class="text-gray-500 mt-3">Handpicked adventures by local providers – from Everest Base Camp to hidden valleys.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($featuredServices as $service)
        @php
            $exchangeRate = 140;
            $priceNPR = $service->price * $exchangeRate;
        @endphp
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                @if($service->cover_image)
                    <img src="{{ asset('storage/' . $service->cover_image) }}" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-mountain text-5xl text-white/80"></i>
                @endif
            </div>
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-800">{{ $service->name }}</h3>
                    @php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $service->currency ?? 'USD';
    $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
@endphp
<div class="text-right">
    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full block">
        {{ $formattedPrice }}
    </span>
</div>
                </div>
                <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
                    <span><i class="far fa-calendar-alt"></i> {{ $service->trekDetail->duration_days ?? 'N/A' }} days</span>
                    @if($service->trekDetail)
                        <span><i class="fas fa-chart-line"></i> {{ ucfirst($service->trekDetail->difficulty) }}</span>
                    @endif
                    <span><i class="fas fa-tag"></i> {{ $service->category->name ?? 'N/A' }}</span>
                </div>
                <p class="text-gray-600 text-sm mt-3">{{ $service->provider->name ?? 'TravelAI Partner' }}</p>
                <a href="{{ route('public.services.show', $service->slug) }}" 
                   class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">
                    View Details →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-10 text-gray-500">No services available yet. Check back soon!</div>
        @endforelse
    </div>
    <div class="text-center mt-12">
        <a href="{{ route('public.services.index') }}" 
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition shadow-md hover:shadow-lg">
            View All Services →
        </a>
    </div>
</section>

  {{-- ========== PRICING CTA SECTION ========== --}}
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
        <h2 class="text-3xl md:text-4xl font-bold mb-3">Ready to Scale Your Business?</h2>
        <p class="text-blue-100 text-lg max-w-2xl mx-auto">Choose a plan that fits your needs. Start free, upgrade anytime.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="{{ route('pages.pricing') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                View Pricing →
            </a>
            <a href="{{ route('register') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                Get Started Free
            </a>
        </div>
    </div>
</div>

  <!-- फिचर ग्रिड (Core intelligence) -->
  <section id="features" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">Core intelligence</span>
      <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">Everything you need for a smarter trek</h2>
      <p class="text-gray-500 mt-3">From AI travel planner to offline emergency SOS — built for Nepal's terrain and modern travellers.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7" id="featuresGrid"></div>
    <!-- Extra badges -->
    <div class="grid md:grid-cols-3 gap-5 mt-10 text-center text-sm text-gray-700" id="extraBadges"></div>
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
        <ul class="mt-6 space-y-3"><li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Reduce manual work by 80% with AI quotations & itineraries</span></li><li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Own branded dashboard – no commission leakage</span></li><li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>Access global trekker network & blockchain-ready permits</span></li></ul>
        <div class="mt-8 flex items-center gap-3"><i class="fas fa-chart-line text-2xl text-blue-600"></i><span class="text-sm font-medium text-gray-700">Trusted by early partners: Himalayan Guides, Nepal Eco Treks, & more</span></div>
      </div>
      <div class="bg-white/40 backdrop-blur-sm p-6 rounded-2xl border border-white shadow-lg">
        <div class="flex justify-between items-center border-b pb-3 mb-3"><span class="font-bold"><i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Today's dashboard preview</span><span class="text-xs bg-green-100 px-2 py-0.5 rounded-full">+32% efficiency</span></div>
        <div class="space-y-3 text-sm"><div class="flex justify-between"><span>📊 Active treks</span><span class="font-semibold">18</span></div><div class="flex justify-between"><span>🧾 Permits issued (auto)</span><span class="font-semibold">46</span></div><div class="flex justify-between"><span>📈 AI revenue forecast</span><span class="font-semibold text-green-600">+22%</span></div></div>
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
      <form class="mt-8 flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" action="#"><input type="email" required placeholder="Your email address" class="flex-1 px-5 py-3 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 border-0"><button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2"><i class="fas fa-rocket"></i> Reserve early spot</button></form>
      <p class="text-gray-400 text-xs mt-4">No spam, only product updates & launch benefits. Zero commitment.</p>
    </div>
  </section>

  <!-- All JavaScript blocks (rendering, AI itinerary, check-ins) remain inside content -->
  <script>
    const siteData = {
      features: [
        { icon: "fas fa-robot", gradient: "from-blue-500 to-indigo-600", title: "AI Trip Planner", desc: "Generate full itineraries based on budget, days, fitness & altitude. Hybrid LLM (Groq/Llama3) for smart solo/group recs.", tags: ["Smart recommendations", "Dynamic pricing"] },
        { icon: "fas fa-qrcode", gradient: "from-emerald-500 to-teal-600", title: "Digital Trek Passport", desc: "QR code check-in/out at checkposts. Real-time location visibility for agencies & rescue teams. Privacy-first design.", tags: [] },
        { icon: "fas fa-sos", gradient: "from-red-500 to-rose-600", title: "Offline Emergency SOS", desc: "No network? No problem. SOS alerts store location locally and auto-sync when signal returns. One tap to alert agencies & guides.", tags: [], extra: "Response within 5 min" },
        { icon: "fas fa-chart-line", gradient: "from-purple-500 to-pink-600", title: "Agency Dashboard", desc: "Bookings, permits, guide assignment, AI quotations and analytics — all in one place. Reduce manual work by 80%.", tags: [] },
        { icon: "fas fa-film", gradient: "from-amber-500 to-orange-600", title: "Trek Memory Replay", desc: "After your trek, AI generates a cinematic route replay with photo timeline. Share on social media — viral growth engine.", tags: [] },
        { icon: "fas fa-link", gradient: "from-slate-600 to-gray-800", title: "Smart Permits (Blockchain)", desc: "Blockchain-ready digital permits for TIMS & Conservation. Reduce corruption and delays — transparent & fast.", tags: [], extraBadge: "Coming 2026", extraIcon: "fas fa-cube", extraText: "Immutable & instant" }
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
      document.getElementById("extraBadges").innerHTML = siteData.extraBadges.map(b => `
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 flex flex-col items-center transition hover:shadow-lg">
          <i class="${b.icon} text-blue-500 text-2xl mb-2"></i>
          <span class="text-gray-800 font-medium">${b.text}</span>
        </div>
      `).join('');
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

  <!-- AI Itinerary JavaScript (Updated) -->
<script>
    // Format JSON itinerary to readable HTML with total cost
    function renderItinerary(days, totalCost, currency) {
        if (!days || days.length === 0) return '<p class="text-gray-500">No itinerary generated.</p>';

        let html = '';

        // Display total cost if available
        if (totalCost !== null && totalCost !== undefined && totalCost > 0) {
            html += `<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">`;
            html += `<p class="text-lg font-bold text-blue-800">💰 Estimated Total Cost: <span class="text-2xl">${currency || 'NPR'} ${totalCost.toLocaleString()}</span></p>`;
            html += `<p class="text-xs text-gray-500 mt-1">* Based on current route cost estimates; optional services/activities are not included.</p>`;
            html += `</div>`;
        }

        // ---- Existing days rendering ----
        days.forEach(day => {
            html += `<div class="mb-6 border-b border-gray-200 pb-4 last:border-0">`;
            html += `<h3 class="text-lg font-bold text-blue-700">Day ${day.day_number}: ${day.title}</h3>`;
            html += `<p class="text-gray-600 text-sm mt-1">${day.description || ''}</p>`;
            if (day.distance_km) html += `<p class="text-xs text-gray-400 mt-1">📏 ${day.distance_km} km  |  ⛰️ ${day.altitude_m || 'N/A'} m</p>`;
            if (day.items && day.items.length > 0) {
                html += `<ul class="list-disc ml-5 mt-2 space-y-1">`;
                day.items.forEach(item => {
                    html += `<li class="text-sm text-gray-700"><span class="font-medium">${item.title}</span> – ${item.description || ''}`;
                    if (item.cost) html += ` <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">${currency || 'NPR'} ${item.cost}</span>`;
                    html += `</li>`;
                });
                html += `</ul>`;
            }
            html += `</div>`;
        });

        return html;
    }

    document.getElementById('itineraryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('generateBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        try {
            // Build payload
            const interestsRaw = document.getElementById('interests').value;
            const interests = interestsRaw ? interestsRaw.split(',').map(s => s.trim()).filter(Boolean) : [];

            const payload = {
                destination: document.getElementById('destination').value,
                days: parseInt(document.getElementById('days').value),
                budget: parseFloat(document.getElementById('budget').value),
                travel_style: document.getElementById('travel_style').value,
                interests: interests,
                fitness_level: document.getElementById('fitness_level').value,
            };

            const response = await fetch('/api/planner/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            // ✅ Updated: Pass total_cost and currency
            if (data.success && data.data && data.data.days) {
                document.getElementById('itineraryResult').innerHTML = renderItinerary(
                    data.data.days,
                    data.data.total_cost,
                    data.data.currency || 'NPR'
                );
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

    // Download as TXT (plain text)
    function downloadItinerary() {
        const container = document.getElementById('itineraryResult');
        const text = container.innerText || container.textContent;
        const blob = new Blob([text], {type: 'text/plain'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'travel_itinerary.txt';
        a.click();
        URL.revokeObjectURL(a.href);
    }

    // Copy to clipboard
    function copyItinerary() {
        const container = document.getElementById('itineraryResult');
        const text = container.innerText || container.textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Itinerary copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy. Please select and copy manually.');
        });
    }
</script>


  <!-- रिसेन्ट चेक-इन जेएस (Anonymous सहित) -->
  <script>
    const checkins = @json($recentCheckins);
    const anonymizedCheckins = checkins.map(item => ({ ...item, trekker_name: 'Anonymous' }));
    let currentIndex = 0, interval;
    function renderCheckin(index) {
      const item = anonymizedCheckins[index];
      if (!item) return;
      const html = `<div class="text-center relative z-10"><div class="text-sm font-semibold text-white drop-shadow-md">📍 ${item.checkpoint}</div><div class="text-xs text-white drop-shadow-sm mt-1">🏔️ ${item.trek_name}</div><div class="text-xs text-white drop-shadow-sm">🏢 ${item.agency_name}</div><div class="text-xs text-white drop-shadow-sm">👤 ${item.trekker_name}</div><div class="text-xs text-white/80 drop-shadow-sm mt-2">🕒 ${item.time_ago}</div></div>`;
      const slider = document.getElementById('checkin-slider');
      slider.innerHTML = html;
      const bgImage = item.cover_image ? item.cover_image : null;
      if (bgImage) slider.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(${bgImage})`;
      else slider.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset("images/default-mountain.jpg") }}')`;
      slider.style.backgroundSize = 'cover';
      slider.style.backgroundPosition = 'center';
      updateDots(index);
    }
    function updateDots(activeIndex) {
      const dotsContainer = document.getElementById('checkin-dots');
      if (!dotsContainer) return;
      let dotsHtml = '';
      for (let i = 0; i < anonymizedCheckins.length; i++) dotsHtml += `<div class="w-2 h-2 rounded-full mx-1 transition-all ${i === activeIndex ? 'bg-blue-600 w-3' : 'bg-gray-300'}"></div>`;
      dotsContainer.innerHTML = dotsHtml;
    }
    function startRotation() { if (anonymizedCheckins.length <= 1) return; interval = setInterval(() => { currentIndex = (currentIndex + 1) % anonymizedCheckins.length; renderCheckin(currentIndex); }, 4500); }
    function stopRotation() { if (interval) clearInterval(interval); }
    const sliderContainer = document.getElementById('checkin-slider')?.parentElement?.parentElement;
    if (sliderContainer) { sliderContainer.addEventListener('mouseenter', stopRotation); sliderContainer.addEventListener('mouseleave', startRotation); }
    if (anonymizedCheckins.length > 0) { renderCheckin(0); startRotation(); }
    else { const slider = document.getElementById('checkin-slider'); slider.innerHTML = '<div class="text-center text-gray-500">No check‑ins yet</div>'; slider.style.backgroundImage = ''; }
  </script>
    <!-- ✅ Auto-fill + Real-time Cost Preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const destinationSelect = document.getElementById('destination');
    const daysInput = document.getElementById('days');
    const budgetInput = document.getElementById('budget');
    const styleSelect = document.getElementById('travel_style');
    const costPreview = document.getElementById('costPreview');

    function estimateCost() {
    const destination = destinationSelect.value;
    const days = parseInt(daysInput.value) || 0;
    const style = styleSelect.value;

    if (!destination || days === 0) {
        costPreview.innerHTML = '';
        return;
    }

    // Compute rough estimate using route name matching
    const estNpr = estimateBudgetFromName(destination, days);
    if (estNpr === 0) {
        costPreview.innerHTML = '';
        return;
    }

    const styleMultiplier = { budget: 0.8, mid_range: 1.0, luxury: 1.5, backpacker: 0.9 };
    const totalNpr = Math.round(estNpr * (styleMultiplier[style] || 1.0));
    const usdTotal = Math.round(totalNpr * 0.0075);

    costPreview.innerHTML = `💰 Estimated Total: <span class="font-bold text-blue-600">~$${usdTotal} USD</span>`;
}

function estimateBudgetFromName(name, days) {
    // Map route names to estimates
    const estimates = {
        'Annapurna Base Camp': { permits: 5000, foodPerDay: 2500, transport: 1000 },
        'Everest Base Camp': { permits: 5000, foodPerDay: 3000, transport: 0 },
        'Langtang Valley': { permits: 5000, foodPerDay: 2500, transport: 1500 },
        'Ghorepani Poon Hill': { permits: 3000, foodPerDay: 2000, transport: 1000 },
        'Annapurna Circuit': { permits: 5000, foodPerDay: 2500, transport: 2000 },
        'Mardi Himal': { permits: 3000, foodPerDay: 2200, transport: 500 },
        'Manaslu Circuit': { permits: 8000, foodPerDay: 3000, transport: 1000 },
        'Gokyo Lakes': { permits: 5000, foodPerDay: 3500, transport: 0 },
        'Three Passes Trek': { permits: 5000, foodPerDay: 3500, transport: 0 },
        'Upper Mustang': { permits: 8000, foodPerDay: 3000, transport: 1000 },
        'Lower Mustang': { permits: 3000, foodPerDay: 2500, transport: 500 },
        'Rara Lake': { permits: 3000, foodPerDay: 2200, transport: 500 },
        'Dolpo Circuit': { permits: 8000, foodPerDay: 3500, transport: 1000 },
        'Phoksundo Lake': { permits: 3000, foodPerDay: 2500, transport: 500 },
        'Kanchenjunga Circuit': { permits: 8000, foodPerDay: 3500, transport: 1000 },
        'Makalu Base Camp': { permits: 8000, foodPerDay: 3500, transport: 1000 },
        'Dhaulagiri Circuit': { permits: 8000, foodPerDay: 3500, transport: 1000 },
        'Kathmandu City Tour': { permits: 0, foodPerDay: 1500, transport: 50 },
        'Kathmandu Valley Heritage Tour': { permits: 0, foodPerDay: 1500, transport: 100 },
        'Bhaktapur Durbar Square Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Patan Durbar Square Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Kirtipur Village Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Lumbini Buddhist Circuit': { permits: 0, foodPerDay: 1200, transport: 50 },
        'Janakpur Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Muktinath Temple Tour': { permits: 0, foodPerDay: 2000, transport: 150 },
        'Chitwan National Park Safari': { permits: 0, foodPerDay: 1500, transport: 50 },
        'Trishuli River Rafting': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Paragliding in Pokhara': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Kusma Bungee': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Bandipur Village Tour': { permits: 0, foodPerDay: 1200, transport: 40 },
        'Tansen Hill Town Tour': { permits: 0, foodPerDay: 1200, transport: 40 },
        'Dhulikhel Scenic Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Panauti Heritage Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Namobuddha Monastery Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Kirtipur Ancient Town Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Sankhu Historical Town Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Khokana Traditional Village Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Bungamati Woodcarving Village Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Chobar Gorge & Temple Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Godavari Botanical Garden Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Pharping Monastery & Cave Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Kakani Hill Station Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Nuwakot Durbar Tour': { permits: 0, foodPerDay: 1200, transport: 40 },
        'Sindhuli Fort Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Bhedetar Hill Station Tour': { permits: 0, foodPerDay: 1200, transport: 40 },
        'Hile Tea Garden Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Dharan City Tour': { permits: 0, foodPerDay: 1000, transport: 30 },
        'Barun Valley Wilderness Tour': { permits: 0, foodPerDay: 1500, transport: 100 },
        'Simikot Remote Town Tour': { permits: 0, foodPerDay: 1500, transport: 100 },
        'Sinja Valley Historical Tour': { permits: 0, foodPerDay: 1200, transport: 50 },
        'Shey Gompa Dolpa Tour': { permits: 0, foodPerDay: 1500, transport: 100 },
    };

    // Try exact match first
    if (estimates[name]) {
        const est = estimates[name];
        return est.permits + est.transport + (est.foodPerDay * days);
    }

    // Try partial match
    const keys = Object.keys(estimates);
    for (const key of keys) {
        if (name.toLowerCase().includes(key.toLowerCase()) || key.toLowerCase().includes(name.toLowerCase())) {
            const est = estimates[key];
            return est.permits + est.transport + (est.foodPerDay * days);
        }
    }

    // Fallback: generic $50/day + $50
    return (days * 50) + 50;
}

    // Auto-fill days & budget on destination change
    function autoFill() {
    const route = destinationSelect.value;
    const routesData = @json($routes->map(function($r) {
        return ['name' => $r->name, 'duration_days' => $r->duration_days];
    }));

    const matched = routesData.find(r => r.name === route || r.name.toLowerCase() === route.toLowerCase());
    if (matched && matched.duration_days) {
        daysInput.value = matched.duration_days;
        // Budget estimate using estimateBudgetFromName
        const estUsd = Math.round(estimateBudgetFromName(matched.name, matched.duration_days) * 0.0075);
        budgetInput.value = estUsd;
        estimateCost();
    } else {
        // Fallback: 3 days, $200
        daysInput.value = 3;
        budgetInput.value = 200;
        estimateCost();
    }
}

    // Event listeners
    destinationSelect.addEventListener('change', function() {
        autoFill();
    });
    daysInput.addEventListener('input', estimateCost);
    budgetInput.addEventListener('input', estimateCost);
    styleSelect.addEventListener('change', estimateCost);

    // Trigger on load
    autoFill();
});
</script>
@endsection

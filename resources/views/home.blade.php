@extends('layouts.public')

@section('title', __('messages.plan_entire') . ' ' . __('messages.nepal_journey'))

@section('content')
  <!-- Hero Section -->
  <section id="home" class="hero-bg relative overflow-hidden pt-4 md:pt-8 pb-12 md:pb-16">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div>
          <h1 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight text-gray-900">
            {{ __('messages.plan_entire') }} <span class="text-blue-600">{{ __('messages.nepal_journey') }}</span>
          </h1>
          <p class="text-gray-600 text-lg md:text-xl mt-6 max-w-xl leading-relaxed">
            {{ __('messages.home_subtitle') }}
          </p>
          <div class="flex flex-wrap gap-4 mt-8">
            <a href="{{ route('public.services.index') }}" class="bg-gray-900 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-xl flex items-center gap-2"><i class="fas fa-map-marked-alt"></i> {{ __('messages.explore_trips') }}</a>
            <a href="{{ route('register') }}" class="border border-gray-300 hover:border-blue-400 bg-white text-gray-800 font-semibold px-6 py-3 rounded-xl transition-all hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-handshake"></i> {{ __('messages.become_partner_btn') }}</a>
          </div>
          <div class="flex flex-wrap gap-6 mt-10 text-sm text-gray-500">
            <div class="flex items-center gap-1"><i class="fas fa-check-circle text-green-500"></i> {{ __('messages.no_hidden_fees') }}</div>
            <a href="{{ route('safety.index') }}" 
   class="flex items-center gap-1 hover:underline hover:opacity-80 transition-all duration-200">
    <i class="fas fa-shield-alt text-blue-500"></i>
    {{ __('messages.realtime_safety') }}
    <span class="text-xs text-gray-400 mx-1">·</span>
    <span class="text-xs text-blue-600 font-medium hover:underline">
        {{ __('messages.view_status') }}
    </span>
</a>
            <div class="flex items-center gap-1"><i class="fas fa-headset text-purple-500"></i> {{ __('messages.local_support_24_7') }}</div>
          </div>
        </div>

        <!-- Recent Check‑ins कार्ड -->
        <div class="relative flex justify-center">
          <div class="w-full max-w-md bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-5 shadow-2xl border border-white/40 backdrop-blur-sm">
            <div class="bg-white/80 rounded-2xl p-4 shadow-inner overflow-hidden relative">
              <div class="flex justify-between items-center border-b pb-2 mb-3">
                <span class="font-bold text-gray-700"><i class="fas fa-history text-blue-500 mr-2"></i> {{ __('messages.recent_checkins') }}</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full" id="checkin-timer">{{ __('messages.live') }}</span>
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
        <h2 class="text-2xl font-bold text-white flex items-center gap-2"><i class="fas fa-robot"></i> {{ __('messages.ai_travel_planner') }}</h2>
        <p class="text-blue-100 text-sm">{{ __('messages.ai_travel_planner_desc') }}</p>
      </div>
      <div class="p-6">
        <form id="itineraryForm" class="space-y-5">
          @csrf
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.destination') }} *</label>
              <input type="text" 
                     name="destination" 
                     id="destination" 
                     list="routeList" 
                     required 
                     placeholder="{{ __('messages.destination_placeholder') }}" 
                     class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
              <datalist id="routeList">
                @foreach($routes as $route)
                  <option value="{{ $route->name }}"></option>
                @endforeach
              </datalist>
              <p class="text-xs text-gray-400 mt-1">{{ __('messages.destination_hint', ['count' => $routes->count()]) }}</p>
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.number_of_days') }} *</label>
              <input type="number" name="days" id="days" min="1" max="30" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.budget_usd') }} *</label>
              <input type="number" name="budget" id="budget" min="100" required placeholder="{{ __('messages.budget_placeholder') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
              <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.travel_style') }} *</label>
              <select name="travel_style" id="travel_style" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="budget">{{ __('messages.budget_travel') }}</option>
                <option value="mid_range" selected>{{ __('messages.mid_range') }}</option>
                <option value="luxury">{{ __('messages.luxury') }}</option>
                <option value="backpacker">{{ __('messages.backpacker') }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.your_interests') }}</label>
            <textarea name="interests" id="interests" rows="2" placeholder="{{ __('messages.interests_placeholder') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
          </div>
          <div>
            <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.fitness_level') }}</label>
            <select name="fitness_level" id="fitness_level" class="w-full border border-gray-300 rounded-lg px-4 py-2">
              <option value="easy">{{ __('messages.easy') }}</option>
              <option value="moderate" selected>{{ __('messages.moderate') }}</option>
              <option value="hard">{{ __('messages.hard') }}</option>
            </select>
          </div>
          <button type="submit" id="generateBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2 shadow-md">
            <i class="fas fa-magic"></i> {{ __('messages.generate_itinerary') }}
          </button>
        </form>
        <div id="result" class="mt-8 hidden">
          <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-map-marked-alt text-blue-600"></i> {{ __('messages.your_personalized_itinerary') }}</h3>
            <div class="flex gap-2">
              <button onclick="downloadItinerary()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                <i class="fas fa-download"></i> {{ __('messages.download_txt') }}
              </button>
              <button onclick="copyItinerary()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                <i class="fas fa-copy"></i> {{ __('messages.copy') }}
              </button>
            </div>
          </div>
          <div id="itineraryResult" class="bg-gray-50 p-5 rounded-xl border border-gray-200 text-gray-800 max-h-[600px] overflow-y-auto"></div>
        </div>
      </div>
    </div>
  </div>

    <!-- Quotation Request Modal -->
  <div id="quotationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
      <h3 class="text-xl font-bold mb-4">Request Quotation</h3>
      <p class="text-sm text-gray-600 mb-4">Select a provider to send your itinerary for a quotation.</p>
      <form id="quotationRequestForm">
        <input type="hidden" name="planner_result_id" id="plannerResultId">
        <div class="mb-4">
          <label class="block font-medium mb-1">Select Provider</label>
          <select name="provider_id" id="providerSelect" class="w-full border rounded-lg px-4 py-2" required>
            <option value="">Loading providers...</option>
          </select>
        </div>

        <div class="mb-4">
    <label class="block font-medium mb-1">Your Full Name *</label>
    <input type="text" name="traveler_name" id="travelerName" 
           class="w-full border rounded-lg px-4 py-2" required 
           placeholder="Enter your full name">
</div>

<div class="mb-4">
    <label class="block font-medium mb-1">Your Email *</label>
    <input type="email" name="traveler_email" id="travelerEmail" 
           class="w-full border rounded-lg px-4 py-2" required 
           placeholder="your@email.com">
</div>

<div class="mb-4">
    <label class="block font-medium mb-1">Your Phone (optional)</label>
    <input type="text" name="traveler_phone" id="travelerPhone" 
           class="w-full border rounded-lg px-4 py-2" 
           placeholder="+977 98XXXXXXXX">
</div>

        <div class="mb-4">
          <label class="block font-medium mb-1">Message (optional)</label>
          <textarea name="message" id="quotationMessage" rows="3" class="w-full border rounded-lg px-4 py-2" placeholder="Any special requests..."></textarea>
        </div>
        <div class="flex gap-3">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">Send Request</button>
          <button type="button" onclick="closeQuotationModal()" class="bg-gray-300 hover:bg-gray-400 px-6 py-2 rounded-lg">Cancel</button>
        </div>
      </form>
      <div id="quotationRequestStatus" class="mt-3 hidden text-center"></div>
    </div>
  </div>

  <!-- Stats Banner -->
<div class="bg-gray-50 border-y border-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-6 flex flex-wrap justify-around gap-6 text-center">
      @foreach($stats as $stat)
      <div>
        <span class="text-3xl font-black text-blue-600">{{ $stat['value'] }}</span>
        <p class="text-xs text-gray-500">
          @if($stat['label'] == 'Tourism Services')
            {{ __('messages.tourism_services') }}
          @elseif($stat['label'] == 'Trusted Providers')
            {{ __('messages.trusted_providers') }}
          @elseif($stat['label'] == 'Happy Travelers')
            {{ __('messages.happy_travelers') }}
          @elseif($stat['label'] == 'Zero Commission')
            {{ __('messages.zero_commission') }}
          @elseif($stat['label'] == 'Smart Contracts Ready')
            {{ __('messages.smart_contracts_ready') }}
          @else
            {{ $stat['label'] }}
          @endif
        </p>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Featured Services सेक्सन --}}
  <section id="services" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">{{ __('messages.explore_nepal') }}</span>
      <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">{{ __('messages.popular_treks_tours_hotels') }}</h2>
      <p class="text-gray-500 mt-3">{{ __('messages.handpicked_adventures') }}</p>
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
            <span><i class="far fa-calendar-alt"></i> {{ $service->trekDetail->duration_days ?? __('messages.na') }} {{ __('messages.days') }}</span>
            @if($service->trekDetail)
              <span><i class="fas fa-chart-line"></i> {{ ucfirst($service->trekDetail->difficulty) }}</span>
            @endif
            <span><i class="fas fa-tag"></i> {{ $service->category->name ?? __('messages.na') }}</span>
          </div>
          <p class="text-gray-600 text-sm mt-3">{{ $service->provider->name ?? 'TravelAI Partner' }}</p>
          <a href="{{ route('public.services.show', $service->slug) }}" 
             class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">
            {{ __('messages.view_details') }} →
          </a>
        </div>
      </div>
      @empty
      <div class="col-span-full text-center py-10 text-gray-500">{{ __('messages.no_services_available') }}</div>
      @endforelse
    </div>
    <div class="text-center mt-12">
      <a href="{{ route('public.services.index') }}" 
         class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition shadow-md hover:shadow-lg">
        {{ __('messages.view_all_services') }} →
      </a>
    </div>
  </section>

  {{-- ========== PRICING CTA SECTION ========== --}}
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl">
      <h2 class="text-3xl md:text-4xl font-bold mb-3">{{ __('messages.ready_to_scale') }}</h2>
      <p class="text-blue-100 text-lg max-w-2xl mx-auto">{{ __('messages.choose_plan_text') }}</p>
      <div class="flex flex-wrap justify-center gap-4 mt-6">
        <a href="{{ route('pages.pricing') }}" class="bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
          {{ __('messages.view_pricing') }} →
        </a>
        <a href="{{ route('register') }}" class="bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
          {{ __('messages.get_started_free') }}
        </a>
      </div>
    </div>
  </div>

  <!-- फिचर ग्रिड (Core intelligence) -->
  <section id="features" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">{{ __('messages.core_intelligence') }}</span>
      <h2 class="text-3xl md:text-4xl font-bold mt-4 text-gray-900">{{ __('messages.everything_need') }}</h2>
      <p class="text-gray-500 mt-3">{{ __('messages.core_intelligence_desc') }}</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7" id="featuresGrid"></div>
    <!-- Extra badges -->
    <div class="grid md:grid-cols-3 gap-5 mt-10 text-center text-sm text-gray-700" id="extraBadges"></div>
  </section>

  <section id="workflow" class="bg-gray-50 py-20 px-6 md:px-10">
    <div class="max-w-7xl mx-auto">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="text-blue-600 font-semibold text-xs uppercase tracking-wide bg-blue-100 px-3 py-1 rounded-full">{{ __('messages.seamless_experience') }}</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3 text-gray-900">{{ __('messages.simple_powerful_workflow') }}</h2>
        <p class="text-gray-500 mt-2">{{ __('messages.for_trekkers_agencies') }}</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8" id="workflowSteps"></div>
    </div>
  </section>

  <section id="agencies" class="py-20 px-6 md:px-10 max-w-7xl mx-auto">
    <div class="grid md:grid-cols-2 gap-12 items-center bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-8 md:p-12">
      <div>
        <span class="text-blue-700 font-semibold text-sm uppercase tracking-wider"><i class="fas fa-building"></i> {{ __('messages.for_travel_agencies') }}</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-2 text-gray-900">{{ __('messages.supercharge_business') }}</h2>
        <p class="text-gray-700 mt-4 leading-relaxed">{{ __('messages.supercharge_desc') }}</p>
        <ul class="mt-6 space-y-3">
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>{{ __('messages.supercharge_bullet1') }}</span></li>
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>{{ __('messages.supercharge_bullet2') }}</span></li>
          <li class="flex gap-2 items-start"><i class="fas fa-check-circle text-green-500 mt-1"></i><span>{{ __('messages.supercharge_bullet3') }}</span></li>
        </ul>
        <div class="mt-8 flex items-center gap-3"><i class="fas fa-chart-line text-2xl text-blue-600"></i><span class="text-sm font-medium text-gray-700">{{ __('messages.trusted_by_early') }}</span></div>
      </div>
      <div class="bg-white/40 backdrop-blur-sm p-6 rounded-2xl border border-white shadow-lg">
        <div class="flex justify-between items-center border-b pb-3 mb-3"><span class="font-bold"><i class="fas fa-calendar-alt text-blue-500 mr-2"></i> {{ __('messages.today_dashboard') }}</span><span class="text-xs bg-green-100 px-2 py-0.5 rounded-full">{{ __('messages.efficiency_plus') }}</span></div>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between"><span>📊 {{ __('messages.active_treks') }}</span><span class="font-semibold">18</span></div>
          <div class="flex justify-between"><span>🧾 {{ __('messages.permits_issued') }}</span><span class="font-semibold">46</span></div>
          <div class="flex justify-between"><span>📈 {{ __('messages.ai_revenue_forecast') }}</span><span class="font-semibold text-green-600">+22%</span></div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-2"><div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div></div>
        <p class="text-xs text-gray-500 mt-3">{{ __('messages.less_paperwork') }}</p>
        <div class="mt-5 text-center text-xs text-gray-500"><i class="fas fa-lock"></i> {{ __('messages.zero_commission_smart_contract') }}</div>
      </div>
    </div>
  </section>

  <section id="early-access" class="py-20 px-6 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-80 h-80 bg-blue-500 opacity-10 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto text-center relative z-10">
      <i class="fas fa-hiking text-4xl text-blue-300 mb-4"></i>
      <h2 class="text-3xl md:text-5xl font-extrabold">{{ __('messages.ready_to_transform') }}</h2>
      <p class="text-gray-300 text-lg mt-4 max-w-2xl mx-auto">{{ __('messages.waitlist_text') }}</p>
      <form class="mt-8 flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" action="#">
        @csrf
        <input type="email" required placeholder="{{ __('messages.email_placeholder') }}" class="flex-1 px-5 py-3 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 border-0">
        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2"><i class="fas fa-rocket"></i> {{ __('messages.reserve_early_spot') }}</button>
      </form>
      <p class="text-gray-400 text-xs mt-4">{{ __('messages.no_spam') }}</p>
    </div>
  </section>

  <!-- All JavaScript blocks (rendering, AI itinerary, check-ins) -->
  <script>
    // ========== Translation Helper ==========
    const trans = {
      ai_trip_planner: "{{ __('messages.ai_trip_planner') }}",
      digital_trek_passport: "{{ __('messages.digital_trek_passport') }}",
      offline_emergency_sos: "{{ __('messages.offline_emergency_sos') }}",
      agency_dashboard: "{{ __('messages.agency_dashboard') }}",
      trek_memory_replay: "{{ __('messages.trek_memory_replay') }}",
      smart_permits_blockchain: "{{ __('messages.smart_permits_blockchain') }}",
      coming_2026_label: "{{ __('messages.coming_2026_label') }}",
      immutable_instant: "{{ __('messages.immutable_instant') }}",
      pwa_offline: "{{ __('messages.pwa_offline') }}",
      realtime_safety_score: "{{ __('messages.realtime_safety_score') }}",
      multilingual: "{{ __('messages.multilingual') }}",
      ai_generates_trek: "{{ __('messages.ai_generates_trek') }}",
      digital_qr_passport: "{{ __('messages.digital_qr_passport') }}",
      safe_trek_memories: "{{ __('messages.safe_trek_memories') }}",
      ai_trip_planner_desc: "{{ __('messages.ai_trip_planner_desc') }}",
      digital_trek_passport_desc: "{{ __('messages.digital_trek_passport_desc') }}",
      offline_emergency_sos_desc: "{{ __('messages.offline_emergency_sos_desc') }}",
      agency_dashboard_desc: "{{ __('messages.agency_dashboard_desc') }}",
      trek_memory_replay_desc: "{{ __('messages.trek_memory_replay_desc') }}",
      smart_permits_blockchain_desc: "{{ __('messages.smart_permits_blockchain_desc') }}",
      smart_recommendations: "{{ __('messages.smart_recommendations') }}",
      dynamic_pricing: "{{ __('messages.dynamic_pricing') }}",
      response_within_5min: "{{ __('messages.response_within_5min') }}",
      workflow_step1_desc: "{{ __('messages.workflow_step1_desc') }}",
      workflow_step2_desc: "{{ __('messages.workflow_step2_desc') }}",
      workflow_step3_desc: "{{ __('messages.workflow_step3_desc') }}",
    };

    const siteData = {
      features: [
        { icon: "fas fa-robot", gradient: "from-blue-500 to-indigo-600", title: trans.ai_trip_planner, desc: trans.ai_trip_planner_desc, tags: [trans.smart_recommendations, trans.dynamic_pricing] },
        { icon: "fas fa-qrcode", gradient: "from-emerald-500 to-teal-600", title: trans.digital_trek_passport, desc: trans.digital_trek_passport_desc, tags: [] },
        { icon: "fas fa-sos", gradient: "from-red-500 to-rose-600", title: trans.offline_emergency_sos, desc: trans.offline_emergency_sos_desc, tags: [], extra: trans.response_within_5min },
        { icon: "fas fa-chart-line", gradient: "from-purple-500 to-pink-600", title: trans.agency_dashboard, desc: trans.agency_dashboard_desc, tags: [] },
        { icon: "fas fa-film", gradient: "from-amber-500 to-orange-600", title: trans.trek_memory_replay, desc: trans.trek_memory_replay_desc, tags: [] },
        { icon: "fas fa-link", gradient: "from-slate-600 to-gray-800", title: trans.smart_permits_blockchain, desc: trans.smart_permits_blockchain_desc, tags: [], extraBadge: trans.coming_2026_label, extraIcon: "fas fa-cube", extraText: trans.immutable_instant }
      ],
      extraBadges: [
        { icon: "fas fa-mobile-alt", text: trans.pwa_offline },
        { icon: "fas fa-chart-simple", text: trans.realtime_safety_score },
        { icon: "fas fa-language", text: trans.multilingual }
      ],
      workflowSteps: [
        { step: 1, icon: "fas fa-brain", title: trans.ai_generates_trek, desc: trans.workflow_step1_desc },
        { step: 2, icon: "fas fa-qrcode", title: trans.digital_qr_passport, desc: trans.workflow_step2_desc },
        { step: 3, icon: "fas fa-shield-heart", title: trans.safe_trek_memories, desc: trans.workflow_step3_desc }
      ]
    };

    function renderFeatures() {
      document.getElementById("featuresGrid").innerHTML = siteData.features.map(f => `
        <div class="glass-card rounded-2xl p-6 shadow-sm bg-white relative">
          ${f.extraBadge ? `<div class="absolute top-3 right-3 bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full">${f.extraBadge}</div>` : ''}
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${f.gradient} flex items-center justify-center mb-4"><i class="${f.icon} text-white text-xl"></i></div>
          <h3 class="text-xl font-bold text-gray-800">${f.title}</h3>
          <p class="text-gray-500 text-sm mt-1">${f.desc}</p>
          ${f.tags && f.tags.length ? `<div class="mt-3 flex gap-2">${f.tags.map(t => `<span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full">${t}</span>`).join('')}</div>` : ''}
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
    document.querySelector('#early-access form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const email = form.querySelector('input[type="email"]').value;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = 'Submitting...';
        btn.disabled = true;

        try {
            const response = await fetch('/waitlist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                form.reset();
            } else {
                alert(data.message || 'Something went wrong. Please try again.');
            }
        } catch (error) {
            alert('Server error. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
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
    function renderItinerary(days, totalCost, breakdown, currency) {
      if (!days || days.length === 0) return '<p class="text-gray-500">{{ __('messages.no_itinerary') }}</p>';

      let html = '';

      // ------ Cost Breakdown Section ------
      if (breakdown && Object.keys(breakdown).length > 0) {
        html += `<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">`;
        html += `<p class="text-lg font-bold text-blue-800">💰 {{ __('messages.estimated_cost_breakdown') }} (${currency || 'NPR'})</p>`;
        html += `<ul class="mt-2 space-y-1 text-sm">`;
        let total = 0;
        for (const [key, item] of Object.entries(breakdown)) {
          if (item.unit === 'note') {
    // Render as warning box with message
    html += `<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-2 rounded shadow-sm">`;
    html += `<div class="flex items-start">`;
    html += `<div class="flex-shrink-0"><i class="fas fa-exclamation-triangle text-yellow-500 text-lg"></i></div>`;
    html += `<div class="ml-3">`;
    html += `<p class="text-sm font-semibold text-yellow-800">${item.name || '⚠️ Budget Warning'}</p>`;
    html += `<p class="text-sm text-yellow-700">${item.message || ''}</p>`;
    html += `</div></div></div>`;
    continue;
}
          const amount = item.amount || 0;
          total += amount;
          const providerDisplay = item.provider_name ? ` (${item.provider_name})` : '';
          html += `<li class="flex justify-between border-b border-gray-100 py-1">
                      <span>${item.name || key}${providerDisplay}</span>
                      <span class="font-medium">${item.currency || 'NPR'} ${Math.round(amount)}</span>
                   </li>`;
        }
        if (totalCost && totalCost > 0) {
          html += `<li class="flex justify-between font-bold text-blue-700 pt-2 border-t-2 border-blue-200">
                      <span>{{ __('messages.total') }}</span>
                      <span>${currency || 'NPR'} ${Math.round(totalCost)}</span>
                   </li>`;
        }
        html += `</ul>`;
        html += `<p class="text-xs text-gray-400 mt-2">* {{ __('messages.cost_breakdown_note') }}</p>`;
        html += `</div>`;
      } else if (totalCost && totalCost > 0) {
        const usdTotal = Math.round(totalCost * 0.0075);
        html += `<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">`;
        html += `<p class="text-lg font-bold text-blue-800">💰 {{ __('messages.estimated_base_cost') }}: <span class="text-2xl">~$${usdTotal} USD</span></p>`;
        html += `<p class="text-xs text-gray-400 mt-1">{{ __('messages.base_cost_note') }}</p>`;
        html += `</div>`;
      }

      // ------ Days Rendering ------
      days.forEach(day => {
        html += `<div class="mb-6 border-b border-gray-200 pb-4 last:border-0">`;
        html += `<h3 class="text-lg font-bold text-blue-700">{{ __('messages.day') }} ${day.day_number}: ${day.title}</h3>`;
        html += `<p class="text-gray-600 text-sm mt-1">${day.description || ''}</p>`;
        if (day.distance_km) html += `<p class="text-xs text-gray-400 mt-1">📏 ${day.distance_km} km  |  ⛰️ ${day.altitude_m || '{{ __('messages.na') }}'} m</p>`;
        if (day.items && day.items.length > 0) {
          html += `<ul class="list-disc ml-5 mt-2 space-y-1">`;
          day.items.forEach(item => {
            let costDisplay = '';
            if (item.service_id && breakdown) {
              const service = Object.values(breakdown).find(b => b.service_id === item.service_id);
              if (service) {
                const providerName = service.provider_name || '{{ __('messages.local_partner') }}';
                const serviceName = service.name || '{{ __('messages.service') }}';
                costDisplay = ` <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">🏨 ${serviceName} (${providerName})</span>`;
                if (service.amount) {
                  costDisplay += ` <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">${currency || 'NPR'} ${Math.round(service.amount)}</span>`;
                }
              }
            }
            if (!costDisplay && item.cost && item.cost > 0) {
              costDisplay = ` <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">${currency || 'NPR'} ${item.cost}</span>`;
            }
            if (!costDisplay && breakdown) {
              const matchedService = Object.values(breakdown).find(b => 
                b.name && item.title && b.name.toLowerCase().includes(item.title.toLowerCase())
              );
              if (matchedService && matchedService.amount) {
                costDisplay = ` <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">${currency || 'NPR'} ${Math.round(matchedService.amount)}</span>`;
              }
            }
            let itemHtml = `<li class="text-sm text-gray-700"><span class="font-medium">${item.title}</span> – ${item.description || ''}`;
            if (costDisplay) itemHtml += costDisplay;
            itemHtml += `</li>`;
            html += itemHtml;
          });
          html += `</ul>`;
        }
        html += `</div>`;
      });

            // ➕ Add "Request Quotation" button after itinerary
      html += `<div class="mt-6 border-t pt-4 text-center">
                <button onclick="openQuotationModal()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 mx-auto">
                    <i class="fas fa-paper-plane"></i> Request Quotation from Providers
                </button>
              </div>`;

      return html;
    }

    document.getElementById('itineraryForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = document.getElementById('generateBtn');
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('messages.generating') }}';
      btn.disabled = true;

      try {
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

        if (data.success && data.data && data.data.days) {
          document.getElementById('itineraryResult').innerHTML = renderItinerary(
            data.data.days,
            data.data.total_cost,
            data.data.breakdown,
            data.data.currency || 'NPR'
          );
          document.getElementById('result').classList.remove('hidden');
                    // Store planner_result_id for quotation
          currentPlannerResultId = data.data.planner_result_id || null;
        } else {
          alert(data.message || '{{ __('messages.something_wrong') }}');
        }
      } catch (error) {
        console.error(error);
        alert('{{ __('messages.server_error') }}');
      } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    });

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

    function copyItinerary() {
      const container = document.getElementById('itineraryResult');
      const text = container.innerText || container.textContent;
      navigator.clipboard.writeText(text).then(() => {
        alert('{{ __('messages.copied_to_clipboard') }}');
      }).catch(() => {
        alert('{{ __('messages.copy_failed') }}');
      });
    }
        // ---------- Quotation Request Functions ----------
    let currentPlannerResultId = null;

    function openQuotationModal() {
        if (!currentPlannerResultId) {
            alert('Please generate an itinerary first.');
            return;
        }
        document.getElementById('plannerResultId').value = currentPlannerResultId;
        // Auto-fill user data if logged in
const userName = "{{ Auth::check() ? Auth::user()->name : '' }}";
const userEmail = "{{ Auth::check() ? Auth::user()->email : '' }}";

if (userName) document.getElementById('travelerName').value = userName;
if (userEmail) document.getElementById('travelerEmail').value = userEmail;
        loadProviders();
        document.getElementById('quotationModal').classList.remove('hidden');
    }

    function closeQuotationModal() {
        document.getElementById('quotationModal').classList.add('hidden');
        // Reset status message
        document.getElementById('quotationRequestStatus').classList.add('hidden');
        document.getElementById('quotationRequestStatus').innerHTML = '';
        document.getElementById('quotationRequestForm').reset();
    }

    function loadProviders() {
        const select = document.getElementById('providerSelect');
        select.innerHTML = '<option value="">Loading...</option>';
        fetch('/api/providers/list')
            .then(res => res.json())
            .then(data => {
                select.innerHTML = '';
                if (data.providers && data.providers.length) {
                    data.providers.forEach(p => {
                        select.innerHTML += `<option value="${p.id}">${p.name}</option>`;
                    });
                } else {
                    select.innerHTML = '<option value="">No providers available</option>';
                }
            })
            .catch(() => {
                select.innerHTML = '<option value="">Error loading providers</option>';
            });
    }

    document.getElementById('quotationRequestForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Sending...';
        btn.disabled = true;

        try {
            const payload = {
    planner_result_id: document.getElementById('plannerResultId').value,
    provider_id: document.getElementById('providerSelect').value,
    traveler_name: document.getElementById('travelerName').value,
    traveler_email: document.getElementById('travelerEmail').value,
    traveler_phone: document.getElementById('travelerPhone').value,
    message: document.getElementById('quotationMessage').value,
};

            const response = await fetch('/api/quotation-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            const statusDiv = document.getElementById('quotationRequestStatus');
            if (data.success) {
                statusDiv.innerHTML = '<p class="text-green-600">✅ Request sent successfully! The provider will get back to you.</p>';
                statusDiv.classList.remove('hidden');
                setTimeout(() => { closeQuotationModal(); }, 3000);
            } else {
                alert(data.message || 'Failed to send request.');
            }
        } catch (error) {
            alert('Server error. Please try again.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

  </script>

  <!-- रिसेन्ट चेक-इन जेएस (Anonymous सहित) -->
  <script>
    const checkins = @json($recentCheckins);
    const anonymizedCheckins = checkins.map(item => ({ ...item, trekker_name: '{{ __('messages.anonymous') }}' }));
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
    else { const slider = document.getElementById('checkin-slider'); slider.innerHTML = '<div class="text-center text-gray-500">{{ __('messages.no_checkins_yet') }}</div>'; slider.style.backgroundImage = ''; }
  </script>

  <!-- ✅ Auto-fill Days Only -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const destinationSelect = document.getElementById('destination');
      const daysInput = document.getElementById('days');

      function autoFill() {
        const route = destinationSelect.value;
        const routesData = @json($routes->map(function($r) {
          return ['name' => $r->name, 'duration_days' => $r->duration_days];
        }));

        const matched = routesData.find(r => r.name === route || r.name.toLowerCase() === route.toLowerCase());
        if (matched && matched.duration_days) {
          daysInput.value = matched.duration_days;
        } else {
          daysInput.value = 3;
        }
      }

      destinationSelect.addEventListener('change', function() {
        autoFill();
      });

      autoFill();
    });
  </script>
@endsection
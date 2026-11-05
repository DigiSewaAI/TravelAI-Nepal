<div style="background: #fff; border-radius: 18px; padding: 20px; border: 1px solid #e5e7eb;">
    <h3 style="font-size: 15px; font-weight: 800; color: #111827; margin: 0 0 16px;">🧰 {{ __('messages.tools_shortcuts') ?? 'Tools & Shortcuts' }}</h3>
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
        <a href="{{ url('/') }}#ai-planner" class="tv2-tool-card" style="text-align: center; padding: 18px 8px; border-radius: 14px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #bfdbfe; text-decoration: none; color: #1e40af; transition: all 0.2s ease; display: block;">
            <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 12px; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px;">🤖</div>
            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ __('messages.ai_travel_planner') ?? 'AI Travel Planner' }}</div>
        </a>
        <a href="#exchange-rates" class="tv2-tool-card" style="text-align: center; padding: 18px 8px; border-radius: 14px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #bbf7d0; text-decoration: none; color: #15803d; transition: all 0.2s ease; display: block;">
            <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 12px; background: #16a34a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px;">💱</div>
            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ __('messages.nav_exchange_rates') ?? 'Exchange Rates' }}</div>
        </a>
        <a href="#memories" class="tv2-tool-card" style="text-align: center; padding: 18px 8px; border-radius: 14px; background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border: 2px solid #f5d0fe; text-decoration: none; color: #7e22ce; transition: all 0.2s ease; display: block;">
            <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 12px; background: #a855f7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px;">📸</div>
            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ __('messages.travel_memories') ?? 'Travel Memories' }}</div>
        </a>
        <a href="{{ route('traveler.journey-replay') }}" class="tv2-tool-card" style="text-align: center; padding: 18px 8px; border-radius: 14px; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #fed7aa; text-decoration: none; color: #c2410c; transition: all 0.2s ease; display: block;">
            <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 12px; background: #ea580c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px;">🎬</div>
            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ __('messages.journey_replay') ?? 'Journey Replay' }}</div>
        </a>
        <a href="{{ route('safety.index') }}" class="tv2-tool-card" style="text-align: center; padding: 18px 8px; border-radius: 14px; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fecaca; text-decoration: none; color: #b91c1c; transition: all 0.2s ease; display: block;">
            <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 12px; background: #dc2626; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px;">🛡️</div>
            <div style="font-size: 12px; font-weight: 700; line-height: 1.3;">{{ __('messages.safety_center') ?? 'Safety Center' }}</div>
        </a>
    </div>
</div>

<style>
    .tv2-tool-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        filter: brightness(1.05);
    }
    @media (max-width: 767px) {
        .tv2-tool-card { padding: 12px 4px !important; }
        .tv2-tool-card > div:first-child { width: 36px !important; height: 36px !important; font-size: 18px !important; }
    }
</style>
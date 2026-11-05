@php
    $rates = $exchangeRates ?? collect();
    $flagCodeMap = ['USD' => 'us', 'EUR' => 'eu', 'GBP' => 'gb', 'INR' => 'in', 'AUD' => 'au'];
    $lastUpdate = $rates->first()->fetched_at ?? null;
@endphp

<div id="exchange-rates" style="background: #fff; border-radius: 18px; padding: 20px; border: 1px solid #e5e7eb; scroll-margin-top: 100px;">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #111827; margin: 0;">💱 {{ __('messages.live_exchange_rates') ?? 'Live Exchange Rates' }}</h3>
        @if($lastUpdate)
            <span style="font-size: 10px; color: #9ca3af;">{{ __('messages.updated_ago') ?? 'Updated' }}: {{ \Carbon\Carbon::parse($lastUpdate)->diffForHumans() }}</span>
        @endif
    </div>
    @if($rates->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($rates as $r)
                @php $flagCode = $flagCodeMap[$r->base_currency] ?? null; @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                    <span style="font-size: 13px; color: #374151; display: flex; align-items: center; gap: 10px;">
                        @if($flagCode)
                            <img src="https://flagcdn.com/w40/{{ $flagCode }}.png" alt="{{ $r->base_currency }}" style="width: 24px; height: 16px; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); object-fit: cover;">
                        @else
                            <span style="width: 24px; text-align: center;">💵</span>
                        @endif
                        <span style="font-weight: 600;">1 {{ $r->base_currency }}</span>
                    </span>
                    <span style="font-size: 13px; font-weight: 700; color: #111827;">NPR {{ number_format($r->rate, 2) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p style="font-size: 13px; color: #9ca3af; margin: 0;">No exchange rates available.</p>
    @endif
</div>
@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.subscription_page_title'))
@section('header', __('messages.subscription_header'))

@section('content')
<div style="max-width: 1100px; margin: 0 auto; padding: 20px;">

    {{-- Current Plan Section --}}
    <div style="background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
        <h2 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 15px;">{{ __('messages.current_plan') }}</h2>

        @if($currentSubscription)
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                <div>
                    <span style="font-size: 24px; font-weight: 800; color: var(--sky);">{{ $currentSubscription->plan->name }}</span>
                    <p style="font-size: 13px; color: var(--muted); margin-top: 5px;">{{ $currentSubscription->plan->description }}</p>
                    <div style="margin-top: 10px;">
                        @if($currentSubscription->plan->requires_contact)
                            <span style="font-size: 14px; font-weight: 700; color: var(--sky);">{{ __('messages.contact_for_pricing') }}</span>
                        @elseif((float) $currentSubscription->plan->price_monthly == 0)
                            <span style="font-size: 14px; font-weight: 700; color: var(--green);">{{ __('messages.free_plan') }}</span>
                        @else
                            <span style="font-size: 14px; font-weight: 700; color: var(--text);">Rs. {{ number_format($currentSubscription->plan->price_monthly) }} / {{ __('messages.month') }}</span>
                        @endif
                        <span style="font-size: 12px; color: var(--muted); margin-left: 10px;">
                            {{ __('messages.active_since') }} {{ $currentSubscription->start_date->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; 
                        background: {{ $currentSubscription->status === 'active' ? 'var(--green-soft)' : 'var(--red-soft)' }}; 
                        color: {{ $currentSubscription->status === 'active' ? 'var(--green)' : 'var(--red)' }};">
                        {{ ucfirst($currentSubscription->status) }}
                    </span>
                    @if($currentSubscription->status === 'active')
                    <form method="POST" action="{{ route('provider.subscriptions.cancel') }}" onsubmit="return confirm('{{ __('messages.cancel_subscription_confirm') }}')" style="margin-top: 15px;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: var(--red); font-size: 13px; font-weight: 700; cursor: pointer;">
                            <i class="fas fa-times-circle"></i> {{ __('messages.cancel_subscription_btn') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @else
            <p style="font-size: 14px; color: var(--muted);">{{ __('messages.no_active_subscription') }}</p>
        @endif
    </div>

    {{-- Available Plans --}}
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ __('messages.available_plans') }}</h2>
        <p style="font-size: 14px; color: var(--muted); margin-top: 5px;">{{ __('messages.choose_plan_subtitle') }}</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        @foreach($plans as $plan)
            @php
                $isPopular = strtolower($plan->slug) === 'professional';
                $isCurrent = $currentSubscription && $currentSubscription->plan_id === $plan->id;
            @endphp
            
            <div style="background: var(--card); border: 1px solid {{ $isPopular ? 'var(--sky)' : 'var(--line)' }}; border-radius: 16px; padding: 25px; position: relative; display: flex; flex-direction: column; transition: transform 0.2s; {{ $isPopular ? 'box-shadow: 0 8px 24px rgba(14,165,233,0.12);' : '' }}">
                
                @if($isPopular)
                <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--sky); color: #fff; font-size: 10px; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">
                    {{ __('messages.most_popular') }}
                </div>
                @endif

                <div style="flex: 1;">
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 5px;">{{ $plan->name }}</h3>
                    <p style="font-size: 12px; color: var(--muted); height: 36px; margin-bottom: 15px;">{{ $plan->description }}</p>
                    
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--line);">
                        @if($plan->requires_contact)
                            <span style="font-size: 24px; font-weight: 800; color: var(--text);">{{ __('messages.custom') }}</span>
                            <p style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ __('messages.contact_for_pricing') }}</p>
                        @elseif($plan->price_monthly > 0)
                            <span style="font-size: 28px; font-weight: 800; color: var(--text);">Rs. {{ number_format($plan->price_monthly) }}</span>
                            <span style="font-size: 13px; color: var(--muted);">/ {{ __('messages.month') }}</span>
                        @else
                            <span style="font-size: 28px; font-weight: 800; color: var(--green);">{{ __('messages.free') }}</span>
                            <p style="font-size: 11px; color: var(--muted); margin-top: 2px;">{{ __('messages.forever_free') }}</p>
                        @endif
                    </div>

                    <ul style="list-style: none; padding: 0; margin: 0 0 25px 0; font-size: 13px; color: var(--text);">
                        @php $features = $plan->features ?? []; @endphp
                        @foreach(array_slice($features, 0, 4) as $feature)
                            <li style="display: flex; align-items: flex-start; margin-bottom: 10px;">
                                <i class="fas fa-check-circle" style="color: var(--green); margin-top: 2px; margin-right: 8px;"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                        @if(count($features) > 4)
                            <li style="font-size: 12px; color: var(--muted); font-style: italic; margin-top: 5px;">+ {{ count($features) - 4 }} more features</li>
                        @endif
                    </ul>
                </div>

                <div style="margin-top: auto;">
                    @if($isCurrent)
                        <button disabled style="width: 100%; background: var(--sky-soft); color: var(--sky-d); font-weight: 800; font-size: 13px; padding: 12px; border-radius: 10px; border: 1px solid var(--sky); cursor: not-allowed;">
                            <i class="fas fa-check"></i> {{ __('messages.current_plan_label') }}
                        </button>
                    @elseif($plan->requires_contact)
                        <a href="{{ route('public.contact-sales') }}" style="display: block; text-align: center; width: 100%; background: var(--text); color: #fff; font-weight: 800; font-size: 13px; padding: 12px; border-radius: 10px; text-decoration: none; transition: 0.2s;">
                            {{ __('messages.contact_for_pricing') }}
                        </a>
                    @else
                        <form method="POST" action="{{ $currentSubscription ? route('provider.subscriptions.upgrade') : route('provider.subscriptions.store') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" style="width: 100%; background: var(--sky); color: #fff; font-weight: 800; font-size: 13px; padding: 12px; border-radius: 10px; border: none; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 12px rgba(14,165,233,0.3);">
                                {{ __('messages.choose_plan') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
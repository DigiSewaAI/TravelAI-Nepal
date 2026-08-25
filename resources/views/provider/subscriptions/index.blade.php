@extends('layouts.provider')

@section('title', __('messages.subscription_page_title'))
@section('header', __('messages.subscription_header'))

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Current Plan -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.current_plan') }}</h2>
        
        @if($currentSubscription)
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-2xl font-bold text-blue-600">{{ $currentSubscription->plan->name }}</span>
                    <p class="text-gray-500 text-sm">{{ $currentSubscription->plan->description }}</p>
                    <div class="mt-2">
                        @if($currentSubscription->plan->price_monthly == 0)
                            <span class="text-sm text-green-600 font-semibold">{{ __('messages.free_plan') }}</span>
                        @else
                            <span class="text-sm text-gray-600">${{ number_format($currentSubscription->plan->price_monthly, 2) }} / {{ __('messages.month') }}</span>
                        @endif
                        <span class="text-xs text-gray-400 ml-2">
                            {{ __('messages.active_since') }} {{ $currentSubscription->start_date->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                        @if($currentSubscription->status === 'active') bg-green-100 text-green-800
                        @elseif($currentSubscription->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @if($currentSubscription->status === 'active') {{ __('messages.active_sub') }}
                        @elseif($currentSubscription->status === 'cancelled') {{ __('messages.cancelled_sub') }}
                        @else {{ ucfirst($currentSubscription->status) }} @endif
                    </span>
                </div>
            </div>
            @if($currentSubscription->status === 'active')
                <div class="mt-4">
                    <form method="POST" action="{{ route('provider.subscriptions.cancel') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            <i class="fas fa-times-circle mr-1"></i> {{ __('messages.cancel_subscription_btn') }}
                        </button>
                    </form>
                </div>
            @endif
        @else
            <p class="text-gray-500">{{ __('messages.no_active_subscription') }}</p>
        @endif
    </div>

    <!-- Available Plans -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.available_plans') }}</h2>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($plans as $plan)
                <div class="border rounded-lg p-4 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800">{{ $plan->name }}</h3>
                    <p class="text-gray-500 text-sm">{{ $plan->description }}</p>
                    <div class="mt-2">
                        @if($plan->price_monthly == 0)
                            <span class="text-lg font-bold text-green-600">{{ __('messages.free') }}</span>
                        @elseif($plan->price_monthly !== null)
                            <span class="text-lg font-bold text-gray-800">${{ number_format($plan->price_monthly, 2) }}</span>
                            <span class="text-sm text-gray-500">/ {{ __('messages.month') }}</span>
                        @else
                            <span class="text-lg font-bold text-gray-800">{{ __('messages.custom') }}</span>
                        @endif
                    </div>
                    
                    <!-- Features -->
                    <ul class="mt-3 space-y-1 text-sm text-gray-600">
                        @php $features = $plan->features ?? []; @endphp
                        @foreach(array_slice($features, 0, 3) as $feature)
                            <li><i class="fas fa-check text-green-500 mr-1"></i> {{ $feature }}</li>
                        @endforeach
                        @if(count($features) > 3)
                            <li class="text-gray-400">{{ __('messages.plus_more', ['count' => count($features) - 3]) }}</li>
                        @endif
                    </ul>

                    <!-- Action -->
                    @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                        <span class="mt-3 inline-block bg-blue-100 text-blue-800 text-sm px-4 py-2 rounded-lg w-full text-center">
                            {{ __('messages.current_plan_label') }}
                        </span>
                    @else
                        <form method="POST" action="{{ $currentSubscription ? route('provider.subscriptions.upgrade') : route('provider.subscriptions.store') }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
                                @if($currentSubscription)
                                    <i class="fas fa-arrow-right mr-1"></i> {{ __('messages.switch_to_plan', ['name' => $plan->name]) }}
                                @else
                                    <i class="fas fa-arrow-right mr-1"></i> {{ __('messages.choose_plan', ['name' => $plan->name]) }}
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
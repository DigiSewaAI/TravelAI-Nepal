@extends('layouts.provider')

@section('title', 'Subscription | TravelAI Nepal')
@section('header', 'Subscription & Plan')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Current Plan -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Current Plan</h2>
        
        @if($currentSubscription)
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-2xl font-bold text-blue-600">{{ $currentSubscription->plan->name }}</span>
                    <p class="text-gray-500 text-sm">{{ $currentSubscription->plan->description }}</p>
                    <div class="mt-2">
                        @if($currentSubscription->plan->price_monthly == 0)
                            <span class="text-sm text-green-600 font-semibold">Free Plan</span>
                        @else
                            <span class="text-sm text-gray-600">${{ number_format($currentSubscription->plan->price_monthly, 2) }} / month</span>
                        @endif
                        <span class="text-xs text-gray-400 ml-2">
                            Active since {{ $currentSubscription->start_date->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                        @if($currentSubscription->status === 'active') bg-green-100 text-green-800
                        @elseif($currentSubscription->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($currentSubscription->status) }}
                    </span>
                </div>
            </div>
        @else
            <p class="text-gray-500">No active subscription. Please choose a plan.</p>
        @endif
    </div>

    <!-- Available Plans -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Available Plans</h2>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($plans as $plan)
                <div class="border rounded-lg p-4 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800">{{ $plan->name }}</h3>
                    <p class="text-gray-500 text-sm">{{ $plan->description }}</p>
                    <div class="mt-2">
                        @if($plan->price_monthly == 0)
                            <span class="text-lg font-bold text-green-600">Free</span>
                        @elseif($plan->price_monthly !== null)
                            <span class="text-lg font-bold text-gray-800">${{ number_format($plan->price_monthly, 2) }}</span>
                            <span class="text-sm text-gray-500">/ month</span>
                        @else
                            <span class="text-lg font-bold text-gray-800">Custom</span>
                        @endif
                    </div>
                    
                    <!-- Features -->
                    <ul class="mt-3 space-y-1 text-sm text-gray-600">
                        @php $features = $plan->features ?? []; @endphp
                        @foreach(array_slice($features, 0, 3) as $feature)
                            <li><i class="fas fa-check text-green-500 mr-1"></i> {{ $feature }}</li>
                        @endforeach
                        @if(count($features) > 3)
                            <li class="text-gray-400">+ {{ count($features) - 3 }} more</li>
                        @endif
                    </ul>

                    <!-- Action -->
                    @if($currentSubscription && $currentSubscription->plan_id === $plan->id)
                        <span class="mt-3 inline-block bg-blue-100 text-blue-800 text-sm px-4 py-2 rounded-lg w-full text-center">
                            Current Plan
                        </span>
                    @elseif($plan->slug === 'free' && $currentSubscription)
                        <form method="POST" action="{{ route('provider.subscriptions.cancel') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition">
                                <i class="fas fa-times mr-1"></i> Cancel & Switch to Free
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('provider.subscriptions.upgrade') }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
                                <i class="fas fa-arrow-right mr-1"></i> Choose {{ $plan->name }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
@extends($provider_layout ?? 'layouts.provider')

@section('title', __('messages.sos_alerts_detail_title'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Back link --}}
    <a href="{{ route('provider.sos-alerts.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-red-600 mb-4 transition">
        <i class="fas fa-arrow-left"></i>
        {{ __('messages.sos_alerts_back') }}
    </a>

    {{-- Success flash --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
        $isActive = !$sos->isTerminal();
        $isPending = in_array($sos->status, ['pending', 'sent']);
        $isAcknowledged = $sos->status === 'acknowledged';
        $isResponding = $sos->status === 'responding';
        $isResolved = $sos->status === 'resolved';
        $isFalseAlarm = $sos->status === 'false_alarm';

        if ($isPending) {
            $heroBg   = 'bg-gradient-to-r from-red-600 to-rose-700';
            $heroIcon = 'fa-exclamation-triangle';
            $heroText = __('messages.sos_alerts_header_active');
        } elseif ($isAcknowledged) {
            $heroBg   = 'bg-gradient-to-r from-red-600 to-rose-700';
            $heroIcon = 'fa-eye';
            $heroText = __('messages.sos_alerts_header_acknowledged');
        } elseif ($isResponding) {
            $heroBg   = 'bg-gradient-to-r from-red-600 to-rose-700';
            $heroIcon = 'fa-truck-medical';
            $heroText = __('messages.sos_alerts_header_responding');
        } elseif ($isResolved) {
            $heroBg   = 'bg-gradient-to-r from-green-600 to-emerald-700';
            $heroIcon = 'fa-check-circle';
            $heroText = __('messages.sos_alerts_header_resolved');
        } else {
            $heroBg   = 'bg-gradient-to-r from-gray-600 to-gray-700';
            $heroIcon = 'fa-ban';
            $heroText = __('messages.sos_alerts_header_false_alarm');
        }
    @endphp

    {{-- Alert header --}}
    <div class="rounded-xl overflow-hidden shadow-lg mb-5 {{ $heroBg }}">
        <div class="p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $heroIcon }} text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">
                        {{ $heroText }}
                    </h1>
                    <p class="text-white/80 text-sm mt-1">
                        {{ $sos->traveler->name ?? __('messages.sos_alerts_unknown_traveler') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Info card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="p-5 space-y-4">

            {{-- Traveler --}}
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">{{ __('messages.sos_alerts_traveler_label') }}</p>
                    <p class="font-semibold text-gray-900">{{ $sos->traveler->name ?? '—' }}</p>
                    @if($sos->traveler && $sos->traveler->email)
                        <p class="text-xs text-gray-500">{{ $sos->traveler->email }}</p>
                    @endif
                </div>
            </div>

            {{-- Booking --}}
            @if($sos->booking && $sos->booking->service)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-hiking text-purple-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400 uppercase tracking-wider">{{ __('messages.sos_alerts_booking_label') }}</p>
                        <p class="font-semibold text-gray-900">{{ $sos->booking->service->name }}</p>
                        <p class="text-xs text-gray-500">{{ $sos->booking->booking_number ?? '—' }}</p>
                    </div>
                </div>
            @endif

            {{-- Location --}}
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-red-600"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">{{ __('messages.sos_alerts_location_label') }}</p>
                    <p class="font-semibold text-gray-900">
                        {{ number_format($sos->latitude, 6) }}, {{ number_format($sos->longitude, 6) }}
                    </p>
                    <a href="https://maps.google.com/?q={{ $sos->latitude }},{{ $sos->longitude }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1 mt-1 text-sm text-blue-600 hover:underline">
                        <i class="fas fa-external-link-alt text-xs"></i>
                        {{ __('messages.sos_alerts_open_map') }}
                    </a>
                </div>
            </div>

            {{-- Time --}}
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <i class="far fa-clock text-amber-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">{{ __('messages.sos_alerts_time_label') }}</p>
                    <p class="font-semibold text-gray-900">{{ $sos->created_at->format('M d, Y · H:i') }}</p>
                    <p class="text-xs text-gray-500">{{ $sos->created_at->diffForHumans() }}</p>
                </div>
            </div>

            {{-- Message --}}
            @if($sos->message)
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.sos_alerts_message_label') }}</p>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 italic">
                        "{{ $sos->message }}"
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Actions --}}
    @if($isActive)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-900 mb-2">{{ __('messages.sos_alerts_actions_title') }}</h3>
            <p class="text-xs text-gray-500 mb-4">{{ __('messages.sos_alerts_actions_desc') }}</p>

            @if($sos->canAcknowledge())
                <form action="{{ route('provider.sos-alerts.acknowledge', $sos) }}" method="POST" class="mb-3"
                      onsubmit="return confirm('Acknowledge this SOS? Siren will stop.')">
                    @csrf
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition shadow-md">
                        <i class="fas fa-eye"></i>
                        Acknowledge (I'm responding)
                    </button>
                </form>
            @endif

            @if($sos->canRespond())
                <form action="{{ route('provider.sos-alerts.respond', $sos) }}" method="POST" class="mb-3"
                      onsubmit="return confirm('Mark as RESPONDING? Rescue is on the way.')">
                    @csrf
                    <button type="submit"
                            class="w-full bg-amber-600 hover:bg-amber-700 text-white px-5 py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition shadow-md">
                        <i class="fas fa-truck-medical"></i>
                        Mark as Responding
                    </button>
                </form>
            @endif

            @if($sos->canResolve())
                <form action="{{ route('provider.sos-alerts.resolve', $sos) }}" method="POST" class="mb-3"
                      onsubmit="return confirm('{{ __('messages.sos_alerts_resolve_confirm') }}')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition shadow-md">
                        <i class="fas fa-check-circle"></i>
                        {{ __('messages.sos_alerts_resolve_btn') }}
                    </button>
                </form>
            @endif

            @if($sos->canMarkFalseAlarm())
                <details class="mt-2">
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Mark as false alarm?</summary>
                    <form action="{{ route('provider.sos-alerts.false-alarm', $sos) }}" method="POST" class="mt-2"
                          onsubmit="return confirm('Mark this SOS as FALSE ALARM? This action is logged.')">
                        @csrf
                        <textarea name="reason" required minlength="5" maxlength="500" rows="2"
                                  class="w-full px-3 py-2 border rounded-lg text-sm mb-2"
                                  placeholder="Reason (required — e.g., accidental trigger, resolved on trail)"></textarea>
                        <button type="submit"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                            <i class="fas fa-exclamation-triangle"></i> Confirm False Alarm
                        </button>
                    </form>
                </details>
            @endif
        </div>
    @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
            <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
            <p class="font-semibold text-green-800">{{ __('messages.sos_alerts_resolved_info') }}</p>
            @if($sos->resolved_at)
                <p class="text-xs text-green-600 mt-1">
                    {{ $sos->resolved_at->format('M d, Y · H:i') }}
                </p>
            @endif
        </div>
    @endif

</div>
@endsection
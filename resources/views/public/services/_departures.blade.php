{{-- PROVIDER-ITINERARY-09B-03: Public Future Scheduled Departures --}}
{{-- Display-only — no booking selection, no departure-specific CTA --}}
{{-- P2: scheduled + end_date >= today only --}}
{{-- P4: hide entire section if empty --}}
@if($service->isItineraryPublished() && $service->departures->isNotEmpty())

<section class="mt-12">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                {{ __('messages.departures_heading') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $service->departures->count() }} {{ __('messages.departures') }}
            </p>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($service->departures as $departure)
            @php
                $reserved   = (int) ($departure->reserved_seats ?? 0);
                $capacity   = (int) $departure->capacity;
                $isSoldOut  = $reserved >= $capacity;
                $duration   = $departure->start_date->diffInDays($departure->end_date) + 1;
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    {{-- Left: dates + duration --}}
                    <div class="flex-1 min-w-[240px]">
                        <div class="text-base md:text-lg font-semibold text-gray-900">
                            {{ $departure->start_date->format('M d, Y') }}
                            <span class="text-gray-400 mx-1">→</span>
                            {{ $departure->end_date->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $duration }} {{ __('messages.days') }}
                        </div>
                    </div>

                    {{-- Middle: capacity/seats --}}
                    <div class="flex-1 min-w-[140px] text-sm">
                        <span class="font-semibold text-gray-800">
                            {{ $reserved }} / {{ $capacity }}
                        </span>
                        <span class="text-gray-500 ml-1">{{ __('messages.capacity') }}</span>
                    </div>

                    {{-- Right: derived badge (P3) --}}
                    <div class="flex-shrink-0">
                        @if($isSoldOut)
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                {{ __('messages.sold_out') }}
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                {{ __('messages.departure_available') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

@endif
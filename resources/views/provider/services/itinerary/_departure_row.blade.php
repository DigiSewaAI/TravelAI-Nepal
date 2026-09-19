@php
    $isPast      = $departure->end_date->isPast();
    $isCancelled = $departure->status === 'cancelled';
    $isEditable  = !$isPast && !$isCancelled;
@endphp

<div class="border border-gray-200 rounded-lg overflow-hidden">
    <div class="p-3 flex flex-wrap items-center gap-3 justify-between bg-white">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm font-semibold text-gray-900">
                {{ $departure->start_date->format('M d, Y') }}
                →
                {{ $departure->end_date->format('M d, Y') }}
            </span>
            <span class="text-xs text-gray-500">{{ $departure->capacity }} {{ __('messages.capacity') }}</span>

            @if($isCancelled)
                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">{{ __('messages.cancelled') }}</span>
            @elseif($isPast)
                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">{{ __('messages.past_departure') }}</span>
            @else
                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">{{ __('messages.scheduled') }}</span>
            @endif
        </div>

        <div class="flex items-center gap-3 text-xs font-medium">
            @if($isEditable)
                <button type="button"
                        onclick="document.getElementById('edit-departure-{{ $departure->id }}').classList.toggle('hidden')"
                        class="text-blue-600 hover:text-blue-800">
                    {{ __('messages.edit_departure') }}
                </button>

                <form method="POST"
                      action="{{ route('provider.services.departures.cancel', [$service, $departure]) }}"
                      class="inline"
                      onsubmit="return confirm('{{ __('messages.cancel_departure') }}?');">
                    @csrf
                    <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                        {{ __('messages.cancel_departure') }}
                    </button>
                </form>
            @endif

            <form method="POST"
                  action="{{ route('provider.services.departures.destroy', [$service, $departure]) }}"
                  class="inline"
                  onsubmit="return confirm('{{ __('messages.delete_departure') }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800">
                    {{ __('messages.delete_departure') }}
                </button>
            </form>
        </div>
    </div>

    @if($isEditable)
        <div id="edit-departure-{{ $departure->id }}" class="hidden bg-gray-50 border-t border-gray-200 p-4">
            <form method="POST"
                  action="{{ route('provider.services.departures.update', [$service, $departure]) }}">
                @csrf
                @method('PUT')
                @include('provider.services.itinerary._departure_form', [
                    'departure'   => $departure,
                    'submitLabel' => __('messages.edit_departure'),
                ])
            </form>
        </div>
    @endif
</div>
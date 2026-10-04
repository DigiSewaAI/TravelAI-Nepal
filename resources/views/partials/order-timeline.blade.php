<div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.order_tracking_timeline') }}</h3>

    <ol class="relative border-l-2 border-gray-200 ml-3">
        @forelse($order->statusHistory as $entry)
            <li class="mb-6 ml-6">
                <span class="absolute -left-3 flex items-center justify-center w-6 h-6
                    @if($entry->to_status === 'delivered') bg-green-500
                    @elseif($entry->to_status === 'confirmed') bg-blue-500
                    @elseif($entry->to_status === 'shipped') bg-indigo-500
                    @elseif($entry->to_status === 'cancelled') bg-red-500
                    @else bg-gray-400 @endif
                    rounded-full ring-4 ring-white">
                    <i class="fas fa-check text-white text-xs"></i>
                </span>
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-semibold text-gray-800">
                        {{ __('messages.order_status_' . $entry->to_status) }}
                    </h4>
                    @if($entry->from_status && $entry->from_status !== $entry->to_status)
                        <span class="text-xs text-gray-400">
                            ({{ __('messages.order_status_' . $entry->from_status) }} →)
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                    <span>{{ $entry->created_at->diffForHumans() }}</span>
                    <span>·</span>
                    <span class="capitalize">{{ $entry->changed_by_role }}</span>
                </div>
                @if($entry->note)
                    <p class="text-sm text-gray-600 mt-1">{{ $entry->note }}</p>
                @endif
            </li>
        @empty
            <li class="ml-6 text-sm text-gray-500">{{ __('messages.order_tracking_empty') }}</li>
        @endforelse
    </ol>
</div>
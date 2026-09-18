<div class="flex items-start gap-2 py-2 border-b border-gray-100 last:border-b-0"
     data-item-id="{{ $item->id }}"
     data-parent-day="{{ $day->id }}">

    {{-- Move buttons --}}
    <div class="flex flex-col">
        <button type="button" onclick="moveItem({{ $day->id }}, {{ $item->id }}, 'up')"
                class="text-xs text-gray-400 hover:text-gray-700 px-1 leading-none">▲</button>
        <button type="button" onclick="moveItem({{ $day->id }}, {{ $item->id }}, 'down')"
                class="text-xs text-gray-400 hover:text-gray-700 px-1 leading-none">▼</button>
    </div>

    {{-- Update form --}}
    <form method="POST"
          action="{{ route('provider.services.itinerary.items.update', [$service, $item]) }}"
          class="flex-1 flex gap-2 items-center">
        @csrf
        @method('PUT')

        <input type="text" name="title" value="{{ $item->title }}" required maxlength="255"
               class="flex-1 px-2 py-1 border rounded text-sm">

        <select name="time_of_day" class="px-2 py-1 border rounded text-sm">
            <option value="">—</option>
            <option value="morning" {{ $item->time_of_day === 'morning' ? 'selected' : '' }}>Morning</option>
            <option value="afternoon" {{ $item->time_of_day === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
            <option value="evening" {{ $item->time_of_day === 'evening' ? 'selected' : '' }}>Evening</option>
        </select>

        <label class="flex items-center text-xs text-gray-600">
            <input type="hidden" name="is_optional" value="0">
            <input type="checkbox" name="is_optional" value="1"
                   {{ $item->is_optional ? 'checked' : '' }} class="mr-1"> Opt
        </label>

        <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs px-2">Save</button>
    </form>

    {{-- Delete form --}}
    <form method="POST"
          action="{{ route('provider.services.itinerary.items.destroy', [$service, $item]) }}"
          onsubmit="return confirm('Delete this item?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:text-red-700 text-xs px-1">✕</button>
    </form>
</div>
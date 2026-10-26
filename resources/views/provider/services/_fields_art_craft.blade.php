<div class="category-fields hidden mt-4 p-4 border rounded-lg bg-gray-50" data-slug="art-craft">
    <h3 class="font-bold mb-3 text-lg">{{ __('messages.art_craft_details') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.craft_type') }}</label>
            <input type="text" name="craft_type"
                   value="{{ old('craft_type', $service?->artCraftDetail?->craft_type ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.materials') }}</label>
            <input type="text" name="materials"
                   value="{{ old('materials', $service?->artCraftDetail?->materials ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.artist_name') }}</label>
            <input type="text" name="artist_name"
                   value="{{ old('artist_name', $service?->artCraftDetail?->artist_name ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.dimensions') }}</label>
            <input type="text" name="dimensions"
                   value="{{ old('dimensions', $service?->artCraftDetail?->dimensions ?? '') }}"
                   class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. 20x30 cm">
        </div>
        <div class="md:col-span-2 flex items-center gap-2 mt-2">
            <input type="checkbox" name="is_handmade" value="1" id="is_handmade"
                   {{ old('is_handmade', $service?->artCraftDetail?->is_handmade ?? false) ? 'checked' : '' }}
                   class="rounded">
            <label for="is_handmade" class="text-sm font-medium">{{ __('messages.is_handmade') }}</label>
        </div>
    </div>
</div>
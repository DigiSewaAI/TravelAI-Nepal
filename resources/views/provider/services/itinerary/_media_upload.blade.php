<form method="POST"
      action="{{ route('provider.services.itinerary.media.store', [$service, $day]) }}"
      enctype="multipart/form-data"
      class="mt-3 flex flex-wrap gap-2 items-center">
    @csrf

    <input type="file" name="files[]" multiple
           accept=".jpg,.jpeg,.png,.webp,.mp4"
           required
           class="text-xs flex-1">

    <select name="media_type" required class="px-2 py-1 border rounded text-xs">
        <option value="image">Image</option>
        <option value="video">Video</option>
    </select>

    <input type="text" name="alt_text" placeholder="Alt text (optional)" maxlength="255"
           class="px-2 py-1 border rounded text-xs flex-1">

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded">
        Upload
    </button>
</form>
<p class="text-xs text-gray-400 mt-1">Max 10 files, 25 MB each. JPG/PNG/WebP/MP4 only.</p>
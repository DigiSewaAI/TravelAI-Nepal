<div class="relative group border rounded overflow-hidden bg-gray-50">
    @if($media->media_type === 'image')
        <img src="{{ asset('storage/' . $media->file_path) }}"
             alt="{{ $media->alt_text ?? 'Day media' }}"
             class="w-full h-20 object-cover">
    @else
        <div class="w-full h-20 flex items-center justify-center bg-gray-200 text-xs text-gray-600">
            🎬 Video
        </div>
    @endif

    {{-- Delete overlay --}}
    <form method="POST"
          action="{{ route('provider.services.itinerary.media.destroy', [$service, $media]) }}"
          class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition"
          onsubmit="return confirm('Delete this media?');">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
            ✕
        </button>
    </form>
</div>
<div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-share-alt text-blue-600"></i> {{ __('messages.share_trip_title') }}
    </h3>

    <div class="mt-4 flex flex-wrap gap-3 items-center">
        <span class="text-sm text-gray-500">{{ __('messages.share_visibility_label') }}</span>
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($booking->visibility === 'private') bg-gray-200 text-gray-700
            @elseif($booking->visibility === 'link') bg-yellow-100 text-yellow-800
            @else bg-green-100 text-green-800 @endif">
            @if($booking->visibility === 'private') {{ __('messages.share_status_private') }}
            @elseif($booking->visibility === 'link') {{ __('messages.share_status_link') }}
            @else {{ __('messages.share_status_public') }} @endif
        </span>
    </div>

    <form action="{{ route('traveler.share.toggle', $booking) }}" method="POST" class="mt-3 flex flex-wrap gap-2">
        @csrf
        <select name="visibility" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="private" {{ $booking->visibility === 'private' ? 'selected' : '' }}>{{ __('messages.share_status_private') }}</option>
            <option value="link" {{ $booking->visibility === 'link' ? 'selected' : '' }}>{{ __('messages.share_status_link') }}</option>
            <option value="public" {{ $booking->visibility === 'public' ? 'selected' : '' }}>{{ __('messages.share_status_public') }}</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">{{ __('messages.share_update_btn') }}</button>
    </form>

    @if($booking->isShareable())
        <div class="mt-3 flex flex-wrap gap-2">
            <input type="text" readonly value="{{ $booking->share_url }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 min-w-[200px]" id="shareUrlInput">
            <button onclick="navigator.clipboard.writeText(document.getElementById('shareUrlInput').value).then(()=>alert('{{ __('messages.share_copy_alert') }}'))" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800">{{ __('messages.share_copy_btn') }}</button>
            <form action="{{ route('traveler.share.revoke', $booking) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">{{ __('messages.share_revoke_btn') }}</button>
            </form>
            <form action="{{ route('traveler.share.regenerate', $booking) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">{{ __('messages.share_regenerate_btn') }}</button>
            </form>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <button onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('{{ $booking->share_url }}'), '_blank')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">{{ __('messages.share_facebook_btn') }}</button>
            <button onclick="window.open('https://wa.me/?text='+encodeURIComponent('{{ __('messages.share_whatsapp_message') }}{{ $booking->share_url }}'), '_blank')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">{{ __('messages.share_whatsapp_btn') }}</button>
        </div>
    @else
        <p class="text-sm text-gray-400 mt-3">{{ __('messages.share_enable_hint') }}</p>
    @endif
</div>
@extends($provider_layout ?? 'layouts.provider')
@section('title', __('messages.sos_alerts_title'))
@section('content')
@if($activeCount > 0)
<div id="sosEnableBanner" onclick="enableSosAudio()" style="position:fixed;top:80px;right:20px;background:#dc2626;color:#fff;padding:14px 20px;border-radius:12px;font-weight:800;z-index:99999;cursor:pointer;font-size:13px;max-width:320px;box-shadow:0 6px 20px rgba(220,38,38,.5);">
    <div style="font-size:15px;margin-bottom:4px;">Active SOS Alert</div>
    <div style="font-size:11px;">Click anywhere to enable sound</div>
</div>
@endif

<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
                {{ __('messages.sos_alerts_title') }}
                @if($activeCount > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $activeCount }}</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.sos_alerts_subtitle') }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(['all' => __('messages.sos_alerts_tab_all'), 'active' => __('messages.sos_alerts_tab_active'), 'resolved' => __('messages.sos_alerts_tab_resolved')] as $key => $label)
            <a href="{{ route('provider.sos-alerts.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $status === $key ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($alerts->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($alerts as $alert)
                    @php $isActive = in_array($alert->status, ['pending', 'sent']); @endphp
                    <a href="{{ route('provider.sos-alerts.show', $alert) }}" class="block hover:bg-gray-50 transition">
                        <div class="p-5 flex flex-wrap items-start gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 {{ $isActive ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                <i class="fas {{ $isActive ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $alert->traveler->name ?? 'Unknown Traveler' }}</h3>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $isActive ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $isActive ? 'Active' : 'Resolved' }}
                                    </span>
                                </div>
                                @if($alert->message)
                                    <p class="text-sm text-gray-600 mt-1">"{{ $alert->message }}"</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                                    <span><i class="fas fa-map-marker-alt text-red-500"></i> {{ $alert->latitude }}, {{ $alert->longitude }}</span>
                                    <span><i class="far fa-clock"></i> {{ $alert->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 self-center"></i>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                {{ $alerts->withQueryString()->links() }}
            </div>
        @else
            <div class="p-10 text-center">
                <i class="fas fa-check-circle text-5xl text-green-400 mb-4"></i>
                <h2 class="text-xl font-bold text-gray-900">No SOS alerts</h2>
                <p class="text-sm text-gray-500 mt-2">You have no emergency alerts at this time.</p>
            </div>
        @endif
    </div>
</div>

<script>
function playAlertSiren() {
    try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.type = 'sawtooth';
        var t = ctx.currentTime;
        osc.frequency.setValueAtTime(600, t);
        gain.gain.setValueAtTime(0.25, t);
        for (var i = 0; i < 4; i++) {
            osc.frequency.linearRampToValueAtTime(1100, t + 0.3 + i*0.6);
            osc.frequency.linearRampToValueAtTime(600, t + 0.6 + i*0.6);
        }
        gain.gain.linearRampToValueAtTime(0.01, t + 2.4);
        osc.start(t); osc.stop(t + 2.5);
    } catch (e) { console.warn('Siren:', e); }
}

var sosAudioEnabled = false;
function enableSosAudio() {
    if (sosAudioEnabled) return;
    try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        ctx.resume().then(function() {
            sosAudioEnabled = true;
            localStorage.setItem('sos_audio_enabled', 'true');
            var b = document.getElementById('sosEnableBanner');
            if (b) b.remove();
            playAlertSiren();
        });
    } catch (e) {}
}

if (localStorage.getItem('sos_audio_enabled') === 'true') {
    sosAudioEnabled = true;
    var b = document.getElementById('sosEnableBanner');
    if (b) b.style.display = 'none';
}

['click', 'keydown', 'touchstart'].forEach(function(evt) {
    document.addEventListener(evt, enableSosAudio, { once: true, passive: true });
});
</script>
@endsection
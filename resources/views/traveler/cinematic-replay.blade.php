@extends('layouts.public')

@section('title', 'Cinematic Journey Replay - TravelAI Nepal')

@section('content')
<div id="cinematic-replay" class="fixed inset-0 bg-black z-50 overflow-hidden">
    <div id="scene-container" class="w-full h-full relative">
        @if(count($data['scenes']) > 0)
            @foreach($data['scenes'] as $index => $scene)
                <div class="scene absolute inset-0 transition-opacity duration-1000 {{ $loop->first ? 'opacity-100' : 'opacity-0' }}" data-index="{{ $index }}">
                    {{-- Background media (image or video) --}}
                    @if(!empty($scene['media']))
                        @php
                            $primary = $scene['media'][0];
                        @endphp
                        @if($primary['type'] === 'image')
                            <div class="w-full h-full bg-cover bg-center transition-transform duration-10000" style="background-image: url('{{ $primary['url'] }}'); transform: scale(1.1);"></div>
                        @else
                            <video src="{{ $primary['url'] }}" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
                        @endif
                    @else
                        {{-- Elegant location card --}}
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-900 to-black">
                            <div class="text-center text-white px-4">
                                <div class="text-6xl mb-4">📍</div>
                                <h2 class="text-4xl md:text-6xl font-bold">{{ $scene['checkpoint'] }}</h2>
                                <p class="text-xl text-gray-300 mt-2">{{ $scene['altitude'] ? $scene['altitude'].'m' : '' }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Overlay: destination info --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/30 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                        <div class="max-w-4xl mx-auto">
                            <div class="text-sm uppercase tracking-widest text-blue-300">Checkpoint {{ $scene['index'] }}</div>
                            <h2 class="text-3xl md:text-5xl font-bold">{{ $scene['checkpoint'] }}</h2>
                            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-300">
                                @if($scene['scanned_at'])
                                    <span>{{ $scene['scanned_at']->format('F j, Y') }}</span>
                                    <span>{{ $scene['scanned_at']->format('g:i A') }}</span>
                                @endif
                                @if($scene['altitude'])
                                    <span>{{ $scene['altitude'] }} m</span>
                                @endif
                            </div>
                            @if(empty($scene['media']) || $scene['media'][0]['source'] === 'fallback')
                                <div class="mt-2 text-xs text-gray-400">📷 Image: TravelAI Nepal</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="w-full h-full flex items-center justify-center bg-black text-white">
                <p>No journey data found.</p>
            </div>
        @endif
    </div>

    {{-- Controls --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex gap-4">
        <button id="prev-btn" class="bg-white/20 backdrop-blur-sm p-3 rounded-full hover:bg-white/30 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button id="play-btn" class="bg-white/20 backdrop-blur-sm p-3 rounded-full hover:bg-white/30 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        <button id="next-btn" class="bg-white/20 backdrop-blur-sm p-3 rounded-full hover:bg-white/30 transition">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
    <button id="exit-btn" class="absolute top-4 right-4 z-20 text-white/70 hover:text-white text-2xl">&times;</button>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scenes = document.querySelectorAll('.scene');
        let current = 0;
        let total = scenes.length;
        let interval = null;

        function showScene(index) {
            scenes.forEach((scene, i) => {
                scene.classList.toggle('opacity-0', i !== index);
                scene.classList.toggle('opacity-100', i === index);
            });
            current = index;
        }

        function nextScene() {
            const next = (current + 1) % total;
            showScene(next);
        }

        function prevScene() {
            const prev = (current - 1 + total) % total;
            showScene(prev);
        }

        function togglePlay() {
            if (interval) {
                clearInterval(interval);
                interval = null;
                document.getElementById('play-btn').innerHTML = `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            } else {
                interval = setInterval(nextScene, 4000);
                document.getElementById('play-btn').innerHTML = `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            }
        }

        document.getElementById('next-btn').addEventListener('click', nextScene);
        document.getElementById('prev-btn').addEventListener('click', prevScene);
        document.getElementById('play-btn').addEventListener('click', togglePlay);
        document.getElementById('exit-btn').addEventListener('click', function() {
            window.location.href = "{{ route('traveler.journey-replay') }}";
        });

        // Keyboard controls
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight') nextScene();
            else if (e.key === 'ArrowLeft') prevScene();
            else if (e.key === ' ') { e.preventDefault(); togglePlay(); }
            else if (e.key === 'Escape') document.getElementById('exit-btn').click();
        });

        // Auto-play on load
        setTimeout(togglePlay, 1000);
    });
</script>
@endpush
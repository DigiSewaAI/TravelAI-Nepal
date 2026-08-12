@extends('layouts.public')

@section('title', 'Careers | TravelAI Nepal')
@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Join Our Team</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Be part of Nepal's first AI-native tourism ecosystem. Build the future of travel with us.
    </p>
</div>

<div class="max-w-5xl mx-auto px-4 py-12">

    {{-- ========== WHY JOIN US ========== --}}
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-rocket text-blue-600 text-3xl"></i>
            <h3 class="font-bold text-gray-800 mt-2">Innovative Culture</h3>
            <p class="text-sm text-gray-500">Work with cutting-edge AI technology and modern tools.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-globe-asia text-blue-600 text-3xl"></i>
            <h3 class="font-bold text-gray-800 mt-2">Global Impact</h3>
            <p class="text-sm text-gray-500">Shape Nepal's tourism future and reach travelers worldwide.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md border text-center hover:shadow-lg transition">
            <i class="fas fa-users text-blue-600 text-3xl"></i>
            <h3 class="font-bold text-gray-800 mt-2">Great Team</h3>
            <p class="text-sm text-gray-500">Work with passionate, talented people who love Nepal.</p>
        </div>
    </div>

    {{-- ========== JOB LISTINGS ========== --}}
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Open Positions</h2>
    <p class="text-gray-500 mb-8">Join us in transforming Nepal's tourism industry.</p>

    <div class="space-y-6">
        @forelse($jobs ?? [] as $job)
            <div class="bg-white rounded-xl shadow-md border p-6 hover:shadow-xl transition group">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">
                            {{ $job['title'] }}
                        </h3>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $job['location'] }}
                            </span>
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">
                                <i class="fas fa-briefcase mr-1"></i>{{ $job['type'] ?? 'Full-time' }}
                            </span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> Active
                            </span>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap">
                        <i class="far fa-clock mr-1"></i> Posted {{ $job['posted_at'] ?? 'Recently' }}
                    </span>
                </div>

                <p class="text-gray-600 mt-3">{{ $job['description'] }}</p>

                <div class="flex justify-end mt-4 pt-3 border-t border-gray-100">
                    <a href="mailto:careers@travelai.com?subject=Application%20for%20{{ urlencode($job['title']) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg text-sm transition shadow-sm hover:shadow-md">
                        Apply Now <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-xl border">
                <i class="fas fa-briefcase text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600">No Open Positions</h3>
                <p class="text-gray-400 mt-2">Check back soon for new opportunities.</p>
            </div>
        @endforelse
    </div>

    {{-- ========== SPONTANEOUS APPLICATION ========== --}}
    <div class="mt-12 bg-gray-50 rounded-2xl p-8 border text-center">
        <h3 class="text-xl font-bold text-gray-800">Don't see the right role?</h3>
        <p class="text-gray-500 mt-1">Send us your CV anyway – we're always looking for great talent.</p>
        <a href="mailto:careers@travelai.com?subject=Spontaneous%20Application" 
           class="inline-block mt-4 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold px-6 py-2 rounded-lg transition">
            Send Open Application →
        </a>
    </div>
</div>

@endsection
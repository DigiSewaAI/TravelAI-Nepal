<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- ========== Homepage ========== --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- ========== Static pages ========== --}}
    @foreach([
        '/features'      => ['0.8', 'weekly'],
        '/explore'       => ['0.9', 'daily'],
        '/pricing'       => ['0.8', 'weekly'],
        '/how-it-works'  => ['0.7', 'monthly'],
        '/providers'     => ['0.8', 'daily'],
        '/about'         => ['0.6', 'monthly'],
        '/careers'       => ['0.5', 'monthly'],
        '/press'         => ['0.5', 'monthly'],
        '/contact'       => ['0.6', 'monthly'],
        '/travel-safety' => ['0.7', 'daily'],
        '/privacy'       => ['0.3', 'yearly'],
        '/terms'         => ['0.3', 'yearly'],
        '/gdpr'          => ['0.3', 'yearly'],
    ] as $path => $meta)
    <url>
        <loc>{{ url($path) }}</loc>
        <changefreq>{{ $meta[1] }}</changefreq>
        <priority>{{ $meta[0] }}</priority>
    </url>
    @endforeach

    {{-- ========== Services (individual pages) ========== --}}
    @foreach($services as $service)
    <url>
        <loc>{{ route('public.services.show', $service->slug) }}</loc>
        @if($service->updated_at)
        <lastmod>{{ $service->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- ========== Providers (individual pages) ========== --}}
    @foreach($providers as $provider)
    <url>
        <loc>{{ route('public.providers.show', $provider->slug ?? $provider->id) }}</loc>
        @if($provider->updated_at)
        <lastmod>{{ $provider->updated_at->toAtomString() }}</lastmod>
        @endif
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

</urlset>
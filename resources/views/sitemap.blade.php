<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('public.services.index') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('pages.pricing') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.providers.index') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <priority>0.8</priority>
    </url>
    @foreach($services as $service)
    <url>
        <loc>{{ route('public.services.show', $service->slug) }}</loc>
        <lastmod>{{ $service->updated_at->format('Y-m-d') }}</lastmod>
        <priority>0.7</priority>
    </url>
    @endforeach
    @foreach($providers as $provider)
    <url>
        <loc>{{ route('public.providers.show', $provider->slug ?? $provider->id) }}</loc>
        <lastmod>{{ $provider->updated_at->format('Y-m-d') }}</lastmod>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
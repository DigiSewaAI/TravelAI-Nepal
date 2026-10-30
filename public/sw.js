// Service Worker for TravelAI Nepal PWA
// Strategy: NETWORK-FIRST for HTML navigation, CACHE-FIRST for assets
const CACHE_NAME = 'travelai-v3';
const ASSETS = [
  '/manifest.json',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Skip cross-origin (CDN, fonts)
  if (url.origin !== self.location.origin) return;

  // Skip dynamic/auth/admin/provider/API endpoints - always network
  if (
    url.pathname.startsWith('/api/') ||
    url.pathname.startsWith('/livewire/') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/logout') ||
    url.pathname.startsWith('/register') ||
    url.pathname.startsWith('/admin/') ||
    url.pathname.startsWith('/provider/') ||
    url.pathname.startsWith('/traveler/') ||
    url.pathname.startsWith('/lang/') ||
    url.pathname.startsWith('/currency/') ||
    url.pathname.startsWith('/cart') ||
    url.pathname.startsWith('/checkout')
  ) {
    return;
  }

  // HTML navigation → NETWORK-FIRST
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cached) => {
            if (cached) return cached;
            return new Response(
              '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Offline - TravelAI Nepal</title></head><body style="font-family:system-ui;text-align:center;padding:50px;background:#f5f7fb"><h1 style="color:#2563eb">You are offline</h1><p style="color:#64748b">Please check your internet connection and try again.</p></body></html>',
              { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            );
          });
        })
    );
    return;
  }

  // Static assets → CACHE-FIRST
  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request).then((response) => {
        if (
          response.status === 200 &&
          response.type === 'basic' &&
          url.pathname.match(/\.(css|js|jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|eot|ico)$/i)
        ) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        }
        return response;
      }).catch(() => new Response('', { status: 503 }));
    })
  );
});
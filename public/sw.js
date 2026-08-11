// Service Worker for TravelAI Nepal PWA
const CACHE_NAME = 'travelai-v1';
const ASSETS = [
  '/',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js',
  // Add more static assets as needed
];

// Install Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(ASSETS);
      })
      .then(() => {
        self.skipWaiting();
      })
  );
});

// Activate Service Worker
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    })
  );
});

// Fetch Strategy: Cache first, fallback to network
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(() => {
          // Offline fallback - show a generic offline page
          return caches.match('/offline');
        });
      })
  );
});

// Sync for offline bookings (optional)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-bookings') {
    event.waitUntil(syncBookings());
  }
});

async function syncBookings() {
  // Implement background sync for bookings
  // This would require IndexedDB or localStorage to store pending bookings
  console.log('Syncing bookings...');
}
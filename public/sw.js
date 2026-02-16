/**
 * LeBonResto - Service Worker (PWA)
 * Cache les pages statiques + API pour une expérience hors-ligne
 */

const CACHE_NAME = 'lebonresto-v2';
const STATIC_ASSETS = [
    '/assets/js/wishlist.js',
];

// Install : pré-cache des assets statiques (resilient - skip failures)
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.all(
                STATIC_ASSETS.map((url) =>
                    cache.add(url).catch(() => console.warn('SW: skip cache', url))
                )
            );
        })
    );
    self.skipWaiting();
});

// Activate : nettoyage des anciens caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch : stratégie Network First avec fallback cache
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Ne pas cacher les requêtes POST ni les API mutations
    if (event.request.method !== 'GET') return;

    // Ne pas cacher les requêtes admin
    if (url.pathname.startsWith('/admin')) return;

    // API GET : Network first, cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, clone);
                    });
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Pages HTML : Network first, cache fallback
    if (event.request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, clone);
                    });
                    return response;
                })
                .catch(() => caches.match(event.request).then((cached) => {
                    return cached || new Response(
                        '<html><body style="font-family:sans-serif;text-align:center;padding:40px"><h1>Hors ligne</h1><p>Vous etes hors ligne. Verifiez votre connexion.</p></body></html>',
                        { headers: { 'Content-Type': 'text/html' } }
                    );
                }))
        );
        return;
    }

    // Assets statiques : Cache first, network fallback
    event.respondWith(
        caches.match(event.request).then((cached) => {
            return cached || fetch(event.request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, clone);
                });
                return response;
            });
        })
    );
});

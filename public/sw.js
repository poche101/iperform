const CACHE_NAME = 'iperform-v1';
const OFFLINE_URL = '/offline';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/offline',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css',
];

// Install — cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Non-critical if CDN assets fail during install
            });
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch strategy:
// - Navigation requests: network first, fall back to offline page
// - Static assets (CSS/JS/fonts): cache first
// - API/form requests: network only (never cache POST)
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Never intercept POST/PUT/DELETE — let Laravel handle CSRF properly
    if (request.method !== 'GET') return;

    // Navigation requests — network first, offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache successful navigation responses
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(request).then(cached => cached || caches.match(OFFLINE_URL))
                )
        );
        return;
    }

    // Static assets (CSS, JS, fonts, images) — cache first
    if (
        url.hostname.includes('cdn.jsdelivr.net') ||
        url.hostname.includes('cdn.tailwindcss.com') ||
        url.hostname.includes('unpkg.com') ||
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font' ||
        request.destination === 'image'
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Everything else — network first
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// Background sync for offline task logs (future enhancement placeholder)
self.addEventListener('sync', event => {
    if (event.tag === 'sync-tasks') {
        event.waitUntil(syncPendingTasks());
    }
});

async function syncPendingTasks() {
    // Placeholder for future offline task sync
    console.log('[iPerform SW] Background sync triggered');
}

// Push notifications (future enhancement placeholder)
self.addEventListener('push', event => {
    if (!event.data) return;
    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title || 'iPerform', {
            body: data.body || 'You have a new notification.',
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-72.png',
            data: { url: data.url || '/' },
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data?.url || '/')
    );
});

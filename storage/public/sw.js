const CACHE_NAME = 'afagh-bimeh-pwa-v2';
const PRECACHE_URLS = [
    '/my/login',
    '/manifest.json',
    '/images/icons/app_policyholders.svg',
    '/images/icons/app_admin.svg'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS).catch(() => {}))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/livewire')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

self.addEventListener('push', (event) => {
    const payload = event.data
        ? event.data.json()
        : { title: 'آفاق بیمه', body: 'یادآور سررسید قسط جدید', url: '/my/dashboard' };

    event.waitUntil(
        self.registration.showNotification(payload.title || 'آفاق بیمه', {
            body: payload.body || '',
            dir: 'rtl',
            lang: 'fa-IR',
            icon: '/images/icons/app_notifications.svg',
            data: { url: payload.url || '/my/dashboard' }
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/my/dashboard';
    event.waitUntil(clients.openWindow(targetUrl));
});
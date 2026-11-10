const CACHE_NAME = 'afagh-bimeh-pwa-v3';
const FONT_CACHE_NAME = 'afagh-bimeh-fonts-v1';

const PRECACHE_URLS = [
    '/my/login',
    '/manifest.json',
    'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css'
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
            Promise.all(
                keys
                    .filter((k) => k !== CACHE_NAME && k !== FONT_CACHE_NAME)
                    .map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // ۱. کش دائمی فونت‌های وزیرمتن (CDN) برای جلوگیری از بهم‌ریختگی فونت در اینترنت ضعیف یا آفلاین
    if (url.hostname.includes('cdn.jsdelivr.net') || url.pathname.endsWith('.woff2') || url.pathname.endsWith('.woff')) {
        event.respondWith(
            caches.open(FONT_CACHE_NAME).then((cache) =>
                cache.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return fetch(event.request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(event.request, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                })
            )
        );
        return;
    }

    // عدم مداخله در درخواست‌های پنل ادمین و Livewire
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/livewire')) {
        return;
    }

    // ۲. استراتژی Network-First با پشتیبانی آفلاین برای صفحات پرتال مشتریان
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
    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/my/dashboard';
    event.waitUntil(clients.openWindow(targetUrl));
});
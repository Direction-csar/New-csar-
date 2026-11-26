/**
 * Service Worker for CSAR Platform
 * Implements caching strategies for optimal performance
 */

const CACHE_NAME = 'csar-v1.0.1';
const STATIC_CACHE_NAME = 'csar-static-v1.0.1';
const DYNAMIC_CACHE_NAME = 'csar-dynamic-v1.0.1';

// Resources to cache immediately
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/css/responsive-optimizations.css',
    '/js/app.js',
    '/js/responsive-charts.js',
    '/js/performance-optimizer.js',
    '/images/logos/LOGO CSAR vectoriel-01.png',
    '/images/csar-hero-image.jpg',
    '/images/csar-hero-mobile.jpg',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/api/stats',
    '/api/warehouses',
    '/api/requests'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Install event');
    
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Service Worker: Static assets cached');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Error caching static assets', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activate event');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE_NAME && cacheName !== DYNAMIC_CACHE_NAME) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Old caches cleaned up');
                return self.clients.claim();
            })
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Pages HTML avec formulaires/jeton CSRF : jamais servies depuis le cache
    // (un jeton périmé provoque « 419 Page Expired »).
    if (isHTMLRequest(request) || url.pathname.includes('login') || url.pathname.includes('logout')) {
        return;
    }

    // Handle different types of requests
    if (isStaticAsset(request)) {
        event.respondWith(cacheFirst(request));
    } else if (isAPIRequest(request)) {
        event.respondWith(networkFirst(request));
    } else if (isHTMLRequest(request)) {
        event.respondWith(staleWhileRevalidate(request));
    } else {
        event.respondWith(networkFirst(request));
    }
});

// Cache first strategy for static assets
async function cacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.error('Cache first strategy failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy for API requests
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        return new Response('Offline', { status: 503 });
    }
}

// Stale while revalidate strategy for HTML pages
async function staleWhileRevalidate(request) {
    const cache = await caches.open(DYNAMIC_CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(error => {
        console.error('Network request failed:', error);
        return cachedResponse;
    });
    
    return cachedResponse || fetchPromise;
}

// Helper functions
function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/);
}

function isAPIRequest(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/') || url.pathname.startsWith('/dg/');
}

function isHTMLRequest(request) {
    return (request.headers.get('accept') || '').includes('text/html');
}

// Background sync for offline form submissions
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    try {
        // Handle offline form submissions
        const formData = await getStoredFormData();
        if (formData) {
            await submitFormData(formData);
            await clearStoredFormData();
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Push notifications
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/images/logos/LOGO CSAR vectoriel-01.png',
            badge: '/images/logos/LOGO CSAR vectoriel-01.png',
            vibrate: [200, 100, 200],
            data: data.data,
            actions: [
                {
                    action: 'open',
                    title: 'Ouvrir',
                    icon: '/images/icons/open.png'
                },
                {
                    action: 'close',
                    title: 'Fermer',
                    icon: '/images/icons/close.png'
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

// Message handler for communication with main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
});

// Helper functions for background sync
async function getStoredFormData() {
    // Implementation to retrieve stored form data
    // This would typically use IndexedDB
    return null;
}

async function submitFormData(formData) {
    // Implementation to submit form data to server
    // This would typically make a fetch request
}

async function clearStoredFormData() {
    // Implementation to clear stored form data
    // This would typically clear IndexedDB
}

// Error handling
self.addEventListener('error', event => {
    console.error('Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', event => {
    console.error('Service Worker unhandled rejection:', event.reason);
});


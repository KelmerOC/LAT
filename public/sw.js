const CACHE_VERSION = 'lat-compacto-v2';
const STATIC_ASSETS = [
  '/css/lat-design-system.css',
  '/css/lat-dashboard.css',
  '/js/lat-i18n.js',
  '/js/lat-offline.js',
  '/js/lat-qr.js',
  '/js/chart.umd.min.js',
  '/fonts/inter-var-latin.woff2',
  '/fonts/gloock-400-latin.woff2',
  '/brand/isotipo_queso_frasco.svg',
  '/brand/logo_lat_azul.svg',
  '/brand/hero_pasturas.jpg',
  '/brand/hero_establo.jpg',
  '/brand/hero_quesos.jpg',
  '/brand/hero_tina.jpg',
  '/favicon.svg',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS)).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Nunca cachear descargas Excel ni endpoints de acceso QR
  if (url.pathname.includes('exportar') || url.pathname.includes('reportes-excel') || url.pathname.startsWith('/acceso-qr')) {
    return;
  }

  // Navegación HTML (/panel, /login, /): Network-First con respaldo en caché para operar sin internet
  if (req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html')) {
    event.respondWith(
      fetch(req)
        .then((res) => {
          if (res && res.status === 200) {
            const copy = res.clone();
            caches.open(CACHE_VERSION).then((cache) => cache.put(req, copy)).catch(() => {});
          }
          return res;
        })
        .catch(() => caches.match(req).then((cached) => cached || caches.match('/panel')))
    );
    return;
  }

  // Assets locales estáticos: Cache-First con actualización silenciosa
  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;
      return fetch(req).then((res) => {
        if (res && res.status === 200) {
          const copy = res.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(req, copy)).catch(() => {});
        }
        return res;
      });
    })
  );
});

const CACHE = 'fr24-v1';
const STATIC = [
  'assets/css/app.css',
  'assets/js/app.js',
  'assets/js/idb.js',
  'assets/js/offline.js',
  'assets/js/interview.js',
  'assets/js/vendor/chart.min.js',
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(STATIC)));
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // Never intercept API calls or non-GET
  if (request.method !== 'GET') return;
  if (url.pathname.includes('/api/')) return;
  if (url.origin !== self.location.origin) return;

  const isStatic = /\.(css|js|png|svg|ico|woff2?)$/.test(url.pathname);

  e.respondWith(
    isStatic
      ? caches.match(request).then(c => c || fetchAndCache(request))
      : fetchAndCache(request).catch(() => caches.match(request))
  );
});

async function fetchAndCache(req) {
  const res = await fetch(req);
  if (res.ok) {
    const cache = await caches.open(CACHE);
    cache.put(req, res.clone());
  }
  return res;
}

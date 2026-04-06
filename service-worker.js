const CACHE_NAME = 'santiye-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/dashboard.php',
  '/header.php',
  '/footer.php',
  '/css/bootstrap.min.css',
  '/js/bootstrap.bundle.min.js'
];

// Install SW
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

// Activate SW
self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

// Fetch
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
      .catch(() => caches.match('/offline.html'))
  );
});
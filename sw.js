const CACHE_NAME = 'santiye-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/header.php',
  '/malzeme_ekle.php',
  '/arac_ekle.php',
  '/style.css',
  '/bootstrap.min.css',
  '/bootstrap.bundle.min.js'
];

self.addEventListener('install', e=>{
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', e=>{
  e.respondWith(
    caches.match(e.request).then(response => response || fetch(e.request))
  );
});
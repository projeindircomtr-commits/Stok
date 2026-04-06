const CACHE_NAME = 'stok-app-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/dashboard.php',
  '/malzemeler.php',
  '/araclar.php',
  '/kategoriler.php',
  '/lokasyon.php',
  '/app.js',
  '/offline.html',
  '/manifest.json'
];

// Service Worker kurulumu
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Cache oluşturuldu');
      return cache.addAll(urlsToCache);
    })
  );
  self.skipWaiting();
});

// Eski cache'leri sil
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Eski cache siliniyor:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Network first, cache fallback
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // API çağrıları için
  if (url.pathname.includes('/api.php')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          const clonedResponse = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(request, clonedResponse);
          });
          return response;
        })
        .catch(() => {
          // Offline ise cache'ten getir
          return caches.match(request).then(response => {
            return response || new Response(
              JSON.stringify({ status: 'offline', message: 'Çevrimdışı mod' }),
              { headers: { 'Content-Type': 'application/json' } }
            );
          });
        })
    );
  } else {
    // HTML/CSS/JS için cache first
    event.respondWith(
      caches.match(request).then(response => {
        return response || fetch(request).then(response => {
          const clonedResponse = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(request, clonedResponse);
          });
          return response;
        }).catch(() => {
          return caches.match('/offline.html');
        });
      })
    );
  }
});

// Background sync
self.addEventListener('sync', event => {
  if (event.tag === 'sync-offline-data') {
    event.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
  try {
    const db = await openDB();
    const pendingData = await getAllPendingData(db);
    
    for (const item of pendingData) {
      await fetch('/api.php?action=' + item.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item.data)
      });
      
      await deletePendingData(db, item.id);
    }
    
    // Tüm clients'a bildir
    self.clients.matchAll().then(clients => {
      clients.forEach(client => {
        client.postMessage({ type: 'SYNC_COMPLETE' });
      });
    });
  } catch (error) {
    console.error('Senkronizasyon hatası:', error);
  }
}

function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('StokAppDB', 1);
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
  });
}

function getAllPendingData(db) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['pending'], 'readonly');
    const store = transaction.objectStore('pending');
    const request = store.getAll();
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
  });
}

function deletePendingData(db, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['pending'], 'readwrite');
    const store = transaction.objectStore('pending');
    const request = store.delete(id);
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve();
  });
}
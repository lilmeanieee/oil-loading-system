/* Service worker for PA Fuel 118.
   - Pages you open are saved, so they open again without internet.
   - Styles, scripts and fonts are saved the first time they load.
   - Anything that sends data (POST) is never cached here. The page script (pwa.js) queues those. */
const VERSION = 'v2';
const SHELL = 'pafuel118-shell-' + VERSION;
const PAGES = 'pafuel118-pages-' + VERSION;
const ASSETS = 'pafuel118-assets-' + VERSION;
const KEEP = [SHELL, PAGES, ASSETS];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(SHELL)
      .then((cache) => cache.addAll(['/offline.html', '/manifest.json', '/icons/icon-192.png', '/icons/icon-512.png']))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => !KEEP.includes(k)).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'PRECACHE') event.waitUntil(precache(data.pages || [], data.assets || []));
  if (data.type === 'CLEAR_PAGES') event.waitUntil(caches.delete(PAGES));
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin === self.location.origin && url.pathname.startsWith('/pwa/')) return; // always live

  if (req.mode === 'navigate') {
    event.respondWith(networkFirst(req));
    return;
  }

  if (url.origin === self.location.origin) {
    if (url.pathname.startsWith('/build/')) { event.respondWith(cacheFirst(req)); return; }
    if (/\.(?:css|js|png|jpg|jpeg|svg|ico|woff2?|json)$/i.test(url.pathname) && url.pathname !== '/sw.js') {
      event.respondWith(staleWhileRevalidate(req));
    }
    return;
  }

  if (url.hostname === 'fonts.bunny.net') event.respondWith(staleWhileRevalidate(req));
});

function withTimeout(promise, ms) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('timeout')), ms);
    promise.then((v) => { clearTimeout(timer); resolve(v); }, (e) => { clearTimeout(timer); reject(e); });
  });
}

async function networkFirst(req) {
  const pages = await caches.open(PAGES);
  try {
    const res = await withTimeout(fetch(req), 6000);
    // A redirect (for example to the login page) must not be saved under the page that was asked for.
    if (res.ok && !res.redirected) pages.put(req, res.clone());
    return res;
  } catch (err) {
    const cached = await pages.match(req, { ignoreVary: true });
    if (cached) return cached;
    const shell = await caches.open(SHELL);
    return (await shell.match('/offline.html')) || new Response('You are offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
  }
}

async function cacheFirst(req) {
  const cache = await caches.open(ASSETS);
  const hit = await cache.match(req);
  if (hit) return hit;
  const res = await fetch(req);
  if (res.ok) cache.put(req, res.clone());
  return res;
}

async function staleWhileRevalidate(req) {
  const cache = await caches.open(ASSETS);
  const hit = await cache.match(req);
  const update = fetch(req).then((res) => { if (res.ok || res.type === 'opaque') cache.put(req, res.clone()); return res; }).catch(() => null);
  return hit || (await update) || new Response('', { status: 504 });
}

async function precache(pages, assets) {
  const pageCache = await caches.open(PAGES);
  await Promise.all(pages.map(async (u) => {
    try {
      const res = await fetch(u, { credentials: 'same-origin' });
      if (res.ok && !res.redirected) await pageCache.put(u, res);
    } catch (e) { /* offline, try again next time */ }
  }));
  const assetCache = await caches.open(ASSETS);
  await Promise.all(assets.map(async (u) => {
    try {
      if (await assetCache.match(u)) return;
      const same = new URL(u, self.location.href).origin === self.location.origin;
      const res = await fetch(u, same ? {} : { mode: 'no-cors' });
      await assetCache.put(u, res);
    } catch (e) { /* ignore */ }
  }));
}

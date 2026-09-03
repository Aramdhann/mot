// ponytail: passthrough service worker — exists only to satisfy Android/Chrome's install
// criteria (a registered SW with a fetch handler). Add offline caching here when asked.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});

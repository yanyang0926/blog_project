// 缓存名称和要缓存的资源
const CACHE_NAME = 'blog-cache-v3';
const urlsToCache = [
    '/',
    '/index.php',
    '/article.php',
    '/res/a.ttf',
    '/res/boke1.jpg',
    '/js/jq.js'
];

// 安装阶段缓存资源
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('正在缓存核心资源');
                return cache.addAll(urlsToCache.map(url => new Request(url, {credentials: 'same-origin'})));
            })
            .catch(err => {
                console.log('缓存失败:', err);
            })
    );
});

// 拦截请求使用缓存
self.addEventListener('fetch', event => {
    // 只缓存同源的GET请求
    if (event.request.method === 'GET' && 
        new URL(event.request.url).origin === self.location.origin) {
        
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    // 返回缓存或网络请求
                    return response || fetch(event.request)
                        .then(response => {
                            // 如果是字体或重要资源，添加到缓存
                            if (event.request.url.includes('/res/') || 
                                event.request.url.includes('/article.php')) {
                                const responseToCache = response.clone();
                                caches.open(CACHE_NAME)
                                    .then(cache => cache.put(event.request, responseToCache));
                            }
                            return response;
                        });
                })
                .catch(() => {
                    // 如果离线且没有缓存，返回备用页面
                    if (event.request.headers.get('accept').includes('text/html')) {
                        return caches.match('/offline.html');
                    }
                })
        );
    }
});

// 清理旧缓存
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('删除旧缓存:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

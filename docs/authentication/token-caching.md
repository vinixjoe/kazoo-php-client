# Token caching (PSR-16)

Use a PSR-16 cache to persist tokens between runs.

```php
use Kazoo\Util\FileCache;
$cache = new FileCache(__DIR__ . '/.cache');

$auth = new Kazoo\Auth\UserAuth(getenv('KAZOO_USER'), getenv('KAZOO_PASS'), getenv('KAZOO_REALM'));
$auth->setCache($cache, 7200); // seconds
```
When a 401 is received, the SDK invalidates the token and retries once.


## Choosing a cache backend via env

Use `CacheFactory::fromEnv()` to pick the best available cache at runtime:

```php
use Kazoo\Util\CacheFactory;
use Kazoo\Auth\UserAuth;

$cache = CacheFactory::fromEnv();
$auth  = (new UserAuth($user, $pass, $realm))
  ->setCache($cache, 3600); // 1 hour TTL
```

**Environment variables**

- `KAZOO_CACHE=filesystem|apcu|memcached` (default: `filesystem`)
- `KAZOO_CACHE_DIR=/var/cache/kazoo` (filesystem backend)
- `MEMCACHED_HOST=127.0.0.1`
- `MEMCACHED_PORT=11211`

If the requested backend isn't available, the factory **falls back** to the filesystem cache.

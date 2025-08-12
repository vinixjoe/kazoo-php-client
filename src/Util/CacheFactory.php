<?php
declare(strict_types=1);

namespace Kazoo\Util;

use Psr\SimpleCache\CacheInterface;

final class CacheFactory
{
    /**
     * Create a PSR-16 cache from environment variables.
     *
     * Env:
     *  - KAZOO_CACHE = filesystem|apcu|memcached (default: filesystem)
     *  - KAZOO_CACHE_DIR (filesystem path; default: sys_get_temp_dir() . '/kazoo-cache')
     *  - MEMCACHED_HOST (default: 127.0.0.1)
     *  - MEMCACHED_PORT (default: 11211)
     */
    public static function fromEnv(): CacheInterface
    {
        $backend = strtolower((string) getenv('KAZOO_CACHE') ?: 'filesystem');

        switch ($backend) {
            case 'apcu':
                if (function_exists('apcu_enabled') ? apcu_enabled() : function_exists('apcu_fetch')) {
                    return new ApcuCache('kazoo:');
                }
                // fallthrough to filesystem if APCu not available
                break;
            case 'memcached':
                if (class_exists('Memcached')) {
                    $host = getenv('MEMCACHED_HOST') ?: '127.0.0.1';
                    $port = (int) (getenv('MEMCACHED_PORT') ?: '11211');
                    $m = new \Memcached();
                    $servers = $m->getServerList();
                    if (empty($servers)) {
                        $m->addServer($host, $port);
                    }
                    return new MemcachedCache($m, 'kazoo:');
                }
                // fallthrough to filesystem if ext not available
                break;
        }

        $dir = getenv('KAZOO_CACHE_DIR') ?: (sys_get_temp_dir() . '/kazoo-cache');
        return new FileCache($dir);
    }
}

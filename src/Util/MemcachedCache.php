<?php
declare(strict_types=1);

namespace Kazoo\Util;

use Psr\SimpleCache\CacheInterface;
use Memcached;

final class MemcachedCache implements CacheInterface
{
    private Memcached $memcached;
    private string $prefix;

    public function __construct(Memcached $client, string $prefix = 'kazoo:')
    {
        $this->memcached = $client;
        $this->prefix = $prefix;
    }

    private function k(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get($key, $default = null)
    {
        $val = $this->memcached->get($this->k((string)$key));
        if ($val === false && $this->memcached->getResultCode() !== Memcached::RES_SUCCESS) {
            return $default;
        }
        return $val;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $ttlSecs = is_int($ttl) ? $ttl : 0;
        return $this->memcached->set($this->k((string)$key), $value, $ttlSecs);
    }

    public function delete($key): bool
    {
        return $this->memcached->delete($this->k((string)$key));
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $fullKeys = [];
        foreach ($keys as $k) {
            $fullKeys[] = $this->k((string)$k);
        }
        $vals = $this->memcached->getMulti($fullKeys);
        $result = [];
        foreach ($keys as $k) {
            $fk = $this->k((string)$k);
            $result[$k] = $vals[$fk] ?? $default;
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $ok = true;
        foreach ($values as $k => $v) {
            $ok = $this->set((string)$k, $v, $ttl) && $ok;
        }
        return $ok;
    }

    public function deleteMultiple($keys): bool
    {
        $ok = true;
        foreach ($keys as $k) {
            $ok = $this->delete((string)$k) && $ok;
        }
        return $ok;
    }

    public function has($key): bool
    {
        $this->get($key, null);
        return $this->memcached->getResultCode() === Memcached::RES_SUCCESS;
    }
}

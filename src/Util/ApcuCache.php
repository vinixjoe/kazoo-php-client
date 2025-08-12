<?php
declare(strict_types=1);

namespace Kazoo\Util;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class ApcuCache implements CacheInterface
{
    private string $prefix;

    public function __construct(string $prefix = 'kazoo:')
    {
        $this->prefix = $prefix;
    }

    private function k(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get($key, $default = null)
    {
        if (!\function_exists('apcu_fetch')) {
            return $default;
        }
        $success = false;
        $value = apcu_fetch($this->k((string)$key), $success);
        return $success ? $value : $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (!\function_exists('apcu_store')) {
            return false;
        }
        $ttlSecs = is_int($ttl) ? $ttl : 0;
        return (bool) apcu_store($this->k((string)$key), $value, $ttlSecs);
    }

    public function delete($key): bool
    {
        if (!\function_exists('apcu_delete')) {
            return false;
        }
        return (bool) apcu_delete($this->k((string)$key));
    }

    public function clear(): bool
    {
        if (!\function_exists('apcu_clear_cache')) {
            return false;
        }
        apcu_clear_cache();
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $k) {
            $result[$k] = $this->get((string)$k, $default);
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
        if (!\function_exists('apcu_exists')) {
            return false;
        }
        return (bool) apcu_exists($this->k((string)$key));
    }
}

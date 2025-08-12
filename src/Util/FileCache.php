<?php
declare(strict_types=1);

namespace Kazoo\Util;

use Psr\SimpleCache\CacheInterface;
use DateInterval;
use RuntimeException;

final class FileCache implements CacheInterface
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0777, true) && !is_dir($this->dir)) {
            throw new RuntimeException('Cannot create cache directory: ' . $this->dir);
        }
    }

    private function path(string $key): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $key);
        return $this->dir . DIRECTORY_SEPARATOR . $safe . '.json';
    }

    public function get($key, $default = null): mixed
    {
        $file = $this->path($key);
        if (!is_file($file)) return $default;
        $raw = @file_get_contents($file);
        if ($raw === false) return $default;
        $data = json_decode($raw, true);
        if (!is_array($data)) return $default;
        $exp = $data['exp'] ?? 0;
        if ($exp > 0 && time() >= $exp) {
            @unlink($file);
            return $default;
        }
        return $data['val'] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $file = $this->path($key);
        $exp = 0;
        if ($ttl instanceof DateInterval) {
            $exp = (new \DateTimeImmutable())->add($ttl)->getTimestamp();
        } elseif (is_int($ttl)) {
            $exp = time() + $ttl;
        } elseif ($ttl === null) {
            $exp = 0;
        }
        $payload = json_encode(['val' => $value, 'exp' => $exp]);
        return @file_put_contents($file, $payload) !== false;
    }

    public function delete($key): bool
    {
        $file = $this->path($key);
        return @unlink($file) || !file_exists($file);
    }

    public function clear(): bool
    {
        $ok = true;
        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*.json') as $f) {
            $ok = @unlink($f) && $ok;
        }
        return $ok;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $out = [];
        foreach ($keys as $k) { $out[$k] = $this->get($k, $default); }
        return $out;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $ok = true;
        foreach ($values as $k => $v) { $ok = $this->set($k, $v, $ttl) && $ok; }
        return $ok;
    }

    public function deleteMultiple($keys): bool
    {
        $ok = true;
        foreach ($keys as $k) { $ok = $this->delete($k) && $ok; }
        return $ok;
    }

    public function has($key): bool
    {
        $file = $this->path($key);
        if (!is_file($file)) return false;
        $raw = @file_get_contents($file);
        if ($raw === false) return false;
        $data = json_decode($raw, true);
        $exp = $data['exp'] ?? 0;
        if ($exp > 0 && time() >= $exp) {
            @unlink($file);
            return false;
        }
        return true;
    }
}

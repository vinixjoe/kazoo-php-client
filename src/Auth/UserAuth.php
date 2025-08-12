<?php
declare(strict_types=1);

namespace Kazoo\Auth;

use Kazoo\Exceptions\AuthException;

/**
 * Username/password/realm flow to obtain a token.
 * Stores token in memory for the duration of the SDK instance.
 */
final class UserAuth implements AuthInterface
{
    private ?string $token = null;
    private ?CacheInterface $cache = null;
    private int $cacheTtl = 3600;
    private bool $cacheEnabled = true;
    private ?\Psr\SimpleCache\CacheInterface $cache = null;
    private int $ttlSeconds = 7200; // default 2h

    public function __construct(
        private string $username,
        private string $password,
        private string $realm
    ) {}

    /** Attach a PSR-16 cache to persist tokens */
    public function setCache(?\Psr\SimpleCache\CacheInterface $cache, int $ttlSeconds = 7200): void
    {
        $this->cache = $cache;
        $this->ttlSeconds = $ttlSeconds;
    }

    /** Invalidate the in-memory and cached token */
    public function invalidate(): void
    {
        $this->token = null;
        if ($this->cache) {
            $this->cache->delete($this->cacheKey());
        }
    }


    
    /** Set PSR-16 cache for token storage */
    public function setCache(?CacheInterface $cache, int $ttlSeconds = 3600): self
    {
        $this->cache = $cache;
        $this->cacheTtl = $ttlSeconds;
        return $this;
    }

    /** Explicitly enable/disable token caching */
    public function setCacheEnabled(bool $enabled): self
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /** Convenience: disable caching */
    public function disableCache(): self
    {
        return $this->setCacheEnabled(false);
    }

    /** Convenience: enable caching */
    public function enableCache(): self
    {
        return $this->setCacheEnabled(true);
    }

public function authenticate(
        \Kazoo\SDK $sdk,
        string $method,
        string $path,
        array $headers,
        ?array $json
    ): array {
        $env = getenv('KAZOO_TOKEN_CACHE');
        if ($env !== false && in_array(strtolower((string)$env), ['off','0','false','no'], true)) {
            $this->cacheEnabled = false;
        }

        if ($this->token === null) {
            if ($this->cacheEnabled && $this->cache) {
                $key = 'kazoo_token:' . sha1(($sdk->baseUrl() ?? '') . '|' . $this->username . '|' . $this->realm);
                $cached = $this->cache->get($key);
                if (is_string($cached) && $cached !== '') {
                    $this->token = $cached;
                }
            }
            if ($this->token === null) {
            // Try cache first
            if ($this->cache) {
                $cached = $this->cache->get($this->cacheKey());
                if (is_string($cached) && $cached !== '') {
                    $this->token = $cached;
                }
            }
            if ($this->token === null) {
            if ($this->cacheEnabled && $this->cache) {
                $key = 'kazoo_token:' . sha1(($sdk->baseUrl() ?? '') . '|' . $this->username . '|' . $this->realm);
                $cached = $this->cache->get($key);
                if (is_string($cached) && $cached !== '') {
                    $this->token = $cached;
                }
            }
            if ($this->token === null) {
            $this->token = $this->login($sdk);
        }
                    if ($this->cacheEnabled && $this->cache && $this->token) {
                $key = 'kazoo_token:' . sha1(($sdk->baseUrl() ?? '') . '|' . $this->username . '|' . $this->realm);
                $this->cache->set($key, $this->token, $this->cacheTtl);
            }
        }
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    private function login(\Kazoo\SDK $sdk): string
    {
        $payload = [
            'data' => [
                'credentials' => [
                    'username' => $this->username,
                    'password' => $this->password,
                ],
                'realm' => $this->realm
            ]
        ];

        $response = $sdk->request('POST', '/v2/user_auth', [], $payload);
        $token = $response['data']['token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new AuthException('Login failed: token missing');
        }
        return $token;
    }
}

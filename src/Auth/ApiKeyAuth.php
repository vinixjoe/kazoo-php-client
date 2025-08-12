<?php
declare(strict_types=1);

namespace Kazoo\Auth;

/**
 * Simple API key header. Adjust header name if your deployment differs.
 */
final class ApiKeyAuth implements AuthInterface
{
    public function __construct(private string $apiKey, private string $headerName = 'X-Auth-Token')
    {
    }

    public function authenticate(
        \Kazoo\SDK $sdk,
        string $method,
        string $path,
        array $headers,
        ?array $json
    ): array {
        return [$this->headerName => $this->apiKey];
    }
}

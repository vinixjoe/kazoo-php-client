<?php
declare(strict_types=1);

namespace Kazoo\Auth;

final class TokenAuth implements AuthInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function authenticate(
        \Kazoo\SDK $sdk,
        string $method,
        string $path,
        array $headers,
        ?array $json
    ): array {
        return ['Authorization' => 'Bearer ' . $this->token];
    }
}

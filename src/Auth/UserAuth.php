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

    public function __construct(
        private string $username,
        private string $password,
        private string $realm
    ) {}

    public function authenticate(
        \Kazoo\SDK $sdk,
        string $method,
        string $path,
        array $headers,
        ?array $json
    ): array {
        if ($this->token === null) {
            $this->token = $this->login($sdk);
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

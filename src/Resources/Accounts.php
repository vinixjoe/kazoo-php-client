<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Accounts resource (subset; extend as needed).
 */
final class Accounts
{
    public function __construct(private SDK $sdk) {}

    /**
     * Fetch current account (aliased Kazoo `/v2/accounts` on token).
     * @return array<string,mixed>
     */
    public function current(): array
    {
        return $this->sdk->request('GET', '/v2/accounts');
    }

    /**
     * Create a new account.
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function create(array $data): array
    {
        return $this->sdk->request('PUT', '/v2/accounts', [], ['data' => $data]);
    }
}

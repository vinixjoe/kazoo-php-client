<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Users resource (subset; extend as needed).
 */
final class Users
{
    public function __construct(private SDK $sdk) {}

    /**
     * List users on the current account.
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/users' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /**
     * Get a single user by id.
     * @return array<string,mixed>
     */
    public function get(string $userId): array
    {
        return $this->sdk->request('GET', '/v2/users/' . rawurlencode($userId));
    }

    /** Iterate all items across pages */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/users', $query);
    }

}

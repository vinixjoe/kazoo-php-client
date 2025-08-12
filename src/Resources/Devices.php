<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Devices resource
 */
final class Devices
{
    public function __construct(private SDK $sdk) {}

    /**
     * List devices
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/devices' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /** Get device by id */
    public function get(string $deviceId): array
    {
        return $this->sdk->request('GET', '/v2/devices/' . rawurlencode($deviceId));
    }

    /** Create a device */
    public function create(array $data): array
    {
        return $this->sdk->request('PUT', '/v2/devices', [], ['data' => $data]);
    }

    /** Update a device */
    public function update(string $deviceId, array $data): array
    {
        return $this->sdk->request('POST', '/v2/devices/' . rawurlencode($deviceId), [], ['data' => $data]);
    }

    /** Delete a device */
    public function delete(string $deviceId): array
    {
        return $this->sdk->request('DELETE', '/v2/devices/' . rawurlencode($deviceId));
    }

    /** Iterate all items across pages */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/devices', $query);
    }

}

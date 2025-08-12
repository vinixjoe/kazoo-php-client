<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Callflows resource
 */
final class Callflows
{
    public function __construct(private SDK $sdk) {}

    /**
     * List callflows
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/callflows' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /** Get callflow by id */
    public function get(string $callflowId): array
    {
        return $this->sdk->request('GET', '/v2/callflows/' . rawurlencode($callflowId));
    }

    /** Create a callflow */
    public function create(array $data): array
    {
        return $this->sdk->request('PUT', '/v2/callflows', [], ['data' => $data]);
    }

    /** Update a callflow */
    public function update(string $callflowId, array $data): array
    {
        return $this->sdk->request('POST', '/v2/callflows/' . rawurlencode($callflowId), [], ['data' => $data]);
    }

    /** Delete a callflow */
    public function delete(string $callflowId): array
    {
        return $this->sdk->request('DELETE', '/v2/callflows/' . rawurlencode($callflowId));
    }

    /** Iterate all items across pages */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/callflows', $query);
    }

}

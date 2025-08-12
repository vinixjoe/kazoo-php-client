<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Channels resource (active calls)
 */
final class Channels
{
    public function __construct(private SDK $sdk) {}

    /**
     * List channels (single page). Use listAll() to auto-paginate when supported.
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/channels' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /** Iterate all channels across pages (if applicable). */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/channels', $query);
    }

    /** Get a single channel by ID */
    public function get(string $channelId): array
    {
        return $this->sdk->request('GET', '/v2/channels/' . rawurlencode($channelId));
    }

    /**
     * Hang up a channel.
     * Many deployments support DELETE on /channels/{id} to end the call.
     */
    public function hangup(string $channelId): array
    {
        return $this->sdk->request('DELETE', '/v2/channels/' . rawurlencode($channelId));
    }

    /**
     * Generic action endpoint for a channel (e.g., hold, unhold, mute, unmute, transfer).
     * Pass the appropriate action payload per your deployment.
     */
    public function action(string $channelId, array $data): array
    {
        return $this->sdk->request('POST', '/v2/channels/' . rawurlencode($channelId), [], ['data' => $data]);
    }
}

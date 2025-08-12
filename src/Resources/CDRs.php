<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * CDRs (Call Detail Records) resource
 */
final class CDRs
{
    public function __construct(private SDK $sdk) {}

    /**
     * List CDRs (single page). Use listAll() to iterate across pages.
     * Common filters include: created_from, created_to, caller_id_number, callee_id_number, direction, hangup_cause.
     * @param array<string,mixed> $query
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/cdrs' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /**
     * Iterate all CDRs across pages using next_start_key.
     * @param array<string,mixed> $query
     * @return iterable<array<string,mixed>>
     */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/cdrs', $query);
    }

    /**
     * Get a specific CDR by id (if supported by your Kazoo version).
     * @return array<string,mixed>
     */
    public function get(string $cdrId): array
    {
        return $this->sdk->request('GET', '/v2/cdrs/' . rawurlencode($cdrId));
    }
}

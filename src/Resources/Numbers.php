<?php
declare(strict_types=1);

namespace Kazoo\Resources;

use Kazoo\SDK;

/**
 * Numbers resource
 */
final class Numbers
{
    public function __construct(private SDK $sdk) {}

    /**
     * List numbers (single page). Use listAll() for automatic pagination.
     * @return iterable<array<string,mixed>>
     */
    public function list(array $query = []): iterable
    {
        $q = $query ? '?' . http_build_query($query) : '';
        $resp = $this->sdk->request('GET', '/v2/numbers' . $q);
        $items = $resp['data'] ?? [];
        if (!is_array($items)) return [];
        foreach ($items as $item) {
            if (is_array($item)) {
                yield $item;
            }
        }
    }

    /**
     * Iterate all numbers across pages using next_start_key.
     * @return iterable<array<string,mixed>>
     */
    public function listAll(array $query = []): iterable
    {
        yield from $this->sdk->paginate('/v2/numbers', $query);
    }

    /** Get number details by E.164 or ID (depending on API config). */
    public function get(string $number): array
    {
        return $this->sdk->request('GET', '/v2/numbers/' . rawurlencode($number));
    }

    /** Update number properties (assignment varies by deployment; pass appropriate payload). */
    public function update(string $number, array $data): array
    {
        return $this->sdk->request('POST', '/v2/numbers/' . rawurlencode($number), [], ['data' => $data]);
    }

    /** Delete/releases a number (depends on permissions/policies). */
    public function delete(string $number): array
    {
        return $this->sdk->request('DELETE', '/v2/numbers/' . rawurlencode($number));
    }

/**
 * Assign a number to a target (helper around update).
 * Provide a payload that matches your deployment, e.g.:
 *   ['callflow_id' => '...'] or ['device_id' => '...'] or ['user_id' => '...']
 */
public function assign(string $number, array $assignment): array
{
    return $this->update($number, $assignment);
}

/** Unassign/release a number (helper). */
public function unassign(string $number): array
{
    return $this->update($number, ['assigned' => false]);
}
}


    /** Batch add phone numbers (account-scoped collection) */
    public function addMany(array $numbers): array
    {
        // Prefer account-scoped path if available
        $path = '/v2/phone_numbers/collection';
        if (method_exists($this->sdk, 'accountId') && $this->sdk->accountId()) {
            $path = '/v2/accounts/' . rawurlencode($this->sdk->accountId()) . '/phone_numbers/collection';
        }
        return $this->sdk->request('PUT', $path, [], ['data' => ['numbers' => array_values($numbers)]]);
    }

    /** Batch update phone numbers */
    public function updateMany(array $updates): array
    {
        $path = '/v2/phone_numbers/collection';
        if (method_exists($this->sdk, 'accountId') && $this->sdk->accountId()) {
            $path = '/v2/accounts/' . rawurlencode($this->sdk->accountId()) . '/phone_numbers/collection';
        }
        return $this->sdk->request('POST', $path, [], ['data' => $updates]);
    }

    /** Batch delete phone numbers */
    public function deleteMany(array $numbers, bool $hard = false): array
    {
        $qs = $hard ? '?hard=true' : '';
        $path = '/v2/phone_numbers/collection' . $qs;
        if (method_exists($this->sdk, 'accountId') && $this->sdk->accountId()) {
            $path = '/v2/accounts/' . rawurlencode($this->sdk->accountId()) . '/phone_numbers/collection' . $qs;
        }
        // Some deployments accept body for DELETE; others expect query/body. We'll send body.
        return $this->sdk->request('DELETE', $path, [], ['data' => ['numbers' => array_values($numbers)]]);
    }

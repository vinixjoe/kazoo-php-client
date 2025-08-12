<?php
declare(strict_types=1);

namespace Kazoo\Util;

/**
 * Tiny paginator wrapper for iterating items across API pages.
 *
 * Usage:
 *   $p = new Paginator(fn(array $q) => $sdk->request('GET', '/v2/devices?' . http_build_query($q)));
 *   $p->each(['paginate' => 'true'], function(array $item) { ... });
 *   foreach ($p->chunk(['paginate' => 'true'], 100) as $batch) { ... }
 */
final class Paginator
{
    /** @var callable */
    private $fetch;

    /**
     * @param callable(array<string,mixed>):array<string,mixed> $fetch
     *        A function that accepts query params and returns a decoded response
     *        with 'data' and (optionally) 'next_start_key'.
     */
    public function __construct(callable $fetch)
    {
        $this->fetch = $fetch;
    }

    /**
     * Iterate items one-by-one across all pages.
     *
     * @param array<string,mixed> $query
     * @return iterable<array<string,mixed>>
     */
    public function iterate(array $query = []): iterable
    {
        $startKey = null;
        while (true) {
            $q = $query;
            $q['paginate'] = $q['paginate'] ?? 'true';
            if ($startKey !== null) {
                $q['start_key'] = $startKey;
            }
            /** @var array<string,mixed> $resp */
            $resp = ($this->fetch)($q);
            $items = $resp['data'] ?? [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        yield $item;
                    }
                }
            }
            $next = $resp['next_start_key'] ?? null;
            if (!is_string($next) || $next === '') {
                break;
            }
            $startKey = $next;
        }
    }

    /**
     * Call a callback for each item across all pages.
     * @param array<string,mixed> $query
     * @param callable(array<string,mixed>):void $callback
     */
    public function each(array $query, callable $callback): void
    {
        foreach ($this->iterate($query) as $item) {
            $callback($item);
        }
    }

    /**
     * Yield arrays (batches) of items of size $size across all pages.
     * @param array<string,mixed> $query
     * @param int $size
     * @return iterable<array<int,array<string,mixed>>>
     */
    public function chunk(array $query, int $size = 100): iterable
    {
        $batch = [];
        foreach ($this->iterate($query) as $item) {
            $batch[] = $item;
            if (count($batch) >= $size) {
                yield $batch;
                $batch = [];
            }
        }
        if ($batch) {
            yield $batch;
        }
    }
}

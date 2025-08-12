<?php
declare(strict_types=1);

namespace Kazoo\Auth;

use Kazoo\SDK;

/**
 * Returns additional headers to add (e.g., Authorization)
 * or an empty array if none.
 *
 * @param array<string,mixed>|null $json
 * @return array<string,string>
 */
interface AuthInterface
{
    public function authenticate(
        SDK $sdk,
        string $method,
        string $path,
        array $headers,
        ?array $json
    ): array;
}

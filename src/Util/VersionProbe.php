<?php
declare(strict_types=1);

namespace Kazoo\Util;

use Kazoo\SDK;

/**
 * VersionProbe: optionally used to print/log capability detection.
 */
final class VersionProbe
{
    public static function summarize(SDK $sdk): array
    {
        return $sdk->probeVersion();
    }
}

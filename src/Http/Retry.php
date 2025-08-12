<?php
declare(strict_types=1);

namespace Kazoo\Http;

final class Retry
{
    private int $maxAttempts;
    private float $baseDelay;

    public function __construct(int $maxAttempts = 4, float $baseDelay = 0.5)
    {
        $this->maxAttempts = $maxAttempts;
        $this->baseDelay = $baseDelay;
    }

    public function shouldRetry(int $attempt, int $status): bool
    {
        return $attempt < $this->maxAttempts;
    }

    public function sleep(int $attempt, ?int $retryAfterSeconds = null): void
    {
        $delay = $retryAfterSeconds !== null
            ? max(0, $retryAfterSeconds)
            : $this->baseDelay * (2 ** ($attempt - 1));

        usleep((int)($delay * 1000000));
    }
}

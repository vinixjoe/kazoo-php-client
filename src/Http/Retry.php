<?php
declare(strict_types=1);

namespace Kazoo\Http;

final class Retry
{
    private int $maxAttempts;
    private float $baseDelay;
    private float $jitter; // +/- fraction, e.g. 0.1 = +/-10%

    public function __construct(int $maxAttempts = 4, float $baseDelay = 0.5, float $jitter = 0.1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->baseDelay = $baseDelay;
        $this->jitter = $jitter;
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

        // apply jitter +/-
        $j = $this->jitter;
        if ($j > 0) {
            $delta = $delay * $j;
            $delay = $delay + ((mt_rand(-1000, 1000) / 1000.0) * $delta);
            if ($delay < 0) $delay = 0;
        }

        usleep((int)($delay * 1000000));
    }
}


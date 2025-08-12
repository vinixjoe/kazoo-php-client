<?php
declare(strict_types=1);

namespace Kazoo\Config;

final class Options
{
    /** @var 'auto'|'v5_4'|'legacy' */
    private string $apiVersionMode;
    private ?string $accountId;

    public function __construct(string $apiVersionMode = 'auto', ?string $accountId = null)
    {
        $this->apiVersionMode = $apiVersionMode;
        $this->accountId = $accountId;
    }

    /** @return 'auto'|'v5_4'|'legacy' */
    public function apiVersionMode(): string { return $this->apiVersionMode; }
    public function accountId(): ?string { return $this->accountId; }

    public function withAccountId(string $accountId): self
    {
        $clone = clone $this;
        $clone->accountId = $accountId;
        return $clone;
    }

    public function withApiVersionMode(string $mode): self
    {
        $clone = clone $this;
        $clone->apiVersionMode = $mode;
        return $clone;
    }
}

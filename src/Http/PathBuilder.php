<?php
declare(strict_types=1);

namespace Kazoo\Http;

use Kazoo\Config\Options;

final class PathBuilder
{
    public function __construct(private Options $options) {}

    public function setOptions(Options $options): void { $this->options = $options; }

    public function base(): string { return '/v2'; }

    public function forUsers(): string
    {
        if ($this->useAccountScoped()) {
            return $this->accountPrefix() . '/users';
        }
        return $this->base() . '/users'; // legacy
    }

    public function forDevices(): string
    {
        if ($this->useAccountScoped()) {
            return $this->accountPrefix() . '/devices';
        }
        return $this->base() . '/devices'; // legacy
    }

    public function forCallflows(): string
    {
        if ($this->useAccountScoped()) {
            return $this->accountPrefix() . '/callflows';
        }
        return $this->base() . '/callflows'; // legacy
    }

    public function forNumbers(): string
    {
        if ($this->useAccountScoped()) {
            return $this->accountPrefix() . '/phone_numbers';
        }
        return $this->base() . '/numbers'; // legacy
    }

    public function forChannels(): string
    {
        if ($this->useAccountScoped()) {
            return $this->accountPrefix() . '/channels';
        }
        return $this->base() . '/channels'; // legacy
    }

    private function useAccountScoped(): bool
    {
        return $this->options->apiVersionMode() !== 'legacy' && $this->options->accountId() !== null;
    }

    private function accountPrefix(): string
    {
        $acc = $this->options->accountId();
        return $this->base() . '/accounts/' . rawurlencode((string)$acc);
    }
}

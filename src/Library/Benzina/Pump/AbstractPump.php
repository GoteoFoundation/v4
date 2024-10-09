<?php

namespace App\Library\Benzina\Pump;

abstract class AbstractPump implements PumpInterface
{
    protected array $config = [];

    public function setConfig(array $config = []): void
    {
        $this->config = $config;
    }

    public function getConfig(?string $key = null): array
    {
        if ($key !== null) {
            return [$key => $this->config[$key]];
        }

        return $this->config;
    }
}

<?php

namespace App\Library\Benzina\Pump;

abstract class AbstractPump implements PumpInterface
{
    protected array $config = [];

    public function setConfig(array $config = []): void
    {
        $this->config = $config;
    }

    public function getConfig(?string $key = null, mixed $default = null): array
    {
        if ($key !== null) {
            return [
                $key => \array_key_exists($key, $this->config)
                    ? $this->config[$key]
                    : $default,
            ];
        }

        return $this->config;
    }
}

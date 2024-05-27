<?php

namespace App\Library\Benzina\Pump;

abstract class AbstractPump implements PumpInterface
{
    protected ?array $config;

    public function configure(?array $config = null): void
    {
        $this->config = $config;
    }
}

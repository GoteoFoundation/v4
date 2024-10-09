<?php

namespace App\Library\Benzina\Pump;

interface PumpInterface
{
    /**
     * Sets flexible configuration values for this pump.
     *
     * @param array $config The configuration array
     */
    public function setConfig(array $config = []): void;

    /**
     * Read the configuration values for this pump.
     *
     * @param string|null $key A configuration array key to return
     *
     * @return array The configuration array at the specified key, or all keys if null
     */
    public function getConfig(?string $key = null): array;

    /**
     * Determines if a data batch is supported by this pump.
     *
     * @param mixed $batch A sample of the streamed records, e.g. rows from an user table
     */
    public function supports(mixed $batch): bool;

    /**
     * Pump a data batch into a final destination.
     *
     * @param mixed $batch The streamed records, e.g. rows from an user table
     */
    public function pump(mixed $batch): void;
}

<?php

namespace App\Library\Benzina\Pump;

interface PumpInterface
{
    /**
     * Sets flexible configuration values for this pump.
     *
     * @param array $config the configuration array
     */
    public function configure(array $config = []): void;

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

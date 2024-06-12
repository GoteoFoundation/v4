<?php

namespace App\Library\Benzina\Pump;

interface PumpInterface
{
    /**
     * Determines if the data is supported by this pump.
     *
     * @param mixed $data The streamed records, e.g. rows from an user table
     * @return bool
     */
    public function supports(mixed $data): bool;

    /**
     * Sets flexible configuration values for this pump.
     * 
     * @param array $config The configuration array.
     * @return void
     */
    public function configure(array $config = []): void;

    /**
     * Process the data to be pumped.
     * 
     * @param mixed $data The streamed records, e.g. rows from an user table
     * @return void
     */
    public function process(mixed $data): void;
}

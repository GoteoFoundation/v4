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
     * Process the data to be pumped.
     * 
     * @param mixed $data The streamed records, e.g. rows from an user table
     * @return bool
     */
    public function process(mixed $data): void;
}

<?php

namespace App\Library\Benzina\Pump\Trait;

trait ArrayPumpTrait
{
    /**
     * Determine if the data array has all the necessary keys.
     *
     * @param array $data A sample of the data
     * @param array $keys The keys that the data should have
     */
    public function hasAllKeys(array $data, array $keys): bool
    {
        if (0 === count(\array_diff($keys, \array_keys($data)))) {
            return true;
        }

        return false;
    }
}

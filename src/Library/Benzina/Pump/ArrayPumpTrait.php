<?php

namespace App\Library\Benzina\Pump;

trait ArrayPumpTrait
{
    /**
     * Determine if the data array has all the necessary keys
     * @param array $data A sample of the data
     * @param array $keys The keys that the data should have
     * @return bool
     */
    public function hasAllKeys(array $data, array $keys): bool
    {
        if (count(\array_diff($keys, \array_keys($data))) === 0) {
            return true;
        }

        return false;
    }
}

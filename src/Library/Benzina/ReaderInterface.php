<?php

namespace App\Library\Benzina;

interface ReaderInterface
{
    public function get(string $entityName): StreamInterface;
}

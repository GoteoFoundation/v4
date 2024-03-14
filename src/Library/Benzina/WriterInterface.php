<?php

namespace App\Library\Benzina;

interface WriterInterface
{
    public function process(ReaderInterface $reader);
}

<?php

namespace App\Mapping;

use AutoMapper\AutoMapper as InnerMapper;
use AutoMapper\AutoMapperInterface;

class AutoMapper implements AutoMapperInterface
{
    private AutoMapperInterface $innerMapper;

    public function __construct(
        iterable $mapProviders,
    ) {
        $this->innerMapper = InnerMapper::create(
            providers: $mapProviders
        );
    }

    public function map(array|object $source, string|array|object $target, array $context = []): array|object|null
    {
        return $this->innerMapper->map($source, $target, $context);
    }
}

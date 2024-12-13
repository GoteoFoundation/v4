<?php

namespace App\Mapping;

use AutoMapper\AutoMapper as InnerMapper;
use AutoMapper\AutoMapperInterface;

class AutoMapper implements AutoMapperInterface
{
    public const SKIP_NULL_VALUES = 'skip_null_values';

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
        $context = [
            self::SKIP_NULL_VALUES => true,
            ...$context
        ];

        return $this->innerMapper->map($source, $target, $context);
    }
}

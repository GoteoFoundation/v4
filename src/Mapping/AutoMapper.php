<?php

namespace App\Mapping;

use AutoMapper\AutoMapper as InnerMapper;
use AutoMapper\AutoMapperInterface;

class AutoMapper implements AutoMapperInterface
{
    public const CACHE_DIR = 'automapper';

    private AutoMapperInterface $innerMapper;

    public function __construct(
        ?string $cacheDirectory = null,
        iterable $mapProviders = [],
    ) {
        $this->innerMapper = InnerMapper::create(
            cacheDirectory: \sprintf('%s%s%s', $cacheDirectory, \DIRECTORY_SEPARATOR, self::CACHE_DIR),
            providers: $mapProviders,
        );
    }

    public function map(array|object $source, string|array|object $target, array $context = []): array|object|null
    {
        return $this->innerMapper->map($source, $target, $context);
    }
}

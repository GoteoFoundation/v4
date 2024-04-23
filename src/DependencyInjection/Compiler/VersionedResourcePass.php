<?php

namespace App\DependencyInjection\Compiler;

use App\Service\ApiResourceNormalizer;
use App\Service\VersionedResourceService;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VersionedResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $allClasses = \get_declared_classes();
        $entityClasses = \array_filter($allClasses, function (string $className) {
            return \str_starts_with($className, 'App\\Entity\\');
        });

        $versionedResourceNames = [];
        foreach ($entityClasses as $entityClass) {
            $reflectionClass = new \ReflectionClass($entityClass);
            $attributes = $reflectionClass->getAttributes(Gedmo\Loggable::class);

            if (empty($attributes)) {
                continue;
            }

            $versionedResourceNames = [
                ...$versionedResourceNames,
                ApiResourceNormalizer::toResource($entityClass)
            ];
        }

        /** @var VersionedResourceService */
        $versionedResourceService = $container->get(VersionedResourceService::class);
        $versionedResourceService->compileNames($versionedResourceNames);
    }
}

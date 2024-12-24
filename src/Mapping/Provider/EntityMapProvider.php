<?php

namespace App\Mapping\Provider;

use AutoMapper\Provider\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

class EntityMapProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function provide(string $targetType, mixed $source, array $context): object|array|null
    {
        if (!isset($source->id)) {
            return null;
        }

        $repository = $this->entityManager->getRepository($targetType);

        if (!$repository) {
            throw new \Exception(\sprintf("No repository found for '%s' class. Is it an Entity?", $targetType));
        }

        return $repository->find($source->id);
    }
}

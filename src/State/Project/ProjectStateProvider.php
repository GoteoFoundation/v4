<?php

namespace App\State\Project;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Project as Resource;
use AutoMapper\AutoMapperInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProjectStateProvider implements ProviderInterface
{
    public function __construct(
        private AutoMapperInterface $autoMapper,
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $collection = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $resources = [];
            foreach ($collection as $entity) {
                $project = $this->autoMapper->map($entity, Resource\Project::class);

                var_dump($project);
                exit;
            }

            return new TraversablePaginator(
                new ArrayCollection($resources),
                $collection->getCurrentPage(),
                $collection->getItemsPerPage(),
                $collection->getTotalItems()
            );
        }
    }
}

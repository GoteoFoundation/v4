<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiResourceStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private AutoMapper $autoMapper,
        private LocalizedContentProvider $localizedContentProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($this->localizedContentProvider->supports($operation)) {
            return $this->localizedContentProvider->provide($operation, $uriVariables, $context);
        }

        $resourceClass = $operation->getClass();

        if ($operation instanceof CollectionOperationInterface) {
            $collection = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $resources = [];
            foreach ($collection as $item) {
                $resources[] = $this->autoMapper->map($item, $resourceClass);
            }

            return new TraversablePaginator(
                new \ArrayIterator($resources),
                $collection->getCurrentPage(),
                $collection->getItemsPerPage(),
                $collection->getTotalItems()
            );
        }

        $item = $this->itemProvider->provide($operation, $uriVariables, $context);

        if (!$item) {
            return null;
        }

        return $this->autoMapper->map($item, $resourceClass);
    }
}

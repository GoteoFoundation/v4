<?php

namespace App\State\Accounting;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Accounting as ApiResource;
use App\Entity\Accounting as Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccountingStateProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $accountings = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $resources = [];
            foreach ($accountings as $accounting) {
                $resources[] = $this->toResource($accounting);
            }

            return new TraversablePaginator(
                new \ArrayIterator($resources),
                $accountings->getCurrentPage(),
                $accountings->getItemsPerPage(),
                $accountings->getTotalItems()
            );
        }

        $accounting = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $this->toResource($accounting);
    }

    private function toResource(?Entity\Accounting $accounting): ?ApiResource\Accounting
    {
        if ($accounting === null) {
            return null;
        }

        $owner = $this->entityManager->find(
            $accounting->getOwnerClass(),
            $accounting->getOwnerId()
        );

        $resource = new ApiResource\Accounting();
        $resource->id = $accounting->getId();
        $resource->currency = $accounting->getCurrency();
        $resource->owner = $owner;

        return $resource;
    }
}

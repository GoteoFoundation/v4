<?php

namespace App\State\Accounting;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AccountingApiResource;
use App\Entity\Accounting\Accounting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccountingStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private EntityManagerInterface $entityManager,
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

    private function toResource(?Accounting $accounting): ?AccountingApiResource
    {
        if ($accounting === null) {
            return null;
        }

        $owner = $this->entityManager->find($accounting->getOwnerClass(), $accounting->getOwnerId());

        return new AccountingApiResource($accounting, $owner);
    }
}

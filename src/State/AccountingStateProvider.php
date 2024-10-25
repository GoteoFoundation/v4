<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AccountingApiResource;
use App\Entity\Accounting;
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

            foreach ($accountings as $key => $accounting) {
                $accountings[$key] = $this->toResource($accounting);
            }

            return $accountings;
        }

        $accounting = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $this->toResource($accounting);
    }

    private function toResource(Accounting $accounting): AccountingApiResource
    {
        $owner = $this->entityManager->find($accounting->getOwnerClass(), $accounting->getOwnerId());

        return new AccountingApiResource($accounting, $owner);
    }
}

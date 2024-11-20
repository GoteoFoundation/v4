<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Mapping\Gateway\CheckoutMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CheckoutStateProvider implements ProviderInterface
{
    public function __construct(
        private CheckoutMapper $checkoutMapper,
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $resources = [];
            foreach ($entities as $entity) {
                $resources[] = $this->checkoutMapper->toResource($entity);
            }

            return $resources;
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($entity === null) {
            return null;
        }

        return $this->checkoutMapper->toResource($entity);
    }
}

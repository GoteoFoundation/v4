<?php

namespace App\State\Accounting;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Accounting\AccountingApiResource;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Accounting\Accounting;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Mapping\AutoMapper;
use App\Service\AccountingService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccountingStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        private AutoMapper $autoMapper,
        private AccountingService $accountingService,
        private IriConverterInterface $iriConverter,
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

        if ($accounting === null) {
            return null;
        }

        return $this->toResource($accounting);
    }

    private function toResource(Accounting $accounting): AccountingApiResource
    {
        /** @var AccountingApiResource */
        $resource = $this->autoMapper->map($accounting, AccountingApiResource::class);
        $resource->balance = $this->accountingService->calcBalance($accounting);

        $owner = $accounting->getOwner();

        $resource->owner = $owner;

        switch ($owner::class) {
            case User::class:
                $resourceClass = UserApiResource::class;
                break;
            case Project::class:
                $resourceClass = ProjectApiResource::class;
                break;
        }

        $resource->owner = $this->autoMapper->map($owner, $resourceClass);

        return $resource;
    }
}

<?php

namespace App\Mapping\Accounting;

use App\ApiResource\Accounting as Resource;
use App\Entity\Accounting as Entity;
use App\Entity\Money;
use App\Repository\Accounting\AccountingRepository;
use App\Service\AccountingService;
use Doctrine\ORM\EntityManagerInterface;

class AccountingMapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AccountingRepository $accountingRepository,
        private AccountingService $accountingService,
    ) {}

    public function toResource(Entity\Accounting $entity): Resource\Accounting
    {
        $owner = $entity->getOwner();

        $resource = new Resource\Accounting();
        $resource->id = $entity->getId();
        $resource->currency = $entity->getCurrency();
        $resource->owner = $owner;
        $resource->balance = $this->getBalance($entity);

        return $resource;
    }

    public function toEntity(Resource\Accounting $resource): Entity\Accounting
    {
        $entity = new Entity\Accounting();

        if ($resource->id !== null) {
            $entity = $this->accountingRepository->find($resource->id);
        }

        $entity->setCurrency($resource->currency);
        $entity->setOwner($resource->owner);

        return $entity;
    }

    private function getBalance(Entity\Accounting $accounting): Money
    {
        return $this->accountingService->calcBalance($accounting);
    }
}

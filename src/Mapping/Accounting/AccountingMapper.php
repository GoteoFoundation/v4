<?php

namespace App\Mapping\Accounting;

use App\ApiResource\Accounting as Resource;
use App\Entity\Accounting as Entity;
use App\Repository\Accounting\AccountingRepository;
use Doctrine\ORM\EntityManagerInterface;

class AccountingMapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AccountingRepository $accountingRepository,
    ) {}

    public function toResource(Entity\Accounting $entity): Resource\Accounting
    {
        $owner = $this->entityManager->find(
            $entity->getOwnerClass(),
            $entity->getOwnerId()
        );

        $resource = new Resource\Accounting();
        $resource->id = $entity->getId();
        $resource->currency = $entity->getCurrency();
        $resource->owner = $owner;

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
}

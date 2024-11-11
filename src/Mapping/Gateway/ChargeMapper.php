<?php

namespace App\Mapping\Gateway;

use App\ApiResource\Gateway as Resource;
use App\Entity\Gateway as Entity;
use App\Mapping\Accounting\AccountingMapper;
use App\Repository\Gateway\ChargeRepository;

class ChargeMapper
{
    public function __construct(
        private AccountingMapper $accountingMapper,
        private ChargeRepository $chargeRepository,
    ) {}

    public function toResource(Entity\Charge $entity): Resource\Charge
    {
        $resource = new Resource\Charge();
        $resource->id = $entity->getId();
        $resource->type = $entity->getType();
        $resource->title = $entity->getTitle();
        $resource->description = $entity->getDescription();
        $resource->money = $entity->getMoney();
        $resource->target = $this->accountingMapper->toResource($entity->getTarget());

        return $resource;
    }

    public function toEntity(Resource\Charge $resource): Entity\Charge
    {
        $entity = new Entity\Charge();

        if ($resource->id !== null) {
            $entity = $this->chargeRepository->find($resource->id);
        }

        $entity->setType($resource->type);
        $entity->setTitle($resource->title);
        $entity->setDescription($resource->description);
        $entity->setMoney($resource->money);
        $entity->setTarget($this->accountingMapper->toEntity($resource->target));

        return $entity;
    }
}

<?php

namespace App\Mapping\Gateway;

use App\ApiResource\Gateway as Resource;
use App\Entity\Gateway as Entity;
use App\Mapping\Accounting\AccountingMapper;

class ChargeMapper
{
    public function __construct(
        private AccountingMapper $accountingMapper,
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
}

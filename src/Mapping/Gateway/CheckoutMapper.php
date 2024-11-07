<?php

namespace App\Mapping\Gateway;

use App\ApiResource\Gateway as Resource;
use App\Entity\Gateway as Entity;
use App\Gateway\GatewayLocator;
use App\Mapping\Accounting\AccountingMapper;
use App\Repository\Gateway\CheckoutRepository;
use Doctrine\Common\Collections\ArrayCollection;

class CheckoutMapper
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private GatewayMapper $gatewayMapper,
        private CheckoutRepository $checkoutRepository,
        private AccountingMapper $accountingMapper,
        private ChargeMapper $chargeMapper,
    ) {}

    public function toResource(Entity\Checkout $entity): Resource\Checkout
    {
        $resource = new Resource\Checkout();
        $resource->id = $entity->getId();

        $gateway = $this->gatewayLocator->getGateway($entity->getGateway());
        $resource->gateway = $this->gatewayMapper->toResource($gateway);

        $resource->origin = $this->accountingMapper->toResource($entity->getOrigin());

        $charges = [];
        foreach ($entity->getCharges() as $charge) {
            $charges[] = $this->chargeMapper->toResource($charge);
        }

        $resource->charges = $charges;

        $resource->status = $entity->getStatus();
        $resource->links = $entity->getLinks();
        $resource->trackings = $entity->getTrackings();

        return $resource;
    }

    public function toEntity(Resource\Checkout $resource): Entity\Checkout
    {
        $entity = new Entity\Checkout();

        if ($resource->id !== null) {
            $entity = $this->checkoutRepository->find($resource->id);
        }

        $entity->setGateway($resource->gateway->name);
        $entity->setOrigin($this->accountingMapper->toEntity($resource->origin));

        $charges = [];
        foreach ($resource->charges as $charge) {
            $charges[] = $this->chargeMapper->toEntity($charge);
        }

        $entity->setCharges(new ArrayCollection($charges));

        $entity->setStatus($resource->status);
        $entity->setLinks($resource->links);

        return $entity;
    }
}

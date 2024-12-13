<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Gateway\GatewayApiResource;
use App\Gateway\GatewayInterface;
use App\Gateway\GatewayLocator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayStateProvider implements ProviderInterface
{
    public function __construct(
        private GatewayLocator $gateways,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        switch ($operation::class) {
            case API\GetCollection::class:
                return $this->getGateways();
            case API\Get::class:
                return $this->getGateway($uriVariables['name']);
        }
    }

    private function getGateways(): array
    {
        $gateways = [];
        foreach ($this->gateways->getAll() as $gateway) {
            $gateways[] = $this->toResource($gateway);
        }

        return $gateways;
    }

    private function getGateway(string $name): GatewayApiResource
    {
        try {
            $gateway = $this->gateways->get($name);

            return $this->toResource($gateway);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Not Found');
        }
    }

    private function toResource(GatewayInterface $gateway): GatewayApiResource
    {
        $resource = new GatewayApiResource();
        $resource->name = $gateway::getName();
        $resource->supports = $gateway::getSupportedChargeTypes();

        return $resource;
    }
}

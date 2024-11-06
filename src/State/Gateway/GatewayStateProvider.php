<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Gateway\Gateway;
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

    private function toResource(GatewayInterface $gateway): Gateway
    {
        $resource = new Gateway();
        $resource->name = $gateway::getName();
        $resource->supports = $gateway::getSupportedChargeTypes();

        return $resource;
    }

    private function getGateways(): array
    {
        $gateways = [];
        foreach ($this->gateways->getGateways() as $gateway) {
            $gateways[] = $this->toResource($gateway);
        }

        return $gateways;
    }

    private function getGateway(string $name): Gateway
    {
        try {
            $gateway = $this->gateways->getGateway($name);

            return $this->toResource($gateway);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Not Found');
        }
    }
}

<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Gateway\Gateway;
use App\Gateway\GatewayLocator;
use App\Mapping\Gateway\GatewayMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayStateProvider implements ProviderInterface
{
    public function __construct(
        private GatewayLocator $gateways,
        private GatewayMapper $gatewayMapper,
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
            $gateways[] = $this->gatewayMapper->toResource($gateway);
        }

        return $gateways;
    }

    private function getGateway(string $name): Gateway
    {
        try {
            $gateway = $this->gateways->get($name);

            return $this->gatewayMapper->toResource($gateway);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Not Found');
        }
    }
}

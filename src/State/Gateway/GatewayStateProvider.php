<?php

namespace App\State\Gateway;

use ApiPlatform\Metadata as API;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Gateway\Gateway;
use App\Gateway\GatewayLocator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayStateProvider implements ProviderInterface
{
    public function __construct(
        private GatewayLocator $gateways,
    ) {}

    private function getGateways(): array
    {
        $gateways = [];
        foreach ($this->gateways->getGateways() as $gateway) {
            $gateways[] = new Gateway($gateway);
        }

        return $gateways;
    }

    private function getGateway(string $name): Gateway
    {
        try {
            $gateway = $this->gateways->getGateway($name);

            return new Gateway($gateway);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Not Found');
        }
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        switch ($operation::class) {
            case API\GetCollection::class:
                return $this->getGateways();
            case API\Get::class:
                return $this->getGateway($uriVariables['name']);
        }
    }
}

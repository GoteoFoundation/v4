<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\Library\Economy\Payment\GatewayInterface;
use App\State\GatewayStateProvider;

/**
 * Gateways represent external payment services
 */
#[API\GetCollection(provider: GatewayStateProvider::class)]
#[API\Get(provider: GatewayStateProvider::class)]
class Gateway
{
    public function __construct(private readonly GatewayInterface $gateway) {
    }

    #[API\ApiProperty(identifier: true)]
    public function getName(): string
    {
        return $this->gateway->getName();
    }
}

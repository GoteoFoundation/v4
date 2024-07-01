<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\Library\Economy\Payment\GatewayInterface;
use App\State\GatewayStateProvider;

/**
 * Gateways represent payment services used to perform the necessary payments for Transactions between Accounts.\
 * \
 * For each Gateway there is an internal implementation that handles the creation and validation of Transactions between Accounts.
 * These implementations make use of external or internal services to gather the funds that are inside a Transaction,
 * perform corroboration of funds and store the Transactions into the system.
 */
#[API\GetCollection(provider: GatewayStateProvider::class)]
#[API\Get(provider: GatewayStateProvider::class)]
class Gateway
{
    public function __construct(private readonly GatewayInterface $gateway)
    {
    }

    #[API\ApiProperty(identifier: true)]
    public function getName(): string
    {
        return $this->gateway->getName();
    }
}

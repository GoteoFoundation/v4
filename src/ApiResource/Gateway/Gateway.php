<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Metadata as API;
use App\State\Gateway\GatewayStateProvider;

/**
 * Gateways represent payment services used to perform the necessary payments for Transactions between Accounts.\
 * \
 * For each Gateway there is an internal implementation that handles the creation and validation of Transactions between Accounts.
 * These implementations make use of external or internal services to gather the funds that are inside a Transaction,
 * perform corroboration of funds and store the Transactions into the system.
 */
#[API\ApiResource()]
#[API\GetCollection(provider: GatewayStateProvider::class)]
#[API\Get(provider: GatewayStateProvider::class)]
class Gateway
{
    #[API\ApiProperty(identifier: true)]
    public ?string $name = null;

    /**
     * @var array<int, \App\Gateway\ChargeType>
     */
    public ?array $supports = null;
}

<?php

namespace App\Mapping\Gateway;

use App\ApiResource\Gateway\Gateway;
use App\Gateway\GatewayInterface;

class GatewayMapper
{
    public function toResource(GatewayInterface $gateway): Gateway
    {
        $resource = new Gateway();
        $resource->name = $gateway::getName();
        $resource->supports = $gateway::getSupportedChargeTypes();

        return $resource;
    }
}

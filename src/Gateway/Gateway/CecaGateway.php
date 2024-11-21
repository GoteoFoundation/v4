<?php

namespace App\Gateway\Gateway;

use App\Gateway\GatewayInterface;

abstract class CecaGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'tpv';
    }
}

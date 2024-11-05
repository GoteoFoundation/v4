<?php

namespace App\Gateway\Gateway;

use App\Gateway\GatewayInterface;

abstract class CashGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'cash';
    }
}

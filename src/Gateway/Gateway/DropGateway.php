<?php

namespace App\Gateway\Gateway;

use App\Gateway\GatewayInterface;

abstract class DropGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'drop';
    }
}

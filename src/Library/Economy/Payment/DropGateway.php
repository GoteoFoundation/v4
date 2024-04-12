<?php

namespace App\Library\Economy\Payment;

abstract class DropGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'drop';
    }
}

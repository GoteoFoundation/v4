<?php

namespace App\Library\Economy\Payment;

abstract class CashGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'cash';
    }
}

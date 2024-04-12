<?php

namespace App\Library\Economy\Payment;

abstract class CecaGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'tpv';
    }
}

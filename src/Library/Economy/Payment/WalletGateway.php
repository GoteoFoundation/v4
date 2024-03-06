<?php

namespace App\Library\Economy\Payment;

abstract class WalletGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'wallet';
    }
}

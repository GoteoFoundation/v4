<?php

namespace App\Library\Economy\Payment;

class WalletGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'wallet';
    }
}

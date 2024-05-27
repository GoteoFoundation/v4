<?php

namespace App\Library\Economy\Payment;

abstract class PaypalGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'paypal';
    }
}

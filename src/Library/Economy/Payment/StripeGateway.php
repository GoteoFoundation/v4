<?php

namespace App\Library\Economy\Payment;

abstract class StripeGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'stripe';
    }
}

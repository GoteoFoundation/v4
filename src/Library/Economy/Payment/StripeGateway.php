<?php

namespace App\Library\Economy\Payment;

class StripeGateway implements GatewayInterface
{
    public function getName(): string
    {
        return 'stripe';
    }
}

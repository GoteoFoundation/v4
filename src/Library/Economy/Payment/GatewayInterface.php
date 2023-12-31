<?php

namespace App\Library\Economy\Payment;

interface GatewayInterface
{
    /**
     * @return string A short, unique, descriptive string of your gateway
     */
    public function getName(): string;
}

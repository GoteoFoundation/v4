<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;

interface GatewayInterface
{
    /**
     * @return string A short, unique, descriptive string for this Gateway
     */
    public static function getName(): string;

    /**
     * Connects with the payment gateway and creates a checkout session to process payment.
     * 
     * Gateways are trusted to have secured the funds in the GatewayCheckout.
     * @param GatewayCheckout $checkout
     * @return GatewayCheckout
     */
    public function create(GatewayCheckout $checkout): GatewayCheckout;
}

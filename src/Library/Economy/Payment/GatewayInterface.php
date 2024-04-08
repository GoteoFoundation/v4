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
     * When processing a GatewayCheckout a Gateway must validate it using it's own means.
     * 
     * Gateways are trusted to have secured the funds in the GatewayCheckout.
     * @param GatewayCheckout $checkout
     * @return GatewayCheckout
     */
    public function process(GatewayCheckout $checkout): GatewayCheckout;
}

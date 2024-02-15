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
     * Create an actual Checkout at the Gateway and update the GatewayCheckout with the data from it
     * @param GatewayCheckout
     * @return GatewayCheckout
     */
    public function process(GatewayCheckout $gatewayCheckout): GatewayCheckout;

    /**
     * Update a Checkout after the Gateway redirects successfully
     * @param GatewayCheckout
     * @return GatewayCheckout
     */
    public function onSuccess(GatewayCheckout $gatewayCheckout): GatewayCheckout;

    /**
     * Update a Checkout after the Gateway redirects with a failure
     * @param GatewayCheckout
     * @return GatewayCheckout
     */
    public function onFailure(GatewayCheckout $gatewayCheckout): GatewayCheckout;
}

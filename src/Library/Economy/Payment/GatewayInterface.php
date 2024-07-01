<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function create(GatewayCheckout $checkout): GatewayCheckout;

    /**
     * Updates a GatewayCheckout after the payment gateway redirects the user.
     *
     * @param Request $request The HTTP Request object
     */
    public function handleRedirect(Request $request): GatewayCheckout;
}

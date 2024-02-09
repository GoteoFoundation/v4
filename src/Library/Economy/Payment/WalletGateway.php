<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;

class WalletGateway implements GatewayInterface
{
    public static function getName(): string
    {
        return 'wallet';
    }

    public function process(GatewayCheckout $gatewayCheckout): GatewayCheckout
    {
        $gatewayCheckout->setCheckoutUrl(
            sprintf(
                'http://wallet.goteo.org/%d',
                $gatewayCheckout->getId()
            )
        );

        return $gatewayCheckout;
    }

    public function onSuccess(GatewayCheckout $gatewayCheckout): GatewayCheckout
    {
        return $gatewayCheckout;
    }

    public function onFailure(GatewayCheckout $gatewayCheckout): GatewayCheckout
    {
        return $gatewayCheckout;
    }
}

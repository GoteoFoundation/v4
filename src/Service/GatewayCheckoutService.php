<?php

namespace App\Service;

use App\Controller\GatewaysController;
use App\Entity\AccountingTransaction;
use App\Entity\GatewayCharge;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use Symfony\Component\Routing\RouterInterface;

class GatewayCheckoutService
{
    public const RESPONSE_TYPE_SUCCESS = 'success';
    public const RESPONSE_TYPE_FAILURE = 'failure';

    public function __construct(
        private RouterInterface $router,
    ) {}

    /**
     * Generates a suitable value for redirection params to external gateways.
     *
     * @return string Absolute URL to the gateways redirection handler
     */
    public function generateRedirectUrl(
        GatewayCheckout $checkout,
        string $type = self::RESPONSE_TYPE_SUCCESS,
        array $parameters = [],
    ): string {
        return $this->router->generate(
            GatewaysController::REDIRECT,
            [
                'type' => $type,
                'gateway' => $checkout->getGateway(),
                'checkoutId' => $checkout->getId(),
                ...$parameters,
            ],
            RouterInterface::ABSOLUTE_URL
        );
    }

    /**
     * Generates a full tracking code for a given charge in a checkout.
     *
     * @return string A tracking code suitable for external gateway data matching
     */
    public function generateTracking(GatewayCheckout $checkout, GatewayCharge $charge): string
    {
        return \sprintf(
            '%s-%s',
            $this->generateCheckoutTracking($checkout),
            $this->generateChargeTracking($charge)
        );
    }

    /**
     * Generates a partial tracking code for any given checkout.
     *
     * @return string A tracking code suitable for external gateway data matching
     */
    public function generateCheckoutTracking(GatewayCheckout $checkout): string
    {
        return \sprintf(
            'AO%d-CO%d',
            $checkout->getOrigin()->getId(),
            $checkout->getId()
        );
    }

    /**
     * Generates a partial tracking code for any given charge.
     *
     * @return string A tracking code suitable for external gateway data matching
     */
    public function generateChargeTracking(GatewayCharge $charge): string
    {
        return \sprintf(
            'CH%d-AT%d',
            $charge->getId(),
            $charge->getTarget()->getId()
        );
    }

    /**
     * Updates a successful GatewayCheckout and generates the transactions for each charge.
     *
     * @param GatewayCheckout $checkout The GatewayCheckout to be updated
     *
     * @return GatewayCheckout The updated GatewayCheckout with updated charges and generated transactions
     */
    public function chargeCheckout(GatewayCheckout $checkout): GatewayCheckout
    {
        $checkout->setStatus(GatewayCheckoutStatus::Charged);

        foreach ($checkout->getCharges() as $charge) {
            $transaction = new AccountingTransaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($checkout->getOrigin());
            $transaction->setTarget($charge->getTarget());

            $charge->addTransaction($transaction);
        }

        return $checkout;
    }
}

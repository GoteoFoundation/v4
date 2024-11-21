<?php

namespace App\Service\Gateway;

use App\Controller\GatewaysController;
use App\Entity\Accounting\Transaction;
use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Gateway\CheckoutStatus;
use Symfony\Component\Routing\RouterInterface;

class CheckoutService
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
        Checkout $checkout,
        string $type = self::RESPONSE_TYPE_SUCCESS,
        array $parameters = [],
    ): string {
        return $this->router->generate(
            GatewaysController::REDIRECT,
            [
                'type' => $type,
                'gateway' => $checkout->getGatewayName(),
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
    public function generateTracking(Checkout $checkout, Charge $charge): string
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
    public function generateCheckoutTracking(Checkout $checkout): string
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
    public function generateChargeTracking(Charge $charge): string
    {
        return \sprintf(
            'CH%d-AT%d',
            $charge->getId(),
            $charge->getTarget()->getId()
        );
    }

    /**
     * Updates a successful Checkout and generates the transactions for each charge.
     *
     * @param Checkout $checkout The Checkout to be updated
     *
     * @return Checkout The updated Checkout with updated charges and generated transactions
     */
    public function chargeCheckout(Checkout $checkout): Checkout
    {
        $checkout->setStatus(CheckoutStatus::Charged);

        foreach ($checkout->getCharges() as $charge) {
            $transaction = new Transaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($checkout->getOrigin());
            $transaction->setTarget($charge->getTarget());

            $charge->addTransaction($transaction);
        }

        return $checkout;
    }
}

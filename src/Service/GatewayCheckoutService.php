<?php

namespace App\Service;

use App\Controller\GatewaysController;
use App\Entity\AccountingTransaction;
use App\Entity\GatewayCharge;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class GatewayCheckoutService
{
    public const RESPONSE_TYPE_SUCCESS = 'success';
    public const RESPONSE_TYPE_FAILURE = 'failure';

    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
    ) {
    }

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

    public function getGatewayReference(GatewayCheckout $checkout, GatewayCharge $charge): string
    {
        return sprintf(
            'AO%d-CO%d-CH%d-AT%d',
            $checkout->getOrigin()->getId(),
            $checkout->getId(),
            $charge->getId(),
            $charge->getTarget()->getId()
        );
    }

    public function chargeCheckout(GatewayCheckout $checkout): GatewayCheckout
    {
        $checkout->setStatus(GatewayCheckoutStatus::Charged);

        foreach ($checkout->getCharges() as $charge) {
            $transaction = new AccountingTransaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($checkout->getOrigin());
            $transaction->setTarget($charge->getTarget());

            $this->entityManager->persist($transaction);

            $charge->setTransaction($transaction);

            $this->entityManager->persist($charge);
        }

        $this->entityManager->flush();

        return $checkout;
    }
}
